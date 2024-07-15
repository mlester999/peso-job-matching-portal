<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployerController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware([
//     'auth',
//     'verified',
// ])->group(function () {
//     Route::group(['middleware' => 'role:admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
//         Route::group(['prefix' => 'employers', 'as' => 'employers.'], function() {
//             Route::get('/', EmployerController::class)->name('index');
    
//             Route::post('/store', [EmployerController::class, 'store'])->name('store');
    
//             Route::put('/update/{id}', [EmployerController::class, 'update'])->name('update');
    
//             Route::delete('/delete/{id}', [EmployerController::class, 'delete'])->name('delete');
//         });
//     });

// });


Route::get('/employers', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('employers');

Route::get('/applicants', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('applicants');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
