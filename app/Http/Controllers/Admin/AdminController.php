<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function index()
    {
        $q = request('q');
        $role = request('role') ?: 'admin'; // default to admin list
        $admins = Admin::when($role && Schema::hasColumn('admins', 'role'), function($query) use ($role) {
                $query->where('role', $role);
            })
            ->when($q, function($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
        $title = $role === 'owner' ? 'Owner Accounts' : 'Admin Accounts';
        return view('admin.admins.index', compact('admins', 'title', 'role'));
    }
    public function create() { return view('admin.admins.create'); }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:admin,owner',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'] ?? 'admin',
        ];
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('admin-profiles', 'public');
            $data['profile_picture'] = $path;
        }
        $admin = Admin::create($data);
        return redirect()->route('admin.admins.show', $admin)->with('success', 'Admin created successfully.');
    }
    public function show(Admin $admin) { return view('admin.admins.show', compact('admin')); }
    public function edit(Admin $admin) { return view('admin.admins.edit', compact('admin')); }
    public function update(Request $request, Admin $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'nullable|in:admin,owner',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);
        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];
        if (!empty($validated['role'])) {
            $data['role'] = $validated['role'];
        }
        if (!empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }
        if ($request->hasFile('profile_picture')) {
            if ($admin->profile_picture) { \Storage::disk('public')->delete($admin->profile_picture); }
            $path = $request->file('profile_picture')->store('admin-profiles', 'public');
            $data['profile_picture'] = $path;
        }
        $admin->update($data);
        return redirect()->route('admin.admins.show', $admin)->with('success', 'Admin updated successfully.');
    }
    public function destroy(Admin $admin)
    {
        if ($admin->profile_picture) { \Storage::disk('public')->delete($admin->profile_picture); }
        $admin->delete();
        return redirect()->route('admin.admins.index')->with('success', 'Admin deleted successfully.');
    }
} 