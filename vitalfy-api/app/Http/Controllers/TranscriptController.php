<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTranscriptRequest;
use App\Models\Transcript;
use App\Services\DashboardService;
use App\Services\TranscriptService;
use App\Support\FeatureFlags;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TranscriptController extends Controller
{
    protected TranscriptService $transcriptService;
    protected DashboardService $dashboardService;

    public function __construct(
        TranscriptService $transcriptService, 
        DashboardService $dashboardService,
    )
    {
        $this->transcriptService = $transcriptService;
        $this->dashboardService = $dashboardService;
    }

    public function indexByUser() {
        $userId = Auth::id(); 
        
        return $this->transcriptService->getUserTranscripts($userId);
    }

    public function store(StoreTranscriptRequest $request): JsonResponse
    {
        $transcript = $this->transcriptService->processAudioAndCreate($request);

        return response()->json($transcript, 201);
    }

    /**
     * SH-R1-02 (ai-vitalfy/action-plans/shared/R1.md): a flag decide em
     * runtime, sem deploy — ver App\Support\FeatureFlags. O front descobre o
     * modo pela própria resposta (200 síncrono × 202 assíncrono), nunca por
     * variável de build.
     */
    public function storeAndGenerateDocument(StoreTranscriptRequest $request): JsonResponse
    {
        if (FeatureFlags::asyncTranscriptPipeline()) {
            $result = $this->transcriptService->enqueueGenerateDocument($request);

            return response()->json($result, 202);
        }

        $result = $this->transcriptService->storeAndGenerateDocument($request);

        return response()->json($result, 200);
    }

    /**
     * BE-R1-07: estado do processamento assíncrono, para o front fazer polling.
     */
    public function status(Transcript $transcript): JsonResponse
    {
        $this->authorize('viewStatus', $transcript);

        return response()->json($this->transcriptService->getStatus($transcript->id));
    }

    public function show(Transcript $transcript)
    {
        $this->authorize('view', $transcript);

        return $this->transcriptService->getTranscriptAndDocument($transcript->id);
    }

    public function update(Transcript $transcript, Request $request): JsonResponse
    {
        $this->authorize('update', $transcript);

        $data = $request->all();
        
        $transcript->update($data);

        return response()->json([
            'success' => $transcript,
            'message' => 'Transcript updated successfully',
        ], 200);
    }

    public function delete(Transcript $transcript): JsonResponse
    {
        $this->authorize('delete', $transcript);

        $this->transcriptService->deleteTranscript($transcript->id);

        return response()->json([
            'success' => true,
            'message' => 'Transcript deleted successfully',
        ], 200);
    }

    public function getConversations(Transcript $transcript) 
    {
        $this->authorize('getConversations', $transcript);

        return $this->transcriptService->getConversations($transcript->id);
    }

    public function filterUserTranscripts(Request $request) 
    {
        $userId = Auth::id();

        return $this->transcriptService->searchUserTranscripts($request->all(), $userId);
    }

    public function getDashboardSummary(Request $request) 
    {
        $period = $request->query('period', 'today');
        return $this->dashboardService->summary($period);
    }
    
    public function getDashboardCharts() 
    {
        return $this->dashboardService->charts();
    }

    public function getlatestRecentTranscripts()
    {
        return $this->dashboardService->latestRecentTranscripts();
    }
}
