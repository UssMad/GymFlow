<x-layouts.app title="Training history | GymFlow" heading="Training history">
    <div class="dashboard-wrap member-history" id="history">
        <section class="dashboard-intro member-intro member-history-intro">
            <div>
                <p class="eyebrow">Training archive</p>
                <h2>Every finished plan, in one place.</h2>
                <p>Review the sessions you completed and the notes you left along the way.</p>
            </div>
            <div class="programme-period member-history-count"><span>Saved programmes</span><strong>{{ $history->count() }}</strong></div>
        </section>

        @if ($history->isNotEmpty())
            <section class="member-history-records" aria-label="Past programmes">
                @foreach ($history as $pastProgramme)
                    <article class="member-history-record">
                        <div class="member-history-record-date"><span>{{ $pastProgramme->date_fin->format('M') }}</span><strong>{{ $pastProgramme->date_fin->format('d') }}</strong><small>{{ $pastProgramme->date_fin->format('Y') }}</small></div>
                        <div class="member-history-record-main"><p class="eyebrow">Finished programme</p><h2>{{ $pastProgramme->titre }}</h2><span>{{ $pastProgramme->date_debut?->format('d M') ?? 'Start' }} to {{ $pastProgramme->date_fin->format('d M Y') }}</span></div>
                        <dl class="member-history-record-stats">
                            <div><dt>Sessions</dt><dd>{{ $pastProgramme->sessions_count }}</dd></div>
                            <div><dt>Completed</dt><dd>{{ $pastProgramme->completed_sessions_count }}</dd></div>
                            <div><dt>Missed</dt><dd>{{ $pastProgramme->missed_sessions_count }}</dd></div>
                        </dl>
                        <a class="button button-small button-secondary member-history-record-action" href="{{ route('member.history.show', $pastProgramme) }}">View programme</a>
                    </article>
                @endforeach
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">No history yet</p><h2>Your completed programmes will collect here.</h2><p>Finish a programme and its sessions will remain available as your training record.</p></section>
        @endif
    </div>
</x-layouts.app>
