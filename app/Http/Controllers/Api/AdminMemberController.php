<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMemberRequest;
use App\Http\Requests\Api\UpdateMemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class AdminMemberController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $members = Member::query()
            ->with(['user', 'coach.user', 'sportProfile'])
            ->latest('id')
            ->paginate();

        return MemberResource::collection($members);
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = DB::transaction(function () use ($request): Member {
            $data = $request->validated();

            $user = User::query()->create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'member',
            ]);

            return Member::query()->create([
                'user_id' => $user->id,
                'coach_id' => $data['coach_id'] ?? null,
                'date_inscription' => $data['date_inscription'] ?? today(),
                'statut_abonnement' => $data['statut_abonnement'] ?? 'actif',
            ]);
        });

        return (new MemberResource($member->load(['user', 'coach.user', 'sportProfile'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Member $member): MemberResource
    {
        return new MemberResource($member->load(['user', 'coach.user', 'sportProfile']));
    }

    public function update(UpdateMemberRequest $request, Member $member): MemberResource
    {
        DB::transaction(function () use ($request, $member): void {
            $data = $request->validated();

            $userData = collect($data)->only(['nom', 'prenom', 'email', 'password'])->all();
            if ($userData !== []) {
                $member->user->update($userData);
            }

            $memberData = collect($data)->only(['coach_id', 'date_inscription', 'statut_abonnement'])->all();
            if ($memberData !== []) {
                $member->update($memberData);
            }
        });

        return new MemberResource($member->fresh()->load(['user', 'coach.user', 'sportProfile']));
    }
}
