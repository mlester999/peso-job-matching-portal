<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Applicant;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $users = Applicant::query()
        ->with('user')
        ->when($searchReq, function($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->whereRaw('LOWER(email) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(contact_number) LIKE LOWER(?)', ['%' . $search . '%']);
                })
                        ->orWhereRaw('LOWER(first_name) LIKE LOWER(?)', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(last_name) LIKE LOWER(?)', ['%' . $search . '%']);
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
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'province' => $user->province,
            'city' => $user->city,
            'barangay' => $user->barangay,
            'street_address' => $user->street_address,
            'zip_code' => $user->zip_code,
            'email' => $user->user->email,
            'contact_number' => $user->contact_number,
            'is_active' => $user->user->is_active,
            'created_at' => $user->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
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


        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
        ]);
    }

    /**
     * Display the add page for users.
     */
    public function add()
    {
        return Inertia::render('Users/Add');
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
        $userValidate = Request::validate([
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
            'email' => $userValidate['email'],
            'password' => Hash::make('password'),
            'user_type' => 1,
            'is_active' => 1
        ]);

        Applicant::create([
            'user_id' => $user['id'],
            'first_name' => $userValidate['first_name'],
            'last_name' => $userValidate['last_name'],
            'province' => $userValidate['province'],
            'city' => $userValidate['city'],
            'barangay' => $userValidate['barangay'],
            'street_address' => $userValidate['street_address'],
            'contact_number' => $userValidate['contact_number'],
            'zip_code' => $userValidate['zip_code'],
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Employer $employer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $applicant = Applicant::with('user')->find($id);

        return Inertia::render('Users/Edit', [
            'applicant' => $applicant,
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
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }
}
