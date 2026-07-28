<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Member;
use App\Models\MemberSubscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    private const COACH_SPECIALITIES = [
        'Strength training',
        'Muscle building',
        'Weight loss and conditioning',
        'Functional training',
        'Cardio and endurance',
        'Mobility and flexibility',
        'General fitness',
    ];

    private const COACH_AVAILABILITIES = [
        'Monday to Friday',
        'Monday, Wednesday, Friday',
        'Tuesday, Thursday, Saturday',
        'Weekday mornings',
        'Weekday evenings',
        'Saturday and Sunday',
        'Flexible schedule',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'membre_id' => ['nullable', 'integer', Rule::exists('members', 'id')],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);
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
        $attendanceHistory = Attendance::query()
            ->with('member.user')
            ->when($filters['membre_id'] ?? null, fn ($query, $memberId) => $query->where('membre_id', $memberId))
            ->when($filters['date_debut'] ?? null, fn ($query, $date) => $query->whereDate('date_presence', '>=', $date))
            ->when($filters['date_fin'] ?? null, fn ($query, $date) => $query->whereDate('date_presence', '<=', $date))
            ->latest('date_presence')
            ->latest('id')
            ->get();
        $coaches = $this->coaches();

        return view('admin.dashboard', [
            'members' => $members,
            'coaches' => $coaches,
            'todayAttendances' => $todayAttendances,
            'attendanceHistory' => $attendanceHistory,
            'filters' => $filters,
            'stats' => [
                'members' => $members->count(),
                'activeMembers' => $members->where('statut_abonnement', 'actif')->count(),
                'checkedIn' => $todayAttendances->count(),
                'coaches' => $coaches->count(),
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
            'member' => $member->load(['user', 'coach.user', 'subscriptions.subscriptionPlan']),
            'coaches' => $this->coaches(),
            'subscriptionPlans' => SubscriptionPlan::query()->where('actif', true)->orderBy('duree_jours')->get(),
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

    public function createCoach(): View
    {
        return view('admin.coaches.create', $this->coachFormOptions());
    }

    public function storeCoach(Request $request): RedirectResponse
    {
        $data = $this->validateCoach($request);

        $coach = DB::transaction(function () use ($data): Coach {
            $user = User::query()->create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'coach',
            ]);

            return Coach::query()->create([
                'user_id' => $user->id,
                'specialite' => $data['specialite'] ?: null,
                'disponibilite' => $data['disponibilite'] ?: null,
            ]);
        });

        return redirect()->route('admin.coaches.edit', $coach)
            ->with('status', 'Coach account created. You can now assign members to this coach.');
    }

    public function editCoach(Coach $coach): View
    {
        return view('admin.coaches.edit', [
            'coach' => $coach->load('user'),
            ...$this->coachFormOptions(),
        ]);
    }

    public function updateCoach(Request $request, Coach $coach): RedirectResponse
    {
        $data = $this->validateCoach($request, $coach);

        DB::transaction(function () use ($coach, $data): void {
            $userData = collect($data)->only(['nom', 'prenom', 'email'])->all();
            if (filled($data['password'] ?? null)) {
                $userData['password'] = Hash::make($data['password']);
            }
            $coach->user->update($userData);
            $coach->update([
                'specialite' => $data['specialite'] ?: null,
                'disponibilite' => $data['disponibilite'] ?: null,
            ]);
        });

        return back()->with('status', 'Coach details saved.');
    }

    public function storeSubscription(Request $request, Member $member): RedirectResponse
    {
        $data = $request->validate([
            'subscription_plan_id' => ['required', 'integer', Rule::exists('subscription_plans', 'id')->where('actif', true)],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ]);
        $status = Carbon::parse($data['date_fin'])->lt(today()) ? 'expire' : 'actif';

        DB::transaction(function () use ($member, $data, $status): void {
            MemberSubscription::query()->create([
                'member_id' => $member->id,
                'subscription_plan_id' => $data['subscription_plan_id'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'statut' => $status,
            ]);
            $member->update(['statut_abonnement' => $status]);
        });

        return back()->with('status', 'Subscription assigned to member.');
    }

    public function storeSubscriptionPlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:subscription_plans,nom'],
            'duree_jours' => ['required', 'integer', 'min:1', 'max:3650'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        SubscriptionPlan::query()->create([...$data, 'actif' => true]);

        return back()->with('status', 'Subscription plan created.');
    }

    private function coaches()
    {
        return Coach::query()->with('user')->withCount('members')->orderBy('id')->get();
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

    private function validateCoach(Request $request, ?Coach $coach = null): array
    {
        $password = $coach
            ? ['nullable', 'confirmed', 'min:12']
            : ['required', 'confirmed', 'min:12'];

        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($coach?->user_id)],
            'password' => $password,
            'specialite' => ['nullable', Rule::in(self::COACH_SPECIALITIES)],
            'disponibilite' => ['nullable', Rule::in(self::COACH_AVAILABILITIES)],
        ]);
    }

    private function coachFormOptions(): array
    {
        return [
            'specialities' => self::COACH_SPECIALITIES,
            'availabilities' => self::COACH_AVAILABILITIES,
        ];
    }
}
