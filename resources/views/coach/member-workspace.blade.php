<x-layouts.app title="{{ $member->user->prenom }} | GymFlow" heading="Member workspace">
    <div class="dashboard-wrap narrow-wrap">
        <section class="dashboard-intro">
            <div>
                <p class="eyebrow">Coaching workspace</p>
                <h2>{{ $member->user->prenom }} {{ $member->user->nom }}</h2>
                <p>Build the context first, then generate and review a programme before it reaches the member.</p>
            </div>
            <a class="button button-secondary" href="{{ route('coach.dashboard') }}#members">Back to members</a>
        </section>

        <section class="stat-grid coach-member-stats" aria-label="Member progress">
            <article class="stat-card"><span>Sessions</span><strong>{{ $progress['completed_sessions'] }}/{{ $progress['total_sessions'] }}</strong><small>Completed workouts</small></article>
            <article class="stat-card stat-teal"><span>Completion</span><strong>{{ $progress['completion_rate'] }}%</strong><small>Across current history</small></article>
            <article class="stat-card stat-coral"><span>Hard sessions</span><strong>{{ $progress['difficulty']['difficile'] }}</strong><small>Useful coaching signal</small></article>
            <article class="stat-card stat-gold"><span>Profile</span><strong>{{ $member->sportProfile ? 'Ready' : 'Needed' }}</strong><small>Required for AI generation</small></article>
        </section>

        <section class="panel management-form profile-panel" id="profile">
            @if ($editingProfile)
                <div class="section-heading profile-panel-heading">
                    <div>
                        <p class="eyebrow">{{ $member->sportProfile ? 'Update profile' : 'Step 1' }}</p>
                        <h2>{{ $member->sportProfile ? 'Edit sport profile' : 'Create sport profile' }}</h2>
                        <p>Use the member's real context. This is the information sent to the programme generator.</p>
                    </div>
                    @if ($member->sportProfile)
                        <a class="button button-secondary button-small" href="{{ route('coach.members.show', $member) }}#profile">Cancel</a>
                    @endif
                </div>
                <form method="POST" action="{{ route('coach.members.sport-profile.update', $member) }}" class="form-grid">
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
                <div class="section-heading profile-panel-heading">
                    <div>
                        <p class="eyebrow">Step 1 complete</p>
                        <h2>Sport profile summary</h2>
                        <p>This saved context will be used when GymFlow prepares the AI programme draft.</p>
                    </div>
                    <a class="button button-secondary button-small" href="{{ route('coach.members.show', ['member' => $member, 'edit' => 'profile']) }}#profile">Edit sport profile</a>
                </div>

                <div class="profile-overview" aria-label="Sport profile summary">
                    <div class="profile-goal"><span>Goal</span><strong>{{ $profile->objectif }}</strong></div>
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

        <section class="panel generation-panel" id="generation">
            <div><p class="eyebrow">Step 2</p><h2>AI programme draft</h2><p>GymFlow queues a draft from the saved profile. The coach always reviews it before publishing.</p></div>
            <form method="POST" action="{{ route('coach.members.ai-generations.store', $member) }}">@csrf<button class="button button-primary" type="submit" @disabled(! $member->sportProfile)>Generate programme</button></form>
        </section>

        @if ($member->aiGenerations->isNotEmpty())
            <section class="generation-history"><p class="eyebrow">Generation history</p><div class="generation-list">@foreach ($member->aiGenerations as $generation)<span class="generation-status generation-status-{{ $generation->statut }}"><strong>{{ ['en_attente' => 'Queued', 'terminee' => 'Generated', 'echec' => 'Generation failed'][$generation->statut] ?? ucfirst(str_replace('_', ' ', $generation->statut)) }}</strong><small>{{ $generation->generee_le?->format('d M, H:i') }}</small>@if ($generation->statut === 'echec')<small>Provider unavailable or quota reached. Try again later.</small>@endif</span>@endforeach</div></section>
        @endif

        <section class="programme-workspace" id="programmes">
            <div class="section-heading"><p class="eyebrow">Step 3</p><h2>Review and publish</h2><p>A draft can be renamed and scheduled before validation. Published programmes become visible to the member.</p></div>
            @forelse ($member->programmes as $programme)
                <article class="panel programme-review-card">
                    <div class="panel-heading"><div><span class="status-pill {{ $programme->statut === 'publie' ? 'status-good' : 'status-review' }}">{{ ucfirst($programme->statut) }}</span><h2>{{ $programme->titre }}</h2><p class="muted-copy">{{ ucfirst($programme->source) }} programme / {{ $programme->sessions->count() }} sessions</p></div></div>
                    @if ($programme->statut === 'brouillon')
                        <form method="POST" action="{{ route('coach.programmes.update', $programme) }}" class="form-grid programme-meta-form">@csrf @method('PUT')
                            <label class="form-span-2">Programme name<input name="titre" value="{{ old('titre', $programme->titre) }}" required></label>
                            <label>Starts<input name="date_debut" type="date" value="{{ old('date_debut', $programme->date_debut?->format('Y-m-d')) }}"></label>
                            <label>Ends<input name="date_fin" type="date" value="{{ old('date_fin', $programme->date_fin?->format('Y-m-d')) }}"></label>
                            <div class="form-actions form-span-2"><button class="button button-secondary" type="submit">Save programme details</button></div>
                        </form>
                    @endif
                    <div class="programme-session-preview">
                        @foreach ($programme->sessions->sortBy('ordre') as $session)
                            <div>
                                <strong>{{ $session->jour }}</strong>
                                <div class="exercise-thumbnail-list">
                                    @foreach ($session->exerciseDetails->sortBy('ordre') as $detail)
                                        <div class="exercise-thumbnail-item">
                                            <img src="{{ $detail->exercise->resolvedImageUrl() }}" alt="{{ $detail->exercise->nom }}" loading="lazy">
                                            <span>{{ $detail->exercise->nom }}</span>
                                            @if ($programme->statut === 'brouillon')
                                                <details class="exercise-edit-details">
                                                    <summary>Edit prescription</summary>
                                                    <form method="POST" action="{{ route('coach.exercise-details.update', $detail) }}" class="exercise-edit-form">
                                                        @csrf @method('PUT')
                                                        <label>Sets<input name="series" type="number" min="0" value="{{ old('series', $detail->series) }}"></label>
                                                        <label>Reps<input name="repetitions" type="number" min="0" value="{{ old('repetitions', $detail->repetitions) }}"></label>
                                                        <label>Rest<input name="repos" value="{{ old('repos', $detail->repos) }}" placeholder="e.g. 60 seconds"></label>
                                                        <label>Cardio (min)<input name="duree_cardio" type="number" min="0" value="{{ old('duree_cardio', $detail->duree_cardio) }}"></label>
                                                        <label class="exercise-edit-notes">Notes<textarea name="notes" rows="2">{{ old('notes', $detail->notes) }}</textarea></label>
                                                        <button class="button button-secondary button-small" type="submit">Save exercise</button>
                                                    </form>
                                                </details>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
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
