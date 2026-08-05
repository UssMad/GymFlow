<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MemberAiConversation;
use App\Models\Programme;
use App\Models\WorkoutSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $data = $this->workspaceData($request);
        $member = $request->user()->member;
        $assistantConversation = MemberAiConversation::query()
            ->where('membre_id', $member->id)
            ->first();
        $assistantMessages = $assistantConversation
            ? $assistantConversation->messages()->latest('id')->take(8)->get()->reverse()->values()
            : collect();

        return view('member.dashboard', $data + [
            'nextSession' => $data['programme']?->sessions->sortBy('ordre')->firstWhere('statut', 'planifie'),
            'assistantMessages' => $assistantMessages,
        ]);
    }

    public function programme(Request $request): View
    {
        return view('member.programme', $this->workspaceData($request));
    }

    public function history(Request $request): View
    {
        return view('member.history', $this->workspaceData($request));
    }

    public function showHistoryProgramme(Request $request, Programme $programme): View
    {
        $member = $request->user()->member;

        abort_unless(
            $member
            && $programme->membre_id === $member->id
            && $programme->statut === 'publie'
            && $programme->date_fin
            && $programme->date_fin->lt(today()),
            404,
        );

        $programme->load('sessions.exerciseDetails.exercise');
        $sessions = $programme->sessions;

        return view('member.history-show', [
            'programme' => $programme,
            'stats' => [
                'total' => $sessions->count(),
                'completed' => $sessions->where('statut', 'realise')->count(),
                'missed' => $sessions->where('statut', 'non_realise')->count(),
            ],
        ]);
    }

    public function completeWorkout(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $this->ensureMemberCanManageCurrentWorkout($request, $workoutSession);
        abort_unless($workoutSession->statut === 'planifie', 422, 'Only planned sessions can be updated.');

        $data = $request->validate([
            'retour_membre' => ['nullable', 'string', 'max:500'],
            'difficulte_ressentie' => ['required', 'in:facile,moderee,difficile'],
        ]);

        $workoutSession->update([
            'statut' => 'realise',
            'realisee_le' => $workoutSession->realisee_le ?? now(),
            'retour_membre' => $data['retour_membre'] ?? $workoutSession->retour_membre,
            'difficulte_ressentie' => $data['difficulte_ressentie'],
            'raison_non_realisation' => null,
        ]);

        return back()->with('status', "{$workoutSession->jour} is marked complete.");
    }

    public function missWorkout(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $this->ensureMemberCanManageCurrentWorkout($request, $workoutSession);
        abort_unless($workoutSession->statut === 'planifie', 422, 'Only planned sessions can be updated.');

        $data = $request->validate([
            'raison_non_realisation' => ['nullable', 'string', 'max:500'],
        ]);

        $workoutSession->update([
            'statut' => 'non_realise',
            'raison_non_realisation' => $data['raison_non_realisation'] ?? null,
            'realisee_le' => null,
            'retour_membre' => null,
            'difficulte_ressentie' => null,
        ]);

        return back()->with('status', "{$workoutSession->jour} is marked as missed.");
    }

    public function reopenWorkout(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $this->ensureMemberCanManageCurrentWorkout($request, $workoutSession);
        abort_unless(in_array($workoutSession->statut, ['realise', 'non_realise'], true), 422, 'Only logged sessions can be reopened.');

        $workoutSession->update([
            'statut' => 'planifie',
            'realisee_le' => null,
            'retour_membre' => null,
            'difficulte_ressentie' => null,
            'raison_non_realisation' => null,
        ]);

        return back()->with('status', "{$workoutSession->jour} is ready to log again.");
    }

    /**
     * Build the shared context used by every member workspace page.
     *
     * @return array{programme: Programme|null, history: \Illuminate\Support\Collection, stats: array{total: int, completed: int, remaining: int, progress: int}}
     */
    private function workspaceData(Request $request): array
    {
        $member = $request->user()->member;
        abort_unless($member, 403);

        $programme = Programme::query()
            ->where('membre_id', $member->id)
            ->where('statut', 'publie')
            ->where(fn ($query) => $query->whereNull('date_debut')->orWhereDate('date_debut', '<=', today()))
            ->where(fn ($query) => $query->whereNull('date_fin')->orWhereDate('date_fin', '>=', today()))
            ->with('sessions.exerciseDetails.exercise')
            ->orderByDesc('date_debut')
            ->latest('id')
            ->first();

        $history = Programme::query()
            ->where('membre_id', $member->id)
            ->where('statut', 'publie')
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '<', today())
            ->withCount([
                'sessions as completed_sessions_count' => fn ($query) => $query->where('statut', 'realise'),
                'sessions as missed_sessions_count' => fn ($query) => $query->where('statut', 'non_realise'),
                'sessions',
            ])
            ->latest('date_fin')
            ->take(12)
            ->get();

        $sessions = $programme?->sessions ?? collect();
        $completedSessions = $sessions->where('statut', 'realise')->count();

        return [
            'programme' => $programme,
            'history' => $history,
            'stats' => [
                'total' => $sessions->count(),
                'completed' => $completedSessions,
                'remaining' => $sessions->count() - $completedSessions,
                'progress' => $sessions->isEmpty() ? 0 : (int) round(($completedSessions / $sessions->count()) * 100),
            ],
        ];
    }

    private function ensureMemberCanManageCurrentWorkout(Request $request, WorkoutSession $workoutSession): void
    {
        $member = $request->user()->member;
        $programme = $workoutSession->programme;

        abort_unless(
            $member
            && $programme->membre_id === $member->id
            && $programme->statut === 'publie'
            && (!$programme->date_debut || $programme->date_debut->isToday() || $programme->date_debut->isPast())
            && (!$programme->date_fin || $programme->date_fin->isToday() || $programme->date_fin->isFuture()),
            404,
        );
    }
}
