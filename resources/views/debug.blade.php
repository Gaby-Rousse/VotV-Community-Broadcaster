<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/x-icon" href="{{asset("/images/favicon.png")}}">
    <!--- Mauvaise idée. Règle ça. --->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{asset("/scripts/autoRefreshPanel.js?v=0.6")}}"></script>
    <title>{{$title}}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="flex flex-col p-4">

<h2 class="font-bold text-2xl">The debug zone</h2>
<p>Access features that could help you. Who knows.</p>
@auth
    <h2 class="text-lg font-bold">Data:</h2>
    <pre class="border-2">
    Username: {{Auth::user()->username}}
    Role: {{Auth::user()->isAdmin() == 1 ? 'Administrator' : 'User'}}
    Remember token by Laravel: {{Auth::viaRemember() ? 'Yes' : 'No'}}
</pre>
    <h2 class="text-lg font-bold">Experimental:</h2>
    <div>Main channel shows blank screen: <a class=" hover:text-blue-500 text-blue-400 underline" href="/refreshTV">Force
            refresh</a></div>
@else
    <h2 class="text-lg font-bold">Not connected. <a class=" hover:text-blue-500 text-blue-400 underline" href="/signin">Connect
            here</a></h2>
@endauth
</body>
</html>
