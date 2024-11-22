<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Employer;
use App\Models\JobAdvertisement;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MonitoringController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Request::only(['search', 'userType']);
        $searchReq = Request::input('search');
        $userTypeReq = Request::input('userType');

        if ($userTypeReq === 'employers') {
            $users = Employer::query()
            ->with('user')
            ->withCount(['jobAdvertisementsApplications as total_hired' => function ($query) {
                $query->where('status', 8); // Count applications with status 8
            }])
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($query) use ($search) {
                        $query->whereRaw('LOWER(email) LIKE LOWER(?)', ['%' . $search . '%'])
                            ->orWhereRaw('LOWER(contact_number) LIKE LOWER(?)', ['%' . $search . '%']);
                    })
                            ->orWhereRaw('LOWER(name) LIKE LOWER(?)', ['%' . $search . '%']);
                });
            })
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($employer) => [
                'id' => $employer->id,
                'name' => $employer->name,
                'province' => $employer->province,
                'city' => $employer->city,
                'barangay' => $employer->barangay,
                'street_address' => $employer->street_address,
                'zip_code' => $employer->zip_code,
                'email' => $employer->user->email,
                'contact_number' => $employer->contact_number,
                'total_hired' => $employer->total_hired,
                'is_active' => $employer->user->is_active,
                'created_at' => $employer->created_at,
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }

            if (empty($userTypeReq)) {
                unset($filters['userType']);
            }
    
            $currentPage = $users->currentPage();
            $lastPage = $users->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $users->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $users->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $users->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $users->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $users->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $users->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $users->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Monitoring/Index', [
                'users' => $users,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
           } else {
        $users = Application::query()
        ->with('applicant.user')
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('applicant.user', function ($query) use ($search) {
                    $query->whereRaw('LOWER(email) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(contact_number) LIKE LOWER(?)', ['%' . $search . '%']);
                })->orWhereHas('applicant', function ($query) use ($search) {
                    $query->whereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                    ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                });
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
        ->through(fn($user) => [
            'id' => $user->id,
            'first_name' => $user->applicant->first_name,
            'last_name' => $user->applicant->last_name,
            'province' => $user->applicant->province,
            'city' => $user->applicant->city,
            'barangay' => $user->applicant->barangay,
            'street_address' => $user->applicant->street_address,
            'zip_code' => $user->applicant->zip_code,
            'email' => $user->applicant->user->email,
            'contact_number' => $user->applicant->contact_number,
            'is_active' => $user->applicant->user->is_active,
            'status' => $user->status,
            'created_at' => $user->applicant->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        if (empty($userTypeReq)) {
            unset($filters['userType']);
        }

        $currentPage = $users->currentPage();
        $lastPage = $users->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $users->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $users->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $users->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $users->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $users->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $users->url($lastPage),
                'label' => $lastPage,
            ];
        }   

        if ($nextPage !== null) {
            $links[] = [
                'url' => $users->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('Monitoring/Index', [
            'users' => $users,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
        ]);
       }
    }

    /**
     * Display the add page for job positions.
     */
    public function add()
    {   
        //
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        //
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
