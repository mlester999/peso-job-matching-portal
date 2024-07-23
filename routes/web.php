<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\JobAdvertisementController;
use App\Models\User;
use App\Models\Employer;
use App\Models\Applicant;

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
    $userCount = Applicant::count();
    $employerCount = Employer::count();
    $applicantCount = Applicant::whereHas('applications')->count();

    return Inertia::render('Dashboard', [
        'userCount' => $userCount,
        'employerCount' => $employerCount,
        'applicantCount' => $applicantCount,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware([
    'auth',
    'verified',
])->group(function () {
    Route::group(['middleware' => 'role:admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::group(['prefix' => 'employers', 'as' => 'employers.'], function() {
            Route::get('/', [EmployerController::class, 'index'])->name('index');

            Route::get('/add', [EmployerController::class, 'add'])->name('add');

            Route::get('/edit/{id}', [EmployerController::class, 'edit'])->name('edit');
    
            Route::post('/store', [EmployerController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [EmployerController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [EmployerController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'users', 'as' => 'users.'], function() {
            Route::get('/', [UserController::class, 'index'])->name('index');

            Route::get('/add', [UserController::class, 'add'])->name('add');

            Route::get('/edit/{id}', [UserController::class, 'edit'])->name('edit');
    
            Route::post('/store', [UserController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'applicants', 'as' => 'applicants.'], function() {
            Route::get('/', [ApplicantController::class, 'index'])->name('index');

            Route::get('/edit/{id}', [ApplicantController::class, 'edit'])->name('edit');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [ApplicantController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'job-positions', 'as' => 'job-positions.'], function() {
            Route::get('/', [JobPositionController::class, 'index'])->name('index');

            Route::get('/add', [JobPositionController::class, 'add'])->name('add');

            Route::get('/edit/{id}', [JobPositionController::class, 'edit'])->name('edit');
    
            Route::post('/store', [JobPositionController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [JobPositionController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [JobPositionController::class, 'delete'])->name('delete');
        });
    });

});

Route::middleware([
    'auth',
    'verified',
])->group(function () {
    Route::group(['middleware' => 'role:employer', 'prefix' => 'employer', 'as' => 'employer.'], function () {
        Route::group(['prefix' => 'job-ads', 'as' => 'job-ads.'], function() {
            Route::get('/', [JobAdvertisementController::class, 'index'])->name('index');

            Route::get('/edit/{id}', [JobAdvertisementController::class, 'edit'])->name('edit');
    
            Route::post('/store', [JobAdvertisementController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [JobAdvertisementController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [JobAdvertisementController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'applicants', 'as' => 'applicants.'], function() {
            Route::get('/', [ApplicantController::class, 'index'])->name('index');

            Route::get('/edit/{id}', [ApplicantController::class, 'edit'])->name('edit');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [ApplicantController::class, 'update'])->name('update');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

require __DIR__ . '/auth.php';
