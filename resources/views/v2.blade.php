<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title inertia>SIAP</title>
    <link rel="icon" href="{{ asset('images/logomini.png') }}" type="image/x-icon">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Reverb Dynamic Config -->
    <script>
        window.reverbConfig = {
            key: "{{ config('reverb.apps.apps.0.key') }}",
            host: "{{ config('reverb.apps.apps.0.options.host') }}",
            port: "{{ config('reverb.apps.apps.0.options.port') }}",
            scheme: "{{ config('reverb.apps.apps.0.options.scheme') }}"
        };
    </script>

    @routes
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-nunito antialiased bg-[#F5F7FF]">
    @inertia
</body>
</html>
