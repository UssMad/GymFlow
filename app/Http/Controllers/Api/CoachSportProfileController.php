<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SportProfileResource;
use App\Models\Member;
use Illuminate\Http\Request;

class CoachSportProfileController extends Controller
{
    public function show(Request $request, Member $member): SportProfileResource
    {
        $this->ensureCoachManagesMember($request, $member);

        return new SportProfileResource($member->sportProfile()->firstOrFail());
    }

    private function ensureCoachManagesMember(Request $request, Member $member): void
    {
        $coach = $request->user()->coach;

        abort_unless($coach && $member->coach_id === $coach->id, 403);
    }
}
