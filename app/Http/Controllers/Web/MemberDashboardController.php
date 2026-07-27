<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\WorkoutSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function index(Request $request): View
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
            ->latest('date_fin')
            ->take(4)
            ->get();
        $sessions = $programme?->sessions ?? collect();
        $completedSessions = $sessions->where('statut', 'realise')->count();

        return view('member.dashboard', [
            'programme' => $programme,
            'history' => $history,
            'stats' => [
                'total' => $sessions->count(),
                'completed' => $completedSessions,
                'remaining' => $sessions->count() - $completedSessions,
                'progress' => $sessions->isEmpty() ? 0 : (int) round(($completedSessions / $sessions->count()) * 100),
            ],
        ]);
    }

    public function completeWorkout(Request $request, WorkoutSession $workoutSession): RedirectResponse
    {
        $member = $request->user()->member;
        abort_unless($member && $workoutSession->programme->membre_id === $member->id && $workoutSession->programme->statut === 'publie', 404);

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
}
