<?php

namespace App\Http\Controllers;

use App\Models\JobAdvertisement;
use App\Models\JobPosition;
use App\Models\Application;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            $jobAdvertisements = JobAdvertisement::with('jobPosition')
            ->where('is_draft', true)
            ->orderBy('id', 'desc')
            ->get();
        } else {
            $jobAdvertisements = JobAdvertisement::with('jobPosition')
            ->where('is_draft', false)
            ->orderBy('id', 'desc')
            ->get();
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
            'industry' => ['nullable', 'string'],
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
                'industry' => $jobPositionValidate['industry'],
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
            'industry' => ['nullable', 'string'],
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
                'industry' => $jobPositionValidate['industry'],
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
            'industry' => ['required', 'string'],
        ]);

        JobAdvertisement::create([
            'job_position_id' => $jobPositionValidate['job_position_id'],
            'employer_id' => $jobPositionValidate['employer_id'],
            'role' => $jobPositionValidate['role'],
            'skills' => json_encode($jobPositionValidate['skills']),
            'position_level' => $jobPositionValidate['position_level'],
            'years_of_experience' => $jobPositionValidate['years_of_experience'],
            'location' => $jobPositionValidate['location'],
            'industry' => $jobPositionValidate['industry'],
            'is_draft' => false,
            'is_active' => 1
        ]);
    }

    public function addNewSkill($id)
    {
        $jobPositionValidate = Request::validate([
            'skillToBeAdd' => ['required'],
        ]);

        $jobPosition = JobPosition::findOrFail($id);

        $currentSkills = json_decode($jobPosition->skills);

        $currentSkills[] = $jobPositionValidate['skillToBeAdd'];

        $jobPosition->skills = $currentSkills;

        $jobPosition->save();
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
            'industry' => ['required', 'string'],
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

        if($jobAdvertisementValidate['industry'] !== $jobAdvertisement->industry) {
            $jobAdvertisement->industry = $jobAdvertisementValidate['industry'];
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

    public function topSkillsDemand()
    {
        $jobAdvertisements = JobAdvertisement::where('is_draft', 0)
        ->where('is_active', 1)
        ->get();

        $skillsCount = $jobAdvertisements->flatMap(function ($job) {
            // Decode the JSON string into an array
            return json_decode($job->skills, true);
        })->countBy();
        
        // Step 3: Get the top 3 skills
        $topSkills = $skillsCount->sortDesc()->take(3);

        $topSkillsArray = $topSkills->map(function ($count, $skill) {
            return ['skill' => $skill, 'count' => $count];
        })->values()->toArray();

        return $topSkillsArray;
    }

    public function industryGrowth()
    {
        $currentYear = Carbon::now()->year;

        $jobAdvertisements = JobAdvertisement::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'), 'industry')
            ->where('is_draft', 0)
            ->where('is_active', 1)
            ->whereYear('created_at', $currentYear) // Filter for the current year
            ->get();

        $monthlyIndustryCount = [];
        $overallIndustryCount = [];

        foreach ($jobAdvertisements as $job) {
            $month = $job->month;
            $industry = $job->industry;

            // Initialize the month if it doesn't exist
            if (!isset($monthlyIndustryCount[$month])) {
                $monthlyIndustryCount[$month] = [];
            }

            // Initialize the industry count for the month if it doesn't exist
            if (!isset($monthlyIndustryCount[$month][$industry])) {
                $monthlyIndustryCount[$month][$industry] = 0;
            }

            // Increment the count for the industry for the month
            $monthlyIndustryCount[$month][$industry]++;

            // Increment the overall count for the industry
            if (!isset($overallIndustryCount[$industry])) {
                $overallIndustryCount[$industry] = 0;
            }
            $overallIndustryCount[$industry]++;
        }

        $topIndustriesPerMonth = [];

        foreach ($monthlyIndustryCount as $month => $industries) {
            // Sort industries by count in descending order and take the top 3
            $topIndustries = collect($industries)
                ->sortDesc()
                ->take(3)
                ->map(function ($count, $industry) {
                    return ['industry' => $industry, 'count' => $count];
                })
                ->values()
                ->toArray();

            $topIndustriesPerMonth[$month] = $topIndustries;
        }


        $overallTopIndustries = collect($overallIndustryCount)
            ->sortDesc()
            ->take(3)
            ->map(function ($count, $industry) {
                return ['industry' => $industry, 'count' => $count];
            })
            ->values()
            ->toArray();


        return response()->json([
            'top_industries_per_month' => $topIndustriesPerMonth,
            'overall_top_industries' => $overallTopIndustries,
        ]);
    }

    public function salaryTrends()
    {
        // Logic for salary trends will go here
    }

    public function topHiringCompanies()
    {

        $currentYear = Carbon::now()->year;

        $startDate = Carbon::create($currentYear, 1, 1)->startOfDay(); // January 1st

        $endDate = Carbon::now()->subMonthNoOverflow()->endOfMonth(); // Last day of the previous month

        $topHiringCompanies = DB::table('applications')
            ->join('job_advertisements', 'applications.job_advertisement_id', '=', 'job_advertisements.id')
            ->join('employers', 'job_advertisements.employer_id', '=', 'employers.id')
            ->select('employers.name', DB::raw('COUNT(applications.id) as total_applications'))
            ->where('applications.status', 8)
            ->whereBetween('applications.created_at', [$startDate, $endDate]) // Filter from January to last month
            ->groupBy('employers.name')
            ->orderByDesc('total_applications')
            ->take(3) // Get the top 3 companies
            ->get();

        return response()->json($topHiringCompanies);
    }

    public function locationBasedTrends()
    {
        $currentYear = Carbon::now()->year;

        $startDate = Carbon::create($currentYear, 1, 1)->startOfDay(); // January 1st

        $endDate = Carbon::now()->subMonthNoOverflow()->endOfMonth(); // Last day of the previous month

        $topLocationBasedTrends = DB::table('applications')
            ->select('applications.barangay', DB::raw('COUNT(applications.barangay) as total_barangays'))
            ->where('applications.status', 8)
            ->whereBetween('applications.created_at', [$startDate, $endDate]) // Filter from January to last month
            ->groupBy('applications.barangay')
            ->orderByDesc('total_barangays')
            ->take(3) // Get the top 3 companies
            ->get();

        return response()->json($topLocationBasedTrends);
    }

    public function skillBasedTrends()
    {
        // Logic for skill-based trends will go here
    }
}
