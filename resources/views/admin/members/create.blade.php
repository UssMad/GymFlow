<x-layouts.app title="Add member | GymFlow" heading="Add member">
    <div class="dashboard-wrap narrow-wrap">
        <section class="page-intro"><p class="eyebrow">Member management</p><h2>Create a member account</h2><p>Members can sign in immediately after you create their account.</p></section>
        <form class="panel management-form" method="POST" action="{{ route('admin.members.store') }}">
            @csrf
            @include('admin.members._form', ['member' => null])
            <div class="form-actions"><a class="button button-secondary" href="{{ route('admin.dashboard') }}#members">Cancel</a><button class="button button-primary" type="submit">Create member</button></div>
        </form>
    </div>
</x-layouts.app>
