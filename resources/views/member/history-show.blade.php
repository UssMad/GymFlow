<x-layouts.app title="{{ $programme->titre }} | GymFlow" heading="Programme history">
    <div class="dashboard-wrap">
        <section class="dashboard-intro member-intro">
            <div>
                <p class="eyebrow">Archived programme</p>
                <h2>{{ $programme->titre }}</h2>
                <p>Review the sessions, exercises, and training notes from this completed programme.</p>
            </div>
            <a class="button button-secondary" href="{{ route('member.history') }}">Back to history</a>
        </section>

        <section class="member-progress-band history-detail-band" aria-label="Programme summary">
            <div><p class="eyebrow">Finished {{ $programme->date_fin->format('d M Y') }}</p><strong>{{ $stats['completed'] }} completed</strong></div>
            <dl class="history-programme-stats history-detail-stats">
                <div><dt>Sessions</dt><dd>{{ $stats['total'] }}</dd></div>
                <div><dt>Completed</dt><dd>{{ $stats['completed'] }}</dd></div>
                <div><dt>Missed</dt><dd>{{ $stats['missed'] }}</dd></div>
            </dl>
            <span>{{ $programme->date_debut?->format('d M') ?? 'Start' }} to {{ $programme->date_fin->format('d M') }}</span>
        </section>

        <section class="member-session-list" aria-label="Archived sessions">
            @foreach ($programme->sessions->sortBy('ordre') as $session)
                <article class="session-card {{ $session->statut === 'realise' ? 'session-done' : '' }}">
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
                    @endif
                </article>
            @endforeach
        </section>
    </div>
</x-layouts.app>
