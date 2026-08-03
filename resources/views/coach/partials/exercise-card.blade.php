@php
    $exercise = $detail->exercise;
    $type = $exercise->type ?: 'musculation';
    $typeLabels = [
        'musculation' => 'Strength',
        'cardio' => 'Cardio',
        'mobilite' => 'Mobility',
    ];
    $hasMetrics = $detail->series || $detail->repetitions || $detail->repos || $detail->duree_cardio;
@endphp

<article class="programme-exercise programme-exercise--{{ $type }}">
    <div class="programme-exercise-media">
        <img src="{{ $exercise->resolvedImageUrl() }}" alt="{{ $exercise->nom }}" loading="lazy" decoding="async">
        <span class="exercise-type-badge">{{ $typeLabels[$type] ?? ucfirst($type) }}</span>
    </div>

    <div class="programme-exercise-body">
        <header class="programme-exercise-heading">
            <div>
                <p>{{ $exercise->groupe_musculaire }}</p>
                <h4>{{ $exercise->nom }}</h4>
            </div>
            <span class="exercise-order">{{ str_pad((string) $detail->ordre, 2, '0', STR_PAD_LEFT) }}</span>
        </header>

        @if ($hasMetrics)
            <dl class="exercise-metrics">
                @if ($detail->series)<div><dt>Sets</dt><dd>{{ $detail->series }}</dd></div>@endif
                @if ($detail->repetitions)<div><dt>Reps</dt><dd>{{ $detail->repetitions }}</dd></div>@endif
                @if ($detail->repos)<div><dt>Rest</dt><dd>{{ $detail->repos }}</dd></div>@endif
                @if ($detail->duree_cardio)<div><dt>Cardio</dt><dd>{{ $detail->duree_cardio }} min</dd></div>@endif
            </dl>
        @endif

        @if ($detail->notes)
            <p class="exercise-coach-note">{{ $detail->notes }}</p>
        @endif

        @if ($editable)
            <details class="exercise-editor">
                <summary>
                    <span>Edit prescription</span>
                    <span aria-hidden="true">+</span>
                </summary>
                <form method="POST" action="{{ route('coach.exercise-details.update', $detail) }}" class="exercise-editor-form">
                    @csrf
                    @method('PUT')
                    <label>Sets<input name="series" type="number" min="0" value="{{ old('series', $detail->series) }}"></label>
                    <label>Reps<input name="repetitions" type="number" min="0" value="{{ old('repetitions', $detail->repetitions) }}"></label>
                    <label>Rest<input name="repos" value="{{ old('repos', $detail->repos) }}" placeholder="60 seconds"></label>
                    <label>Cardio<input name="duree_cardio" type="number" min="0" value="{{ old('duree_cardio', $detail->duree_cardio) }}" placeholder="Minutes"></label>
                    <label class="exercise-editor-notes">Notes<textarea name="notes" rows="3">{{ old('notes', $detail->notes) }}</textarea></label>
                    <button class="button button-secondary button-small" type="submit">Save changes</button>
                </form>
            </details>
        @endif
    </div>
</article>
