<x-layouts.app title="My training | GymFlow" heading="My training">
    <div class="dashboard-wrap" id="overview">
        <section class="dashboard-intro member-intro">
            <div>
                <p class="eyebrow">Your training space</p>
                <h2>Do the session. Keep the signal.</h2>
                <p>Follow this week's plan, then share how each session felt.</p>
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

            <section class="member-overview-grid" aria-label="This week's training">
                <article class="panel member-next-session">
                    @if ($nextSession)
                        <p class="eyebrow">Next planned session</p>
                        <h2>{{ $nextSession->jour }}</h2>
                        <p>{{ $nextSession->notes ?: ($nextSession->exerciseDetails->count() === 1 ? '1 exercise is ready for you.' : $nextSession->exerciseDetails->count().' exercises are ready for you.') }}</p>
                        <dl class="member-session-metrics">
                            <div><dt>Session</dt><dd>{{ $nextSession->ordre }} / {{ $stats['total'] }}</dd></div>
                            <div><dt>Exercises</dt><dd>{{ $nextSession->exerciseDetails->count() }}</dd></div>
                        </dl>
                        <a class="button button-primary" href="{{ route('member.programme') }}#session-{{ $nextSession->id }}">Open session</a>
                    @else
                        <p class="eyebrow">This week</p>
                        <h2>Programme complete</h2>
                        <p>You have logged every session in this programme.</p>
                        <a class="button button-secondary" href="{{ route('member.programme') }}">Review programme</a>
                    @endif
                </article>

                <article class="panel member-weekly-signal">
                    <p class="eyebrow">Week at a glance</p>
                    <h2>{{ $stats['remaining'] }} session{{ $stats['remaining'] === 1 ? '' : 's' }} left</h2>
                    <dl class="member-signal-list">
                        <div><dt>Completed</dt><dd>{{ $stats['completed'] }}</dd></div>
                        <div><dt>Remaining</dt><dd>{{ $stats['remaining'] }}</dd></div>
                        <div><dt>Current plan</dt><dd>{{ $programme->sessions->count() }} session{{ $programme->sessions->count() === 1 ? '' : 's' }}</dd></div>
                    </dl>
                </article>
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">Nothing published yet</p><h2>Your coach is shaping your next programme.</h2><p>A published programme will appear here with your sessions and exercises.</p></section>
        @endif
    </div>
</x-layouts.app>
