<?php

use App\Enums\PriceIdsEnum;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\TranscriptTypesController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::middleware('throttle:auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
});

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook']);

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

Route::middleware([
    'auth:sanctum', 
    'throttle:api'
])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/email/resend-verification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/tokens', [AuthController::class, 'tokens']);
    Route::delete('/tokens/{id}', [AuthController::class, 'revokeToken']);

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'show']);
        Route::put('/', [UserController::class, 'update']);
    });
    
    Route::prefix('documents')->group(function () {
        Route::post('/generate', [DocumentController::class, 'generate']);
        Route::post('/refine', [DocumentController::class, 'refine'])
            ->middleware('check.subscription');
        Route::post('/{document}/regenerate-insights', [DocumentController::class, 'regenerateInsights']);
        Route::put('/{document}', [DocumentController::class, 'update']);
        Route::get('/{document}/pdf', [DocumentController::class, 'generatePdf']);
        Route::get('/{document}/insights', [DocumentController::class, 'insights']);
    });
    Route::get('user/transcripts', [TranscriptController::class, 'indexByUser']);

    Route::prefix('transcripts')->group(function () {
        Route::middleware([
            'free.transcript.limit',
            'throttle:transcripts',
            // BE-R1-04 (ai-vitalfy/action-plans/backend/R1.md): concorrência,
            // não taxa — impede o mesmo usuário de empilhar processamentos e
            // esgotar o pool php-fpm compartilhado.
            'no.concurrent.transcript',
        ])->group(function () {
            Route::post('/', [TranscriptController::class, 'store']);
            Route::post('/generate-document', [TranscriptController::class, 'storeAndGenerateDocument']);
        });
        // BE-R1-07: fora do grupo de lock acima — consultar status não deve
        // esperar nem disputar o lock de um processamento em andamento.
        Route::get('/{transcript}/status', [TranscriptController::class, 'status']);
        Route::get('/user/filter', [TranscriptController::class, 'filterUserTranscripts']);
        Route::put('/{transcript}', [TranscriptController::class, 'update']);
        Route::get('/{transcript}', [TranscriptController::class, 'show']);
        Route::get('/{transcript}/conversations', [TranscriptController::class, 'getConversations']);
        Route::delete('/{transcript}', [TranscriptController::class, 'delete']);
    });

    Route::prefix('templates')->group(function () {
        Route::get('/', [DocumentTemplateController::class, 'index']);
        Route::get('/minimal', [DocumentTemplateController::class, 'listIdNameTemplate']);
        Route::get('/with-documents-count', [DocumentTemplateController::class, 'listTemplatesWithUserDocumentsCount']);
        Route::get('/count-categories', [DocumentTemplateController::class, 'listCountCategories']);
    });
    Route::get('transcript-types', [TranscriptTypesController::class, 'index']);
    Route::get('transcript-types/minimal', [TranscriptTypesController::class, 'listMinimal']);
    
    Route::prefix('dashboard')->group(function () {
        Route::get('/summary', [TranscriptController::class, 'getDashboardSummary']);
        Route::get('/charts', [TranscriptController::class, 'getDashboardCharts']);
        Route::get('/last-transcripts', [TranscriptController::class, 'getlatestRecentTranscripts']);
    });

    Route::prefix('subscription')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index']);
        Route::post('/checkout', [SubscriptionController::class, 'checkout']);
        Route::post('/cancel', [SubscriptionController::class, 'cancel']);
        Route::get('/verify-checkout', [SubscriptionController::class, 'verifyCheckout']);
    });
});
