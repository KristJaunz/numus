<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});


\Route::get('api/upload', function () {

    return response()->json(['status' => 'success', 'message' => 'Data received successfully']);
});

\Route::get('api/aaaaxxx34r3t4g334g4g3g34fgsdfsd/store-list/xw2rf3f', function () {

    return response()->json(\App\Models\Shop::pluck('id','name')->toArray());
});





require __DIR__.'/auth.php';
