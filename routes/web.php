<?php

use Illuminate\Support\Facades\Route;
use App\Services\WeatherAdapterAgent;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Halaman untuk menjalankan agent secara manual via browser
Route::get('/agent', function () {
    return view('agent');
})->middleware(['auth', 'verified'])->name('agent');

Route::post('/agent/run', function () {
    $date  = request('date', now()->format('Y-m-d'));
    $agent = app(WeatherAdapterAgent::class);
    $result = $agent->executeAgentForDate($date);

    return back()->with([
        'success' => true,
        'count'   => count($result),
        'date'    => $date,
        'result'  => $result,
    ]);
})->middleware(['auth', 'verified'])->name('agent.run');

require __DIR__.'/auth.php';
