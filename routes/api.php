<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FlutterwavePaymentController;
use App\Http\Controllers\Api\SkillController;
use App\Http\Controllers\Api\ServiceJobController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\JobNegotiationController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserNotificationController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-phone', [AuthController::class, 'verifyPhone']);
    Route::get('verify-email/{user:id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->name('api.auth.verify-email');
});

Route::get('/skills', [SkillController::class, 'index']);
Route::get('/skills/{skill:id}', [SkillController::class, 'show']);
Route::post('/payments/webhook/paystack', [PaymentController::class, 'webhook']);
Route::post('/payments/webhook/flutterwave', [FlutterwavePaymentController::class, 'webhook']);


Route::middleware(['token.active', 'auth:sanctum', 'account.active', 'throttle:api'])->group(function () {

    Route::prefix('auth')->group(function () {

        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::post('/change-password', [AuthController::class, 'changePassword']);

        Route::put('/profile', [AuthController::class, 'updateProfile']);

        Route::post('/upload-id', [AuthController::class, 'uploadId']);

        Route::post('/upload-photo', [AuthController::class, 'uploadPhoto']);

        Route::post('/availability', [AuthController::class, 'updateAvailability']);

        Route::post('/send-verification-token', [AuthController::class, 'sendVerificationToken']);

        Route::post('/verify-contact', [AuthController::class, 'verifyContact']);
    });

    // Route::get('/skills', [SkillController::class, 'index']);

    Route::prefix('jobs')->group(function () {

        Route::get('/', [ServiceJobController::class, 'index']);

        Route::post('/', [ServiceJobController::class, 'store'])->middleware('throttle:10,1');

        Route::get('/{job:id}', [ServiceJobController::class, 'show']);

        Route::post('/{job:id}/apply', [ServiceJobController::class, 'apply']);

        Route::post('/{job:id}/negotiate', [JobNegotiationController::class, 'store']);

        Route::post('/{job:id}/negotiate/{negotiation:id}/accept', [JobNegotiationController::class, 'accept']);

        Route::post('/{job:id}/negotiate/{negotiation:id}/counter', [JobNegotiationController::class, 'counter']);

        Route::post('/{job:id}/negotiate/{negotiation:id}/reject', [JobNegotiationController::class, 'reject']);

        Route::post('/{job:id}/hire', [ServiceJobController::class, 'hire']);

        Route::post('/{job:id}/accept', [ServiceJobController::class, 'accept']);
        Route::post('/{job:id}/reject', [ServiceJobController::class, 'reject']);

        Route::post('/{job:id}/start', [ServiceJobController::class, 'start']);

        Route::post('/{job:id}/complete', [ServiceJobController::class, 'complete']);

        Route::post('/{job:id}/mark-paid', [ServiceJobController::class, 'markPaid']);

        Route::post('/{job:id}/confirm-payment', [ServiceJobController::class, 'confirmPayment']);

        Route::post('/{job:id}/rate', [ServiceJobController::class, 'rate']);
        Route::patch('/{job:id}/cancel', [ServiceJobController::class, 'cancel'])->middleware('admin');
        Route::patch('/{job:id}/rollback', [ServiceJobController::class, 'rollback'])->middleware('admin');

        Route::get(
            '/{job:id}/suggested-workers',
            [ServiceJobController::class, 'suggestedWorkers']
        );

        Route::get('/my/jobs', [ServiceJobController::class, 'myJobs']);
        // Route::get('/service-jobs/recent', [ServiceJobController::class, 'recent']);
        Route::get('/search', [ServiceJobController::class, 'search']);
    });

    Route::prefix('messages')->group(function () {

        Route::get('/conversations', [ChatMessageController::class, 'conversations']);

        Route::get('/conversation/{conversation}', [ChatMessageController::class, 'messages']);

        Route::post('/send', [ChatMessageController::class, 'send'])->middleware('throttle:30,1');

        Route::post('/conversation/{conversation}/read', [ChatMessageController::class, 'markRead']);
    });

    Route::prefix('payments')->group(function () {

        Route::post('/initialize', [PaymentController::class, 'initialize']);

        Route::post('/verify', [PaymentController::class, 'verify']);

        Route::post('/flutterwave/initialize', [FlutterwavePaymentController::class, 'initialize']);

        Route::post('/flutterwave/verify', [FlutterwavePaymentController::class, 'verify']);
    });

    Route::prefix('notifications')->group(function () {

        Route::get('/', [UserNotificationController::class, 'index']);

        Route::post('/read', [UserNotificationController::class, 'markRead']);

        Route::post('/read-all', [UserNotificationController::class, 'markAllRead']);
    });
});
