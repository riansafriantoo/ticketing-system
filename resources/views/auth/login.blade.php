<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Helpdesk Support</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['DM Sans', 'system-ui', 'sans-serif'] },
            colors: {
              brand: {
                50:  '#eff6ff',
                100: '#dbeafe',
                500: '#2f6fed',
                600: '#1d5fe0',
                700: '#1750c4',
              },
            },
          },
        },
      }
    </script>

    <style>
      body { font-family: 'DM Sans', system-ui, sans-serif; }

      .auth-input::placeholder { color: #94a3b8; }

      .auth-input:focus {
        outline: none;
        border-color: #1d5fe0;
        box-shadow: 0 0 0 3px rgba(29, 95, 224, 0.12);
      }

      @keyframes float-up {
        0%, 100% { transform: translateY(0); }
        50%      { transform: translateY(-8px); }
      }
      .illus-float { animation: float-up 6s ease-in-out infinite; }
    </style>
</head>
<body class="h-full bg-white">

<div class="flex min-h-screen">

    {{-- ════════════════════════════════════════════════════════════════
         LEFT — Illustration panel
    ════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:flex flex-1 items-center justify-center px-12 relative overflow-hidden">

        {{-- Faint background dot grid --}}
        <svg class="absolute inset-0 w-full h-full opacity-[0.04]" aria-hidden="true">
            <defs>
                <pattern id="dots" width="28" height="28" patternUnits="userSpaceOnUse">
                    <circle cx="2" cy="2" r="1.5" fill="#1d5fe0"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#dots)"/>
        </svg>

        <div class="illus-float relative z-10 w-full max-w-md">
            {{-- Desk + chair + person illustration --}}
            
            <svg viewBox="0 0 420 380" class="w-full h-auto" role="img" aria-label="Illustration of a person working at a desk with a laptop">
                {{-- Cactus --}}
                <rect x="362" y="312" width="28" height="22" rx="3" fill="#0f172a"/>
                <ellipse cx="376" cy="290" rx="14" ry="24" fill="#2f6fed"/>
                <ellipse cx="362" cy="298" rx="7" ry="14" fill="#1d5fe0"/>
                <ellipse cx="390" cy="298" rx="7" ry="14" fill="#1d5fe0"/>

                {{-- Desk --}}
                <rect x="40" y="248" width="210" height="10" rx="2" fill="#1d5fe0"/>
                <rect x="50" y="258" width="10" height="65" fill="#0f172a" opacity="0.85"/>
                <rect x="225" y="258" width="10" height="65" fill="#0f172a" opacity="0.85"/>

                {{-- Wall panels behind desk --}}
                <rect x="55" y="120" width="70" height="130" rx="4" fill="#eef2f7"/>
                <rect x="160" y="100" width="55" height="60" rx="4" fill="#eef2f7"/>
                <rect x="160" y="170" width="55" height="50" rx="4" fill="#eef2f7"/>

                {{-- Clock --}}
                <circle cx="190" cy="78" r="16" fill="none" stroke="#cbd5e1" stroke-width="3"/>
                <line x1="190" y1="78" x2="190" y2="68" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>
                <line x1="190" y1="78" x2="197" y2="78" stroke="#94a3b8" stroke-width="2" stroke-linecap="round"/>

                {{-- Pixel/circuit decoration --}}
                <circle cx="95" cy="148" r="14" fill="#2f6fed"/>
                <g fill="#1d5fe0" opacity="0.5">
                    <rect x="65" y="170" width="6" height="6"/>
                    <rect x="76" y="170" width="6" height="6"/>
                    <rect x="65" y="182" width="6" height="6"/>
                    <rect x="87" y="182" width="6" height="6"/>
                    <rect x="65" y="194" width="6" height="6"/>
                    <rect x="76" y="194" width="6" height="6"/>
                    <rect x="98" y="194" width="6" height="6"/>
                </g>

                {{-- Monitor on desk --}}
                <rect x="130" y="195" width="68" height="50" rx="3" fill="#0f172a"/>
                <rect x="135" y="200" width="58" height="38" rx="1" fill="#bcd2f7"/>
                <rect x="155" y="245" width="18" height="8" fill="#475569"/>
                <rect x="148" y="253" width="32" height="4" rx="2" fill="#475569"/>

            </svg>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         RIGHT — Blue panel with floating card
    ════════════════════════════════════════════════════════════════ --}}
    <div class="w-full lg:w-[560px] bg-brand-600 relative flex items-center justify-center px-6 py-12 overflow-hidden">

        {{-- Decorative arcs bottom-right --}}
        <svg class="absolute bottom-0 right-0 w-72 h-72 opacity-30" viewBox="0 0 300 300" aria-hidden="true">
            <circle cx="300" cy="300" r="130" fill="none" stroke="white" stroke-width="1.5"/>
            <circle cx="300" cy="300" r="180" fill="none" stroke="white" stroke-width="1.5"/>
        </svg>

        {{-- Floating white card --}}
        <div class="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-xl px-8 py-9">

            <div class="flex justify-center mb-4">
                <img src="{{ asset('images/logo.png') }}" class=" rounded-full flex-shrink-0 w-32 h-32 object-cover" alt="Logo">
            </div>
            <h1 class="text-3xl font-bold text-slate-900 mb-1">Helpdesk Ticketing</h1>
            <p class="text-base text-slate-500 mb-7">Sign in to your account</p>

            @if(session('error'))
            <div class="mb-4 px-3 py-2.5 rounded-lg bg-red-50 border border-red-100 text-xs text-red-600 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                {{-- Email --}}
                <div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus
                               placeholder="Email Address"
                               class="auth-input w-full pl-12 pr-4 py-3.5 rounded-full border border-slate-200 text-sm text-slate-700 transition-shadow
                                      @error('email') border-red-300 @enderror">
                    </div>
                    @error('email')
                    <p class="mt-1.5 ml-4 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-12V7a4 4 0 10-8 0v2"/>
                            </svg>
                        </span>
                        <input type="password" name="password" id="password-field"
                               placeholder="Password"
                               class="auth-input w-full pl-12 pr-12 py-3.5 rounded-full border border-slate-200 text-sm text-slate-700 transition-shadow
                                      @error('password') border-red-300 @enderror">
                        <button type="button" onclick="togglePassword()"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <svg id="eye-open" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                    <p class="mt-1.5 ml-4 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember me --}}
                <label class="flex items-center gap-2 px-1 text-xs text-slate-500 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-300">
                    Remember this device
                </label>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-3.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-full transition-colors shadow-sm">
                    Login
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password-field');
    const eyeOpen = document.getElementById('eye-open');
    const eyeClosed = document.getElementById('eye-closed');
    const isPassword = input.type === 'password';

    input.type = isPassword ? 'text' : 'password';
    eyeOpen.classList.toggle('hidden', isPassword);
    eyeClosed.classList.toggle('hidden', !isPassword);
}
</script>
</body>
</html>