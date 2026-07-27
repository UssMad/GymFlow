<x-layouts.app title="Admin dashboard | GymFlow" heading="Gym pulse">
    <div class="dashboard-wrap" id="overview">
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
            <article class="panel panel-wide" id="members">
                <div class="panel-heading">
                    <div><p class="eyebrow">Member directory</p><h2>Today’s check-in</h2></div>
                    <a class="button button-small button-primary" href="{{ route('admin.members.create') }}">Add member</a>
                </div>
                @if ($members->isEmpty())
                    <div class="empty-state"><strong>No members yet.</strong><span>Add members through the admin API to begin tracking attendance.</span></div>
                @else
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Member</th><th>Coach</th><th>Membership</th><th>Attendance</th><th></th></tr></thead>
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
                                        <td><a class="table-link" href="{{ route('admin.members.edit', $member) }}">Manage</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </article>

            <aside class="panel attendance-panel" id="attendance">
                <div class="panel-heading"><div><p class="eyebrow">Attendance log</p><h2>Attendance history</h2></div></div>
                <form class="attendance-filter" method="GET" action="{{ route('admin.dashboard') }}#attendance">
                    <label><span>Member</span><select name="membre_id"><option value="">All members</option>@foreach ($members as $member)<option value="{{ $member->id }}" @selected((string) ($filters['membre_id'] ?? '') === (string) $member->id)>{{ $member->user->prenom }} {{ $member->user->nom }}</option>@endforeach</select></label>
                    <label><span>From</span><input type="date" name="date_debut" value="{{ $filters['date_debut'] ?? '' }}"></label>
                    <label><span>To</span><input type="date" name="date_fin" value="{{ $filters['date_fin'] ?? '' }}"></label>
                    <div class="attendance-filter-actions"><a href="{{ route('admin.dashboard') }}#attendance">Clear</a><button class="button button-small button-secondary" type="submit">Filter</button></div>
                </form>
                @forelse ($attendanceHistory as $attendance)
                    <div class="attendance-item">
                        <span class="avatar">{{ strtoupper(substr($attendance->member->user->prenom, 0, 1)) }}{{ strtoupper(substr($attendance->member->user->nom, 0, 1)) }}</span>
                        <span><strong>{{ $attendance->member->user->prenom }} {{ $attendance->member->user->nom }}</strong><small>{{ $attendance->date_presence->format('d M Y') }} / {{ $attendance->enregistre_le?->format('H:i') ?? 'Checked in' }}</small></span>
                    </div>
                @empty
                    <div class="empty-state compact"><strong>No matching attendance.</strong><span>Use the member directory to record a check-in or adjust the filters.</span></div>
                @endforelse
            </aside>
        </section>

        <section class="panel coach-directory" id="coaches">
            <div class="panel-heading">
                <div><p class="eyebrow">Coaching team</p><h2>Coach accounts</h2></div>
                <a class="button button-small button-primary" href="{{ route('admin.coaches.create') }}">Add coach</a>
            </div>
            @if ($coaches->isEmpty())
                <div class="empty-state"><strong>No coaches yet.</strong><span>Create a coach account before assigning members to a coach.</span></div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Coach</th><th>Speciality</th><th>Availability</th><th>Assigned members</th><th></th></tr></thead>
                        <tbody>
                            @foreach ($coaches as $coach)
                                <tr>
                                    <td><strong>{{ $coach->user->prenom }} {{ $coach->user->nom }}</strong><small>{{ $coach->user->email }}</small></td>
                                    <td>{{ $coach->specialite ?: 'Not specified' }}</td>
                                    <td>{{ $coach->disponibilite ?: 'Not specified' }}</td>
                                    <td>{{ $coach->members_count }}</td>
                                    <td><a class="table-link" href="{{ route('admin.coaches.edit', $coach) }}">Manage</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-layouts.app>
