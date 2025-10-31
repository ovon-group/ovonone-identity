<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.auth')]
#[Title('Login')]
class Login extends Component
{
    public $email = '';

    public $password = '';

    public $remember = false;

    // Multi-step login properties
    public $step = 1; // 1 = email/phone, 2 = auth methods, 3 = OTP input

    public $user = null;

    public $inputType = 'email'; // 'email' or 'phone'

    public $userHasPassword = false;

    // OTP related properties
    public $otpCode = '';

    public $otpChannel = 'email'; // 'email' or 'sms'

    protected $rules = [
        'email' => 'required|string',
        'password' => 'required|min:6',
    ];

    protected $messages = [
        'email.required' => 'Email address or phone number is required.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 6 characters.',
    ];

    public function validateEmail()
    {
        $this->validate(['email' => 'required|string']);

        // Determine if input is email or phone number
        if ($this->isPhoneNumber($this->email)) {
            $this->inputType = 'phone';
            $user = User::whereIn('mobile', $this->normalizePhoneNumber($this->email))->first();

            if (! $user) {
                throw ValidationException::withMessages([
                    'email' => 'No account found with this phone number.',
                ]);
            }

            $this->user = $user;
            $this->userHasPassword = $user->hasPassword();
            $this->otpChannel = 'sms';

            // Send SMS OTP directly for phone numbers
            try {
                $user->sendOneTimePasswordViaSms();
                $this->step = 3; // Skip auth methods, go straight to OTP input
                session()->flash('otp-sent', 'Verification code sent to your mobile number.');
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'email' => 'Failed to send verification code: '.$e->getMessage(),
                ]);
            }
        } else {
            // Handle as email
            if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'email' => 'Please enter a valid email address or phone number.',
                ]);
            }

            $this->inputType = 'email';
            $user = User::where('email', $this->email)->first();

            if (! $user) {
                throw ValidationException::withMessages([
                    'email' => 'No account found with this email address.',
                ]);
            }

            $this->user = $user;
            $this->userHasPassword = $user->hasPassword();
            $this->step = 2; // Move to authentication methods step
        }
    }

    private function isPhoneNumber($input)
    {
        // Remove all non-digit characters and check if it looks like a phone number
        $cleaned = preg_replace('/[^0-9]/', '', $input);

        // Check if it's between 10-15 digits (typical phone number range)
        if (strlen($cleaned) < 10 || strlen($cleaned) > 15) {
            return false;
        }

        // Check specific patterns
        return preg_match('/^(\+?1)?[0-9]{10}$/', $input) || // US format
               preg_match('/^(\+?44)?[0-9]{10,11}$/', $input) || // UK international format
               preg_match('/^0[0-9]{10}$/', $input) || // UK national format (07545191039)
               preg_match('/^(\+?[0-9]{1,4})?[0-9]{7,14}$/', $input); // Other international formats
    }

    private function normalizePhoneNumber($phone)
    {
        $possibilities = [];

        // Remove all non-digit characters except +
        $cleaned = preg_replace('/[^+0-9]/', '', $phone);

        // Handle different phone number formats
        if (str_starts_with($cleaned, '+')) {
            // Already in international format (e.g., +447545191039)
            $possibilities[] = $cleaned;
            $possibilities[] = '0'.ltrim($cleaned, '+44');
        } elseif (str_starts_with($cleaned, '0')) {
            // National format (e.g., 07545191039 for UK)
            $possibilities[] = $cleaned;
            $possibilities[] = '+44'.ltrim($cleaned, '0');
            $possibilities[] = '44'.ltrim($cleaned, '0');
        } else {
            // Assume international format without + (e.g., 447545191039)
            $possibilities[] = '+'.$cleaned;
            $possibilities[] = '0'.ltrim($cleaned, '44');
        }

        return $possibilities;
    }

    public function loginWithPassword()
    {
        if (! $this->user) {
            throw ValidationException::withMessages([
                'password' => 'Please start over by entering your email or phone number.',
            ]);
        }

        if (! $this->userHasPassword) {
            throw ValidationException::withMessages([
                'password' => 'This account does not have a password set. Please use a different sign-in method.',
            ]);
        }

        $this->validate([
            'password' => 'required|min:6',
        ]);

        // Use the user's email for authentication (even if they entered phone number)
        if (! Auth::attempt(['email' => $this->user->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect.',
            ]);
        }

        session()->regenerate();

        return redirect()->intended('/');
    }

    public function requestOtp($channel = 'email')
    {
        if (! $this->user) {
            throw ValidationException::withMessages([
                'email' => 'Please start over by entering your email address.',
            ]);
        }

        if ($channel === 'sms' && ! $this->user->mobile) {
            throw ValidationException::withMessages([
                'general' => 'No mobile number associated with this account.',
            ]);
        }

        $this->otpChannel = $channel;

        try {
            if ($channel === 'email') {
                $this->user->sendOneTimePasswordViaEmail();
            } else {
                $this->user->sendOneTimePasswordViaSms();
            }

            $this->step = 3; // Move to OTP input step
            session()->flash('otp-sent', 'One-time password sent via '.($channel === 'email' ? 'email' : 'SMS').'.');
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'general' => 'Failed to send one-time password: '.$e->getMessage(),
            ]);
        }
    }

    public function loginWithOtp()
    {
        $this->validate(['otpCode' => 'required|string|min:6']);

        if (! $this->user) {
            throw ValidationException::withMessages([
                'otpCode' => 'Session expired. Please request a new code.',
            ]);
        }

        $result = $this->user->attemptLoginUsingOneTimePassword($this->otpCode, $this->remember);

        if ($result->isOk()) {
            session()->regenerate();

            return redirect()->intended('/');
        }

        throw ValidationException::withMessages([
            'otpCode' => $result->validationMessage(),
        ]);
    }

    public function backToAuthMethods()
    {
        $this->reset(['otpCode', 'password']);
        $this->step = 2;
    }

    public function backToEmail()
    {
        $this->reset(['password', 'otpCode', 'user', 'otpChannel', 'inputType', 'userHasPassword']);
        $this->step = 1;
    }

    public function render()
    {
        return view('livewire.login');
    }
}
