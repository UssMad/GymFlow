<x-layouts.app title="My programme | GymFlow" heading="My programme">
    <div class="dashboard-wrap">
        <section class="dashboard-intro member-intro">
            <div>
                <p class="eyebrow">Current programme</p>
                <h2>Train with the plan.</h2>
                <p>Complete each session and leave an honest note for your coach.</p>
            </div>
            @if ($programme)
                <div class="programme-period">{{ $programme->date_debut?->format('d M') ?? 'Now' }} to {{ $programme->date_fin?->format('d M') ?? 'Open-ended' }}</div>
            @endif
        </section>

        @if ($programme)
            <section class="member-progress-band" aria-label="Programme completion">
                <div><p class="eyebrow">{{ $programme->titre }}</p><strong>{{ $stats['progress'] }}% complete</strong></div>
                <div class="member-progress-track"><span style="width: {{ $stats['progress'] }}%"></span></div>
                <span>{{ $stats['completed'] }} of {{ $stats['total'] }} sessions</span>
            </section>

            <section class="member-session-list">
                @foreach ($programme->sessions->sortBy('ordre') as $session)
                    <article class="session-card {{ $session->statut === 'realise' ? 'session-done' : '' }}" id="session-{{ $session->id }}">
                        <div class="session-heading">
                            <div><p class="eyebrow">Session {{ $session->ordre }}</p><h2>{{ $session->jour }}</h2></div>
                            <span class="status-pill {{ $session->statut === 'realise' ? 'status-good' : ($session->statut === 'non_realise' ? 'status-muted' : 'status-review') }}">{{ match ($session->statut) { 'realise' => 'Completed', 'non_realise' => 'Missed', default => 'Planned' } }}</span>
                        </div>
                        @if ($session->notes)<p class="session-note">{{ $session->notes }}</p>@endif
                        <div class="exercise-list">
                            @foreach ($session->exerciseDetails->sortBy('ordre') as $detail)
                                <div class="exercise-row">
                                    <img class="exercise-thumbnail" src="{{ $detail->exercise->resolvedImageUrl() }}" alt="{{ $detail->exercise->nom }}" loading="lazy">
                                    <div class="exercise-copy"><strong>{{ $detail->exercise->nom }}</strong><span>{{ collect([$detail->series ? $detail->series.' sets' : null, $detail->repetitions ? $detail->repetitions.' reps' : null, $detail->duree_cardio ? $detail->duree_cardio.' min' : null])->filter()->join(' / ') ?: 'See coach notes' }}</span></div>
                                    @if ($detail->repos)<small>{{ $detail->repos }} rest</small>@endif
                                </div>
                            @endforeach
                        </div>
                        @if ($session->statut === 'realise')
                            <div class="session-complete-note"><strong>Logged {{ $session->realisee_le?->format('d M, H:i') }}</strong><span>{{ ucfirst($session->difficulte_ressentie ?? 'no difficulty shared') }}{{ $session->retour_membre ? ' / '.$session->retour_membre : '' }}</span></div>
                        @elseif ($session->statut === 'non_realise')
                            <div class="session-complete-note"><strong>Marked as missed</strong><span>{{ $session->raison_non_realisation ?: 'No reason shared.' }}</span></div>
                        @else
                            <form method="POST" action="{{ route('member.workouts.complete', $session) }}" class="complete-form">
                                @csrf @method('PUT')
                                <label><span>How did it feel?</span><select name="difficulte_ressentie" required><option value="">Choose difficulty</option><option value="facile">Easy</option><option value="moderee">Moderate</option><option value="difficile">Hard</option></select></label>
                                <label><span>Quick note (optional)</span><input type="text" name="retour_membre" maxlength="500" placeholder="Energy, pain, a personal best..."></label>
                                <button class="button button-primary" type="submit">Mark complete</button>
                            </form>
                            <details class="missed-workout"><summary>Could not do this session?</summary><form method="POST" action="{{ route('member.workouts.missed', $session) }}">@csrf @method('PUT')<label><span>Reason (optional)</span><input type="text" name="raison_non_realisation" maxlength="500" placeholder="No time, pain, recovery day..."></label><button class="button button-secondary" type="submit">Mark as missed</button></form></details>
                        @endif
                    </article>
                @endforeach
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">Nothing published yet</p><h2>Your coach is shaping your next programme.</h2><p>A published programme will appear here with your sessions and exercises.</p></section>
        @endif
    </div>
</x-layouts.app>
