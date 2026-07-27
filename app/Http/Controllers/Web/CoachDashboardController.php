<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\CoachMemberProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachDashboardController extends Controller
{
    public function index(Request $request, CoachMemberProgressService $progress): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $members = Member::query()
            ->where('coach_id', $coach->id)
            ->with(['user', 'sportProfile', 'programmes' => fn ($query) => $query->latest('id')])
            ->get();

        $progressByMember = $members->mapWithKeys(
            fn (Member $member): array => [$member->id => $progress->summary($member)],
        );
        $programmesForReview = $members->flatMap->programmes
            ->whereIn('statut', ['brouillon', 'valide'])
            ->sortByDesc('updated_at')
            ->take(6);
        $averageCompletion = $members->isEmpty()
            ? 0
            : (int) round($progressByMember->avg('completion_rate'));

        return view('coach.dashboard', [
            'members' => $members,
            'progressByMember' => $progressByMember,
            'programmesForReview' => $programmesForReview,
            'stats' => [
                'members' => $members->count(),
                'completion' => $averageCompletion,
                'review' => $programmesForReview->count(),
                'profiles' => $members->filter->sportProfile->count(),
            ],
        ]);
    }
}
