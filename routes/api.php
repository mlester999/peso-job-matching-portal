<?php

use App\Http\Controllers\ApplicantController;
use App\Http\Controllers\JobPositionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    $user = $request->user()->load(['applicant', 'applicant.applications']); // Eager load the 'applicant' relationship
    return response()->json($user);
});

// Route::middleware('auth:sanctum')->get('/job-positions/details', function (Request $request) {
//     $jobPositionDetails = JobPosition::all();
//     return response()->json($jobPositionDetails);
// });

// Authenticating and signing up user
Route::post('login', [ApplicantController::class, 'login']);
Route::post('register', [ApplicantController::class, 'register']);
Route::post('logout', [ApplicantController::class, 'logout']);
Route::post('refresh', [ApplicantController::class, 'refresh']);
Route::get('details', [ApplicantController::class, 'details']);

// Job Position
Route::get('job-positions', [JobPositionController::class, 'jobPositions']);

// Verify user
Route::post('verify-using-email/{id}', [ApplicantController::class, 'verifyUsingEmail']);
Route::post('verify-using-sms/{id}', [ApplicantController::class, 'verifyUsingSms']);
Route::put('verify-email-address/{id}', [ApplicantController::class, 'verifyEmailAddress']);
Route::put('verify-contact-number/{id}', [ApplicantController::class, 'verifyContactNumber']);

// Onboarding user
Route::post('submit-personal-information/{id}', [ApplicantController::class, 'submitPersonalInformation']);
Route::post('submit-educational-background/{id}', [ApplicantController::class, 'submitEducationalBackground']);
Route::post('submit-work-experience/{id}', [ApplicantController::class, 'submitWorkExperience']);
Route::post('submit-skills/{id}', [ApplicantController::class, 'submitSkills']);
Route::post('confirm-onboarding/{id}', [ApplicantController::class, 'confirmOnboarding']);

// Portal user
Route::put('my-profile/update-personal-information/{id}', [ApplicantController::class, 'updatePersonalInformation']);
Route::put('my-profile/update-password/{id}', [ApplicantController::class, 'updatePassword']);
Route::post('my-profile/logout-other-sessions/{id}', [ApplicantController::class, 'logoutOtherSessions']);

