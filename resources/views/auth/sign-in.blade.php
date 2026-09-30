<x-layouts.guest>
    <form method="POST" action="{{ route('auth.sign-in.action') }}" class="space-y-4 rounded-lg bg-white p-6 shadow-sm">
        @csrf

        <div>
            <x-helper.form.field.text name="email" title="Email" required :value="old('email')" />
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password<span class="text-red-500">*</span></label>
            <input id="password" name="password" type="password" required
                   class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm {{ $errors->has('email') ? 'border-red-500 ring-1 ring-red-500' : '' }}">
        </div>

        <div class="flex items-center">
            <input id="remember" name="remember" type="checkbox" value="1"
                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
        </div>

        <x-button class="w-full">Sign in</x-button>
    </form>
</x-layouts.guest>
