<x-layouts.app title="Admin dashboard | GymFlow" heading="Gym pulse">
    <div class="dashboard-wrap">
        <section class="dashboard-intro">
            <div>
                <p class="eyebrow">Today / {{ now()->format('d M Y') }}</p>
                <h2>Keep the floor moving.</h2>
                <p>Membership activity and today’s attendance, in one place.</p>
            </div>
            <div class="signal-label"><span></span> Live attendance</div>
        </section>

        <section class="stat-grid" aria-label="Gym summary">
            <article class="stat-card"><span>Total members</span><strong>{{ $stats['members'] }}</strong><small>Registered in GymFlow</small></article>
            <article class="stat-card stat-teal"><span>Active members</span><strong>{{ $stats['activeMembers'] }}</strong><small>With an active membership</small></article>
            <article class="stat-card stat-coral"><span>Checked in</span><strong>{{ $stats['checkedIn'] }}</strong><small>Present today</small></article>
            <article class="stat-card stat-gold"><span>Coaches</span><strong>{{ $stats['coaches'] }}</strong><small>Available to members</small></article>
        </section>

        <section class="content-grid admin-grid">
            <article class="panel panel-wide">
                <div class="panel-heading">
                    <div><p class="eyebrow">Member directory</p><h2>Today’s check-in</h2></div>
                    <span class="count-label">{{ $members->count() }} members</span>
                </div>
                @if ($members->isEmpty())
                    <div class="empty-state"><strong>No members yet.</strong><span>Add members through the admin API to begin tracking attendance.</span></div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Member</th><th>Coach</th><th>Membership</th><th>Attendance</th></tr></thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr>
                                        <td><strong>{{ $member->user->prenom }} {{ $member->user->nom }}</strong><small>{{ $member->user->email }}</small></td>
                                        <td>{{ $member->coach?->user ? $member->coach->user->prenom.' '.$member->coach->user->nom : 'Not assigned' }}</td>
                                        <td><span class="status-pill {{ $member->statut_abonnement === 'actif' ? 'status-good' : 'status-muted' }}">{{ ucfirst($member->statut_abonnement) }}</span></td>
                                        <td>
                                            @if ($member->attended_today)
                                                <span class="checked-in">Checked in</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.attendance.store', $member) }}">
                                                    @csrf
                                                    <button class="button button-small button-primary" type="submit">Check in</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <aside class="panel attendance-panel">
                <div class="panel-heading"><div><p class="eyebrow">Attendance log</p><h2>On the floor</h2></div></div>
                @forelse ($todayAttendances as $attendance)
                    <div class="attendance-item">
                        <span class="avatar">{{ strtoupper(substr($attendance->member->user->prenom, 0, 1)) }}{{ strtoupper(substr($attendance->member->user->nom, 0, 1)) }}</span>
                        <span><strong>{{ $attendance->member->user->prenom }} {{ $attendance->member->user->nom }}</strong><small>{{ $attendance->enregistre_le?->format('H:i') ?? 'Checked in' }}</small></span>
                    </div>
                @empty
                    <div class="empty-state compact"><strong>No check-ins yet.</strong><span>Use the member directory to record the first arrival.</span></div>
                @endforelse
            </aside>
        </section>
    </div>
</x-layouts.app>
