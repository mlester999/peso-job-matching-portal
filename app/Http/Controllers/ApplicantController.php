<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\JobAdvertisement;
use App\Models\JobPosition;
use App\Models\User;
use App\Models\Notification;
use App\Mail\SendOtpEmail;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Semaphore\Facades\Semaphore;
use Carbon\Carbon;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Request::only(['search', 'classification', 'location', 'jobAds', 'listedTime', 'jobAdvertisementId']);
        $searchReq = Request::input('search');
        $classificationReq = Request::input('classification');
        $locationReq = Request::input('location');
        $jobPositionReq = Request::input('jobPosition');
        $listedTimeReq = Request::input('listedTime');
        $jobAdvertisementReq = Request::input('jobAdvertisementId');
        $authUser = Auth::user();

        $jobPositions = JobPosition::where('is_active', 1)->get();

        // $applicationQuery = Application::query()->whereJsonContains('skills', strtolower($searchReq))->get();

        // dd($applicationQuery);

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where('employer_id', $authUser->employer->id)->where('is_active', 1)->with('jobPosition')->get();
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 1)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    })->orWhereRaw("
                    LOWER(skills->>'skills') LIKE ?", 
                    ['%' . strtolower($search) . '%']
                );
                });
            })
            ->when($classificationReq, function($query, $search) {
                $query->whereRaw("
                (work_experience IS NOT NULL AND 
                jsonb_array_length(work_experience::jsonb) > 0 AND 
                LOWER((work_experience::jsonb ->> (jsonb_array_length(work_experience::jsonb) - 1)::text || '.industry')) LIKE ?)", 
                ['%' . strtolower($search) . '%']
            );
            })
            ->when($locationReq, function($query, $search) {
                $query->whereRaw('LOWER(barangay) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->when($jobPositionReq, function($query, $search) {
                $query->whereRaw("
                LOWER(skills->>'jobPositionTitle') LIKE ?", 
                ['%' . strtolower($search) . '%']
            );
            })
            ->when($listedTimeReq, function($query, $search) {
                switch (strtolower($search)) {
                    case 'any time':
                        // No date filter for "any time"
                        break;
            
                    case 'today':
                        // Filter for today's records
                        $query->whereDate('created_at', today());
                        break;
            
                    case 'last 3 days':
                        // Filter for the last 3 days
                        $query->whereDate('created_at', '>=', now()->subDays(3));
                        break;
            
                    case 'last week':
                        // Filter for the last 7 days (1 week)
                        $query->whereDate('created_at', '>=', now()->subWeek());
                        break;
            
                    case 'last month':
                        // Filter for the last 30 days (1 month)
                        $query->whereDate('created_at', '>=', now()->subMonth());
                        break;
            
                    default:
                        // In case the search term does not match any of the above
                        $query->whereRaw('LOWER(created_at) LIKE ?', ['%' . strtolower($search) . '%']);
                        break;
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'sex' => $application->sex,
                'birth_date' => $application->birth_date,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'list_of_credentials' => $application->list_of_credentials,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);

            if (empty($searchReq)) {
                unset($filters['search']);
            }

            if (empty($classificationReq)) {
                unset($filters['classification']);
            }

            if (empty($locationReq)) {
                unset($filters['location']);
            }

            if (empty($jobPositionReq)) {
                unset($filters['jobPosition']);
            }

            if (empty($listedTimeReq)) {
                unset($filters['listedTime']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
            return Inertia::render('Applications/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'jobPositions' => $jobPositions,
                'jobAds' => $currentJobAds,
                'jobAdvertisementId' => $jobAdvertisementReq,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 1)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    })->orWhereRaw("
                    LOWER(skills->>'skills') LIKE ?", 
                    ['%' . strtolower($search) . '%']
                );
                });
            })
            ->when($classificationReq, function($query, $search) {
                $query->whereRaw("
                (work_experience IS NOT NULL AND 
                jsonb_array_length(work_experience::jsonb) > 0 AND 
                LOWER((work_experience::jsonb ->> (jsonb_array_length(work_experience::jsonb) - 1)::text || '.industry')) LIKE ?)", 
                ['%' . strtolower($search) . '%']
            );
            })
            ->when($locationReq, function($query, $search) {
                $query->whereRaw('LOWER(barangay) LIKE ?', ['%' . strtolower($search) . '%']);
            })
            ->when($jobPositionReq, function($query, $search) {
                $query->whereRaw("
                    LOWER(skills->>'jobPositionTitle') LIKE ?", 
                    ['%' . strtolower($search) . '%']
                );
        })
            ->when($listedTimeReq, function($query, $search) {
                switch (strtolower($search)) {
                    case 'any time':
                        // No date filter for "any time"
                        break;
            
                    case 'today':
                        // Filter for today's records
                        $query->whereDate('created_at', today());
                        break;
            
                    case 'last 3 days':
                        // Filter for the last 3 days
                        $query->whereDate('created_at', '>=', now()->subDays(3));
                        break;
            
                    case 'last week':
                        // Filter for the last 7 days (1 week)
                        $query->whereDate('created_at', '>=', now()->subWeek());
                        break;
            
                    case 'last month':
                        // Filter for the last 30 days (1 month)
                        $query->whereDate('created_at', '>=', now()->subMonth());
                        break;
            
                    default:
                        // In case the search term does not match any of the above
                        $query->whereRaw('LOWER(created_at) LIKE ?', ['%' . strtolower($search) . '%']);
                        break;
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'list_of_credentials' => $application->list_of_credentials,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }

            if (empty($classificationReq)) {
                unset($filters['classification']);
            }

            if (empty($locationReq)) {
                unset($filters['location']);
            }

            if (empty($jobPositionReq)) {
                unset($filters['jobPosition']);
            }

            if (empty($listedTimeReq)) {
                unset($filters['listedTime']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Applications/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'jobPositions' => $jobPositions,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        }

    }

    /**
     * Display a listing of the resource for interview applications.
     */
    public function indexInterview()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 2)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Interview/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 2)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Interview/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for interview applications.
     */
    public function indexForInterview()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 3)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForInterview/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 3)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForInterview/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for requirements applications.
     */
    public function indexRequirements()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 5)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Requirements/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 5)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [    
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Requirements/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for requirements applications.
     */
    public function indexForRequirements()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 6)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForRequirements/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 6)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [    
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForRequirements/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for qualified applications.
     */
    public function indexForQualified()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 4)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Qualified/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 4)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Qualified/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for for deployment applications.
     */
    public function indexForDeployment()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 7)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForDeployment/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 5)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('ForDeployment/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for deployed applications.
     */
    public function indexForDeployed()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 8)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Deployed/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 6)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Deployed/Index', [
                'applications' => $applications,
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
     * Display a listing of the resource for deployed applications.
     */
    public function indexForDisqualified()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');
        $authUser = Auth::user();

        if ($authUser->employer) {
            $currentJobAds = JobAdvertisement::where(['employer_id' => $authUser->employer->id])->get();

            $applications = Application::query()
            ->with('applicant')
            ->where('status', 0)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            // ->where(function ($query) use ($currentJobAds) {
            //     foreach ($currentJobAds as $jobAd) {
            //         $jobPositionId = $jobAd->jobPosition->id;
            //         $query->orWhereJsonContains('skills->jobPositionId', $jobPositionId);
            //     }
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Disqualified/Index', [
                'applications' => $applications,
                'filters' => $filters,
                'pagination' => [
                    'current_page' => $currentPage,
                    'last_page' => $lastPage,
                    'links' => $links,
                ],
            ]);
        } else {
            $applications = Application::query()
            ->with('applicant')
            ->where('status', 0)
            // ->whereHas('applications', function ($query) {
            //     $query->where('status', 1);
            // })
            ->when($searchReq, function($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('applicant', function ($query) use ($search) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->whereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                                  ->orWhereRaw('LOWER(contact_number) LIKE ?', ['%' . strtolower($search) . '%']);
                        })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
                    });
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($application) => [
                'id' => $application->id,
                'first_name' => $application->applicant->first_name,
                'middle_name' => $application->applicant->middle_name,
                'last_name' => $application->applicant->last_name,
                'province' => $application->province,
                'city' => $application->city,
                'barangay' => $application->barangay,
                'street_address' => $application->street_address,
                'zip_code' => $application->zip_code,
                'email' => $application->applicant->user->email,
                'contact_number' => $application->applicant->contact_number,
                'education' => $application->education,
                'work_experience' => $application->work_experience,
                'skills' => $application->skills,
                'is_active' => $application->applicant->user->is_active,
                'created_at' => Carbon::parse($application->created_at)->format('F d, Y'),
            ]);
    
            if (empty($searchReq)) {
                unset($filters['search']);
            }
    
            $currentPage = $applications->currentPage();
            $lastPage = $applications->lastPage();
            $firstPage = 1;
    
            $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
            $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;
    
            $links = [];
    
            if ($previousPage !== null) {
                $links[] = [
                    'url' => $applications->url($previousPage),
                    'label' => 'Previous',
                ];
            }
    
            $links[] = [
                'url' => $applications->url(1),
                'label' => 1,
            ];
    
            if ($currentPage > 3) {
                $links[] = [
                    'url' => $applications->url($currentPage - 1),
                    'label' => '...',
                ];
            }
    
            $rangeStart = max(2, $currentPage - 1);
            $rangeEnd = min($lastPage - 1, $currentPage + 1);
    
            for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
                $links[] = [
                    'url' => $applications->url($i),
                    'label' => $i,
                ];
            }
    
    
            if ($currentPage < $lastPage - 2) {
                $links[] = [
                    'url' => $applications->url($currentPage + 1),
                    'label' => '...',
                ];
            }
    
            if ($firstPage !== $lastPage) {
                $links[] = [
                    'url' => $applications->url($lastPage),
                    'label' => $lastPage,
                ];
            }
    
            if ($nextPage !== null) {
                $links[] = [
                    'url' => $applications->url($nextPage),
                    'label' => 'Next',
                ];
            }
    
    
            return Inertia::render('Disqualified/Index', [
                'applications' => $applications,
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
     * Display the add page for applicants.
     */
    public function add()
    {
        return Inertia::render('Applicant/Add');
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
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', 'unique:users'],
            'province' => ['required', 'max:50'],
            'city' => ['required', 'max:50'],
            'barangay' => ['required', 'max:50'],
            'street_address' => ['required', 'max:50'],
            'contact_number' => ['required', 'digits:10'],
            'zip_code' => ['required', 'max:50'],
        ]);

        $user = User::create([
            'email' => $applicantValidate['email'],
            'password' => Hash::make('password'),
            'user_type' => 1,
            'is_active' => 1
        ]);

        Applicant::create([
            'user_id' => $user['id'],
            'first_name' => $applicantValidate['first_name'],
            'last_name' => $applicantValidate['last_name'],
            'province' => $applicantValidate['province'],
            'city' => $applicantValidate['city'],
            'barangay' => $applicantValidate['barangay'],
            'street_address' => $applicantValidate['street_address'],
            'contact_number' => $applicantValidate['contact_number'],
            'zip_code' => $applicantValidate['zip_code'],
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Applicant $applicant)
    {
        //
    }

    /**
     * Show the form for viewing the specified resource.
     */
    public function view($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Applications/View', [
            'application' => $application,
        ]);
    }

    /**
     * Show the form for viewing the specified resource.
     */
    public function viewInterview($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Interview/View', [
            'application' => $application,
        ]);
    }

        /**
     * Show the form for viewing the specified resource.
     */
    public function viewForInterview($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('ForInterview/View', [
            'application' => $application,
        ]);
    }

            /**
     * Show the form for viewing the specified resource.
     */
    public function viewRequirements($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Requirements/View', [
            'application' => $application,
        ]);
    }

                /**
     * Show the form for viewing the specified resource.
     */
    public function viewForRequirements($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('ForRequirements/View', [
            'application' => $application,
        ]);
    }

    
            /**
     * Show the form for viewing the specified resource.
     */
    public function viewForQualified($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Qualified/View', [
            'application' => $application,
        ]);
    }

                /**
     * Show the form for viewing the specified resource.
     */
    public function viewForDeployment($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('ForDeployment/View', [
            'application' => $application,
        ]);
    }

                    /**
     * Show the form for viewing the specified resource.
     */
    public function viewForDeployed($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Deployed/View', [
            'application' => $application,
        ]);
    }

                        /**
     * Show the form for viewing the specified resource.
     */
    public function viewForDisqualified($id)
    {
        $application = Application::with('applicant.user')->find($id);

        return Inertia::render('Disqualified/View', [
            'application' => $application,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id)
    {
        $applicantValidate = Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', Rule::unique('users')->ignore(Applicant::findOrFail($id)->user->id)],
            'province' => ['required', 'max:50'],
            'city' => ['required', 'max:50'],
            'barangay' => ['required', 'max:50'],
            'street_address' => ['required', 'max:50'],
            'contact_number' => ['required', 'digits:10'],
            'zip_code' => ['required', 'max:50'],
            'is_active' => ['required', 'max:1'],
        ]);

        $applicant = Applicant::findOrFail($id);
        $user = Applicant::findOrFail($id)->user;

        if($applicantValidate['first_name'] !== $applicant->first_name) {
            $applicant->first_name = $applicantValidate['first_name'];
        }

        if($applicantValidate['last_name'] !== $applicant->last_name) {
            $applicant->last_name = $applicantValidate['last_name'];
        }

        if($applicantValidate['email'] !== $user->email) {
            $user->email = $applicantValidate['email'];
        }

        if($applicantValidate['province'] !== $applicant->province) {
            $applicant->province = $applicantValidate['province'];
        }

        if($applicantValidate['city'] !== $applicant->city) {
            $applicant->city = $applicantValidate['city'];
        }

        if($applicantValidate['barangay'] !== $applicant->barangay) {
            $applicant->barangay = $applicantValidate['barangay'];
        }

        if($applicantValidate['street_address'] !== $applicant->street_address) {
            $applicant->street_address = $applicantValidate['street_address'];
        }

        if($applicantValidate['contact_number'] !== $applicant->contact_number) {
            $applicant->contact_number = $applicantValidate['contact_number'];
        }

        if($applicantValidate['zip_code'] !== $applicant->zip_code) {
            $applicant->zip_code = $applicantValidate['zip_code'];
        }

        if($applicantValidate['is_active'] !== $user->is_active) {
            $user->is_active = $applicantValidate['is_active'];
        }

        $user->save();
        $applicant->save();
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateStatus($id)
    {
        $applicationValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'jobAdvertisementId' => ['nullable', 'string'],
        ]);

        $application = Application::findOrFail($id);

        if($applicationValidate['status'] !== $application->status) {
            $application->status = $applicationValidate['status'];
        }

        if(isset($applicationValidate['jobAdvertisementId'])) {
            if($applicationValidate['jobAdvertisementId'] !== $application->job_advertisement_id) {
                $application->job_advertisement_id = $applicationValidate['jobAdvertisementId'];
            }
        }

        if ($applicationValidate['status']) {
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
        
Congratulations! Your application has been approved.

Please stay tuned for further details regarding your scheduled interview. We will notify you soon with the exact date, time, and venue (or link, if it's a virtual interview).
    
If you have any questions or need further clarification, please feel free to contact us.

Thank you,
PESO Cabuyao";  
        } else {
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
        
Unfortunately, we regret to inform you that you have been disqualified from the application process.

We appreciate your interest and effort, and we encourage you to apply for future opportunities.

If you have any questions or need further clarification, please feel free to contact us.

Thank you,
PESO Cabuyao";  
        }
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

        Notification::create([
            'applicant_id' => $application->applicant->id,
            'title' => 'Congratulations! Your application has been approved.',
            'description' => "Please stay tuned for further details regarding your scheduled interview. We will notify you soon with the exact date, time, and venue (or link, if it's a virtual interview). If you have any questions or need further clarification, please feel free to contact us.",
            'is_viewed' => 1
        ]);

        $application->save();
    }

            /**
     * Update the specified resource in storage.
     */
    public function updateInterview($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'interview_date' => ['required', 'date', 'after_or_equal:today'],
            'interview_time' => ['required', 'date_format:H:i'],
            'interview_type' => ['required', 'in:Online,In-Person'],
            'interview_location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $interviewDate = \Carbon\Carbon::createFromFormat('Y-m-d', $interviewValidate['interview_date'])->format('F j, Y');
            $interviewTime = Carbon::createFromFormat('H:i', $interviewValidate['interview_time'])->format('h:i A');
            $interviewLocation = $interviewValidate['interview_location'];
            $interviewType = $interviewValidate['interview_type'];
            $interviewNotes = $interviewValidate['notes'];
    
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
Your interview for the {$jobPositionTitle} position has been scheduled.

Details:
- Date & Time: {$interviewDate} at {$interviewTime}
- ". ($interviewType == 'Online' ? 'Type' : 'Location') .": " . ($interviewType == 'Online' ? $interviewType : $interviewLocation) . "
- Note: {$interviewNotes}

Please be prepared for your interview. If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => "Your interview for the {$jobPositionTitle} position has been scheduled.",
                'description' => "Details:
- Date & Time: {$interviewDate} at {$interviewTime}
- ". ($interviewType == 'Online' ? 'Type' : 'Location') .": " . ($interviewType == 'Online' ? $interviewType : $interviewLocation) . "
- Note: {$interviewNotes}",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

                /**
     * Update the specified resource in storage.
     */
    public function updateForInterview($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $interviewNotes = $interviewValidate['notes'];
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
Your interview has been approved. Congratulations!

Note: {$interviewNotes}

Please prepare for the requirements that is needed. If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => "Your interview has been approved. Congratulations!",
                'description' => "Note: {$interviewNotes}",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateRequirements($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'requirements' => ['required', 'string', 'max:255'],
            'requirements_deadline' => ['required', 'date', 'after_or_equal:today'],
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $requirementsDeadline = \Carbon\Carbon::createFromFormat('Y-m-d', $interviewValidate['requirements_deadline'])->format('F j, Y');
            $requirements = $interviewValidate['requirements'];
    
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
Below are the requirements you need to bring:
{$requirements}

Failure to submit all required documents by {$requirementsDeadline} may result in disqualification. Please ensure everything is complete and submitted on time.

If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => "You can now proceed by providing the requirements for the job that will be assigned to you.",
                'description' => "Below are the requirements you need to bring:
{$requirements}

Failure to submit all required documents by {$requirementsDeadline} may result in disqualification. Please ensure everything is complete and submitted on time.",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

                /**
     * Update the specified resource in storage.
     */
    public function updateForRequirements($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'notes' => ['required', 'string', 'max:255']
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $interviewNotes = $interviewValidate['notes'];
    
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
Your requirements has been approved. Get ready for your deployment!

Note: {$interviewNotes}

If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => "Your requirements has been approved. Get ready for your deployment!",
                'description' => "Your requirements has been approved. Get ready for your deployment!
Note: {$interviewNotes}",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

                   /**
     * Update the specified resource in storage.
     */
    public function updateForQualified($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'notes' => ['required', 'string'],
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $notes = $interviewValidate['notes'];
    
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
Great news! Your submitted requirements have been reviewed and are all in order. You are now officially qualified and ready for deployment.

We will provide you with the details of your deployment soon, including the date, location, and any additional instructions you need to follow.

- Note: {$notes}

If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => 'Great news! Your submitted requirements have been reviewed and are all in order. You are now officially qualified and ready for deployment.',
                'description' => "We will provide you with the details of your deployment soon, including the date, location, and any additional instructions you need to follow.

- Note: {$notes}",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

                       /**
     * Update the specified resource in storage.
     */
    public function updateForDeployment($id)
    {
        $interviewValidate = Request::validate([
            'status' => ['required', 'digits:1'],
            'work_location' => ['required', 'string'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string'],
        ]);

        $application = Application::findOrFail($id);

        $jobPositionTitle = json_decode($application->skills)->jobPositionTitle;

        if($interviewValidate['status'] !== $application->status) {
            $application->status = $interviewValidate['status'];
        }

        if ($interviewValidate['status']) {
            $notes = $interviewValidate['notes'];

            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $interviewValidate['start_date'])->format('F j, Y');
            $workLocation = $interviewValidate['work_location'];
            
            $message = "Hello {$application->applicant->first_name} {$application->applicant->last_name},
    
You are now deployed!

Your deployment is at {$workLocation}. Please ensure you report to the location on {$startDate}. Bring all necessary items and documents as instructed.

- Note: {$notes}

If you have any questions, feel free to contact us.

Thank you,
PESO Cabuyao";
    
            $messageResponse = Semaphore::message()->send(
                $application->applicant->contact_number,
                $message
                );

            Notification::create([
                'applicant_id' => $application->applicant->id,
                'title' => "You are now deployed! Your deployment is at {$workLocation}.",
                'description' => "Please ensure you report to the location on {$startDate}. Bring all necessary items and documents as instructed.

- Note: {$notes}",
                'is_viewed' => 1
            ]);
        }

        $application->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employer $applicant)
    {
        //
    }


    // For API
    // Register a new user
    public function register(\Illuminate\Http\Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email:dns,rfc|unique:users',
            'contact_number' => 'required|string|unique:'.Applicant::class,
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = User::create([
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'user_type' => 0,
            'is_active' => 1
        ]);

        Applicant::create([
            'user_id' => $user->id,
            'contact_number' => $request->input('contact_number'),
            'first_name' => $request->input('firstName'),
            'last_name' => $request->input('lastName')
        ]);

        $token = $user->createToken('Applicant Dashboard')->plainTextToken;

        // Semaphore::message()->sendOtp(
        //     $request->input('contactNumber'),
        //     'NEVER SHARE YOUR ONE-TIME PIN.'
        // );

        return response()->json(['token' => $token], 201);
    }
    
        // Log in a user
        public function login(\Illuminate\Http\Request $request)
        {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
    
            if ($user->user_type === 0) {
                $token = $user->createToken('Applicant Dashboard')->plainTextToken;
                return response()->json(['token' => $token], 200);
            } else {
                return response()->json(['error' => 'These credentials do not match our records.'], 401);
            }
        } else {
            return response()->json(['error' => 'These credentials do not match our records.'], 401);
        }
    }
    
        // Log out a user
        public function logout(\Illuminate\Http\Request $request)
        {
            $request->user()->token()->revoke();
    
            return response()->json(['message' => 'Successfully logged out']);
        }
    
        // Refresh access token
        public function refresh(\Illuminate\Http\Request $request)
        {
            $token = $request->user()->token();
            $token->revoke();
    
            $newToken = $request->user()->createToken('MyApp')->accessToken;
    
            return response()->json(['token' => $newToken], 200);
        }
    
        // Get the authenticated user
        public function details(\Illuminate\Http\Request $request)
        {
            return response()->json($request->user());
        }

        // Submit the personal information of the applicant
        public function submitPersonalInformation(\Illuminate\Http\Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string',
                'middleName' => 'nullable|string',
                'lastName' => 'required|string',
                'email' => [
                    'nullable',
                    'max:50',
                    'email',
                    Rule::unique('users')->ignore(Applicant::findOrFail($id)->user->id),
                ],
                'birthDate' => 'required|date',
                'sex' => 'required|string',
                'province' => 'required|string',
                'city' => 'required|string',
                'barangay' => 'required|string',
                'streetAddress' => 'required|string',
                'zipCode' => 'required|string',
                'contactNumber' => 'required|string',
                'isCreate' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $applicant = Applicant::findOrFail($id);
            $application = Application::where(['applicant_id' => $applicant->id])
            ->orderBy('created_at', 'desc')
            ->first();

            $validatedData = $validator->validated();

            if ($validatedData['isCreate'] && !$application->is_draft) {
                Application::create([
                    'applicant_id' => $applicant->id,
                    'birth_date' => $validatedData['birthDate'],
                    'sex' => $validatedData['sex'],
                    'province' => $validatedData['province'],
                    'city' => $validatedData['city'],
                    'barangay' => $validatedData['barangay'],
                    'street_address' => $validatedData['streetAddress'],
                    'zip_code' => $validatedData['zipCode'],
                    'status' => 1,
                    'is_draft' => 1
                ]);
            } else {
                if($validatedData['birthDate'] !== $application->birth_date) {
                    $application->birth_date = $validatedData['birthDate'];
                }

                if($validatedData['sex'] !== $application->sex) {
                    $application->sex = $validatedData['sex'];
                }
        
                if($validatedData['province'] !== $application->province) {
                    $application->province = $validatedData['province'];
                }
        
                if($validatedData['city'] !== $application->city) {
                    $application->city = $validatedData['city'];
                }
        
                if($validatedData['barangay'] !== $application->barangay) {
                    $application->barangay = $validatedData['barangay'];
                }
        
                if($validatedData['streetAddress'] !== $application->street_address) {
                    $application->street_address = $validatedData['streetAddress'];
                }
        
                if($validatedData['zipCode'] !== $application->zip_code) {
                    $application->zip_code = $validatedData['zipCode'];
                }
        
                $application->save();
            }

            return response()->json(['message' => 'Personal Information updated successfully'], 201);
        }

        
        // Update the personal information of the applicant
        public function updatePersonalInformation(\Illuminate\Http\Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string',
                'middleName' => 'nullable|string',
                'lastName' => 'required|string',
                'email' => [
                    'nullable',
                    'max:50',
                    'email'
                ],
                'birthDate' => 'required|date',
                'sex' => 'required|string',
                'province' => 'required|string',
                'city' => 'required|string',
                'barangay' => 'required|string',
                'streetAddress' => 'required|string',
                'zipCode' => 'required|string',
                'contactNumber' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $application = Application::findOrFail($id);

            $validatedData = $validator->validated();

                if($validatedData['birthDate'] !== $application->birth_date) {
                    $application->birth_date = $validatedData['birthDate'];
                }

                if($validatedData['sex'] !== $application->sex) {
                    $application->sex = $validatedData['sex'];
                }
        
                if($validatedData['province'] !== $application->province) {
                    $application->province = $validatedData['province'];
                }
        
                if($validatedData['city'] !== $application->city) {
                    $application->city = $validatedData['city'];
                }
        
                if($validatedData['barangay'] !== $application->barangay) {
                    $application->barangay = $validatedData['barangay'];
                }
        
                if($validatedData['streetAddress'] !== $application->street_address) {
                    $application->street_address = $validatedData['streetAddress'];
                }
        
                if($validatedData['zipCode'] !== $application->zip_code) {
                    $application->zip_code = $validatedData['zipCode'];
                }
        
                $application->save();

            return response()->json(['message' => 'Personal Information updated successfully'], 201);
        }

        // Submit the educational background of the applicant
        public function submitEducationalBackground(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.schoolName' => 'required|string|max:255',
                '*.educationalLevel' => 'required|string',
                '*.educationalLevelQuery' => 'nullable|string',
                '*.level' => 'nullable|string|max:255',
                '*.levelQuery' => 'nullable|string|max:255',
                '*.course' => 'nullable|string|max:255',
                '*.courseQuery' => 'nullable|string|max:255',
                '*.startDate' => 'required|date',
                '*.endDate' => 'required|date|after_or_equal:*.startDate',
            ], [
                '*.schoolName.required' => 'The school name field is required.',
                '*.educationalLevel.required' => 'The educational level field is required.',
                '*.startDate.required' => 'The start date field is required.',
                '*.endDate.required' => 'The end date field is required.',
                '*.endDate.after_or_equal' => 'The end date must be a date after or equal to the start date.',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $applicant = Applicant::findOrFail($id);

            $application = Application::where(['applicant_id' => $applicant->id])
            ->orderBy('created_at', 'desc')
            ->first();

            $validatedData = $validator->validated();

            $application->education = json_encode($validatedData);

            $application->save();

            return response()->json(['message' => 'Educational Background updated successfully'], 201);
        }

        // Update the educational background of the applicant
        public function updateEducationalBackground(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.schoolName' => 'required|string|max:255',
                '*.educationalLevel' => 'required|string',
                '*.educationalLevelQuery' => 'nullable|string',
                '*.level' => 'nullable|string|max:255',
                '*.levelQuery' => 'nullable|string|max:255',
                '*.course' => 'nullable|string|max:255',
                '*.courseQuery' => 'nullable|string|max:255',
                '*.startDate' => 'required|date',
                '*.endDate' => 'required|date|after_or_equal:*.startDate',
            ], [
                '*.schoolName.required' => 'The school name field is required.',
                '*.educationalLevel.required' => 'The educational level field is required.',
                '*.startDate.required' => 'The start date field is required.',
                '*.endDate.required' => 'The end date field is required.',
                '*.endDate.after_or_equal' => 'The end date must be a date after or equal to the start date.',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $application = Application::findOrFail($id);

            $validatedData = $validator->validated();

            $application->education = json_encode($validatedData);

            $application->save();

            return response()->json(['message' => 'Educational Background updated successfully'], 201);
        }

        // Submit the work experience of the applicant
        public function submitWorkExperience(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.companyName' => 'nullable|string|max:255',
                '*.companyAddress' => 'nullable|string|max:255',
                '*.employmentType' => 'nullable|string',
                '*.employmentTypeQuery' => 'nullable|string',
                '*.jobTitle' => 'nullable|string|max:255',
                '*.industry' => 'nullable|string|max:255',
                '*.industryQuery' => 'nullable|string|max:255',
                '*.startDate' => 'nullable|date',
                '*.endDate' => 'nullable|date|after_or_equal:*.startDate',
            ], [
                '*.endDate.after_or_equal' => 'The end date must be a date after or equal to the start date.',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $applicant = Applicant::findOrFail($id);

            $application = Application::where(['applicant_id' => $applicant->id])
            ->orderBy('created_at', 'desc')
            ->first();

            $validatedData = $validator->validated();

            $application->work_experience = json_encode($validatedData);
    
            $application->save();

            return response()->json(['message' => 'Work Experience updated successfully'], 201);
        }

        // Update the work experience of the applicant
        public function updateWorkExperience(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.companyName' => 'nullable|string|max:255',
                '*.companyAddress' => 'nullable|string|max:255',
                '*.employmentType' => 'nullable|string',
                '*.employmentTypeQuery' => 'nullable|string',
                '*.jobTitle' => 'nullable|string|max:255',
                '*.industry' => 'nullable|string|max:255',
                '*.industryQuery' => 'nullable|string|max:255',
                '*.startDate' => 'nullable|date',
                '*.endDate' => 'nullable|date|after_or_equal:*.startDate',
            ], [
                '*.endDate.after_or_equal' => 'The end date must be a date after or equal to the start date.',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $application = Application::findOrFail($id);

            $validatedData = $validator->validated();

            $application->work_experience = json_encode($validatedData);
    
            $application->save();

            return response()->json(['message' => 'Work Experience updated successfully'], 201);
        }

        // Submit the list of credentials of the applicant
        public function submitListOfCredentials(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.certificationName' => 'nullable|string|max:255',
                '*.certifyingAgency' => 'nullable|string|max:255',
                '*.dateOfObtainment' => 'nullable|date',
                '*.location' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $applicant = Applicant::findOrFail($id);

            $application = Application::where(['applicant_id' => $applicant->id])
            ->orderBy('created_at', 'desc')
            ->first();

            $validatedData = $validator->validated();

            $application->list_of_credentials = json_encode($validatedData);
    
            $application->save();

            return response()->json(['message' => 'Credentials updated successfully'], 201);
        }

        // Update the list of credentials of the applicant
        public function updateListOfCredentials(\Illuminate\Http\Request $request, $id)
        {
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                '*.certificationName' => 'nullable|string|max:255',
                '*.certifyingAgency' => 'nullable|string|max:255',
                '*.dateOfObtainment' => 'nullable|date',
                '*.location' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $application = Application::findOrFail($id);

            $validatedData = $validator->validated();

            $application->list_of_credentials = json_encode($validatedData);
    
            $application->save();

            return response()->json(['message' => 'Credentials updated successfully'], 201);
        }

         // Submit the skills of the applicant
         public function submitSkills(\Illuminate\Http\Request $request, $id)
         { 
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                 'jobPositionId' => 'required|numeric|max:255',
                 'jobPositionTitle' => 'required|string|max:255',
                 'jobPositionSkills' => 'required|array',
                 'jobPositionQuery' => 'nullable|string|max:255',
                 'skills' => 'required|array',
             ]);
 
             if ($validator->fails()) {
                 return response()->json(['error' => $validator->errors()], 422);
             }
 
             $applicant = Applicant::findOrFail($id);

             $application = Application::where(['applicant_id' => $applicant->id])
             ->orderBy('created_at', 'desc')
             ->first();
 
             $validatedData = $validator->validated();
 
             $application->skills = json_encode($validatedData);
     
             $application->save();
 
             return response()->json(['message' => 'Skills updated successfully'], 201);
         }

        // Update the skills of the applicant
        public function updateSkills(\Illuminate\Http\Request $request, $id)
        { 
            $input = $request->all();

            // Replace null values with empty strings
            array_walk_recursive($input, function (&$item) {
                $item = $item === null ? '' : $item;
            });

            $validator = Validator::make($input, [
                'jobPositionId' => 'required|numeric|max:255',
                'jobPositionTitle' => 'required|string|max:255',
                'jobPositionSkills' => 'required|array',
                'jobPositionQuery' => 'nullable|string|max:255',
                'skills' => 'required|array',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $application = Application::findOrFail($id);

            $validatedData = $validator->validated();

            $application->skills = json_encode($validatedData);
    
            $application->save();

            return response()->json(['message' => 'Skills updated successfully'], 201);
        }

        // Submit all the information of the applicant
        public function confirmOnboarding($id)
        { 
            $applicant = Applicant::findOrFail($id);

            $application = Application::where(['applicant_id' => $applicant->id])
            ->orderBy('created_at', 'desc')
            ->first();

            $application->is_draft = 0;

            Notification::create([
                'applicant_id' => $applicant->id,
                'title' => 'Welcome to PESO Job Matching Website',
                'description' => 'Employers are now reviewing your applications. While you wait for the results, feel free to update your profile or explore trending job opportunities. Good luck!',
                'is_viewed' => 1
            ]);

            $application->save();

            return response()->json(['message' => 'Applicant onboarded successfully'], 201);
        }

        // Update the profile information of the applicant
        public function updateProfileInformation(\Illuminate\Http\Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'firstName' => 'nullable|string',
                'middleName' => 'nullable|string',
                'lastName' => 'nullable|string',
                'email' => [
                    'nullable',
                    'max:50',
                    'email',
                    Rule::unique('users')->ignore(Applicant::findOrFail($id)->user->id),
                ]
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $applicant = Applicant::findOrFail($id);
            $user = Applicant::findOrFail($id)->user;

            $validatedData = $validator->validated();

            if($validatedData['firstName'] !== $applicant->first_name) {
                $applicant->first_name = $validatedData['firstName'];
            }

            if($validatedData['middleName'] !== $applicant->middle_name) {
                $applicant->middle_name = $validatedData['middleName'];
            }
    
            if($validatedData['lastName'] !== $applicant->last_name) {
                $applicant->last_name = $validatedData['lastName'];
            }
    
            if($validatedData['email'] !== $user->email) {
                $user->email = $validatedData['email'];
            }
    
            $user->save();
            $applicant->save();

            return response()->json(['message' => 'Personal Information updated successfully'], 201);
        }

        // Update the password of the applicant
        public function updatePassword(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);
            $user = Applicant::findOrFail($id)->user;

            $validator = Validator::make($request->all(), [
                'currentPassword' => [
                    'required',
                    'string',
                    'min:6',
                    function ($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            $fail('The current password is incorrect.');
                        }
                    },
                ],
                'newPassword' => 'required|string|min:6',
                'confirmNewPassword' => 'required|string|same:newPassword'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();
    
            $user->password = Hash::make($validatedData['newPassword']);
    
            $user->save();

            return response()->json(['message' => 'Password updated successfully'], 201);
        }

        
        // Logout the other sessions of the applicant
        public function logoutOtherSessions(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);
            $user = Applicant::findOrFail($id)->user;

            $validator = Validator::make($request->all(), [
                'currentPassword' => [
                    'required',
                    'string',
                    'min:6',
                    function ($attribute, $value, $fail) use ($user) {
                        if (!Hash::check($value, $user->password)) {
                            $fail('The current password is incorrect.');
                        }
                    },
                ]
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();
            Auth::logoutOtherDevices($validatedData['currentPassword']);

            return response()->json(['message' => 'Logout other sessions successfully'], 201);
        }

        // Verify the account of the applicant using SMS
        public function verifyUsingSms(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);

            $messageResponse = Semaphore::message()->sendOtp(
            $applicant->contact_number,
            'NEVER SHARE YOUR ONE-TIME PIN.'
            );

            return response()->json(['otp' => $messageResponse], 201);
        }

        // Verify the account of the applicant using SMS
        public function verifyContactNumber(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);
            $applicant->contact_number_verified_at = now();

            Application::create([
                'applicant_id' => $applicant->id,
                'status' => 1,
                'is_draft' => 1
            ]);

            $applicant->save();
            return response()->json(['message' => 'Contact number verified successfully'], 201);
        }

        // Verify the account of the applicant using email
        public function verifyUsingEmail(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);
            $user = Applicant::findOrFail($id)->user;

            $otp = random_int(100000, 999999);

            Mail::to($user->email)->send(new SendOtpEmail($applicant->first_name, $otp));

            return response()->json(['otp' => $otp], 201);
        }

        // Verify the account of the applicant using SMS
        public function verifyEmailAddress(\Illuminate\Http\Request $request, $id)
        {
            $applicant = Applicant::findOrFail($id);
            $user = Applicant::findOrFail($id)->user;
            $user->email_verified_at = now();

            Application::create([
                'applicant_id' => $applicant->id,
                'status' => 1,
                'is_draft' => 1
            ]);

            $user->save();
            return response()->json(['message' => 'Email address verified successfully'], 201);
        }
        
        // Verify the account of the applicant using SMS
        public function sendResetPasswordLink(\Illuminate\Http\Request $request)
        {
            $validator = Validator::make($request->all(), [
                'email' => [
                    'required',
                    'max:50',
                    'email:dns,rfc'
                ]
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $validatedData = $validator->validated();

            $user = User::where('email', $validatedData['email'])->with('applicant')->first();

            if (!$user) {
                return response()->json(['error' => 'No account with that email address exists.'], 422);
            }

            // Store the verification code in the password_resets table
            $status = Password::broker()->createToken($user);

            // if ($status !== Password::RESET_LINK_SENT) {
            //     return response()->json(['error' => __($status)], 422);
            // }

            // Send the email with the reset link and verification code
            Mail::to($user->email)->send(new ResetPasswordMail($status, $user->applicant->first_name, $user->email));

            return response()->json(['message' => 'Reset password link sent successfully'], 201);
        }

        public function getEmailFromToken(\Illuminate\Http\Request $request)
        {
            $request->validate([
                'token' => 'required',
                'email' => 'required|email:dns,rfc',
            ]);

            // Find the password reset entry in the database
            $resetEntry = DB::table('password_reset_tokens')->where('email', $request->email)->first();

            if (!$resetEntry || !Hash::check($request->token, $resetEntry->token)) {
                return response()->json(['message' => 'The reset token is invalid. Please try again.'], 422);
            }
        }

        public function resetPassword(\Illuminate\Http\Request $request)
        {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email:dns,rfc',
                'password' => 'required|string|min:6',
                'confirmNewPassword' => 'required|string|same:password'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }   

            $user = User::where('email', $request->email)->firstOrFail();

            if (!$user) {
                return response()->json(['error' => 'No account with that email address exists.'], 422);
            }
    
            if ($user) {
                $resetStatus = Password::broker()->reset(
                    $request->only('email', 'password', 'token'),
                    function ($user, $password) use ($request) {
                        $user->password = Hash::make($password);
                        $user->save();
                    }
                );
                if ($resetStatus === Password::INVALID_TOKEN) {
                    return response()->json(['message' => 'The reset token is invalid or expired.', 'resetStatus' => $resetStatus, 'invalidToken' => Password::INVALID_TOKEN], 422);
                }
                return response()->json(['message' => 'Password has been reset!'], 201);
            }
            return response()->json(['message' => 'The reset token is invalid or expired.'], 422);
        }
}
