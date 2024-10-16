<?php

namespace App\Http\Controllers;

use App\Models\JobAdvertisement;
use App\Models\JobPosition;
use App\Models\Application;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class JobAdvertisementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $jobPositions = JobPosition::orderBy('title', 'asc')->get();

        return Inertia::render('JobAds/Add', [
            'jobPositions' => $jobPositions
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function reports()
    {
        $filters = Request::only(['tab']);
        $postedJobsCounts = JobAdvertisement::with('jobPosition')->where('is_draft', false)->count();
        $draftJobsCounts = JobAdvertisement::with('jobPosition')->where('is_draft', true)->count();
        $jobAdvertisements;
        $applications = Application::query()
            ->with('applicant')
            ->where('status', 1)->get();

        if (isset($filters['tab']) && $filters['tab'] === 'drafts') {
            $jobAdvertisements = JobAdvertisement::with('jobPosition')->where('is_draft', true)->get();
        } else {
            $jobAdvertisements = JobAdvertisement::with('jobPosition')->where('is_draft', false)->get();
        }

        return Inertia::render('JobAds/Reports', [
            'jobAdvertisements' => $jobAdvertisements,
            'filters' => $filters,
            'postedJobsCount' => $postedJobsCounts,
            'draftJobsCounts' => $draftJobsCounts,
            'applications' => $applications
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function saveDraft()
    {
        $jobPositionValidate = Request::validate([
            'job_position_id' => ['nullable'],
            'employer_id' => ['nullable'],
            'role' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'position_level' => ['nullable', 'string'],
            'years_of_experience' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
        ]);

        $data = JobAdvertisement::updateOrCreate(
            ['id' => Request::get('id')],
            [
                'job_position_id' => $jobPositionValidate['job_position_id'],
                'employer_id' => $jobPositionValidate['employer_id'],
                'role' => $jobPositionValidate['role'],
                'skills' => json_encode($jobPositionValidate['skills']),
                'position_level' => $jobPositionValidate['position_level'],
                'years_of_experience' => $jobPositionValidate['years_of_experience'],
                'location' => $jobPositionValidate['location'],
                'is_draft' => true,
                'is_active' => 0
            ]
        );
    }

    public function editDraft($id)
    {
        $jobPositionValidate = Request::validate([
            'job_position_id' => ['nullable'],
            'employer_id' => ['nullable'],
            'role' => ['nullable', 'string'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:50'],
            'position_level' => ['nullable', 'string'],
            'years_of_experience' => ['nullable', 'string'],
            'location' => ['nullable', 'string'],
        ]);

        $data = JobAdvertisement::updateOrCreate(
            ['id' => $id],
            [
                'job_position_id' => $jobPositionValidate['job_position_id'],
                'employer_id' => $jobPositionValidate['employer_id'],
                'role' => $jobPositionValidate['role'],
                'skills' => json_encode($jobPositionValidate['skills']),
                'position_level' => $jobPositionValidate['position_level'],
                'years_of_experience' => $jobPositionValidate['years_of_experience'],
                'location' => $jobPositionValidate['location'],
                'is_draft' => true,
                'is_active' => 0
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $jobPositionValidate = Request::validate([
            'job_position_id' => ['required'],
            'employer_id' => ['required'],
            'role' => ['required', 'string'],
            'skills' => ['required', 'array'],
            'skills.*' => ['string', 'max:50'],
            'position_level' => ['required', 'string'],
            'years_of_experience' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        JobAdvertisement::create([
            'job_position_id' => $jobPositionValidate['job_position_id'],
            'employer_id' => $jobPositionValidate['employer_id'],
            'role' => $jobPositionValidate['role'],
            'skills' => json_encode($jobPositionValidate['skills']),
            'position_level' => $jobPositionValidate['position_level'],
            'years_of_experience' => $jobPositionValidate['years_of_experience'],
            'location' => $jobPositionValidate['location'],
            'is_draft' => false,
            'is_active' => 1
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(JobAdvertisement $jobAdvertisement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jobAdvertisement = JobAdvertisement::with('jobPosition')->find($id);

        if (!$jobAdvertisement) {
            return back()->with('message', 'No Job Advertisement Found');
        }

        $jobPositions = JobPosition::orderBy('title', 'asc')->get();

        return Inertia::render('JobAds/Edit', [
            'jobPositions' => $jobPositions,
            'jobAdvertisement' => $jobAdvertisement
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $jobAdvertisementValidate = Request::validate([
            'job_position_id' => ['required'],
            'employer_id' => ['required'],
            'role' => ['required', 'string'],
            'skills' => ['required', 'array'],
            'skills.*' => ['string', 'max:50'],
            'position_level' => ['required', 'string'],
            'years_of_experience' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        $jobAdvertisement = JobAdvertisement::findOrFail($id);

        if($jobAdvertisementValidate['job_position_id'] !== $jobAdvertisement->job_position_id) {
            $jobAdvertisement->job_position_id = $jobAdvertisementValidate['job_position_id'];
        }

        if($jobAdvertisementValidate['role'] !== $jobAdvertisement->role) {
            $jobAdvertisement->role = $jobAdvertisementValidate['role'];
        }

        if($jobAdvertisementValidate['skills'] !== $jobAdvertisement->skills) {
            $jobAdvertisement->skills = json_encode($jobAdvertisementValidate['skills']);
        }

        if($jobAdvertisementValidate['position_level'] !== $jobAdvertisement->position_level) {
            $jobAdvertisement->position_level = $jobAdvertisementValidate['position_level'];
        }
        
        if($jobAdvertisementValidate['years_of_experience'] !== $jobAdvertisement->years_of_experience) {
            $jobAdvertisement->years_of_experience = $jobAdvertisementValidate['years_of_experience'];
        }

        if($jobAdvertisementValidate['location'] !== $jobAdvertisement->location) {
            $jobAdvertisement->location = $jobAdvertisementValidate['location'];
        }

        if ($jobAdvertisement->is_draft) {
            $jobAdvertisement->is_active = 1;
            $jobAdvertisement->is_draft = false;
        }

        $jobAdvertisement->save();
    }

       /**
     * Update the specified resource in storage.
     */
    public function activate($id)
    {
        $jobAdvertisement = JobAdvertisement::findOrFail($id);
        $jobAdvertisement->is_active = 1;
        $jobAdvertisement->save();
    }

           /**
     * Update the specified resource in storage.
     */
    public function deactivate($id)
    {
        $jobAdvertisement = JobAdvertisement::findOrFail($id);
        $jobAdvertisement->is_active = 0;
        $jobAdvertisement->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobAdvertisement $jobAdvertisement)
    {
        //
    }
}
