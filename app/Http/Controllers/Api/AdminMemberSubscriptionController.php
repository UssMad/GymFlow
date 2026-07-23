<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMemberSubscriptionRequest;
use App\Http\Resources\MemberSubscriptionResource;
use App\Models\Member;
use App\Models\MemberSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminMemberSubscriptionController extends Controller
{
    public function index(Member $member): AnonymousResourceCollection
    {
        $subscriptions = $member->subscriptions()
            ->with('subscriptionPlan')
            ->latest('date_fin')
            ->get();

        return MemberSubscriptionResource::collection($subscriptions);
    }

    public function store(StoreMemberSubscriptionRequest $request, Member $member): JsonResponse
    {
        $subscription = DB::transaction(function () use ($request, $member): MemberSubscription {
            $data = $request->validated();
            $status = Carbon::parse($data['date_fin'])->lt(today()) ? 'expire' : 'actif';

            $subscription = MemberSubscription::query()->create([
                'member_id' => $member->id,
                'subscription_plan_id' => $data['subscription_plan_id'],
                'date_debut' => $data['date_debut'],
                'date_fin' => $data['date_fin'],
                'statut' => $status,
            ]);

            $member->update(['statut_abonnement' => $status]);

            return $subscription;
        });

        return (new MemberSubscriptionResource($subscription->load('subscriptionPlan')))
            ->additional(['message' => 'Subscription assigned successfully.'])
            ->response()
            ->setStatusCode(201);
    }
}
