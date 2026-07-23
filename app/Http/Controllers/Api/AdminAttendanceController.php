<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexAttendanceRequest;
use App\Http\Requests\Api\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AdminAttendanceController extends Controller
{
    public function index(IndexAttendanceRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();
        $attendances = Attendance::query()
            ->with('member.user')
            ->when($data['membre_id'] ?? null, fn ($query, $memberId) => $query->where('membre_id', $memberId))
            ->when($data['date_debut'] ?? null, fn ($query, $date) => $query->whereDate('date_presence', '>=', $date))
            ->when($data['date_fin'] ?? null, fn ($query, $date) => $query->whereDate('date_presence', '<=', $date))
            ->latest('date_presence')
            ->paginate();

        return AttendanceResource::collection($attendances);
    }

    public function store(StoreAttendanceRequest $request, Member $member): JsonResponse
    {
        $data = $request->validated();
        $attendance = Attendance::query()->firstOrNew([
            'membre_id' => $member->id,
            'date_presence' => $data['date_presence'],
        ]);
        $attendance->fill([
            'enregistre_le' => $attendance->enregistre_le ?? now(),
            'notes' => $data['notes'] ?? null,
        ])->save();

        return (new AttendanceResource($attendance->load('member.user')))
            ->response()
            ->setStatusCode($attendance->wasRecentlyCreated ? 201 : 200);
    }
}
