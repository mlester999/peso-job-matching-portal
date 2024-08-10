<?php

namespace App\Http\Controllers;

use App\Models\JobPosition;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class JobPositionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $jobPositions = JobPosition::query()
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(title) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(description) LIKE LOWER(?)', ['%' . $search . '%']);
            });

            // Use this if we like to perform a case-insensitive and approximate matching search

            // $query->where(function ($query) use ($search) {
            //     $lowerSearch = strtolower($search);
            //     $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
            //           ->orWhereRaw('SOUNDEX(last_name) = SOUNDEX(?)', [$lowerSearch]);
            // })
            // ->orWhere(function ($query) use ($search) {
            //     $lowerSearch = strtolower($search);
            //     $query->whereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%'])
            //           ->orWhereRaw('SOUNDEX(first_name) = SOUNDEX(?)', [$lowerSearch]);
            // });
        })
        ->orderBy('id', 'asc')
        ->paginate(10)
        ->withQueryString()
        ->through(fn($jobPosition) => [
            'id' => $jobPosition->id,
            'title' => $jobPosition->title,
            'description' => $jobPosition->description,
            'skills' => $jobPosition->skills,
            'is_active' => $jobPosition->is_active,
            'created_at' => $jobPosition->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $jobPositions->currentPage();
        $lastPage = $jobPositions->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $jobPositions->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $jobPositions->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $jobPositions->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $jobPositions->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $jobPositions->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $jobPositions->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $jobPositions->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('JobPositions/Index', [
            'jobPositions' => $jobPositions,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
        ]);
    }

    /**
     * Display the add page for job positions.
     */
    public function add()
    {
        return Inertia::render('JobPositions/Add');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        $applicantValidate = Request::validate([
            'title' => ['required', 'max:50', 'unique:job_positions'],
            'description' => ['required', 'max:200'],
            'skills' => ['required', 'array'],
            'skills.*' => ['string', 'max:50'],
        ]);

        JobPosition::create([
            'title' => $applicantValidate['title'],
            'description' => $applicantValidate['description'],
            'skills' => json_encode($applicantValidate['skills']),
            'is_active' => 1
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(JobPosition $jobPosition)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $jobPosition = JobPosition::find($id);

        return Inertia::render('JobPositions/Edit', [
            'jobPosition' => $jobPosition,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $jobPositionValidate = Request::validate([
            'title' => ['required', 'max:50', Rule::unique('job_positions')->ignore(JobPosition::findOrFail($id)->id)],
            'description' => ['required', 'max:200'],
            'skills' => ['required', 'array'],
            'skills.*' => ['string', 'max:50'],
            'is_active' => ['required', 'max:1'],
        ]);

        $jobPosition = JobPosition::findOrFail($id);

        if($jobPositionValidate['title'] !== $jobPosition->title) {
            $jobPosition->title = $jobPositionValidate['title'];
        }

        if($jobPositionValidate['description'] !== $jobPosition->description) {
            $jobPosition->description = $jobPositionValidate['description'];
        }

        if($jobPositionValidate['skills'] !== $jobPosition->skills) {
            $jobPosition->skills = json_encode($jobPositionValidate['skills']);
        }

        if($jobPositionValidate['is_active'] !== $jobPosition->is_active) {
            $jobPosition->is_active = $jobPositionValidate['is_active'];
        }

        $jobPosition->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employer $employer)
    {
        //
    }

    /**
     * Show the job positions.
     */
    public function jobPositions()
    {
        $jobPositions = JobPosition::all();

        return response()->json(['jobPositions' => $jobPositions], 201);
    }
}
