<x-layouts.app title="Manage coach | GymFlow" heading="Manage coach">
    <div class="dashboard-wrap narrow-wrap">
        <section class="page-intro"><p class="eyebrow">Coach management</p><h2>{{ $coach->user->prenom }} {{ $coach->user->nom }}</h2><p>Update account details, login credentials, speciality, and availability.</p></section>
        <form method="POST" action="{{ route('admin.coaches.update', $coach) }}" class="panel management-form">@csrf @method('PUT')
            @include('admin.coaches._form', ['coach' => $coach])
            <div class="form-actions"><a class="button button-secondary" href="{{ route('admin.dashboard') }}#coaches">Back</a><button class="button button-primary" type="submit">Save coach</button></div>
        </form>
    </div>
</x-layouts.app>
