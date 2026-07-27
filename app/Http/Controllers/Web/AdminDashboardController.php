<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
