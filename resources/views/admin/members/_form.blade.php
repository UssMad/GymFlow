<div class="form-grid">
    <label><span>First name</span><input name="prenom" value="{{ old('prenom', $member?->user->prenom) }}" required></label>
    <label><span>Last name</span><input name="nom" value="{{ old('nom', $member?->user->nom) }}" required></label>
    <label class="form-span-2"><span>Email address</span><input type="email" name="email" value="{{ old('email', $member?->user->email) }}" required></label>
    <label><span>{{ $member ? 'New password (optional)' : 'Temporary password' }}</span><input type="password" name="password" {{ $member ? '' : 'required' }} minlength="12"></label>
    <label><span>Confirm password</span><input type="password" name="password_confirmation" {{ $member ? '' : 'required' }} minlength="12"></label>
    <label><span>Coach</span><select name="coach_id"><option value="">Not assigned</option>@foreach ($coaches as $coach)<option value="{{ $coach->id }}" @selected((string) old('coach_id', $member?->coach_id) === (string) $coach->id)>{{ $coach->user->prenom }} {{ $coach->user->nom }} / {{ $coach->specialite }}</option>@endforeach</select></label>
    <label><span>Membership status</span><select name="statut_abonnement" required>@foreach (['actif' => 'Active', 'expire' => 'Expired', 'suspendu' => 'Suspended'] as $value => $label)<option value="{{ $value }}" @selected(old('statut_abonnement', $member?->statut_abonnement ?? 'actif') === $value)>{{ $label }}</option>@endforeach</select></label>
    <label><span>Registration date</span><input type="date" name="date_inscription" value="{{ old('date_inscription', $member?->date_inscription?->toDateString() ?? today()->toDateString()) }}" required></label>
</div>
