<x-layouts.app title="Members | GymFlow" heading="My members">
    <div class="dashboard-wrap">
        <section class="dashboard-intro">
            <div>
                <p class="eyebrow">Coaching roster</p>
                <h2>Members and their training signal.</h2>
                <p>Open a member workspace to update their profile, generate a draft, or review progress.</p>
            </div>
            <a class="button button-secondary" href="{{ route('coach.programmes.index') }}">View programmes</a>
        </section>

        <section class="panel">
            <div class="panel-heading"><div><p class="eyebrow">Assigned members</p><h2>Progress board</h2></div><span class="count-label">{{ $members->count() }} members</span></div>
            @forelse ($members as $member)
                @php($summary = $progressByMember->get($member->id))
                <div class="progress-row">
                    <div class="progress-member">
                        <span class="avatar">{{ strtoupper(substr($member->user->prenom, 0, 1)) }}{{ strtoupper(substr($member->user->nom, 0, 1)) }}</span>
                        <span><strong>{{ $member->user->prenom }} {{ $member->user->nom }}</strong><small>{{ $member->sportProfile?->objectif ?? 'Sport profile pending' }}</small></span>
                    </div>
                    <div class="progress-track-wrap">
                        <div class="progress-meta"><span>{{ $summary['completed_sessions'] }}/{{ $summary['total_sessions'] }} sessions</span><strong>{{ $summary['completion_rate'] }}%</strong></div>
                        <div class="progress-track"><span style="width: {{ $summary['completion_rate'] }}%"></span></div>
                    </div>
                    <span class="difficulty-label">{{ $summary['difficulty']['difficile'] }} hard</span>
                    <a class="table-link" href="{{ route('coach.members.show', $member) }}">Open workspace</a>
                </div>
            @empty
                <div class="empty-state"><strong>No members assigned.</strong><span>An administrator must assign a member to your coaching account first.</span></div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
