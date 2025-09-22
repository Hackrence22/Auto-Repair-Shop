<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProfileController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminPaymentMethodController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\SlotSettingController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AnalyticsApiController;
use App\Http\Controllers\ServiceDetailsController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\ShopRatingController;
use App\Http\Controllers\Admin\ShopRatingAdminController;

// Welcome page route
Route::get('/', [HomeController::class, 'index'])->name('welcome');

// Terms and Conditions Route
Route::get('/terms', function () {
    return view('terms');
})->name('terms');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    
    // Social Authentication Routes
    Route::get('/auth/google/login', [SocialAuthController::class, 'redirectToGoogleLogin'])->name('auth.google.login');
    Route::get('/auth/google/register', [SocialAuthController::class, 'redirectToGoogleRegister'])->name('auth.google.register');
    Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});
// Resend pending registration verification
Route::post('/registration/resend', function(\Illuminate\Http\Request $request) {
    $request->validate(['email' => 'required|email']);
    $pending = \App\Models\PendingRegistration::where('email', $request->input('email'))->first();
    if (!$pending) {
        return back()->with('error', 'No pending registration found for that email.');
    }
    if ($pending->expires_at && $pending->expires_at->isPast()) {
        $pending->update(['token' => \Illuminate\Support\Str::random(64), 'expires_at' => now()->addHours(24)]);
    }
    $verifyUrl = url('/verify-registration/'.$pending->token);
    \Mail::to($pending->email)->send(new \App\Mail\VerifyRegistrationMail($pending->name, $verifyUrl));
    if ($request->expectsJson()) {
        return response()->json(['success' => true, 'message' => 'Verification email resent.']);
    }
    return view('auth.registration-pending', ['email' => $pending->email])
        ->with('success', 'Verification email resent.');
})->name('registration.resend');
// Email verification routes
Route::middleware('auth')->group(function() {
    Route::get('/email/verify', [\App\Http\Controllers\Auth\VerificationController::class, 'show'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/resend', [\App\Http\Controllers\Auth\VerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.resend');
});

// Custom pending registration verification (pre-account)
Route::get('/verify-registration/{token}', function($token) {
    $pending = \App\Models\PendingRegistration::where('token', $token)->where(function($q){
        $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
    })->first();
    if (!$pending) {
        return redirect()->route('register')->with('error', 'Invalid or expired verification link. Please register again.');
    }
    // Create user now
    $user = \App\Models\User::create([
        'name' => $pending->name,
        'email' => $pending->email,
        'phone' => $pending->phone,
        'password' => $pending->password,
        'email_verified_at' => now(),
    ]);
    // Cleanup
    $pending->delete();
    return redirect()->route('login')->with('success', 'Email verified! You can now log in.');
})->name('registration.verify');


// Logout Route (must be outside guest middleware)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Customer Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/picture', [ProfileController::class, 'updatePicture'])->name('profile.update-picture');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Appointment Routes
    Route::get('appointments/history', [\App\Http\Controllers\AppointmentController::class, 'history'])->name('appointments.history');
    Route::get('appointments/history/csv', [\App\Http\Controllers\AppointmentController::class, 'historyCsv'])->name('appointments.history.csv');
    Route::get('appointments/history/pdf', [\App\Http\Controllers\AppointmentController::class, 'historyPdf'])->name('appointments.history.pdf');
    Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/appointments/slots', [AppointmentController::class, 'getSlots'])->name('appointments.slots');
    Route::get('/appointments/optimal-slots', [AppointmentController::class, 'getOptimalSlots'])->name('appointments.optimal-slots');
    Route::get('/appointments/alternative-suggestions', [AppointmentController::class, 'getAlternativeSuggestions'])->name('appointments.alternative-suggestions');
    Route::get('/appointments/count', [HomeController::class, 'getAppointmentCount'])->name('appointments.count');
    
    // Recommendation System Routes
    Route::get('/recommendations', [\App\Http\Controllers\RecommendationController::class, 'index'])->name('recommendations.index');
    Route::get('/recommendations/data', [\App\Http\Controllers\RecommendationController::class, 'getRecommendations'])->name('recommendations.data');
    Route::get('/recommendations/maintenance-due', [\App\Http\Controllers\RecommendationController::class, 'getMaintenanceDue'])->name('recommendations.maintenance-due');
    Route::get('/recommendations/shop/{shopId}', [\App\Http\Controllers\RecommendationController::class, 'getShopRecommendations'])->name('recommendations.shop');
    Route::get('/recommendations/urgent', [\App\Http\Controllers\RecommendationController::class, 'getUrgentRecommendations'])->name('recommendations.urgent');
    Route::get('/recommendations/seasonal', [\App\Http\Controllers\RecommendationController::class, 'getSeasonalRecommendations'])->name('recommendations.seasonal');
    Route::get('/recommendations/cross-selling', [\App\Http\Controllers\RecommendationController::class, 'getCrossSellingRecommendations'])->name('recommendations.cross-selling');
    Route::get('/recommendations-page', [\App\Http\Controllers\RecommendationController::class, 'index'])->name('recommendations.page');

    // Shop Ratings
    Route::post('/shops/{shop}/ratings', [ShopRatingController::class, 'store'])->name('shops.ratings.store');
    
    // Pricing Optimization Routes
    Route::get('/pricing/optimal', [\App\Http\Controllers\PricingController::class, 'getOptimalPricing'])->name('pricing.optimal');
    Route::get('/pricing/bulk', [\App\Http\Controllers\PricingController::class, 'getBulkPricing'])->name('pricing.bulk');
    Route::get('/pricing/trends', [\App\Http\Controllers\PricingController::class, 'getPricingTrends'])->name('pricing.trends');
    Route::get('/pricing/competitive', [\App\Http\Controllers\PricingController::class, 'getCompetitiveAnalysis'])->name('pricing.competitive');
    Route::get('/pricing/seasonal', [\App\Http\Controllers\PricingController::class, 'getSeasonalPricing'])->name('pricing.seasonal');
    Route::get('/pricing/demand', [\App\Http\Controllers\PricingController::class, 'getDemandPricing'])->name('pricing.demand');
    Route::get('/pricing/risk', [\App\Http\Controllers\PricingController::class, 'getPricingRisk'])->name('pricing.risk');
    Route::get('/pricing/opportunities', [\App\Http\Controllers\PricingController::class, 'getPricingOpportunities'])->name('pricing.opportunities');
    Route::get('/pricing/summary', [\App\Http\Controllers\PricingController::class, 'getPricingSummary'])->name('pricing.summary');
    Route::get('/pricing-page', [\App\Http\Controllers\PricingController::class, 'index'])->name('pricing.page');
    
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    
    // Customer Service Routes
    Route::resource('customer-service', \App\Http\Controllers\CustomerServiceController::class)->except(['edit', 'update', 'destroy']);
});

