<x-layouts.app title="Coach dashboard | GymFlow" heading="Coaching desk">
    <div class="dashboard-wrap">
        <section class="dashboard-intro">
            <div>
                <p class="eyebrow">Your coaching roster</p>
                <h2>See the work behind the programme.</h2>
                <p>Member consistency, coaching signals, and the programmes that need your eye.</p>
            </div>
            <div class="signal-label"><span></span> Progress is updated from completed sessions</div>
        </section>

        <section class="stat-grid" aria-label="Coaching summary">
            <article class="stat-card"><span>Assigned members</span><strong>{{ $stats['members'] }}</strong><small>Your active coaching roster</small></article>
            <article class="stat-card stat-teal"><span>Average completion</span><strong>{{ $stats['completion'] }}%</strong><small>Across all recorded sessions</small></article>
            <article class="stat-card stat-coral"><span>Need review</span><strong>{{ $stats['review'] }}</strong><small>Draft or validated programmes</small></article>
            <article class="stat-card stat-gold"><span>Profile ready</span><strong>{{ $stats['profiles'] }}</strong><small>Members with sport context</small></article>
        </section>

        <section class="content-grid coach-grid">
            <article class="panel panel-wide">
                <div class="panel-heading"><div><p class="eyebrow">Member progress</p><h2>Consistency board</h2></div><span class="count-label">{{ $members->count() }} members</span></div>
                @forelse ($members as $member)
                    @php($summary = $progressByMember->get($member->id))
                    <div class="progress-row">
                        <div class="progress-member">
                            <span class="avatar">{{ strtoupper(substr($member->user->prenom, 0, 1)) }}{{ strtoupper(substr($member->user->nom, 0, 1)) }}</span>
                            <span><strong>{{ $member->user->prenom }} {{ $member->user->nom }}</strong><small>{{ $member->sportProfile?->objectif ?? 'Sport profile pending' }}</small></span>
                        </div>
                        <div class="progress-track-wrap">
                            <div class="progress-meta"><span>{{ $summary['completed_sessions'] }}/{{ $summary['total_sessions'] }} sessions</span><strong>{{ $summary['completion_rate'] }}%</strong></div>
                            <div class="progress-track" aria-label="{{ $summary['completion_rate'] }} percent complete"><span style="width: {{ $summary['completion_rate'] }}%"></span></div>
                        </div>
                        <span class="difficulty-label">{{ $summary['difficulty']['difficile'] }} hard</span>
                    </div>
                @empty
                    <div class="empty-state"><strong>No members assigned.</strong><span>Ask an administrator to assign a member to your coaching profile.</span></div>
                @endforelse
            </article>

            <aside class="panel review-panel">
                <div class="panel-heading"><div><p class="eyebrow">Programme queue</p><h2>Review next</h2></div></div>
                @forelse ($programmesForReview as $programme)
                    <div class="review-item">
                        <span class="status-pill {{ $programme->statut === 'valide' ? 'status-good' : 'status-review' }}">{{ ucfirst($programme->statut) }}</span>
                        <strong>{{ $programme->titre }}</strong>
                        <small>{{ $programme->member->user->prenom }} {{ $programme->member->user->nom }} / {{ ucfirst($programme->source) }}</small>
                    </div>
                @empty
                    <div class="empty-state compact"><strong>Your queue is clear.</strong><span>Draft and validated programmes will show up here.</span></div>
                @endforelse
            </aside>
        </section>
    </div>
</x-layouts.app>
