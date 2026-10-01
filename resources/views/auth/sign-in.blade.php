<x-layouts.guest>
    <x-hero eyebrow="Sign in" title="Welcome back" description="Sign in to pick up where you left off." />

    <form method="POST" action="{{ route('auth.sign-in.action') }}" class="mt-6 space-y-5 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
        @csrf

        <x-helper.form.field.text name="email" title="Email" required :value="old('email')" autocomplete="username" />

        {{-- A failed sign-in is reported against the email field, so it marks the password too. --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="form-control mt-1.5 {{ $errors->has('email') ? 'form-control-error' : '' }}">
        </div>

        <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-sm text-gray-700">
            <input id="remember" name="remember" type="checkbox" value="1" class="size-4 cursor-pointer rounded accent-brand-700">
            Remember me
        </label>

        <x-button class="w-full">Sign in</x-button>
    </form>
</x-layouts.guest>
