<x-layouts.app title="Manage member | GymFlow" heading="Manage member">
    <div class="dashboard-wrap narrow-wrap">
        <section class="page-intro"><p class="eyebrow">Member management</p><h2>{{ $member->user->prenom }} {{ $member->user->nom }}</h2><p>Update account details, assignment, and membership status.</p></section>
        <form class="panel management-form" method="POST" action="{{ route('admin.members.update', $member) }}">
            @csrf @method('PUT')
            @include('admin.members._form', ['member' => $member])
            <div class="form-actions"><a class="button button-secondary" href="{{ route('admin.dashboard') }}#members">Back to members</a><button class="button button-primary" type="submit">Save changes</button></div>
        </form>

        <section class="subscription-grid" id="subscriptions">
            <article class="panel management-form">
                <div class="panel-heading section-heading"><div><p class="eyebrow">Membership</p><h2>Assign subscription</h2></div></div>
                @if ($subscriptionPlans->isEmpty())
                    <div class="empty-state compact"><strong>No subscription plans yet.</strong><span>Create a plan first using the form beside this panel.</span></div>
                @else
                    <form method="POST" action="{{ route('admin.members.subscriptions.store', $member) }}" class="form-grid compact-form">
                        @csrf
                        <label class="form-span-2"><span>Subscription plan</span><select name="subscription_plan_id" required><option value="">Choose a plan</option>@foreach ($subscriptionPlans as $plan)<option value="{{ $plan->id }}">{{ $plan->nom }} / {{ $plan->duree_jours }} days</option>@endforeach</select></label>
                        <label><span>Start date</span><input type="date" name="date_debut" value="{{ today()->toDateString() }}" required></label>
                        <label><span>End date</span><input type="date" name="date_fin" required></label>
                        <div class="form-submit-row form-span-2"><button class="button button-primary" type="submit">Assign subscription</button></div>
                    </form>
                @endif
                <div class="subscription-history">
                    <p class="eyebrow">History</p>
                    @forelse ($member->subscriptions->sortByDesc('date_fin') as $subscription)
                        <div class="subscription-item"><span><strong>{{ $subscription->subscriptionPlan->nom }}</strong><small>{{ $subscription->date_debut->format('d M Y') }} to {{ $subscription->date_fin->format('d M Y') }}</small></span><span class="status-pill {{ $subscription->resolvedStatus() === 'actif' ? 'status-good' : 'status-muted' }}">{{ ucfirst($subscription->resolvedStatus()) }}</span></div>
                    @empty
                        <p class="muted-copy">No subscriptions have been assigned.</p>
                    @endforelse
                </div>
            </article>

            <article class="panel management-form">
                <div class="panel-heading section-heading"><div><p class="eyebrow">Plans</p><h2>Create a plan</h2></div></div>
                <form method="POST" action="{{ route('admin.subscription-plans.store') }}" class="form-stack plan-form">
                    @csrf
                    <label><span>Plan name</span><input name="nom" placeholder="Monthly" required></label>
                    <label><span>Duration in days</span><input type="number" min="1" name="duree_jours" placeholder="30" required></label>
                    <label><span>Description (optional)</span><input name="description" placeholder="Access to gym facilities"></label>
                    <button class="button button-secondary" type="submit">Create plan</button>
                </form>
            </article>
        </section>
    </div>
</x-layouts.app>
