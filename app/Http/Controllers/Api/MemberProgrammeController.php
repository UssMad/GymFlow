<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgrammeResource;
use App\Models\Programme;
use Illuminate\Http\Request;

class MemberProgrammeController extends Controller
{
    public function current(Request $request): ProgrammeResource
    {
        $programme = $this->publishedProgrammes($request)
            ->where(fn ($query) => $query->whereNull('date_debut')->orWhere('date_debut', '<=', today()))
            ->where(fn ($query) => $query->whereNull('date_fin')->orWhere('date_fin', '>=', today()))
            ->orderByDesc('date_debut')
            ->latest('id')
            ->first();

        abort_unless($programme, 404, 'No published programme is currently available.');

        return new ProgrammeResource($programme);
    }

    public function show(Request $request, Programme $programme): ProgrammeResource
    {
        $member = $request->user()->member;
        abort_unless($member && $programme->membre_id === $member->id && $programme->statut === 'publie', 404);

        return new ProgrammeResource($programme->load('sessions.exerciseDetails.exercise'));
    }

    private function publishedProgrammes(Request $request)
    {
        $member = $request->user()->member;
        abort_unless($member, 404);

        return $member->programmes()
            ->where('statut', 'publie')
            ->with('sessions.exerciseDetails.exercise');
    }
}
