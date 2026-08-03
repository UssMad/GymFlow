<x-layouts.app title="Training history | GymFlow" heading="Training history">
    <div class="dashboard-wrap">
        <section class="dashboard-intro member-intro">
            <div>
                <p class="eyebrow">Previous programmes</p>
                <h2>See the work behind you.</h2>
                <p>Each finished programme stays here as a simple record of your training.</p>
            </div>
            <div class="programme-period">{{ $history->count() }} saved programme{{ $history->count() === 1 ? '' : 's' }}</div>
        </section>

        @if ($history->isNotEmpty())
            <section class="history-programme-grid" aria-label="Past programmes">
                @foreach ($history as $pastProgramme)
                    <article class="panel history-programme-card">
                        <p class="eyebrow">Finished {{ $pastProgramme->date_fin->format('d M Y') }}</p>
                        <h2>{{ $pastProgramme->titre }}</h2>
                        <dl class="history-programme-stats">
                            <div><dt>Sessions</dt><dd>{{ $pastProgramme->sessions_count }}</dd></div>
                            <div><dt>Completed</dt><dd>{{ $pastProgramme->completed_sessions_count }}</dd></div>
                            <div><dt>Missed</dt><dd>{{ $pastProgramme->missed_sessions_count }}</dd></div>
                        </dl>
                        <a class="button button-small button-secondary history-programme-action" href="{{ route('member.history.show', $pastProgramme) }}">View programme</a>
                    </article>
                @endforeach
            </section>
        @else
            <section class="panel empty-programme"><p class="eyebrow">No history yet</p><h2>Your completed programmes will collect here.</h2><p>Finish a programme and its sessions will remain available as your training record.</p></section>
        @endif
    </div>
</x-layouts.app>
