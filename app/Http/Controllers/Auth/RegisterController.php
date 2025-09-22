<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use App\Models\PendingRegistration;
use Illuminate\Support\Str;
use App\Services\EmailService;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'terms' => 'required|accepted',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        // Create pending record, do not create user yet
        $token = Str::random(64);
        $pending = PendingRegistration::updateOrCreate(
            ['email' => $request->input('email')],
            [
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->input('password')),
                'token' => $token,
                'expires_at' => now()->addHours(24),
            ]
        );

        $verifyUrl = url('/verify-registration/'.$pending->token);
        $emailService = app(EmailService::class);
        $emailService->sendRegistrationVerification($pending->email, $pending->name, $verifyUrl);

        return view('auth.registration-pending', [
            'email' => $pending->email,
        ])->with('success', 'Verification email sent.');
    }
} 