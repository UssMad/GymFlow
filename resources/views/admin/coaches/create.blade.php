<x-layouts.app title="Add coach | GymFlow" heading="Add coach">
    <div class="dashboard-wrap narrow-wrap">
        <section class="page-intro"><p class="eyebrow">Coach management</p><h2>Create a coach account</h2><p>The coach will use these credentials to access their member workspace and programme tools.</p></section>
        <form method="POST" action="{{ route('admin.coaches.store') }}" class="panel management-form">@csrf
            @include('admin.coaches._form', ['coach' => null])
            <div class="form-actions"><a class="button button-secondary" href="{{ route('admin.dashboard') }}#coaches">Cancel</a><button class="button button-primary" type="submit">Create coach</button></div>
        </form>
    </div>
</x-layouts.app>
