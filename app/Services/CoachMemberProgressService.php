<?php

namespace App\Services;

use App\Models\Member;
use App\Models\WorkoutSession;

class CoachMemberProgressService
{
    public function summary(Member $member): array
    {
        $sessions = WorkoutSession::query()->whereHas('programme', fn ($query) => $query->where('membre_id', $member->id));
        $totalSessions = (clone $sessions)->count();
        $completedSessions = (clone $sessions)->where('statut', 'realise')->count();
        $difficultyCounts = (clone $sessions)
            ->where('statut', 'realise')
            ->whereNotNull('difficulte_ressentie')
            ->selectRaw('difficulte_ressentie, count(*) as total')
            ->groupBy('difficulte_ressentie')
            ->pluck('total', 'difficulte_ressentie');

        return [
            'total_sessions' => $totalSessions,
            'completed_sessions' => $completedSessions,
            'completion_rate' => $totalSessions === 0 ? 0 : round(($completedSessions / $totalSessions) * 100, 2),
            'last_completed_at' => (clone $sessions)->where('statut', 'realise')->max('realisee_le'),
            'difficulty' => [
                'facile' => $difficultyCounts->get('facile', 0),
                'moderee' => $difficultyCounts->get('moderee', 0),
                'difficile' => $difficultyCounts->get('difficile', 0),
            ],
            'recent_completed_sessions' => (clone $sessions)
                ->with('programme:id,titre')
                ->where('statut', 'realise')
                ->orderByDesc('realisee_le')
                ->limit(10)
                ->get(),
        ];
    }
}
