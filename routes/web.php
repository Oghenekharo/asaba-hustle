<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminJobController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminRatingController;
use App\Http\Controllers\Admin\AdminSkillController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Web\AuthController as WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\JobController;
use App\Http\Controllers\Web\MessageController;
use App\Http\Controllers\Web\NegotiationController;
use App\Http\Controllers\Web\NotificationController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\WebPaymentController;

Route::get('/', [HomeController::class, 'index'])->name('web.home');
// Route::get('/', function () {
//     return view('welcome');
// })->name('web.home');
// Route::get('/home', function () {
//     return view('home');
// })->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('web.register');
    Route::get('/forgot-password', [WebAuthController::class, 'showForgotPassword'])->name('web.password.request');
    Route::get('/reset-password', [WebAuthController::class, 'showResetPassword'])->name('web.password.reset');

    Route::post('/login', [WebAuthController::class, 'login'])->name('web.login.submit');
    Route::post('/register', [WebAuthController::class, 'register'])->name('web.register.submit');
    Route::post('/forgot-password', [WebAuthController::class, 'forgotPassword'])->name('web.password.email');
    Route::post('/reset-password', [WebAuthController::class, 'resetPassword'])->name('web.password.update');
});

Route::get('/verify-phone', [WebAuthController::class, 'showVerifyPhone'])->name('web.verify.phone.page');
Route::post('/verify-phone', [WebAuthController::class, 'verifyPhone'])->name('web.verify.phone.submit');

Route::middleware(['web', 'auth', 'session.active', 'account.active', 'phone.verified'])->group(function () {
    Route::get('/app', [DashboardController::class, 'index'])->name('web.app');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('web.logout');

    Route::prefix('app')->name('web.app.')->group(function () {
        Route::get('/me', [ProfileController::class, 'me'])->name('me');
        Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('password.change');
        Route::post('/upload-id', [ProfileController::class, 'uploadId'])->name('id.upload');
        Route::post('/upload-photo', [ProfileController::class, 'uploadPhoto'])->name('photo.upload');
        Route::post('/availability', [ProfileController::class, 'updateAvailability'])->name('availability');
        Route::post('/send-verification-token', [ProfileController::class, 'sendVerificationToken'])->name('verification.send');
        Route::post('/verify-contact', [ProfileController::class, 'verifyContact'])->name('verification.verify');

        Route::get('/jobs', [JobController::class, 'jobs'])->name('jobs');
        Route::get('/jobs/{job}', [JobController::class, 'showJob'])->name('jobs.show');
        Route::get('/my-jobs', [JobController::class, 'myJobs'])->name('my-jobs');
        Route::post('/jobs', [JobController::class, 'storeJob'])->name('jobs.store');
        Route::post('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');
        Route::post('/jobs/{job}/negotiate', [NegotiationController::class, 'create'])->name('negotiate.submit');
        Route::post('/jobs/negotiate/{negotiation}/accept', [NegotiationController::class, 'accept'])->name('negotiate.accept');
        Route::post('/jobs/negotiate/{negotiation}/counter', [NegotiationController::class, 'counter'])->name('negotiate.counter');
        Route::post('/jobs/negotiate/{negotiation}/reject', [NegotiationController::class, 'reject'])->name('negotiate.reject');
        Route::post('/jobs/{job}/hire', [JobController::class, 'hire'])->name('jobs.hire');
        Route::post('/jobs/{job}/accept', [JobController::class, 'accept'])->name('jobs.accept');
        Route::post('/jobs/{job}/reject', [JobController::class, 'reject'])->name('jobs.reject');
        Route::post('/jobs/{job}/start', [JobController::class, 'start'])->name('jobs.start');
        Route::post('/jobs/{job}/complete', [JobController::class, 'complete'])->name('jobs.complete');
        Route::post('/jobs/{job}/mark-paid', [JobController::class, 'markPaid'])->name('jobs.mark-paid');
        Route::post('/jobs/{job}/confirm-payment', [JobController::class, 'confirmPayment'])->name('jobs.confirm-payment');
        Route::post('/jobs/{job}/rate', [JobController::class, 'rate'])->name('jobs.rate');
        Route::get('/jobs/{job}/suggested-workers', [JobController::class, 'suggestedWorkers'])->name('jobs.suggested-workers');

        Route::get('/conversations', [MessageController::class, 'conversations'])->name('conversations');
        Route::get('/conversations/{conversation}/messages', [MessageController::class, 'messages'])->name('conversations.messages');
        Route::post('/messages', [MessageController::class, 'sendMessage'])->name('messages.send');
        Route::post('/conversations/{conversation}/read', [MessageController::class, 'markConversationRead'])->name('conversations.read');

        Route::get('/notifications', [NotificationController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/read', [NotificationController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

        Route::post('/payments/paystack/initialize', [WebPaymentController::class, 'initializePaystack'])->name('payments.paystack.initialize');
        Route::post('/payments/paystack/verify', [WebPaymentController::class, 'verifyPaystack'])->name('payments.paystack.verify');
        Route::post('/payments/flutterwave/initialize', [WebPaymentController::class, 'initializeFlutterwave'])->name('payments.flutterwave.initialize');
        Route::post('/payments/flutterwave/verify', [WebPaymentController::class, 'verifyFlutterwave'])->name('payments.flutterwave.verify');
    });
});


Route::prefix('admin')
    ->name('admin.')
    ->middleware(['web', 'auth', 'account.active', 'admin', 'throttle:admin'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::prefix('users')->group(function () {

            Route::get('/', [AdminUserController::class, 'index'])
                ->name('users.index');

            Route::get('{user}', [AdminUserController::class, 'show'])
                ->name('users.show');

            Route::post('bulk', [AdminUserController::class, 'bulk'])
                ->name('users.bulk');

            Route::patch(
                '{user}/status',
                [AdminUserController::class, 'updateStatus']
            )->name('users.status');
        });

        Route::prefix('jobs')->group(function () {

            Route::get('/', [AdminJobController::class, 'index'])
                ->name('jobs.index');

            Route::get('{job}', [AdminJobController::class, 'show'])
                ->name('jobs.show');

            Route::patch(
                '{job}/cancel',
                [AdminJobController::class, 'cancel']
            )->name('jobs.cancel');

            Route::patch(
                '{job}/rollback',
                [AdminJobController::class, 'rollback']
            )->name('jobs.rollback');
        });

        Route::prefix('skills')->group(function () {
            Route::get('/', [AdminSkillController::class, 'index'])
                ->name('skills.index');

            Route::post('/', [AdminSkillController::class, 'store'])
                ->name('skills.store');

            Route::get('{skill}/edit', [AdminSkillController::class, 'edit'])
                ->name('skills.edit');

            Route::patch('{skill}', [AdminSkillController::class, 'update'])
                ->name('skills.update');

            Route::delete('{skill}', [AdminSkillController::class, 'destroy'])
                ->name('skills.destroy');
        });

        Route::get(
            '/payments',
            [AdminPaymentController::class, 'index']
        )->name('payments.index');

        Route::get(
            '/ratings',
            [AdminRatingController::class, 'index']
        )->name('ratings.index');

        Route::delete(
            '/ratings/{rating}',
            [AdminRatingController::class, 'destroy']
        )->name('ratings.destroy');

        Route::get(
            '/activity-logs',
            [AdminActivityLogController::class, 'index']
        )->name('activity.index');
    });
