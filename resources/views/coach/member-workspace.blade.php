<x-layouts.app title="{{ $member->user->prenom }} | GymFlow" heading="Member workspace">
    <div class="dashboard-wrap narrow-wrap">
        <section class="dashboard-intro member-workspace-intro">
            <div>
                <p class="eyebrow">Coaching workspace</p>
                <h2>{{ $member->user->prenom }} {{ $member->user->nom }}</h2>
                <p>Build the context first, then generate and review a programme before it reaches the member.</p>
            </div>
            <a class="button button-secondary" href="{{ route('coach.dashboard') }}#members">Back to members</a>
        </section>

        <section class="member-signal-strip" aria-label="Member progress">
            <article class="member-signal">
                <span class="member-signal-index">01</span>
                <div><small>Sessions</small><strong>{{ $progress['completed_sessions'] }}/{{ $progress['total_sessions'] }}</strong><p>Workouts completed</p></div>
            </article>
            <article class="member-signal member-signal--teal">
                <span class="member-signal-index">02</span>
                <div><small>Completion</small><strong>{{ $progress['completion_rate'] }}%</strong><p>Current history</p></div>
            </article>
            <article class="member-signal member-signal--coral">
                <span class="member-signal-index">03</span>
                <div><small>Hard sessions</small><strong>{{ $progress['difficulty']['difficile'] }}</strong><p>Coaching signal</p></div>
            </article>
            <article class="member-signal member-signal--gold">
                <span class="member-signal-index">04</span>
                <div><small>Sport profile</small><strong>{{ $member->sportProfile ? 'Ready' : 'Needed' }}</strong><p>AI generation context</p></div>
            </article>
        </section>

        <section class="member-profile-panel" id="profile">
            @if ($editingProfile)
                <div class="member-panel-heading profile-panel-heading">
                    <div>
                        <p class="eyebrow">{{ $member->sportProfile ? 'Update profile' : 'Step 1' }}</p>
                        <h2>{{ $member->sportProfile ? 'Edit sport profile' : 'Create sport profile' }}</h2>
                        <p>Use the member's real context. This is the information sent to the programme generator.</p>
                    </div>
                    @if ($member->sportProfile)
                        <a class="button button-secondary button-small" href="{{ route('coach.members.show', $member) }}#profile">Cancel</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('coach.members.sport-profile.update', $member) }}" class="form-grid profile-edit-form">
                    @csrf @method('PUT')
                    <label>Goal<input name="objectif" value="{{ old('objectif', $member->sportProfile?->objectif) }}" required></label>
                    <label>Level<select name="niveau" required><option value="">Choose level</option>@foreach (['debutant' => 'Beginner', 'intermediaire' => 'Intermediate', 'avance' => 'Advanced'] as $value => $label)<option value="{{ $value }}" @selected(old('niveau', $member->sportProfile?->niveau) === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label>Weight (kg)<input name="poids" type="number" min="0" step="0.01" value="{{ old('poids', $member->sportProfile?->poids) }}"></label>
                    <label>Height (cm)<input name="taille" type="number" min="0" step="0.01" value="{{ old('taille', $member->sportProfile?->taille) }}"></label>
                    <label class="form-span-2">Injuries or constraints<textarea name="blessures" rows="3" placeholder="e.g. sensitive knee, no jumping">{{ old('blessures', $member->sportProfile?->blessures) }}</textarea></label>
                    <fieldset class="form-span-2 choice-field"><legend>Available days</legend><div class="choice-list">@foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)<label><input type="checkbox" name="jours_disponibles[]" value="{{ strtolower($day) }}" @checked(in_array(strtolower($day), old('jours_disponibles', $member->sportProfile?->jours_disponibles ?? [])))><span>{{ $day }}</span></label>@endforeach</div></fieldset>
                    <label class="form-span-2">Preferences<textarea name="preferences" rows="3" required placeholder="Equipment, cardio preference, exercises to avoid...">{{ old('preferences', is_array($member->sportProfile?->preferences) ? implode(', ', $member->sportProfile->preferences) : $member->sportProfile?->preferences) }}</textarea></label>
                    <div class="form-actions form-span-2"><button class="button button-primary" type="submit">Save sport profile</button></div>
                </form>
            @else
                @php
                    $profile = $member->sportProfile;
                    $levelLabels = ['debutant' => 'Beginner', 'intermediaire' => 'Intermediate', 'avance' => 'Advanced'];
                    $displayNumber = fn ($value) => $value === null ? 'Not provided' : rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
                @endphp
                <div class="member-panel-heading profile-panel-heading">
                    <div>
                        <p class="eyebrow">Step 1 / Profile complete</p>
                        <h2>Sport profile summary</h2>
                        <p>This saved context will be used when GymFlow prepares the AI programme draft.</p>
                    </div>
                    <a class="button button-secondary button-small" href="{{ route('coach.members.show', ['member' => $member, 'edit' => 'profile']) }}#profile">Edit sport profile</a>
                </div>

                <div class="profile-overview" aria-label="Sport profile summary">
                    <div class="profile-goal"><span>Primary goal</span><strong>{{ $profile->objectif }}</strong><small>Programme direction</small></div>
                    <dl class="profile-metrics">
                        <div><dt>Level</dt><dd>{{ $levelLabels[$profile->niveau] }}</dd></div>
                        <div><dt>Weight</dt><dd>{{ $displayNumber($profile->poids) }}{{ $profile->poids !== null ? ' kg' : '' }}</dd></div>
                        <div><dt>Height</dt><dd>{{ $displayNumber($profile->taille) }}{{ $profile->taille !== null ? ' cm' : '' }}</dd></div>
                    </dl>
                </div>

                <dl class="profile-details">
                    <div>
                        <dt>Available days</dt>
                        <dd class="day-tags">@foreach ($profile->jours_disponibles as $day)<span>{{ ucfirst($day) }}</span>@endforeach</dd>
                    </div>
                    <div>
                        <dt>Injuries or constraints</dt>
                        <dd>{{ $profile->blessures ?: 'None recorded' }}</dd>
                    </div>
                    <div class="profile-detail-wide">
                        <dt>Preferences</dt>
                        <dd>{{ is_array($profile->preferences) ? implode(', ', $profile->preferences) : $profile->preferences }}</dd>
                    </div>
                </dl>
            @endif
        </section>

        <section class="generation-panel" id="generation">
            <span class="workflow-step-number">02</span>
            <div><p class="eyebrow">Programme generation</p><h2>AI programme draft</h2><p>GymFlow queues a draft from the saved profile. The coach always reviews it before publishing.</p></div>
            <form method="POST" action="{{ route('coach.members.ai-generations.store', $member) }}">@csrf<button class="button button-primary" type="submit" @disabled(! $member->sportProfile)>Generate programme</button></form>
        </section>

        @if ($member->aiGenerations->isNotEmpty())
            <section class="generation-history" aria-labelledby="generation-history-title">
                <div class="generation-history-heading">
                    <div><p class="eyebrow">AI activity</p><h2 id="generation-history-title">Generation history</h2></div>
                    <span>{{ $member->aiGenerations->count() }} attempts</span>
                </div>
                <ol class="generation-timeline">
                    @foreach ($member->aiGenerations as $generation)
                        @php
                            $generationLabels = ['en_attente' => 'Queued', 'terminee' => 'Generated', 'echec' => 'Generation failed'];
                            $generationDescriptions = [
                                'en_attente' => 'Waiting for the AI worker to prepare a new draft.',
                                'terminee' => 'Draft created and ready for coach review.',
                                'echec' => 'Provider unavailable or quota reached. Try again later.',
                            ];
                        @endphp
                        <li class="generation-event generation-event--{{ $generation->statut }}">
                            <span class="generation-event-marker" aria-hidden="true"></span>
                            <div class="generation-event-copy">
                                <div>
                                    <strong>{{ $generationLabels[$generation->statut] ?? ucfirst(str_replace('_', ' ', $generation->statut)) }}</strong>
                                    @if ($loop->first)<span class="generation-latest">Latest</span>@endif
                                </div>
                                <p>{{ $generationDescriptions[$generation->statut] ?? 'Generation status updated.' }}</p>
                            </div>
                            <time datetime="{{ ($generation->generee_le ?? $generation->created_at)?->toIso8601String() }}">
                                {{ ($generation->generee_le ?? $generation->created_at)?->format('d M') }}
                                <small>{{ ($generation->generee_le ?? $generation->created_at)?->format('H:i') }}</small>
                            </time>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        <section class="programme-workspace" id="programmes">
            <div class="section-heading programme-section-heading"><p class="eyebrow">Step 3 / Coach review</p><h2>Review and publish</h2><p>A draft can be renamed and scheduled before validation. Published programmes become visible to the member.</p></div>
            @forelse ($member->programmes as $programme)
                <article class="programme-review-card">
                    <div class="programme-review-heading">
                        <div><span class="status-pill {{ $programme->statut === 'publie' ? 'status-good' : 'status-review' }}">{{ ucfirst($programme->statut) }}</span><h2>{{ $programme->titre }}</h2></div>
                        <div class="programme-review-facts"><span>{{ $programme->sessions->count() }} {{ Str::plural('session', $programme->sessions->count()) }}</span><span>{{ ucfirst($programme->source) }} source</span></div>
                    </div>
                    @if ($programme->statut === 'brouillon')
                        <form method="POST" action="{{ route('coach.programmes.update', $programme) }}" class="programme-settings-form">@csrf @method('PUT')
                            <label class="programme-title-field">Programme name<input name="titre" value="{{ old('titre', $programme->titre) }}" required></label>
                            <label>Starts<input name="date_debut" type="date" value="{{ old('date_debut', $programme->date_debut?->format('Y-m-d')) }}"></label>
                            <label>Ends<input name="date_fin" type="date" value="{{ old('date_fin', $programme->date_fin?->format('Y-m-d')) }}"></label>
                            <button class="button button-secondary" type="submit">Save details</button>
                        </form>
                    @endif
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
                                    @foreach ($session->exerciseDetails->sortBy('ordre') as $detail)
                                        @include('coach.partials.exercise-card', [
                                            'detail' => $detail,
                                            'editable' => $programme->statut === 'brouillon',
                                        ])
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    </div>
                    <div class="review-actions">
                        @if ($programme->statut === 'brouillon')<form method="POST" action="{{ route('coach.programmes.validate', $programme) }}">@csrf<button class="button button-primary" type="submit">Validate programme</button></form>@endif
                        @if ($programme->statut === 'valide')<form method="POST" action="{{ route('coach.programmes.publish', $programme) }}">@csrf<button class="button button-primary" type="submit">Publish for member</button></form>@endif
                        <form method="POST" action="{{ route('coach.programmes.destroy', $programme) }}">@csrf @method('DELETE')<button class="button button-secondary" type="submit">Delete programme</button></form>
                    </div>
                </article>
            @empty
                <div class="empty-state panel"><strong>No programme yet.</strong><span>Save the sport profile, then generate an AI draft for review.</span></div>
            @endforelse
        </section>
    </div>
</x-layouts.app>