// User routes
Route::get('/shops', [App\Http\Controllers\ShopController::class, 'index'])->name('shops.index');
Route::get('/shops/{shop}', [App\Http\Controllers\ShopController::class, 'show'])->name('shops.show');

// Admin Authentication
Route::group(['prefix' => 'admin', 'middleware' => ['guest:admin']], function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login.form');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login');
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:admin'], 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

    // Admin Profile Routes
    Route::get('/profile', [AdminProfileController::class, 'show'])->name('profile');
    Route::get('/profile/edit', [AdminProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/picture', [AdminProfileController::class, 'updatePicture'])->name('profile.picture');
    Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');

    // Admin Appointment Routes
    Route::resource('appointments', AdminAppointmentController::class);
    Route::patch('/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.updateStatus');
    Route::patch('/appointments/{appointment}/approve', [AdminAppointmentController::class, 'approve'])->name('appointments.approve');
    Route::patch('/appointments/{appointment}/reject', [AdminAppointmentController::class, 'reject'])->name('appointments.reject');

    // Services
    Route::resource('services', AdminServiceController::class);

    // Payment Methods
    Route::resource('payment-methods', AdminPaymentMethodController::class);
    Route::patch('/payment-methods/{paymentMethod}/toggle-status', [AdminPaymentMethodController::class, 'toggleStatus'])->name('payment-methods.toggle-status');

    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [AdminSettingsController::class, 'update'])->name('settings.update');

    // Slot Settings
    Route::resource('slot-settings', SlotSettingController::class);

    // User Management
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    Route::resource('admins', \App\Http\Controllers\Admin\AdminController::class);

    // Shop Management
    Route::resource('shops', \App\Http\Controllers\Admin\ShopController::class);
    Route::post('shops/{shop}/toggle-status', [\App\Http\Controllers\Admin\ShopController::class, 'toggleStatus'])->name('shops.toggle-status');

    // Feedback routes
    Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [AdminFeedbackController::class, 'show'])->name('feedback.show');
    Route::delete('/feedback/{feedback}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');
    Route::post('/feedback/{feedback}/reply', [\App\Http\Controllers\Admin\AdminFeedbackController::class, 'reply'])->name('feedback.reply');

    // Customer Service routes
    Route::resource('customer-service', \App\Http\Controllers\Admin\AdminCustomerServiceController::class);
    Route::get('/customer-service-dashboard', [\App\Http\Controllers\Admin\AdminCustomerServiceController::class, 'dashboard'])->name('customer-service.dashboard');
    Route::post('/customer-service/{customerService}/assign-to-me', [\App\Http\Controllers\Admin\AdminCustomerServiceController::class, 'assignToMe'])->name('customer-service.assign-to-me');

    // Payment Management
    Route::get('payments', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'index'])->name('payments.index');
    Route::post('payments/{id}/confirm', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'confirm'])->name('payments.confirm');
    Route::post('payments/{id}/reject', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'reject'])->name('payments.reject');
    Route::delete('payments/{id}', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'destroy'])->name('payments.destroy');
    Route::get('payments/history', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'history'])->name('payments.history');
    Route::get('payments/history/csv', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'historyCsv'])->name('payments.history.csv');
    Route::get('payments/history/pdf', [\App\Http\Controllers\Admin\PaymentManagementController::class, 'historyPdf'])->name('payments.history.pdf');

    // Ratings
    Route::get('ratings', [ShopRatingAdminController::class, 'index'])->name('ratings.index');
    
    // Admin Notification Routes (separate from user notifications)
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'adminIndex'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('notifications/toggle-read/{id}', [\App\Http\Controllers\NotificationController::class, 'toggleRead'])->name('notifications.toggleRead');
    Route::post('notifications/delete/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::delete('notifications/delete/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    Route::post('notifications/delete-all', [\App\Http\Controllers\NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
    Route::post('notifications/mark-all-unread', [\App\Http\Controllers\NotificationController::class, 'markAllUnread'])->name('notifications.markAllUnread');
    Route::get('notifications/sender-info/{id}', [\App\Http\Controllers\NotificationController::class, 'senderInfo'])->name('notifications.senderInfo');
    Route::get('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');

    // Technician Management
    Route::resource('technicians', \App\Http\Controllers\Admin\TechnicianController::class);
    Route::post('technicians/{technician}/toggle-availability', [\App\Http\Controllers\Admin\TechnicianController::class, 'toggleAvailability'])->name('technicians.toggle-availability');
    Route::get('technicians-by-shop', [\App\Http\Controllers\Admin\TechnicianController::class, 'getTechniciansByShop'])->name('technicians.by-shop');
    Route::get('technicians-by-shop-date', [\App\Http\Controllers\Admin\TechnicianController::class, 'getTechniciansByShopAndDate'])->name('technicians.by-shop-date');
});

Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

// Analytics Routes
Route::prefix('admin/analytics')->name('admin.analytics.')->middleware(['auth:admin'])->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('/revenue', [AnalyticsController::class, 'revenue'])->name('revenue');
    Route::get('/appointments', [AnalyticsController::class, 'appointments'])->name('appointments');
    Route::get('/customers', [AnalyticsController::class, 'customers'])->name('customers');
    Route::get('/services', [AnalyticsController::class, 'services'])->name('services');
    Route::get('/export', [AnalyticsController::class, 'export'])->name('export');
});

