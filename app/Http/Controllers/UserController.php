<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('company')->paginate(15);
        return view('themes.blk.back.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companies = Company::all();
        return view('themes.blk.back.users.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'company_owner', 'employee'])],
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Validate company_id is required for company_owner and employee roles
        if (in_array($request->role, ['company_owner', 'employee']) && !$request->company_id) {
            return back()->withErrors(['company_id' => 'Company is required for this role.'])->withInput();
        }

        // Prevent creating super_admin unless current user is super_admin
        if ($request->role === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Unauthorized to create Super Admin users.'])->withInput();
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'company_id' => $validated['company_id'],
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('themes.blk.back.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $companies = Company::all();
        return view('themes.blk.back.users.edit', compact('user', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'company_owner', 'employee'])],
            'company_id' => 'nullable|exists:companies,id',
        ]);

        // Validate company_id is required for company_owner and employee roles
        if (in_array($request->role, ['company_owner', 'employee']) && !$request->company_id) {
            return back()->withErrors(['company_id' => 'Company is required for this role.'])->withInput();
        }

        // Prevent modifying super_admin unless current user is super_admin
        if ($request->role === 'super_admin' && !auth()->user()->isSuperAdmin()) {
            return back()->withErrors(['role' => 'Unauthorized to modify Super Admin users.'])->withInput();
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'company_id' => $validated['company_id'],
        ]);

        // Only update password if provided
        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully!');
    }
}
