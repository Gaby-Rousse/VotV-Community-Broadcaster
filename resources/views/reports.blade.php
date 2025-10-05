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
<br>
<h2 class="text-xl font-bold text-center">Current reports</h2>
<div class="overflow-y-auto mr-auto ml-auto flex flex-col gap-4 mt-3 w-1/2" style="height: 500px" >

    @foreach($reports as $report)
        @php
            $controller = 'uploadAudio';
            $format = substr($report->filename, -3);
            if($format == 'mp4')
            {
               $controller = 'uploadVideo';
            }
        @endphp
        <div class="border-2 flex-col flex p-2 w-full">
            <div>By: {{$report->from}}</div>
            <div>For: <a class="hover:text-blue-500 text-blue-400 underline" target="_blank" href="{{"https://votvbroadcast.com/" . $controller ."?keywords=filename:" . urlencode($report->filename)}}">{{$report->filename}}</a></div>
            <div>Reason: {{$report->reason}}</div>
            <a class="ml-auto hover:text-blue-500 text-blue-400 underline" href="{{"/deleteReport/" . $report->id}}" >Delete</a>
        </div>
    @endforeach
</div>

</body>
</html>
