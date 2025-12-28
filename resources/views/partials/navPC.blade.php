<div class=" flex flex-row gap-1 border-solid border-3 md:m-0.5">
    <div class="overflow-x-auto flex flex-row gap-1 flex-nowrap w-full">
        <a class="computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 flex-shrink-0 flex"
           href="/"><img class="size-6" src="{{asset('images/home.png')}}?v=0.1"></a>
        <a href="/uploadAudio" id="navRadio"
           class="{{session('media_type') == null || session('media_type') == 'audios' ? 'selected' : ''}} w-28 flex-shrink-0 navButton computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 pl-4 pr-4">
            Radio
        </a>
        <a href="/uploadVideo"
           class="{{session('media_type') == 'videos' ? 'selected' : ''}} w-28 flex-shrink-0 navButton computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 pl-4 pr-4">
            TV
        </a>
        <div class="flex flex-row ml-auto flex-shrink-0">
            @auth
                @if(Auth::user()->isAdmin())
                    <a href="/reports"
                       class="computerButton flex-shrink-0 border-2 w-35 border-solid p-0.5 mt-1 mb-1 mr-1 pl-4 pr-4 hover:cursor-pointer"
                       id="reports">
                        Reports ({{$reportCount}})
                    </a>
                @endif
            @endauth
            <button
                    id="settingButton"
                    class="computerButton flex-shrink-0 border-2 w-28 border-solid p-0.5 mt-1 mb-1 mr-1 pl-4 pr-4 hover:cursor-pointer">
                Settings
            </button>
            @auth
                <button id="notificationButton"
                        class="computerButton flex-shrink-0 hover:cursor-pointer border-2 border-solid p-0.5 pr-1 mt-1 mb-1 mr-1 flex flex-row">
                    <img class="size-6" alt="notifications" src="{{asset('images/bell.png')}}">
                    <div class="dos notifCount">0</div>
                </button>
            @endauth
        </div>
    </div>
</div>
