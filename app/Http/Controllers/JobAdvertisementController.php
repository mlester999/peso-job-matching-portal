<?php

namespace App\Http\Controllers;

use App\Models\JobAdvertisement;
use App\Models\JobPosition;
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

        if (isset($filters['tab']) && $filters['tab'] === 'drafts') {
            $jobAdvertisements = JobAdvertisement::with('jobPosition')->where('is_draft', true)->get();
        } else {
            $jobAdvertisements = JobAdvertisement::with('jobPosition')->where('is_draft', false)->get();
        }

        return Inertia::render('JobAds/Reports', [
            'jobAdvertisements' => $jobAdvertisements,
            'filters' => $filters,
            'postedJobsCount' => $postedJobsCounts,
            'draftJobsCounts' => $draftJobsCounts
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
            'role' => ['required', 'string'],
            'skills' => ['required', 'array'],
            'skills.*' => ['string', 'max:50'],
            'position_level' => ['required', 'string'],
            'years_of_experience' => ['required', 'string'],
            'location' => ['required', 'string'],
        ]);

        JobAdvertisement::create([
            'job_position_id' => $jobPositionValidate['job_position_id'],
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
    public function update(Request $request, JobAdvertisement $jobAdvertisement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JobAdvertisement $jobAdvertisement)
    {
        //
    }
}
