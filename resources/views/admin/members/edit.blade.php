<x-layouts.app title="Manage member | GymFlow" heading="Manage member">
    <div class="dashboard-wrap narrow-wrap">
        <section class="page-intro"><p class="eyebrow">Member management</p><h2>{{ $member->user->prenom }} {{ $member->user->nom }}</h2><p>Update account details, assignment, and membership status.</p></section>
        <form class="panel management-form" method="POST" action="{{ route('admin.members.update', $member) }}">
            @csrf @method('PUT')
            @include('admin.members._form', ['member' => $member])
            <div class="form-actions"><a class="button button-secondary" href="{{ route('admin.dashboard') }}#members">Back to members</a><button class="button button-primary" type="submit">Save changes</button></div>
        </form>
    </div>
</x-layouts.app>
