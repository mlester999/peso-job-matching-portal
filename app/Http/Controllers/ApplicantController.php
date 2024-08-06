<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ApplicantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filters = Request::only(['search']);
        $searchReq = Request::input('search');

        $applicants = Applicant::query()
        ->with('user')
        ->whereHas('applications')
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
        ->through(fn($applicant) => [
            'id' => $applicant->id,
            'name' => $applicant->name,
            'province' => $applicant->province,
            'city' => $applicant->city,
            'barangay' => $applicant->barangay,
            'street_address' => $applicant->street_address,
            'zip_code' => $applicant->zip_code,
            'email' => $applicant->user->email,
            'contact_number' => $applicant->contact_number,
            'is_active' => $applicant->user->is_active,
            'created_at' => $applicant->created_at,
        ]);

        if (empty($searchReq)) {
            unset($filters['search']);
        }

        $currentPage = $applicants->currentPage();
        $lastPage = $applicants->lastPage();
        $firstPage = 1;

        $previousPage = $currentPage - 1 > 0 ? $currentPage - 1 : null;
        $nextPage = $currentPage + 1 <= $lastPage ? $currentPage + 1 : null;

        $links = [];

        if ($previousPage !== null) {
            $links[] = [
                'url' => $applicants->url($previousPage),
                'label' => 'Previous',
            ];
        }

        $links[] = [
            'url' => $applicants->url(1),
            'label' => 1,
        ];

        if ($currentPage > 3) {
            $links[] = [
                'url' => $applicants->url($currentPage - 1),
                'label' => '...',
            ];
        }

        $rangeStart = max(2, $currentPage - 1);
        $rangeEnd = min($lastPage - 1, $currentPage + 1);

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $links[] = [
                'url' => $applicants->url($i),
                'label' => $i,
            ];
        }


        if ($currentPage < $lastPage - 2) {
            $links[] = [
                'url' => $applicants->url($currentPage + 1),
                'label' => '...',
            ];
        }

        if ($firstPage !== $lastPage) {
            $links[] = [
                'url' => $applicants->url($lastPage),
                'label' => $lastPage,
            ];
        }

        if ($nextPage !== null) {
            $links[] = [
                'url' => $applicants->url($nextPage),
                'label' => 'Next',
            ];
        }


        return Inertia::render('Applicants/Index', [
            'applicants' => $applicants,
            'filters' => $filters,
            'pagination' => [
                'current_page' => $currentPage,
                'last_page' => $lastPage,
                'links' => $links,
            ],
        ]);
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
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $applicant = Applicant::with('user')->find($id);

        return Inertia::render('Applicant/Edit', [
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
            'email' => 'required|email|unique:users',
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
            'first_name' => $request->input('firstName'),
            'last_name' => $request->input('lastName')
        ]);

        $token = $user->createToken('Applicant Dashboard')->plainTextToken;

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

        // Register a new user
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

            if($validatedData['birthDate'] !== $applicant->birthDate) {
                $applicant->birth_date = $validatedData['birthDate'];
            }

            if($validatedData['sex'] !== $applicant->sex) {
                $applicant->sex = $validatedData['sex'];
            }
    
            if($validatedData['province'] !== $applicant->province) {
                $applicant->province = $validatedData['province'];
            }
    
            if($validatedData['city'] !== $applicant->city) {
                $applicant->city = $validatedData['city'];
            }
    
            if($validatedData['barangay'] !== $applicant->barangay) {
                $applicant->barangay = $validatedData['barangay'];
            }
    
            if($validatedData['streetAddress'] !== $applicant->street_address) {
                $applicant->street_address = $validatedData['streetAddress'];
            }
    
            if($validatedData['contactNumber'] !== $applicant->contact_number) {
                $applicant->contact_number = $validatedData['contactNumber'];
            }
    
            if($validatedData['zipCode'] !== $applicant->zip_code) {
                $applicant->zip_code = $validatedData['zipCode'];
            }
    
            $user->save();
            $applicant->save();

            return response()->json(['message' => 'Personal Information updated successfully'], 201);
        }
}
