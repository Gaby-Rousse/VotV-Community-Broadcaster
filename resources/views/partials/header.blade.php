<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="{{asset('styles/styles.css?v=0.1')}}">
    <link rel="icon" type="image/x-icon" href="{{asset("/images/favicon.png")}}">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{asset("/scripts/autoRefreshPanel.js?v=0.7")}}"></script>
    <script src="{{asset("scripts/votvDropdown.js?v=0.7")}}"></script>
    <title>{{$title}}</title>
    @if($title == "VOTV Community Broadcaster")
    <meta content="VOTV Community Broadcaster" property="og:title" />
    <meta content="A fan-made radio station for Voices of the Void!" property="og:description" />
    <meta content="https://votvbroadcast.com/" property="og:url" />
    <meta content="https://votvbroadcast.com/images/VOTV%20Community%20Radio%20Poster.png?v=1" property="og:image" />
    <meta content="#04a96c" data-react-helmet="true" name="theme-color" />
        @endif
</head>
<body class="flex flex-col">
