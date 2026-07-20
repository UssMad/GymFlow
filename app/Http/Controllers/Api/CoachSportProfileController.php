<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateSportProfileRequest;
use App\Http\Resources\SportProfileResource;
use App\Models\Member;
use App\Models\SportProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachSportProfileController extends Controller
{
    public function show(Request $request, Member $member): SportProfileResource
    {
        $this->ensureCoachManagesMember($request, $member);

        return new SportProfileResource($member->sportProfile()->firstOrFail());
    }

    public function update(UpdateSportProfileRequest $request, Member $member): JsonResponse
    {
        $this->ensureCoachManagesMember($request, $member);

        $profile = SportProfile::query()->updateOrCreate(
            ['membre_id' => $member->id],
            $request->validated(),
        );

        return (new SportProfileResource($profile))
            ->additional(['message' => 'Sport profile saved successfully.'])
            ->response();
    }

    private function ensureCoachManagesMember(Request $request, Member $member): void
    {
        $coach = $request->user()->coach;

        abort_unless($coach && $member->coach_id === $coach->id, 403);
    }
}
