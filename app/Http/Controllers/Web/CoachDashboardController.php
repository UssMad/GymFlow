<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateWorkoutProgrammeDraft;
use App\Models\AiGeneration;
use App\Models\Member;
use App\Models\Programme;
use App\Models\SportProfile;
use App\Services\CoachMemberProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachDashboardController extends Controller
{
    public function index(Request $request, CoachMemberProgressService $progress): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $members = Member::query()
            ->where('coach_id', $coach->id)
            ->with(['user', 'sportProfile', 'programmes.member.user' => fn ($query) => $query->latest('id')])
            ->get();

        $progressByMember = $members->mapWithKeys(
            fn (Member $member): array => [$member->id => $progress->summary($member)],
        );
        $programmesForReview = $members->flatMap->programmes
            ->whereIn('statut', ['brouillon', 'valide'])
            ->sortByDesc('updated_at')
            ->take(6);
        $averageCompletion = $members->isEmpty()
            ? 0
            : (int) round($progressByMember->avg('completion_rate'));

        return view('coach.dashboard', [
            'members' => $members,
            'progressByMember' => $progressByMember,
            'programmesForReview' => $programmesForReview,
            'stats' => [
                'members' => $members->count(),
                'completion' => $averageCompletion,
                'review' => $programmesForReview->count(),
                'profiles' => $members->filter->sportProfile->count(),
            ],
        ]);
    }

    public function showMember(Request $request, Member $member, CoachMemberProgressService $progress): View
    {
        $this->ensureCoachManagesMember($request, $member);

        $member->load([
            'user',
            'sportProfile',
            'aiGenerations' => fn ($query) => $query->latest('id')->take(4),
            'programmes' => fn ($query) => $query->latest('id')->with('sessions.exerciseDetails.exercise'),
        ]);

        return view('coach.member-workspace', [
            'member' => $member,
            'progress' => $progress->summary($member),
            'editingProfile' => $request->query('edit') === 'profile' || ! $member->sportProfile,
        ]);
    }

    public function members(Request $request, CoachMemberProgressService $progress): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $members = Member::query()
            ->where('coach_id', $coach->id)
            ->with(['user', 'sportProfile'])
            ->orderBy('id')
            ->get();

        return view('coach.members', [
            'members' => $members,
            'progressByMember' => $members->mapWithKeys(
                fn (Member $member): array => [$member->id => $progress->summary($member)],
            ),
        ]);
    }

    public function programmes(Request $request): View
    {
        $coach = $request->user()->coach;
        abort_unless($coach, 403);

        $programmes = Programme::query()
            ->whereHas('member', fn ($query) => $query->where('coach_id', $coach->id))
            ->with(['member.user', 'sessions.exerciseDetails.exercise'])
            ->latest('updated_at')
            ->get();

        return view('coach.programmes', ['programmes' => $programmes]);
    }

    public function updateSportProfile(Request $request, Member $member): RedirectResponse
    {
        $this->ensureCoachManagesMember($request, $member);

        $data = $request->validate([
            'objectif' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'in:debutant,intermediaire,avance'],
            'poids' => ['nullable', 'numeric', 'between:0,999.99'],
            'taille' => ['nullable', 'numeric', 'between:0,999.99'],
            'blessures' => ['nullable', 'string', 'max:2000'],
            'jours_disponibles' => ['required', 'array', 'min:1'],
            'jours_disponibles.*' => ['string', 'max:30'],
            'preferences' => ['required', 'string', 'max:2000'],
        ]);

        SportProfile::query()->updateOrCreate(['membre_id' => $member->id], $data);

        return redirect()
            ->route('coach.members.show', $member)
            ->with('status', 'Sport profile saved.');
    }

    public function generateProgramme(Request $request, Member $member): RedirectResponse
    {
        $coach = $this->ensureCoachManagesMember($request, $member);
        $profile = $member->sportProfile;

        if (! $profile) {
            return back()->withErrors(['sport_profile' => 'Save the sport profile before generating a programme.']);
        }

        $generation = AiGeneration::query()->create([
            'membre_id' => $member->id,
            'demande_par_coach_id' => $coach->id,
            'statut' => 'en_attente',
            'contexte_utilise' => [
                'objectif' => $profile->objectif,
                'niveau' => $profile->niveau,
                'poids' => $profile->poids,
                'taille' => $profile->taille,
                'blessures' => $profile->blessures,
                'jours_disponibles' => $profile->jours_disponibles,
                'preferences' => $profile->preferences,
                'historique_programmes' => $member->programmes()->latest('id')->limit(3)->get(['titre', 'statut'])->all(),
                'coach_constraints' => [
                    'specialite' => $coach->specialite,
                    'disponibilite' => $coach->disponibilite,
                ],
            ],
            'generee_le' => now(),
        ]);

        GenerateWorkoutProgrammeDraft::dispatch($generation->id);

        return back()->with('status', 'AI programme generation has been queued for review.');
    }

    public function updateProgramme(Request $request, Programme $programme): RedirectResponse
    {
        $this->ensureCoachOwnsProgramme($request, $programme);
        abort_unless($programme->statut === 'brouillon', 422, 'Only draft programmes can be edited.');

        $data = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $programme->update($data);

        return back()->with('status', 'Programme details updated.');
    }

    public function validateProgramme(Request $request, Programme $programme): RedirectResponse
    {
        $coach = $this->ensureCoachOwnsProgramme($request, $programme);
        abort_unless($programme->statut === 'brouillon', 422, 'Only draft programmes can be validated.');

        $programme->update([
            'statut' => 'valide',
            'coach_validateur_id' => $coach->id,
            'date_validation' => now(),
        ]);

        return back()->with('status', 'Programme validated. It is ready to publish.');
    }

    public function publishProgramme(Request $request, Programme $programme): RedirectResponse
    {
        $this->ensureCoachOwnsProgramme($request, $programme);
        abort_unless($programme->statut === 'valide', 422, 'Only validated programmes can be published.');

        $programme->update(['statut' => 'publie']);

        return back()->with('status', 'Programme published for the member.');
    }

    private function ensureCoachManagesMember(Request $request, Member $member): \App\Models\Coach
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $member->coach_id === $coach->id, 403);

        return $coach;
    }

    private function ensureCoachOwnsProgramme(Request $request, Programme $programme): \App\Models\Coach
    {
        $programme->loadMissing('member');

        return $this->ensureCoachManagesMember($request, $programme->member);
    }
}
