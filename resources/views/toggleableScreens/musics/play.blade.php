@if(session('media_type') == null || session('media_type') == 'audios')
    <!--- Écran d'écoute --->
    <div class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" id="playScreen">

        <!--- Header --->
        <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
            <img class="w-12" src="{{asset('images/media_screen.png')}}" alt="">
            <div class="flex flex-col ml-1">
                <div class="dos">Media Player</div>
            </div>
            @auth
                <div class="ml-auto flex flex-row gap-0.5">
                    <img class="m-auto ml-0 report w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/empty_flag.png')}}" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 favorite w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/empty_heart.png')}}" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 share h-11 w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/share.png')}}?v=4" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 cross w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
                </div>
            @else
                <img class="m-auto share h-11 w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                     src="{{asset('images/share.png')}}?v=4" alt="" style="border-color:#F8FE50">
            @endauth
        </div>

        <div class="flex flex-row">
            <div class="dos ml-1">Title:</div>
            <div id="ptitle" class="dos border-none flex-grow ml-0.5"></div>
        </div>

        <div class="flex flex-row">
            <div class="dos ml-1">Artist:</div>
            <div id="partist" class="dos border-none flex-grow ml-0.5"></div>
        </div>

        <div class="flex flex-row">
            <div class="dos ml-1">Genre:</div>
            <div id="pgenre" class="dos border-none flex-grow ml-0.5"></div>
        </div>

        <div class="flex flex-row">
            <div class="dos ml-1">Year:</div>
            <div id="pyear" class="dos border-none flex-grow ml-0.5"></div>
        </div>
        <div class="flex flex-row">
            <div class="dos ml-1">Description:</div>
            <div id="pdescription" class="dos border-none flex-grow ml-0.5"></div>
        </div>

        <div class="flex flex-row gap-1 mt-auto mb-0.5">
            <label for="volume" class="dos ml-1 ">Volume: </label>
            <input id="volume" type="range" min="0" max="100" value="50" class="slider w-[25%] mt-auto mb-auto">
            <div id="audioValue" class="dos">50</div>
        </div>


        <!--- Footer --->
        <div class="h-12 w-full flex flex-row" style="border-top: solid white 1px">

            <div class="flex flex-row flex-grow">
                <img class="w-12 pcurrentCover cover" alt="">
                <div class="hidden xl:flex !max-w-[30%]  flex-col ml-1">
                    <div class="dos">File loaded:</div>
                    <!--- LA VIE MÉRITE D'ÊTRE VÉCU: https://stackoverflow.com/questions/71093772/how-to-truncate-text-in-tailwindcss --->
                    <div class="dos line-clamp-1 playing_file">None</div>
                </div>
                <div class="flex flex-row flex-grow max-w-[70%] gap-1 mb-auto mt-auto mr-2 ml-2">
                    <div id="elapsed" class="dos">00:00:00</div>
                    <input id="seeking" type="range" min="0" max="0" value="0" class="slider mt-auto mb-auto w-full">
                    <div id="duration" class="dos">00:00:00</div>
                </div>
                <img id="playPause"
                     class="computerButton border-2 w-16 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer"
                     src="{{asset('/images/play.png')}}">
            </div>
        </div>

        <audio src="" class="hidden"></audio>

    </div>
@elseif(session('media_type') == 'videos')
    <div class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" id="playScreen">
        <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
            <img class="w-12" src="{{asset('images/media_screen.png')}}" alt="">
            <div class="flex flex-col ml-1">
                <div class="dos">Media Player</div>
            </div>
            @auth
                <div class="ml-auto flex flex-row gap-0.5">
                    <img class="m-auto ml-0 report w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/empty_flag.png')}}" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 favorite w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/empty_heart.png')}}" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 share h-11 w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/share.png')}}?v=4" alt="" style="border-color:#F8FE50">
                    <img class="m-auto ml-0 cross w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                         src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
                </div>
            @else
                <img class="m-auto share h-11 w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                     src="{{asset('images/share.png')}}?v=4" alt="" style="border-color:#F8FE50">
            @endauth
        </div>
        <div class="h-full max-h-full relative">
            <video class="absolute h-full overflow-hidden" src=""
                   style=" top: 50%; left: 50%; transform: translate(-50%, -50%)"></video>
        </div>
        <div id="details" class="flex  mt-1 flex-col transition-all duration-300" style="height:26px">
            <div id="openDetails"
                 class=" mr-auto ml-auto border-1 text-center border-b-black rounded-t-md pl-12 pr-12 hover:cursor-pointer z-10 "
                 style=" margin-bottom: -1px;">&#9650;
            </div>
            <div class="flex flex-col w-full h-full border-b-black  border-1 mr-auto ml-auto"
                 style="margin-bottom: -1px">
                <div class="flex flex-row">
                    <div class="dos ml-1">Title:</div>
                    <div id="ptitle" class="dos border-none flex-grow ml-0.5"></div>
                </div>

                <div class="flex flex-row">
                    <div class="dos ml-1">Artist:</div>
                    <div id="partist" class="dos border-none flex-grow ml-0.5"></div>
                </div>

                <div class="flex flex-row">
                    <div class="dos ml-1">Genre:</div>
                    <div id="pgenre" class="dos border-none flex-grow ml-0.5"></div>
                </div>

                <div class="flex flex-row">
                    <div class="dos ml-1">Year:</div>
                    <div id="pyear" class="dos border-none flex-grow ml-0.5"></div>
                </div>

                <div class="flex flex-row">
                    <div class="dos ml-1">Description:</div>
                    <div id="pdescription" class="dos border-none flex-grow ml-0.5"></div>
                </div>

                <div class="flex flex-row gap-1 mt-auto mb-0.5">
                    <label for="volume" class="dos ml-1 ">Volume: </label>
                    <input id="volume" type="range" min="0" max="100" value="50" class="slider w-[25%] mt-auto mb-auto">
                    <div id="audioValue" class="dos">50</div>
                </div>
            </div>

        </div>

        <div class="h-12 w-full flex z-10 bg-black flex-row">

            <div class="flex flex-row flex-grow">
                <img class="w-12 cover pcurrentCover" alt="">
                <div class="hidden xl:flex !max-w-[30%]  flex-col ml-1">
                    <div class="dos">File loaded:</div>
                    <!--- LA VIE MÉRITE D'ÊTRE VÉCU: https://stackoverflow.com/questions/71093772/how-to-truncate-text-in-tailwindcss --->
                    <div class="dos line-clamp-1 playing_file">None</div>
                </div>
                <div class="flex flex-row flex-grow max-w-[70%] gap-1 mb-auto mt-auto mr-2 ml-2">
                    <div id="elapsed" class="dos">00:00:00</div>
                    <input id="seeking" type="range" min="0" max="0" value="0" class="slider mt-auto mb-auto w-full">
                    <div id="duration" class="dos">00:00:00</div>
                </div>
                <img id="playPause"
                     class="computerButton border-2 w-16 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer"
                     src="{{asset('/images/play.png')}}">
            </div>
        </div>
    </div>
    <script>
        $(() => {
            $('#openDetails').on('click', function () {
                let details = $('#details')

                if ($(this).html() === '▲') {
                    $(this).html('▼');
                    details.css('height', 400);
                } else {
                    $(this).html('▲');
                    details.css('height', 26);
                }
            })
        })
    </script>
@endif
