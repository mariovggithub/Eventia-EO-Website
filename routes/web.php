<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\EO\DashboardController as EODashboard;
use App\Http\Controllers\EO\HiringController;
use App\Http\Controllers\EO\ProfileController as EOProfile;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboard;
use App\Http\Controllers\Vendor\ProductController;

// PUBLIC ROUTES
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

// AUTHENTICATION
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// USER ROUTES
Route::middleware(['auth', 'role:user'])->group(function () {
    // Order Management
    Route::get('/order', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/{order}', [OrderController::class, 'show'])->name('order.show');
    Route::get('/my-orders', [OrderController::class, 'myOrders'])->name('order.my-orders');
    
    // Price Agreement
    Route::post('/order/{order}/agree-price', [OrderController::class, 'agreePrice'])->name('order.agree-price');
    Route::post('/order/{order}/reject-price', [OrderController::class, 'rejectPrice'])->name('order.reject-price');
    
    // Payment
    Route::get('/order/{order}/payment', [OrderController::class, 'showPayment'])->name('order.payment');
    Route::post('/order/{order}/payment', [OrderController::class, 'processPayment'])->name('order.payment.process');
    
    // Order Actions
    Route::post('/order/{order}/cancel', [OrderController::class, 'cancel'])->name('order.cancel');
    Route::post('/order/{order}/complete', [OrderController::class, 'markCompleted'])->name('order.complete');
    
    // Revision Management (3x limit, 14 days)
    Route::post('/order/{order}/revision', [RevisionController::class, 'store'])->name('order.revision.store');
    Route::get('/order/{order}/revision/check', [RevisionController::class, 'checkLimits'])->name('order.revision.check');
    
    // Rating
    Route::post('/order/{order}/rating', [RatingController::class, 'store'])->name('order.rating.store');
    
    // Job Application
    Route::post('/jobs/{job}/apply', [JobController::class, 'apply'])->name('jobs.apply');
});

// CHAT ROUTES (Simple - No Real-time)
Route::middleware(['auth'])->group(function () {
    Route::post('/order/{order}/chat', [ChatController::class, 'store'])->name('order.chat.store');
    Route::get('/order/{order}/chat/messages', [ChatController::class, 'getMessages'])->name('order.chat.messages');
});

// EVENT ORGANIZER ROUTES
Route::middleware(['auth', 'role:eo'])->prefix('eo')->name('eo.')->group(function () {
    // Profile
    Route::get('/profile', [EOProfile::class, 'show'])->name('profile');
    Route::put('/profile', [EOProfile::class, 'update'])->name('profile.update');
    
    // Orders
    Route::get('/orders', [EODashboard::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [EODashboard::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/approve', [EODashboard::class, 'approveOrder'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [EODashboard::class, 'rejectOrder'])->name('orders.reject');
    Route::post('/orders/{order}/update-price', [EODashboard::class, 'updatePrice'])->name('orders.update-price');
    Route::post('/orders/{order}/update-status', [EODashboard::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/orders/{order}/complete', [EODashboard::class, 'completeOrder'])->name('orders.complete');
    
    // Hiring
    Route::get('/hiring', [HiringController::class, 'index'])->name('hiring');
    Route::post('/hiring', [HiringController::class, 'store'])->name('hiring.store');
    Route::delete('/hiring/{job}', [HiringController::class, 'destroy'])->name('hiring.destroy');
    
    // Revision Response
    Route::post('/revision/{revision}/respond', [RevisionController::class, 'respond'])->name('revision.respond');
});

// VENDOR ROUTES
Route::middleware(['auth', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/orders', [VendorDashboard::class, 'orders'])->name('orders');
    Route::get('/products', [ProductController::class, 'index'])->name('products');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
});

// API ROUTES
Route::get('/api/vendors/{category}', [OrderController::class, 'getVendorsByCategory'])->name('api.vendors');