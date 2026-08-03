<x-layouts.app title="My programme | GymFlow" heading="My programme">
    <div class="dashboard-wrap member-programme" id="programme">
        <section class="dashboard-intro member-intro member-programme-intro">
            <div>
                <p class="eyebrow">Current training week</p>
                <h2>Your plan, session by session.</h2>
                <p>Follow the prescription, then leave a clear signal for your coach when you finish.</p>
            </div>
            @if ($programme)
                <div class="programme-period member-programme-period"><span>Programme window</span><strong>{{ $programme->date_debut?->format('d M') ?? 'Now' }} to {{ $programme->date_fin?->format('d M') ?? 'Open-ended' }}</strong></div>
            @endif
        </section>

        @if ($programme)
            <section class="member-progress-rail" aria-label="Programme completion">
                <div class="member-progress-heading"><p class="eyebrow">Current programme</p><h2>{{ $programme->titre }}</h2></div>
                <div class="member-progress-visual"><div class="member-progress-track"><span style="width: {{ $stats['progress'] }}%"></span></div><div class="member-progress-label"><span>Completed</span><strong>{{ $stats['completed'] }} of {{ $stats['total'] }} sessions</strong></div></div>
                <strong class="member-progress-value">{{ $stats['progress'] }}<small>%</small></strong>
            </section>

            <section class="member-programme-session-list" aria-label="Programme sessions">
                @foreach ($programme->sessions->sortBy('ordre') as $session)
                    <article class="member-programme-session member-programme-session--{{ $session->statut }}" id="session-{{ $session->id }}">
                        <header class="member-programme-session-header">
                            <div class="member-programme-session-index">{{ str_pad((string) $session->ordre, 2, '0', STR_PAD_LEFT) }}</div>
                            <div class="member-programme-session-title"><p class="eyebrow">Session {{ $session->ordre }}</p><h2>{{ ucfirst($session->jour) }}</h2></div>
                            <div class="member-programme-session-state"><span>{{ $session->exerciseDetails->count() }} movement{{ $session->exerciseDetails->count() === 1 ? '' : 's' }}</span><span class="status-pill {{ $session->statut === 'realise' ? 'status-good' : ($session->statut === 'non_realise' ? 'status-muted' : 'status-review') }}">{{ match ($session->statut) { 'realise' => 'Completed', 'non_realise' => 'Missed', default => 'Planned' } }}</span></div>
                        </div>
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
                            <div class="member-session-feedback member-session-feedback--complete"><div><strong>Logged {{ $session->realisee_le?->format('d M, H:i') }}</strong><span>{{ ucfirst($session->difficulte_ressentie ?? 'no difficulty shared') }}{{ $session->retour_membre ? ' / '.$session->retour_membre : '' }}</span></div><form class="session-correction-form" method="POST" action="{{ route('member.workouts.reopen', $session) }}">@csrf @method('PUT')<button class="button button-small button-secondary" type="submit">Log again</button></form></div>
                        @elseif ($session->statut === 'non_realise')
                            <div class="member-session-feedback member-session-feedback--missed"><div><strong>Marked as missed</strong><span>{{ $session->raison_non_realisation ?: 'No reason shared.' }}</span></div><form class="session-correction-form" method="POST" action="{{ route('member.workouts.reopen', $session) }}">@csrf @method('PUT')<button class="button button-small button-secondary" type="submit">Log again</button></form></div>
                        @else
                            <div class="member-session-checkin">
                                <div class="member-session-checkin-heading"><div><p class="eyebrow">After the session</p><strong>Leave your training signal</strong></div><span>Visible to your coach</span></div>
                                <form method="POST" action="{{ route('member.workouts.complete', $session) }}" class="complete-form">
                                    @csrf @method('PUT')
                                    <label><span>How did it feel?</span><select name="difficulte_ressentie" required><option value="">Choose difficulty</option><option value="facile">Easy</option><option value="moderee">Moderate</option><option value="difficile">Hard</option></select></label>
                                    <label><span>Quick note (optional)</span><input type="text" name="retour_membre" maxlength="500" placeholder="Energy, pain, a personal best..."></label>
                                    <button class="button button-primary" type="submit">Mark complete</button>
                                </form>
                                <details class="missed-workout"><summary>Could not do this session?</summary><form method="POST" action="{{ route('member.workouts.missed', $session) }}">@csrf @method('PUT')<label><span>Reason (optional)</span><input type="text" name="raison_non_realisation" maxlength="500" placeholder="No time, pain, recovery day..."></label><button class="button button-secondary" type="submit">Mark as missed</button></form></details>
                            </div>
                        @endif
                    </article>
                @endforeach
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">Nothing published yet</p><h2>Your coach is shaping your next programme.</h2><p>A published programme will appear here with your sessions and exercises.</p></section>
        @endif
    </div>
</x-layouts.app>
