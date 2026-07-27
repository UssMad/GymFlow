<x-layouts.app title="My programme | GymFlow" heading="My training">
    <div class="dashboard-wrap">
        <section class="dashboard-intro member-intro">
            <div>
                <p class="eyebrow">Your training space</p>
                <h2>Do the session. Keep the signal.</h2>
                <p>Your coach sees completed sessions and difficulty feedback, so the next programme can fit you better.</p>
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
                    <article class="session-card {{ $session->statut === 'realise' ? 'session-done' : '' }}">
                        <div class="session-heading">
                            <div><p class="eyebrow">Session {{ $session->ordre }}</p><h2>{{ $session->jour }}</h2></div>
                            <span class="status-pill {{ $session->statut === 'realise' ? 'status-good' : 'status-review' }}">{{ $session->statut === 'realise' ? 'Completed' : 'Planned' }}</span>
                        </div>
                        @if ($session->notes)<p class="session-note">{{ $session->notes }}</p>@endif
                        <div class="exercise-list">
                            @foreach ($session->exerciseDetails->sortBy('ordre') as $detail)
                                <div class="exercise-row">
                                    <strong>{{ $detail->exercise->nom }}</strong>
                                    <span>{{ collect([$detail->series ? $detail->series.' sets' : null, $detail->repetitions ? $detail->repetitions.' reps' : null, $detail->duree_cardio ? $detail->duree_cardio.' min' : null])->filter()->join(' / ') ?: 'See coach notes' }}</span>
                                    @if ($detail->repos)<small>{{ $detail->repos }} rest</small>@endif
                                </div>
                            @endforeach
                        </div>
                        @if ($session->statut === 'realise')
                            <div class="session-complete-note"><strong>Logged {{ $session->realisee_le?->format('d M, H:i') }}</strong><span>{{ ucfirst($session->difficulte_ressentie ?? 'no difficulty shared') }}{{ $session->retour_membre ? ' / '.$session->retour_membre : '' }}</span></div>
                        @else
                            <form method="POST" action="{{ route('member.workouts.complete', $session) }}" class="complete-form">
                                @csrf @method('PUT')
                                <label><span>How did it feel?</span><select name="difficulte_ressentie" required><option value="">Choose difficulty</option><option value="facile">Easy</option><option value="moderee">Moderate</option><option value="difficile">Hard</option></select></label>
                                <label><span>Quick note (optional)</span><input type="text" name="retour_membre" maxlength="500" placeholder="Energy, pain, a personal best..."></label>
                                <button class="button button-primary" type="submit">Mark complete</button>
                            </form>
                        @endif
                    </article>
                @endforeach
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">Nothing published yet</p><h2>Your coach is shaping your next programme.</h2><p>A published programme will appear here with your sessions and exercises.</p></section>
        @endif

        @if ($history->isNotEmpty())
            <section class="history-section"><div><p class="eyebrow">Past programmes</p><h2>Training history</h2></div><div class="history-list">@foreach ($history as $pastProgramme)<span>{{ $pastProgramme->titre }} <small>{{ $pastProgramme->date_fin->format('M Y') }}</small></span>@endforeach</div></section>
        @endif
    </div>
</x-layouts.app>
