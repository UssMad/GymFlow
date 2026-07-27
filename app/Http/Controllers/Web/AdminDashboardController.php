<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $members = Member::query()
            ->with(['user', 'coach.user'])
            ->withCount([
                'attendances as attended_today' => fn ($query) => $query->whereDate('date_presence', today()),
            ])
            ->latest('id')
            ->get();

        $todayAttendances = Attendance::query()
            ->with('member.user')
            ->whereDate('date_presence', today())
            ->latest('enregistre_le')
            ->get();

        return view('admin.dashboard', [
            'members' => $members,
            'todayAttendances' => $todayAttendances,
            'stats' => [
                'members' => $members->count(),
                'activeMembers' => $members->where('statut_abonnement', 'actif')->count(),
                'checkedIn' => $todayAttendances->count(),
                'coaches' => Coach::query()->count(),
            ],
        ]);
    }

    public function storeAttendance(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $attendance = Attendance::query()->firstOrCreate(
            ['membre_id' => $member->id, 'date_presence' => today()],
            ['enregistre_le' => now(), 'notes' => $data['notes'] ?? null],
        );

        return back()->with('status', $attendance->wasRecentlyCreated
            ? "{$member->user->prenom} has been checked in."
            : "{$member->user->prenom} is already checked in today.");
    }

    public function createMember(): View
    {
        return view('admin.members.create', ['coaches' => $this->coaches()]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $data = $this->validateMember($request);

        $member = DB::transaction(function () use ($data): Member {
            $user = User::query()->create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'member',
            ]);

            return Member::query()->create([
                'user_id' => $user->id,
                'coach_id' => $data['coach_id'] ?? null,
                'date_inscription' => $data['date_inscription'] ?? today(),
                'statut_abonnement' => $data['statut_abonnement'],
            ]);
        });

        return redirect()->route('admin.members.edit', $member)
            ->with('status', 'Member account created. You can now assign a subscription.');
    }

    public function editMember(Member $member): View
    {
        return view('admin.members.edit', [
            'member' => $member->load(['user', 'coach.user']),
            'coaches' => $this->coaches(),
        ]);
    }

    public function updateMember(Request $request, Member $member): RedirectResponse
    {
        $data = $this->validateMember($request, $member);

        DB::transaction(function () use ($member, $data): void {
            $userData = collect($data)->only(['nom', 'prenom', 'email'])->all();
            if (filled($data['password'] ?? null)) {
                $userData['password'] = Hash::make($data['password']);
            }
            $member->user->update($userData);
            $member->update([
                'coach_id' => $data['coach_id'] ?? null,
                'date_inscription' => $data['date_inscription'],
                'statut_abonnement' => $data['statut_abonnement'],
            ]);
        });

        return back()->with('status', 'Member details saved.');
    }

    private function coaches()
    {
        return Coach::query()->with('user')->orderBy('id')->get();
    }

    private function validateMember(Request $request, ?Member $member = null): array
    {
        $password = $member
            ? ['nullable', 'confirmed', 'min:12']
            : ['required', 'confirmed', 'min:12'];

        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($member?->user_id)],
            'password' => $password,
            'coach_id' => ['nullable', 'integer', Rule::exists('coaches', 'id')],
            'date_inscription' => ['required', 'date'],
            'statut_abonnement' => ['required', 'in:actif,expire,suspendu'],
        ]);
    }
}
