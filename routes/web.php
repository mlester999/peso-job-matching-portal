<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
// use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployerController;
use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\JobAdvertisementController;
use App\Models\User;
use App\Models\Employer;
use App\Models\Applicant;
use App\Models\JobAdvertisement;
use App\Models\Application;
use Carbon\Carbon;

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
    // Initialize an array with 12 zeros, representing each month.
    $qualifiedApplicantsData = array_fill(0, 12, 0);
    $disqualifiedApplicantsData = array_fill(0, 12, 0);
    $jobAdvertisementsData = array_fill(0, 12, 0);

    $authUser = Auth::user();
    $userCount = Applicant::count();
    $employerCount = Employer::count();
    $applicantCount = Applicant::whereHas('applications')->count();

    if($authUser->employer) {
        $applicationCount = Application::whereHas('jobAdvertisement', function ($query) {
            $authUser = Auth::user();
            $query->where('employer_id', $authUser->employer->id);
        })->count();
        $jobAdvertisementCount = JobAdvertisement::where('employer_id', $authUser->employer->id)->where('is_active', 1)->count();

        $qualifiedApplicants = Applicant::whereHas('applications', function ($query) {
                $query->where('status', 8);
            })->get();
        $disqualifiedApplicants = Applicant::whereHas('applications', function ($query) {
            $query->where('status', 0);
        })->get();
        $jobAdvertisements = JobAdvertisement::where('employer_id', $authUser->employer->id)->where('is_active', 1)->get();

        // Loop through each applicant and check the month of the application.
        foreach ($qualifiedApplicants as $applicant) {
            foreach ($applicant->applications as $application) {
                if ($application->jobAdvertisement) {
                    if ($application->status == 8 && $application->jobAdvertisement->employer_id === $authUser->employer->id) {
                        // Assuming 'created_at' is the date field for each application.
                        $month = Carbon::parse($application->created_at)->month;
    
                        // Increment the corresponding month in the array.
                        $qualifiedApplicantsData[$month - 1]++; // Subtract 1 because arrays are 0-indexed.
                    }
                }
            }
        }

        // Loop through each applicant and check the month of the application.
        foreach ($disqualifiedApplicants as $applicant) {
            foreach ($applicant->applications as $application) {
                if ($application->jobAdvertisement) {
                    if ($application->status == 0 && $application->jobAdvertisement->employer_id === $authUser->employer->id) {
                        // Assuming 'created_at' is the date field for each application.
                        $month = Carbon::parse($application->created_at)->month;
    
                        // Increment the corresponding month in the array.
                        $disqualifiedApplicantsData[$month - 1]++; // Subtract 1 because arrays are 0-indexed.
                    }
                }
            }
        }

        // Loop through each job ads and check the month of the application.
        foreach ($jobAdvertisements as $jobAd) {
            // Assuming 'created_at' is the date field for each application.
            $month = Carbon::parse($jobAd->created_at)->month;

            // Increment the corresponding month in the array.
            $jobAdvertisementsData[$month - 1]++; // Subtract 1 because arrays are 0-indexed.
        }

        return Inertia::render('Dashboard', [
            'userCount' => $userCount,
            'employerCount' => $employerCount,
            'applicantCount' => $applicantCount,
            'jobAdvertisementCount' => $jobAdvertisementCount,
            'applicationCount' => $applicationCount,
            'qualifiedApplicants' => $qualifiedApplicantsData,
            'disqualifiedApplicants' => $disqualifiedApplicantsData,
            'jobAdvertisements' => $jobAdvertisementsData
        ]);
    }
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

        Route::group(['prefix' => 'applications', 'as' => 'applications.'], function() {
            Route::get('/', [ApplicantController::class, 'index'])->name('index');

            Route::get('/view/{id}', [ApplicantController::class, 'view'])->name('view');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [ApplicantController::class, 'update'])->name('update');

            Route::put('/update-status/{id}', [ApplicantController::class, 'updateStatus'])->name('updateStatus');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'interview', 'as' => 'interview.'], function() {
            Route::get('/', [ApplicantController::class, 'indexInterview'])->name('indexInterview');

            Route::get('/view/{id}', [ApplicantController::class, 'viewInterview'])->name('viewInterview');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-interview/{id}', [ApplicantController::class, 'updateInterview'])->name('updateInterview');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'for-interview', 'as' => 'for-interview.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForInterview'])->name('indexForInterview');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForInterview'])->name('viewForInterview');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-interview/{id}', [ApplicantController::class, 'updateForInterview'])->name('updateForInterview');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'requirements', 'as' => 'requirements.'], function() {
            Route::get('/', [ApplicantController::class, 'indexRequirements'])->name('indexRequirements');

            Route::get('/view/{id}', [ApplicantController::class, 'viewRequirements'])->name('viewRequirements');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-requirements/{id}', [ApplicantController::class, 'updateRequirements'])->name('updateRequirements');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'for-requirements', 'as' => 'for-requirements.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForRequirements'])->name('indexForRequirements');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForRequirements'])->name('viewForRequirements');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-requirements/{id}', [ApplicantController::class, 'updateForRequirements'])->name('updateForRequirements');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'qualified', 'as' => 'qualified.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForQualified'])->name('indexForQualified');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForQualified'])->name('viewForQualified');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-qualified/{id}', [ApplicantController::class, 'updateForQualified'])->name('updateForQualified');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'for-deployment', 'as' => 'for-deployment.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForDeployment'])->name('indexForDeployment');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForDeployment'])->name('viewForDeployment');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-deployment/{id}', [ApplicantController::class, 'updateForDeployment'])->name('updateForDeployment');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'deployed', 'as' => 'deployed.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForDeployed'])->name('indexForDeployed');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForDeployed'])->name('viewForDeployed');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-deployed/{id}', [ApplicantController::class, 'updateForDeployed'])->name('updateForDeployed');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'monitoring', 'as' => 'monitoring.'], function() {
            Route::get('/', [MonitoringController::class, 'index'])->name('index');
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
        Route::group(['prefix' => 'reports', 'as' => 'reports.'], function() {
            Route::get('/', [JobAdvertisementController::class, 'reports'])->name('index');
        });

        Route::group(['prefix' => 'job-ads', 'as' => 'job-ads.'], function() {
            Route::get('/add', [JobAdvertisementController::class, 'index'])->name('index');

            Route::get('/edit/{id}', [JobAdvertisementController::class, 'edit'])->name('edit');

            Route::post('/draft', [JobAdvertisementController::class, 'saveDraft']);

            Route::post('/edit-draft/{id}', [JobAdvertisementController::class, 'editDraft']);
    
            Route::post('/store', [JobAdvertisementController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [JobAdvertisementController::class, 'update'])->name('update');

            Route::put('/activate/{id}', [JobAdvertisementController::class, 'activate'])->name('activate');

            Route::put('/deactivate/{id}', [JobAdvertisementController::class, 'deactivate'])->name('deactivate');
    
            Route::put('/add-skill/{id}', [JobAdvertisementController::class, 'addNewSkill'])->name('addSkill');

            Route::delete('/delete/{id}', [JobAdvertisementController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'applications', 'as' => 'applications.'], function() {
            Route::get('/', [ApplicantController::class, 'index'])->name('index');

            Route::get('/view/{id}', [ApplicantController::class, 'view'])->name('view');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update/{id}', [ApplicantController::class, 'update'])->name('update');

            Route::put('/update-status/{id}', [ApplicantController::class, 'updateStatus'])->name('updateStatus');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'interview', 'as' => 'interview.'], function() {
            Route::get('/', [ApplicantController::class, 'indexInterview'])->name('indexInterview');

            Route::get('/view/{id}', [ApplicantController::class, 'viewInterview'])->name('viewInterview');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-interview/{id}', [ApplicantController::class, 'updateInterview'])->name('updateInterview');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        
        Route::group(['prefix' => 'for-interview', 'as' => 'for-interview.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForInterview'])->name('indexForInterview');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForInterview'])->name('viewForInterview');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-interview/{id}', [ApplicantController::class, 'updateForInterview'])->name('updateForInterview');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'requirements', 'as' => 'requirements.'], function() {
            Route::get('/', [ApplicantController::class, 'indexRequirements'])->name('indexRequirements');

            Route::get('/view/{id}', [ApplicantController::class, 'viewRequirements'])->name('viewRequirements');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-requirements/{id}', [ApplicantController::class, 'updateRequirements'])->name('updateRequirements');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'for-requirements', 'as' => 'for-requirements.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForRequirements'])->name('indexForRequirements');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForRequirements'])->name('viewForRequirements');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-requirements/{id}', [ApplicantController::class, 'updateForRequirements'])->name('updateForRequirements');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'qualified', 'as' => 'qualified.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForQualified'])->name('indexForQualified');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForQualified'])->name('viewForQualified');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-qualified/{id}', [ApplicantController::class, 'updateForQualified'])->name('updateForQualified');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'for-deployment', 'as' => 'for-deployment.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForDeployment'])->name('indexForDeployment');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForDeployment'])->name('viewForDeployment');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-deployment/{id}', [ApplicantController::class, 'updateForDeployment'])->name('updateForDeployment');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'deployed', 'as' => 'deployed.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForDeployed'])->name('indexForDeployed');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForDeployed'])->name('viewForDeployed');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-deployed/{id}', [ApplicantController::class, 'updateForDeployed'])->name('updateForDeployed');
    
            Route::delete('/delete/{id}', [ApplicantController::class, 'delete'])->name('delete');
        });

        Route::group(['prefix' => 'disqualified', 'as' => 'disqualified.'], function() {
            Route::get('/', [ApplicantController::class, 'indexForDisqualified'])->name('indexForDisqualified');

            Route::get('/view/{id}', [ApplicantController::class, 'viewForDisqualified'])->name('viewForDisqualified');
    
            Route::post('/store', [ApplicantController::class, 'store'])->name('store');
    
            Route::put('/update-for-disqualified/{id}', [ApplicantController::class, 'updateForDisqualified'])->name('updateForDisqualified');
    
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
