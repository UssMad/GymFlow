<x-layouts.app title="My training | GymFlow" heading="My training">
    <div class="dashboard-wrap member-overview" id="overview">
        <section class="dashboard-intro member-intro member-overview-intro">
            <div>
                <p class="eyebrow">Current training week</p>
                <h2>Make this week count.</h2>
                <p>Start with the next session, then keep your coach informed with an honest check-in.</p>
            </div>
            @if ($programme)
                <div class="programme-period member-overview-period"><span>Programme window</span><strong>{{ $programme->date_debut?->format('d M') ?? 'Now' }} to {{ $programme->date_fin?->format('d M') ?? 'Open-ended' }}</strong></div>
            @endif
        </section>

        @if ($programme)
            <section class="member-progress-rail" aria-label="Programme completion">
                <div class="member-progress-heading"><p class="eyebrow">Current programme</p><h2>{{ $programme->titre }}</h2></div>
                <div class="member-progress-visual"><div class="member-progress-track"><span style="width: {{ $stats['progress'] }}%"></span></div><div class="member-progress-label"><span>Completed</span><strong>{{ $stats['completed'] }} of {{ $stats['total'] }} sessions</strong></div></div>
                <strong class="member-progress-value">{{ $stats['progress'] }}<small>%</small></strong>
            </section>

            <section class="member-overview-grid" aria-label="This week's training">
                <article class="member-next-session">
                    @if ($nextSession)
                        <div class="next-session-topline"><p class="eyebrow">Next planned session</p><span>Session {{ str_pad((string) $nextSession->ordre, 2, '0', STR_PAD_LEFT) }}</span></div>
                        <div class="next-session-heading"><h2>{{ ucfirst($nextSession->jour) }}</h2><span>{{ $nextSession->exerciseDetails->count() }} movement{{ $nextSession->exerciseDetails->count() === 1 ? '' : 's' }}</span></div>
                        <p class="next-session-note">{{ $nextSession->notes ?: 'Your next session is ready when you are.' }}</p>
                        <ul class="next-session-exercise-list" aria-label="Exercises in the next session">
                            @foreach ($nextSession->exerciseDetails->sortBy('ordre')->take(3) as $detail)
                                <li><span>{{ str_pad((string) $detail->ordre, 2, '0', STR_PAD_LEFT) }}</span><strong>{{ $detail->exercise->nom }}</strong></li>
                            @endforeach
                        </ul>
                        <div class="next-session-footer"><dl class="member-session-metrics"><div><dt>Programme position</dt><dd>{{ $nextSession->ordre }} of {{ $stats['total'] }}</dd></div><div><dt>Ready now</dt><dd>Yes</dd></div></dl><a class="button button-primary" href="{{ route('member.programme') }}#session-{{ $nextSession->id }}">Open session</a></div>
                    @else
                        <div class="next-session-topline"><p class="eyebrow">This week</p><span>Complete</span></div>
                        <h2>Programme complete</h2>
                        <p class="next-session-note">You have logged every session in this programme.</p>
                        <a class="button button-secondary" href="{{ route('member.programme') }}">Review programme</a>
                    @endif
                </article>

                <article class="member-weekly-signal">
                    <p class="eyebrow">Week at a glance</p>
                    <div class="member-weekly-heading"><h2>{{ $stats['remaining'] }} session{{ $stats['remaining'] === 1 ? '' : 's' }} left</h2><span>{{ $stats['progress'] }}% logged</span></div>
                    <dl class="member-signal-list">
                        <div><dt>Completed</dt><dd>{{ $stats['completed'] }}<small>sessions</small></dd></div>
                        <div><dt>Still to train</dt><dd>{{ $stats['remaining'] }}<small>sessions</small></dd></div>
                        <div><dt>Programme length</dt><dd>{{ $programme->sessions->count() }}<small>sessions</small></dd></div>
                    </dl>
                </article>
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">Nothing published yet</p><h2>Your coach is shaping your next programme.</h2><p>A published programme will appear here with your sessions and exercises.</p></section>
        @endif
    </div>
</x-layouts.app>
