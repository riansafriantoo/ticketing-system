<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Forbidden</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['DM Sans','system-ui','sans-serif']}}}}</script>
</head>
<body class="h-full bg-gray-50 flex items-center justify-center font-sans">
    <div class="text-center">
        <p class="text-6xl font-semibold text-brand-600" style="color:#3b55e8">403</p>
        <h1 class="mt-4 text-2xl font-semibold text-gray-900">Access denied</h1>
        <p class="mt-2 text-sm text-gray-400">You don't have permission to view this page.</p>
        <a href="{{ url('/') }}" class="mt-6 inline-flex px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
            Go home
        </a>
    </div>
</body>
</html>