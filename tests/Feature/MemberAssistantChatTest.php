<?php

use App\Ai\Agents\MemberTrainingAssistant;
use App\Models\Exercise;
use App\Models\Member;
use App\Models\Programme;
use App\Models\SportProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeMemberForTrainingAssistant(): Member
{
    $member = Member::query()->create([
        'user_id' => User::factory()->create(['role' => 'member', 'prenom' => 'Sara', 'nom' => 'Member'])->id,
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
        'statut' => 'publie',
        'date_debut' => today()->subDay(),
        'date_fin' => today()->addDay(),
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

it('stores a private member question and contextual assistant reply', function () {
    MemberTrainingAssistant::fake(['Keep your chest tall, lower only as far as comfortable, and ask your coach if the movement causes shoulder pain.']);
    $member = makeMemberForTrainingAssistant();

    $this->actingAs($member->user)
        ->post(route('member.assistant.messages.store'), [
            'question' => 'How should I do the goblet squat?',
        ])
        ->assertRedirect(route('member.dashboard').'#assistant');

    $this->assertDatabaseHas('member_ai_conversations', [
        'membre_id' => $member->id,
    ]);
    $this->assertDatabaseHas('member_ai_messages', [
        'role' => 'member',
        'contenu' => 'How should I do the goblet squat?',
    ]);
    $this->assertDatabaseHas('member_ai_messages', [
        'role' => 'assistant',
        'contenu' => 'Keep your chest tall, lower only as far as comfortable, and ask your coach if the movement causes shoulder pain.',
    ]);

    MemberTrainingAssistant::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Goblet squat')
        && str_contains($prompt->prompt, 'Sensitive shoulder')
        && str_contains($prompt->prompt, 'How should I do the goblet squat?'));

    $this->actingAs($member->user)
        ->get(route('member.dashboard'))
        ->assertOk()
        ->assertSee('member-assistant-panel', false)
        ->assertSee('GymFlow assistant')
        ->assertSee('Keep your chest tall');
});

it('does not let a coach use the member training assistant', function () {
    MemberTrainingAssistant::fake(['This reply must not be reached.']);
    $coach = User::factory()->create(['role' => 'coach']);

    $this->actingAs($coach)
        ->post(route('member.assistant.messages.store'), ['question' => 'Can I ask this?'])
        ->assertForbidden();

    $this->assertDatabaseCount('member_ai_conversations', 0);
});

it('keeps the member question and shows an error when the AI provider fails', function () {
    MemberTrainingAssistant::fake([fn () => throw new RuntimeException('Provider unavailable')]);
    $member = makeMemberForTrainingAssistant();

    $this->actingAs($member->user)
        ->from(route('member.dashboard'))
        ->post(route('member.assistant.messages.store'), ['question' => 'Can I swap this exercise?'])
        ->assertRedirect(route('member.dashboard').'#assistant')
        ->assertSessionHasErrors('question');

    $this->assertDatabaseHas('member_ai_messages', [
        'role' => 'member',
        'contenu' => 'Can I swap this exercise?',
    ]);
    $this->assertDatabaseMissing('member_ai_messages', ['role' => 'assistant']);
});
