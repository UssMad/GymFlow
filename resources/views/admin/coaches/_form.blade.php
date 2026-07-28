<div class="form-grid">
    <label><span>First name</span><input name="prenom" value="{{ old('prenom', $coach?->user->prenom) }}" required></label>
    <label><span>Last name</span><input name="nom" value="{{ old('nom', $coach?->user->nom) }}" required></label>
    <label class="form-span-2"><span>Email address</span><input type="email" name="email" value="{{ old('email', $coach?->user->email) }}" required></label>
    <label><span>{{ $coach ? 'New password (optional)' : 'Temporary password' }}</span><input type="password" name="password" {{ $coach ? '' : 'required' }} minlength="12"></label>
    <label><span>Confirm password</span><input type="password" name="password_confirmation" {{ $coach ? '' : 'required' }} minlength="12"></label>
    <label><span>Speciality</span><select name="specialite"><option value="">Choose a speciality</option>@foreach ($specialities as $speciality)<option value="{{ $speciality }}" @selected(old('specialite', $coach?->specialite) === $speciality)>{{ $speciality }}</option>@endforeach</select></label>
    <label><span>Availability</span><select name="disponibilite"><option value="">Choose availability</option>@foreach ($availabilities as $availability)<option value="{{ $availability }}" @selected(old('disponibilite', $coach?->disponibilite) === $availability)>{{ $availability }}</option>@endforeach</select></label>
</div>
