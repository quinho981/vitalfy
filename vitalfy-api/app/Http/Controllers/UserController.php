<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Services\TranscriptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(private TranscriptService $transcriptService)
    {
    }

    public function show(): JsonResponse
    {
        $user = Auth::user();
        $remainingTranscripts = null;

        $plan = $user->plan();

        if (!$user->hasProPlan()) {
            $remainingTranscripts = $this->transcriptService->getRemainingMonthlyTranscripts($user->id);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'recording_tour_completed' => $user->recording_tour_completed,
                'email_verified' => $user->hasVerifiedEmail(),
            ],
            'plan' => $plan,
            'remaining' => $remainingTranscripts
        ]);
    }

    public function update(UpdateUserRequest $request)
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
        ], 200);
    }
}
