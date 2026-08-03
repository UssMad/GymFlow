<x-layouts.app title="Programmes | GymFlow" heading="Programme review">
    <div class="dashboard-wrap">
        <section class="dashboard-intro">
            <div>
                <p class="eyebrow">Coaching queue</p>
                <h2>Review every programme before it goes live.</h2>
                <p>Drafts require validation. Validated programmes can then be published for the member.</p>
            </div>
            <a class="button button-secondary" href="{{ route('coach.members.index') }}">View members</a>
        </section>

        <section class="programme-workspace">
            @forelse ($programmes as $programme)
                <article class="programme-review-card">
                    <div class="programme-review-heading">
                        <div>
                            <span class="status-pill {{ $programme->statut === 'publie' ? 'status-good' : 'status-review' }}">{{ ucfirst($programme->statut) }}</span>
                            <h2>{{ $programme->titre }}</h2>
                            <p class="muted-copy">{{ $programme->member->user->prenom }} {{ $programme->member->user->nom }} / {{ ucfirst($programme->source) }} / {{ $programme->sessions->count() }} sessions</p>
                        </div>
                        <a class="table-link" href="{{ route('coach.members.show', $programme->member) }}">Open workspace</a>
                    </div>
                    <div class="programme-sessions">
                        @foreach ($programme->sessions->sortBy('ordre') as $session)
                            <section class="programme-session">
                                <header class="programme-session-header">
                                    <span class="programme-session-index">{{ str_pad((string) $session->ordre, 2, '0', STR_PAD_LEFT) }}</span>
                                    <div>
                                        <p>Training day</p>
                                        <h3>{{ ucfirst($session->jour) }}</h3>
                                        @if ($session->notes)<span>{{ $session->notes }}</span>@endif
                                    </div>
                                </header>
                                <div class="programme-exercise-grid">
                                    @forelse ($session->exerciseDetails->sortBy('ordre') as $detail)
                                        @include('coach.partials.exercise-card', ['detail' => $detail, 'editable' => false])
                                    @empty
                                        <p class="programme-session-empty">Exercises pending.</p>
                                    @endforelse
                                </div>
                            </section>
                        @endforeach
                    </div>
                </article>
            @empty
                <div class="empty-state panel"><strong>No programmes yet.</strong><span>Open a member workspace, complete their sport profile, and generate a draft.</span></div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