// Analytics API Routes
Route::prefix('admin/api/analytics')->name('admin.api.analytics.')->middleware(['auth:admin'])->group(function () {
    Route::get('/dashboard', [AnalyticsApiController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [AnalyticsApiController::class, 'appointments'])->name('appointments');
    Route::get('/revenue', [AnalyticsApiController::class, 'revenue'])->name('revenue');
    Route::get('/customers', [AnalyticsApiController::class, 'customers'])->name('customers');
    Route::get('/services', [AnalyticsApiController::class, 'services'])->name('services');
    Route::get('/performance', [AnalyticsApiController::class, 'performance'])->name('performance');
    Route::get('/vehicle-types', [AnalyticsApiController::class, 'vehicleTypes'])->name('vehicle-types');
    Route::get('/payment-methods', [AnalyticsApiController::class, 'paymentMethods'])->name('payment-methods');
    Route::get('/time-based', [AnalyticsApiController::class, 'timeBased'])->name('time-based');
    Route::get('/comparison', [AnalyticsApiController::class, 'comparison'])->name('comparison');
    Route::get('/real-time', [AnalyticsApiController::class, 'realTime'])->name('real-time');
    Route::get('/custom-range', [AnalyticsApiController::class, 'customRange'])->name('custom-range');
});

Route::post('notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
Route::post('/notifications/toggle-read/{id}', [App\Http\Controllers\NotificationController::class, 'toggleRead'])->name('notifications.toggleRead');
Route::post('/notifications/delete/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
Route::delete('/notifications/delete/{id}', [App\Http\Controllers\NotificationController::class, 'destroy']);
Route::post('notifications/delete-all', [\App\Http\Controllers\NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
Route::post('notifications/mark-all-unread', [\App\Http\Controllers\NotificationController::class, 'markAllUnread'])->name('notifications.markAllUnread');
Route::get('notifications/sender-info/{id}', [\App\Http\Controllers\NotificationController::class, 'senderInfo']);
Route::get('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'show']);



Route::get('/services/{service}', [\App\Http\Controllers\ServiceDetailsController::class, 'show'])->name('services.details');

// TEMPORARY DEBUG ROUTE - REMOVE AFTER TESTING
Route::get('/debug/customer-service', function() {
    echo '<h1>Customer Service Debug</h1>';
    
    try {
        $count = \App\Models\CustomerService::count();
        echo '<p>Total customer service requests: ' . $count . '</p>';
        
        if ($count > 0) {
            $requests = \App\Models\CustomerService::with(['user', 'shop'])->limit(5)->get();
            echo '<h2>First 5 requests:</h2>';
            foreach ($requests as $request) {
                echo '<div style="border: 1px solid #ccc; margin: 10px; padding: 10px;">';
                echo '<p><strong>ID:</strong> ' . $request->id . '</p>';
                echo '<p><strong>Subject:</strong> ' . $request->subject . '</p>';
                echo '<p><strong>User:</strong> ' . ($request->user ? $request->user->name : 'NO USER') . '</p>';
                echo '<p><strong>Shop:</strong> ' . ($request->shop ? $request->shop->name : 'NO SHOP') . '</p>';
                echo '<p><strong>Shop Admin ID:</strong> ' . ($request->shop ? $request->shop->admin_id : 'NO ADMIN') . '</p>';
                echo '<p><strong>Category:</strong> ' . $request->category . '</p>';
                echo '<p><strong>Status:</strong> ' . $request->status . '</p>';
                echo '</div>';
            }
        }
        
        echo '<h2>Shops:</h2>';
        $shops = \App\Models\Shop::all();
        foreach ($shops as $shop) {
            echo '<p>Shop: ' . $shop->name . ' (Admin ID: ' . $shop->admin_id . ')</p>';
        }
        
    } catch (Exception $e) {
        echo '<p style="color: red;">Error: ' . $e->getMessage() . '</p>';
    }
});


