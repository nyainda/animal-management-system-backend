<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    EmailVerificationNotificationController,
    NewPasswordController,
    PasswordResetLinkController,
    RegisteredUserController,
    VerifyEmailController
};
use App\Http\Controllers\API\{
    AuthController,
    GoogleController,
    DashboardController,
    TransactionPaymentController,
    TransactionController,
    SupplierController,
    AnimalProductionController,
    FeedTypeController,
    FeedingRecordController,
    FeedingScheduleController,
    FeedAnalyticController,
    FeedInventoryController,
    NoteController,
    BreedingController,
    TreatController,
    AnimalController,
    AnimalRelationshipController,
    ActivityController,
    HealthController,
    TaskController
};

Route::middleware('api')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login');
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    });

    // Authentication Required Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Auth & Verification
        Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::middleware('throttle:6,1')->group(function () {
            Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware('signed')
                ->name('verification.verify');
            Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->name('verification.send');
        });

        // Animal Management
        Route::apiResource('animals', AnimalController::class);

        // Animal Relationships
        Route::prefix('animals/{animal}')->group(function () {
            // Relationship Routes
            Route::post('/dam', [AnimalRelationshipController::class, 'setDam']);
            Route::delete('/dam', [AnimalRelationshipController::class, 'removeDam']);
            Route::post('/sire', [AnimalRelationshipController::class, 'setSire']);
            Route::delete('/sire', [AnimalRelationshipController::class, 'removeSire']);
            Route::post('/offspring', [AnimalRelationshipController::class, 'addOffspring']);
            Route::get('/family-tree', [AnimalRelationshipController::class, 'familyTree']);

            // Health Routes
            Route::prefix('health')->group(function () {
                Route::get('/reports', [HealthController::class, 'reports']);
                //Route::get('/vaccinations', [HealthController::class, 'vaccinations']);
                Route::get('/', [HealthController::class, 'index']);
                Route::post('/', [HealthController::class, 'store']);
                Route::get('/{health}', [HealthController::class, 'show'])->whereUuid('health');
                Route::match(['put', 'patch'], '/{health}', [HealthController::class, 'update'])->whereUuid('health');
                Route::delete('/{health}', [HealthController::class, 'destroy'])->whereUuid('health');
            });
        });

        Route::prefix('animals/{animal}')->middleware(['auth:sanctum'])->group(function () {
            // Transactions summary route
            Route::get('transactions/summary', [TransactionController::class, 'summary'])
                ->name('transactions.summary');

            // Payments summary route (defined BEFORE apiResource to avoid conflict)
            Route::get('payments/summary', [TransactionPaymentController::class, 'summary'])
                ->name('payments.summary');

            // Resource routes
            Route::apiResource('transactions', TransactionController::class);
            Route::apiResource('payments', TransactionPaymentController::class);
        });


        Route::apiResource('animals.treats', TreatController::class);
       // Route::apiResource('tasks', TaskController::class);
        Route::apiResource('animals.tasks', TaskController::class);
        Route::apiResource('animals.breedings', BreedingController::class);
        Route::apiResource('animals.notes', NoteController::class);
        Route::apiResource('animals.feed-types.feed-inventories', FeedInventoryController::class);
        Route::get('animals/{animal}/feed-inventories/analytics', [FeedInventoryController::class, 'analytics']);
        Route::apiResource('animals.feed-types.feed-analytics', FeedAnalyticController::class);
        Route::get('animals/{animal}/feed-analytics/report', [FeedAnalyticController::class, 'generateReport'])
        ->name('animals.feed-analytics.report');
       // Route::get('animals/{animal}/feed-analytics/report', [FeedAnalyticController::class, 'generateReport']);
        Route::apiResource('animals.feed-types.feeding-schedules', FeedingScheduleController::class);
        Route::get('animals/{animal}/feeding-schedules/upcoming', [FeedingScheduleController::class, 'upcomingSchedule']);
        Route::apiResource('animals.feed-types.feeding-records', FeedingRecordController::class);
        Route::apiResource('animals.feed-types', FeedTypeController::class);
        Route::prefix('animals/{animal}')->group(function () {
            // Get form data for specific animal
            Route::get('production/form-data', [AnimalProductionController::class, 'getFormData']);

            // CRUD routes for production records
            Route::apiResource('production', AnimalProductionController::class);
        });
        Route::get('/animals/{animal}/production-statistics', [AnimalProductionController::class, 'getProductionStatistics']);
        Route::apiResource('animals.suppliers', SupplierController::class);


        // Activities
        Route::prefix('activities')->group(function () {
            Route::post('/generate-birthdays', [ActivityController::class, 'generateBirthdayActivities']);
        });
        Route::apiResource('animals.activities', ActivityController::class)
            ->except(['update'])
            ->shallow()
            ->scoped(['animal' => 'id']);
    });
});
