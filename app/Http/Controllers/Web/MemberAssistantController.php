<?php

namespace App\Http\Controllers\Web;

use App\Ai\Agents\MemberTrainingAssistant;
use App\Ai\Prompts\MemberTrainingAssistantPrompt;
use App\Http\Controllers\Controller;
use App\Models\MemberAiConversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class MemberAssistantController extends Controller
{
    public function store(Request $request, MemberTrainingAssistant $assistant): RedirectResponse
    {
        $member = $request->user()->member;
        abort_unless($member, 403);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:1200'],
        ]);

        $member->load([
            'user',
            'sportProfile',
            'programmes' => fn ($query) => $query
                ->where('statut', 'publie')
                ->where(fn ($dates) => $dates->whereNull('date_debut')->orWhereDate('date_debut', '<=', today()))
                ->where(fn ($dates) => $dates->whereNull('date_fin')->orWhereDate('date_fin', '>=', today()))
                ->latest('id')
                ->limit(1)
                ->with('sessions.exerciseDetails.exercise'),
        ]);

        $conversation = MemberAiConversation::query()->firstOrCreate([
            'membre_id' => $member->id,
        ]);

        $conversation->messages()->create([
            'role' => 'member',
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
                MemberTrainingAssistantPrompt::for($member, $history),
            )->text);
        } catch (Throwable $exception) {
            Log::warning('Member AI assistant request failed.', [
                'member_id' => $member->id,
                'exception' => $exception::class,
            ]);

            return redirect()
                ->route('member.dashboard')
                ->withFragment('assistant')
                ->withErrors(['question' => 'The training assistant is unavailable. Please try again shortly.']);
        }

        if ($answer === '') {
            return redirect()
                ->route('member.dashboard')
                ->withFragment('assistant')
                ->withErrors(['question' => 'The training assistant returned an empty answer. Please try again.']);
        }

        $conversation->messages()->create([
            'role' => 'assistant',
            'contenu' => $answer,
        ]);

        return redirect()
            ->route('member.dashboard')
            ->withFragment('assistant');
    }
}
