<div>
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
        </div>
        <div class="ml-3">
            <h1 class="text-2xl font-bold text-gray-900">Welcome back</h1>
            <p class="text-sm text-gray-500">Sign in to your account</p>
        </div>
    </div>

    <div class="mt-8">
        {{-- Step 1: Email Input --}}
        @if($step === 1)
            <form wire:submit.prevent="validateEmail" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address or phone number
                    </label>
                    <div class="mt-1">
                        <input
                            wire:model="email"
                            id="email"
                            name="email"
                            type="text"
                            autocomplete="username"
                            required
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('email') border-red-300 @enderror"
                            placeholder="Enter your email or phone number"
                        >
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        We'll send a verification code to your phone number, or show sign-in options for your email.
                    </p>
                </div>

                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                    >
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg wire:loading.remove wire:target="validateEmail" class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                            <svg wire:loading wire:target="validateEmail" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="validateEmail">Continue</span>
                        <span wire:loading wire:target="validateEmail">Checking...</span>
                    </button>
                </div>
            </form>

        {{-- Step 2: Authentication Method Selection --}}
        @elseif($step === 2)
            <div class="space-y-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Signing in as <strong>{{ $email }}</strong>
                    </p>
                    <button type="button" wire:click="backToEmail" class="text-xs text-blue-600 hover:text-blue-500 mt-1">
                        Use different {{ $inputType === 'phone' ? 'phone number' : 'email' }}
                    </button>
                </div>

                <div>
                    <h3 class="text-sm font-medium text-gray-700 mb-4">Choose your sign-in method</h3>
                </div>

                {{-- Password Login --}}
                @if($userHasPassword)
                    <form wire:submit.prevent="loginWithPassword" class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1">
                            <input
                                wire:model="password"
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('password') border-red-300 @enderror"
                                placeholder="Enter your password"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                wire:model="remember"
                                id="remember-me"
                                name="remember-me"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-500">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg wire:loading.remove wire:target="loginWithPassword" class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                <svg wire:loading wire:target="loginWithPassword" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="loginWithPassword">Sign in with password</span>
                            <span wire:loading wire:target="loginWithPassword">Signing in...</span>
                        </button>
                    </div>
                    </form>
                @endif

                {{-- Divider --}}
                @if($userHasPassword)
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>
                @endif

                {{-- OTP Login Options --}}
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        wire:click="requestOtp('email')"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="requestOtp('email')">Email code</span>
                        <span wire:loading wire:target="requestOtp('email')">Sending...</span>
                    </button>
                    
                    <button
                        type="button"
                        wire:click="requestOtp('sms')"
                        class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200 @if(!$user?->mobile) opacity-50 cursor-not-allowed @endif"
                        @if(!$user?->mobile) disabled @endif
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="requestOtp('sms')">
                            {{ $user?->mobile ? 'SMS code' : 'SMS unavailable' }}
                        </span>
                        <span wire:loading wire:target="requestOtp('sms')">Sending...</span>
                    </button>
                </div>
                
                @error('general')
                    <div class="rounded-md bg-red-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800">{{ $message }}</p>
                            </div>
                        </div>
                    </div>
                @enderror
            </div>

        {{-- Step 3: OTP Input --}}
        @elseif($step === 3)
            <div class="space-y-6">
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Verification code sent to your {{ $otpChannel === 'email' ? 'email' : 'mobile' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($inputType === 'phone')
                            <strong>{{ $user?->mobile ? substr($user->mobile, 0, 3) . '***' . substr($user->mobile, -2) : $email }}</strong>
                        @else
                            <strong>{{ $email }}</strong>{{ $otpChannel === 'sms' && $user?->mobile ? ' (' . substr($user->mobile, 0, 3) . '***' . substr($user->mobile, -2) . ')' : '' }}
                        @endif
                    </p>
                    <button type="button" wire:click="{{ $inputType === 'phone' ? 'backToEmail' : 'backToAuthMethods' }}" class="text-xs text-blue-600 hover:text-blue-500 mt-1">
                        {{ $inputType === 'phone' ? 'Use different number' : 'Try different method' }}
                    </button>
                </div>

                @if(session('otp-sent'))
                    <div class="rounded-md bg-green-50 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800">
                                    {{ session('otp-sent') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                
                <form wire:submit.prevent="loginWithOtp" class="space-y-6">
                    <div>
                        <label for="otpCode" class="block text-sm font-medium text-gray-700">
                            Enter verification code
                        </label>
                        <div class="mt-1">
                            <input
                                wire:model="otpCode"
                                id="otpCode"
                                name="otpCode"
                                type="text"
                                autocomplete="one-time-code"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm text-center text-lg font-mono tracking-widest @error('otpCode') border-red-300 @enderror"
                                placeholder="123456"
                                maxlength="6"
                                style="letter-spacing: 0.5em;"
                            >
                        </div>
                        @error('otpCode')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500 text-center">
                            Code will expire in 2 minutes
                        </p>
                    </div>

                    <div class="flex items-center justify-center">
                        <div class="flex items-center">
                            <input
                                wire:model="remember"
                                id="remember-otp"
                                name="remember-otp"
                                type="checkbox"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label for="remember-otp" class="ml-2 block text-sm text-gray-900">
                                Remember me
                            </label>
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:scale-105"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg wire:loading.remove wire:target="loginWithOtp" class="h-5 w-5 text-blue-500 group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <svg wire:loading wire:target="loginWithOtp" class="animate-spin h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 818-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="loginWithOtp">Verify & Sign in</span>
                            <span wire:loading wire:target="loginWithOtp">Verifying...</span>
                        </button>
                    </div>
                </form>
            </div>
        @endif

        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-white text-gray-500">Or continue with</span>
                </div>
            </div>

            <div class="mt-6">
                <x-passkeys::authenticate>
                    <button type="button" class="w-full flex justify-center items-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Sign in with Passkey
                    </button>
                </x-passkeys::authenticate>
            </div>
        </div>

    </div>
</div>