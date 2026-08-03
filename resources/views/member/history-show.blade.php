<x-layouts.app title="{{ $programme->titre }} | GymFlow" heading="Programme history">
    <div class="dashboard-wrap member-history-detail" id="history-programme">
        <section class="dashboard-intro member-intro member-history-detail-intro">
            <div>
                <p class="eyebrow">Archived programme</p>
                <h2>{{ $programme->titre }}</h2>
                <p>Review the sessions, exercises, and training signals from this completed programme.</p>
            </div>
            <a class="button button-secondary" href="{{ route('member.history') }}">Back to history</a>
        </section>

        <section class="member-history-summary" aria-label="Programme summary">
            <div class="member-history-summary-heading"><p class="eyebrow">Finished {{ $programme->date_fin->format('d M Y') }}</p><strong>{{ $stats['completed'] }} of {{ $stats['total'] }} sessions completed</strong></div>
            <dl class="member-history-summary-stats">
                <div><dt>Sessions</dt><dd>{{ $stats['total'] }}</dd></div>
                <div><dt>Completed</dt><dd>{{ $stats['completed'] }}</dd></div>
                <div><dt>Missed</dt><dd>{{ $stats['missed'] }}</dd></div>
            </dl>
            <div class="member-history-summary-window"><span>Programme window</span><strong>{{ $programme->date_debut?->format('d M') ?? 'Start' }} to {{ $programme->date_fin->format('d M') }}</strong></div>
        </section>

        <section class="member-programme-session-list member-history-session-list" aria-label="Archived sessions">
            @foreach ($programme->sessions->sortBy('ordre') as $session)
                <article class="member-programme-session member-programme-session--{{ $session->statut }}">
                    <header class="member-programme-session-header">
                        <div class="member-programme-session-index">{{ str_pad((string) $session->ordre, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="member-programme-session-title"><p class="eyebrow">Session {{ $session->ordre }}</p><h2>{{ ucfirst($session->jour) }}</h2></div>
                        <div class="member-programme-session-state"><span>{{ $session->exerciseDetails->count() }} movement{{ $session->exerciseDetails->count() === 1 ? '' : 's' }}</span><span class="status-pill {{ $session->statut === 'realise' ? 'status-good' : ($session->statut === 'non_realise' ? 'status-muted' : 'status-review') }}">{{ match ($session->statut) { 'realise' => 'Completed', 'non_realise' => 'Missed', default => 'Planned' } }}</span></div>
                    </header>
                    @if ($session->notes)<p class="member-programme-session-note">{{ $session->notes }}</p>@endif
                    <div class="member-programme-exercises">
                        @foreach ($session->exerciseDetails->sortBy('ordre') as $detail)
                            <div class="member-programme-exercise">
                                <div class="member-programme-exercise-order">{{ str_pad((string) $detail->ordre, 2, '0', STR_PAD_LEFT) }}</div>
                                <img class="member-programme-exercise-image" src="{{ $detail->exercise->resolvedImageUrl() }}" alt="{{ $detail->exercise->nom }}" loading="lazy">
                                <div class="member-programme-exercise-copy"><p>{{ $detail->exercise->type ?? 'Exercise' }}</p><strong>{{ $detail->exercise->nom }}</strong><span>{{ collect([$detail->series ? $detail->series.' sets' : null, $detail->repetitions ? $detail->repetitions.' reps' : null, $detail->duree_cardio ? $detail->duree_cardio.' min' : null])->filter()->join(' / ') ?: 'Follow the coach prescription' }}</span></div>
                                @if ($detail->repos)<small class="member-programme-exercise-rest">{{ $detail->repos }} rest</small>@endif
                            </div>
                        @endforeach
                    </div>
                    @if ($session->statut === 'realise')
                        <div class="member-session-feedback member-session-feedback--complete"><div><strong>Logged {{ $session->realisee_le?->format('d M, H:i') }}</strong><span>{{ ucfirst($session->difficulte_ressentie ?? 'no difficulty shared') }}{{ $session->retour_membre ? ' / '.$session->retour_membre : '' }}</span></div></div>
                    @elseif ($session->statut === 'non_realise')
                        <div class="member-session-feedback member-session-feedback--missed"><div><strong>Marked as missed</strong><span>{{ $session->raison_non_realisation ?: 'No reason shared.' }}</span></div></div>
                    @endif
                </article>
            @endforeach
        </section>
    </div>
</x-layouts.app>
