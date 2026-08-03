<?php

use App\Ai\Agents\CoachMemberAssistant;
use App\Models\Coach;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\SportProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeCoachForAssistantChat(): Coach
{
    return Coach::query()->create([
        'user_id' => User::factory()->create(['role' => 'coach'])->id,
        'specialite' => 'Strength training',
        'disponibilite' => 'Weekdays',
    ]);
}

function makeMemberForAssistantChat(Coach $coach): Member
{
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member', 'prenom' => 'Sara', 'nom' => 'Member'])->id,
        'coach_id' => $coach->id,
    ]);

    SportProfile::query()->create([
        'membre_id' => $member->id,
        'objectif' => 'Build strength',
        'niveau' => 'debutant',
        'blessures' => 'Sensitive shoulder',
        'jours_disponibles' => ['tuesday', 'thursday'],
        'preferences' => 'Cardio and free weights',
    ]);

    $programme = Programme::query()->create([
        'membre_id' => $member->id,
        'titre' => 'Shoulder-friendly strength',
        'source' => 'ia',
        'statut' => 'brouillon',
    ]);

    $programme->sessions()->create(['jour' => 'Tuesday', 'ordre' => 1])
        ->exerciseDetails()
        ->create([
            'exercice_id' => Exercise::query()->create([
                'nom' => 'Goblet squat',
                'groupe_musculaire' => 'Legs',
                'type' => 'musculation',
            ])->id,
            'ordre' => 1,
            'series' => 3,
            'repetitions' => 10,
            'repos' => '75 seconds',
        ]);

    return $member;
}

it('stores a coach question and contextual assistant reply for the assigned member', function () {
    CoachMemberAssistant::fake(['Keep the goblet squat at a comfortable depth and stop if shoulder positioning is painful.']);
    $coach = makeCoachForAssistantChat();
    $member = makeMemberForAssistantChat($coach);

    $this->actingAs($coach->user)
        ->post(route('coach.members.assistant.messages.store', $member), [
            'question' => 'Should I change the goblet squat for the shoulder?',
        ])
        ->assertRedirect(route('coach.members.show', $member).'#assistant');

    $this->assertDatabaseHas('coach_ai_conversations', [
        'coach_id' => $coach->id,
        'membre_id' => $member->id,
    ]);
    $this->assertDatabaseHas('coach_ai_messages', [
        'role' => 'coach',
        'contenu' => 'Should I change the goblet squat for the shoulder?',
    ]);
    $this->assertDatabaseHas('coach_ai_messages', [
        'role' => 'assistant',
        'contenu' => 'Keep the goblet squat at a comfortable depth and stop if shoulder positioning is painful.',
    ]);

    CoachMemberAssistant::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Goblet squat')
        && str_contains($prompt->prompt, 'Sensitive shoulder')
        && str_contains($prompt->prompt, 'Should I change the goblet squat for the shoulder?'));

    $this->actingAs($coach->user)
        ->get(route('coach.members.show', $member))
        ->assertOk()
        ->assertSee('coach-assistant-panel', false)
        ->assertSee('GymFlow assistant')
        ->assertSee('Keep the goblet squat at a comfortable depth');
});

it('does not let a coach access another coach member conversation', function () {
    CoachMemberAssistant::fake(['This reply must not be reached.']);
    $owner = makeCoachForAssistantChat();
    $otherCoach = makeCoachForAssistantChat();
    $member = makeMemberForAssistantChat($owner);

    $this->actingAs($otherCoach->user)
        ->post(route('coach.members.assistant.messages.store', $member), ['question' => 'Can I change this?'])
        ->assertForbidden();

    $this->assertDatabaseCount('coach_ai_conversations', 0);
});

it('keeps the coach question and shows an error when the AI provider fails', function () {
    CoachMemberAssistant::fake([fn () => throw new \RuntimeException('Provider unavailable')]);
    $coach = makeCoachForAssistantChat();
    $member = makeMemberForAssistantChat($coach);

    $this->actingAs($coach->user)
        ->from(route('coach.members.show', $member))
        ->post(route('coach.members.assistant.messages.store', $member), ['question' => 'Can we add cardio?'])
        ->assertRedirect(route('coach.members.show', $member).'#assistant')
        ->assertSessionHasErrors('question');

    $this->assertDatabaseHas('coach_ai_messages', [
        'role' => 'coach',
        'contenu' => 'Can we add cardio?',
    ]);
    $this->assertDatabaseMissing('coach_ai_messages', ['role' => 'assistant']);
});
