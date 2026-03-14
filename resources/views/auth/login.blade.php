<x-guest-layout :title="'Login - '.config('app.name')" maxWidth="5xl">
    <div class="grid lg:grid-cols-[1.1fr_0.9fr]">
        <div class="hidden lg:flex flex-col justify-between bg-slate-900 px-10 py-12 text-white">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.25em] text-rose-100">
                    Secure Access
                </div>
                <h1 class="mt-6 text-4xl font-black leading-tight">
                    Welcome back to the
                    <span class="text-amber-300">Confidence Club Members</span>
                    Portal
                </h1>
                <p class="mt-4 max-w-lg text-sm leading-7 text-slate-300">
                    Sign in to manage members, record contributions, generate receipts, and review reports with role-based access.
                </p>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 flex h-10 w-10 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-200">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 1.75a4.25 4.25 0 0 0-4.25 4.25v1.34A3.75 3.75 0 0 0 3 10.95v3.3A3.75 3.75 0 0 0 6.75 18h6.5A3.75 3.75 0 0 0 17 14.25v-3.3a3.75 3.75 0 0 0-2.75-3.61V6A4.25 4.25 0 0 0 10 1.75Zm2.75 5.2V6a2.75 2.75 0 1 0-5.5 0v.95h5.5Z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-white">Role-based permissions</h2>
                            <p class="mt-1 text-sm text-slate-300">Admin and treasurer accounts each see only the tools they are allowed to use.</p>
                        </div>
                    </div>
                </div>

                    <div class="grid grid-cols-2 gap-3 text-center text-xs font-semibold text-slate-200">
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-4">Admin</div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 px-3 py-4">Treasurer</div>
                    </div>
            </div>
        </div>

        <div class="px-6 py-8 sm:px-10 sm:py-12">
            <div class="mx-auto max-w-md">
                <div class="mb-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.25em] text-rose-700">Login</p>
                    <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Sign in to continue</h2>
                    <p class="mt-3 text-sm leading-6 text-slate-500">
                        Use your registered email address and password to access the dashboard.
                    </p>
                </div>

                <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('Email address') }}</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                            placeholder="name@example.com"
                        >
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-600" />
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <label for="password" class="block text-sm font-semibold text-slate-700">{{ __('Password') }}</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm font-medium text-rose-700 transition hover:text-rose-800 hover:underline">
                                    {{ __('Forgot password?') }}
                                </a>
                            @endif
                        </div>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-rose-400 focus:ring-4 focus:ring-rose-100"
                            placeholder="Enter your password"
                        >
                        <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-600" />
                    </div>

                    <label for="remember_me" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <input
                            id="remember_me"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-rose-700 focus:ring-rose-500"
                        >
                        <span>{{ __('Keep me signed in on this device') }}</span>
                    </label>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center rounded-2xl bg-rose-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-rose-700/20 transition hover:bg-rose-800 focus:outline-none focus:ring-4 focus:ring-rose-200"
                    >
                        {{ __('Log in') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
