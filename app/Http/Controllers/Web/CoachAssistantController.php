<?php

namespace App\Http\Controllers\Web;

use App\Ai\Agents\CoachMemberAssistant;
use App\Ai\Prompts\CoachMemberAssistantPrompt;
use App\Http\Controllers\Controller;
use App\Models\CoachAiConversation;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CoachAssistantController extends Controller
{
    public function store(Request $request, Member $member, CoachMemberAssistant $assistant): RedirectResponse
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $member->coach_id === $coach->id, 403);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:1200'],
        ]);

        $member->load([
            'user',
            'sportProfile',
            'programmes' => fn ($query) => $query->latest('id')->limit(3)->with('sessions.exerciseDetails.exercise'),
        ]);

        $conversation = CoachAiConversation::query()->firstOrCreate([
            'coach_id' => $coach->id,
            'membre_id' => $member->id,
        ]);

        $conversation->messages()->create([
            'role' => 'coach',
            'contenu' => $data['question'],
        ]);

        $history = $conversation->messages()
            ->latest('id')
            ->take(8)
            ->get()
            ->reverse()
            ->values();

        try {
            $answer = trim($assistant->prompt(
                CoachMemberAssistantPrompt::for($member, $coach, $history),
            )->text);
        } catch (Throwable $exception) {
            Log::warning('Coach AI assistant request failed.', [
                'coach_id' => $coach->id,
                'member_id' => $member->id,
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('coach.members.show', $member)
                ->withFragment('assistant')
                ->withErrors(['question' => 'The AI assistant is unavailable. Please try again shortly.']);
        }

        if ($answer === '') {
            return redirect()
                ->route('coach.members.show', $member)
                ->withFragment('assistant')
                ->withErrors(['question' => 'The AI assistant returned an empty answer. Please try again.']);
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'contenu' => $answer,
        ]);

        return redirect()
            ->route('coach.members.show', $member)
            ->withFragment('assistant');
    }
}
