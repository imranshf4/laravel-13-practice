<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {

    // Verification notice page
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // Verify email using signed URL
    Route::get(
        '/email/verify/{id}/{hash}',
        function (EmailVerificationRequest $request) {
            $request->fulfill();

            return redirect('/dashboard')
                ->with('success', 'Email verified successfully!');
        }
    )->middleware(['signed', 'throttle:6,1'])
      ->name('verification.verify');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect('/dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with(
            'status',
            'verification-link-sent'
        );
    })->middleware('throttle:6,1')
      ->name('verification.send');

});

require __DIR__.'/auth.php';
