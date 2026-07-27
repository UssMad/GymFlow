<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CoachMemberProgressResource;
use App\Models\Member;
use App\Services\CoachMemberProgressService;
use Illuminate\Http\Request;

class CoachMemberProgressController extends Controller
{
    public function show(Request $request, Member $member, CoachMemberProgressService $progress): CoachMemberProgressResource
    {
        $coach = $request->user()->coach;
        abort_unless($coach && $member->coach_id === $coach->id, 403);

        return new CoachMemberProgressResource($member->load('user'), $progress->summary($member));
    }
}
