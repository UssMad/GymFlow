<div class="form-grid">
    <label><span>First name</span><input name="prenom" value="{{ old('prenom', $coach?->user->prenom) }}" required></label>
    <label><span>Last name</span><input name="nom" value="{{ old('nom', $coach?->user->nom) }}" required></label>
    <label class="form-span-2"><span>Email address</span><input type="email" name="email" value="{{ old('email', $coach?->user->email) }}" required></label>
    <label><span>{{ $coach ? 'New password (optional)' : 'Temporary password' }}</span><input type="password" name="password" {{ $coach ? '' : 'required' }} minlength="12"></label>
    <label><span>Confirm password</span><input type="password" name="password_confirmation" {{ $coach ? '' : 'required' }} minlength="12"></label>
    <label><span>Speciality</span><input name="specialite" value="{{ old('specialite', $coach?->specialite) }}" placeholder="e.g. Strength training"></label>
    <label><span>Availability</span><input name="disponibilite" value="{{ old('disponibilite', $coach?->disponibilite) }}" placeholder="e.g. Monday to Friday"></label>
</div>
