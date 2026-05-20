<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — it-ticketing</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: { extend: {
          fontFamily: { sans: ['DM Sans','system-ui','sans-serif'] },
          colors: { brand: { 50:'#f0f4ff', 100:'#e0eaff', 600:'#3b55e8', 700:'#2a3fcc' } },
        }},
      }
    </script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center font-sans py-10">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <div class="w-11 h-11 rounded-xl bg-brand-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-gray-900">Create your account</h1>
            <p class="text-sm text-gray-400 mt-1">Get support for any IT issue</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Full name</label>
                    <input type="text" name="name" value="{{ old('name') }}" autofocus
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Department <span class="text-gray-300">(optional)</span></label>
                    <input type="text" name="department" value="{{ old('department') }}"
                           placeholder="e.g. Finance, HR, Operations"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 @error('password') border-red-400 @enderror">
                    @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Confirm password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-300 mt-2">
                    Create Account
                </button>
            </form>
        </div>

        <p class="mt-5 text-center text-xs text-gray-400">
            Already have an account?
            <a href="{{ route('login') }}" class="text-brand-600 hover:underline">Sign in</a>
        </p>
    </div>
</body>
</html>