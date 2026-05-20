<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — it-ticketing</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] },
            colors: { brand: { 600: '#3b55e8', 700: '#2a3fcc' } },
          },
        },
      }
    </script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center font-sans">
    <div class="w-full max-w-sm">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <div class="w-11 h-11 rounded-xl bg-brand-600 flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-gray-900">it-ticketing</h1>
            <p class="text-sm text-gray-400 mt-1">Sign in to your account</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" autofocus
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100 @error('email') border-red-400 @enderror">
                    @error('email')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-medium text-gray-600">Password</label>
                    </div>
                    <input type="password" name="password"
                           class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-brand-600 focus:ring-2 focus:ring-brand-100">
                    @error('password')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-300">
                    Remember me
                </label>

                <button type="submit"
                        class="w-full py-2.5 bg-brand-600 text-white text-sm font-medium rounded-lg hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-300">
                    Sign In
                </button>
            </form>
        </div>

        <p class="mt-5 text-center text-xs text-gray-400">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-brand-600 hover:underline">Create one</a>
        </p>
    </div>
</body>
</html>