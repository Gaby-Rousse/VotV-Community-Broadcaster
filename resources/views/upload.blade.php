@include('partials.header')
<script src="scripts/musicUpload.js?v=3.4.2"></script>
<input type="hidden" id="approvedMaxPages" value="{{$approvedMaxPages}}">
<input type="hidden" id="pendingMaxPages" value="{{$pendingMaxPages}}">


@if(!Auth::check())
    <div id="overlay" class="flex fixed w-full h-full top-0 left-0 right-0 bottom-0 z-50 hover:cursor-pointer"
         style="background-color: rgba(0,0,0,0.5)">
        <div class="w-[75%] h-[75%] flex border-3 gap-1 flex-col border-solid m-auto bg-black">
            <div class="dos ml-1">It seems you're not connected.</div>
            <div class="dos ml-1">Functionalities will be limited.</div>
            <div class="dos ml-1">Please connect if you want to upload files.</div>
            <a href="/signin" class="url dos ml-1 mr-auto">Click here to login</a>
            <div class="dos m-auto mb-5">Click anywhere to close this pop-up</div>
        </div>
    </div>
@endif
<div id="settings" class="hidden fixed w-full h-full top-0 left-0 right-0 bottom-0 z-30"
     style="background-color: rgba(0,0,0,0.5)">
    <window class="m-auto w-[75%] h-[75%] flex flex-col">
        <div class="flex flex-row">
            <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">Playback</button>
            <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
            <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
            <button class="hidden md:inline pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
            <button class="hidden md:inline pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
            <button class="hidden md:inline pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
        </div>
        <div class="w-full flex header h-8">
            <div class="m-auto">Playback</div>
        </div>
        <div class="settingItem h-8 flex flex-row">
            <div class="mt-auto mb-auto ml-2">
                Autoplay
            </div>
            <label class="ml-auto checkContainer">
                <input id="autoplaySetting" type="checkbox">
                <span class="checkmark"></span>
            </label>
        </div>
        <div class="settingItem h-8 flex flex-row">
            <div class="mt-auto mb-auto ml-2">
                Loading feedback when scrolling
            </div>
            <label class="ml-auto checkContainer">
                <input id="loadingSetting" type="checkbox">
                <span class="checkmark"></span>
            </label>
        </div>
        <div class="mt-auto flex flex-row">
            <button id="backButton" class="pl-8 pr-8 pt-1 pb-1">Back</button>
        </div>
    </window>
</div>

<!--- https://tailwindcss.com/docs/pointer-events --->
<div class="flex flex-row fixed w-full h-full top-0 right-0 z-20 pointer-events-none">
    <div class="mt-auto mb-auto w-full gap-1 flex flex-col items-end pl-2" id="popups"></div>
</div>

<!--- Écran au complet --->
<div class="md:border-3 border-solid h-screen bg-black flex flex-col p-0.5">

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
    @if(App\Providers\Functions::retrieveDestinationTable() == 'audios')
        <div class="ml-1 flex flex-col md:flex-row md:gap-1 ">
            <div id="broadcasting" class="flex flex-row gap-1 hover:cursor-pointer">
                <div class="dot flex-shrink-0 size-3 mt-auto mb-auto"></div>
                <div class="dos text-nowrap">Currently broadcasting:</div>
                <div class="dos metadata truncate">Loading...</div>
            </div>
            <a class="md:ml-auto md:mr-1 dos url mr-auto" target="_blank"
               href="https://radio.votvbroadcast.com/votv.mp3">
                radio.votvbroadcast.com</a>

        </div>
        <div class="flex flex-row">
            <div class="ml-1 dos">Currently monitoring:</div>
            <div class="ml-1" id="watchingChannels"></div>
            <input type="hidden" id="monitoringChannel">
        </div>

    @endif

    <!--- Écrans --->
    <!--- Flex grow: Prend tout l'espace... Overflow-hidden: Mais empêche tes enfants de dépasser. --->
    <div class="flex-grow flex flex-col md:flex-row">

        <!--- Sous-Écran 1 (Coté Gauche): Liste --->
        <div class="flex flex-col w-full md:w-1/2 border-3 border-solid m-0.5 md:h-[100%]">
            <!-- Infos -->
            <div class="h-12 w-full flex flex-row border-b border-white">
                <img class="w-12" src="{{asset('images/spinningdisc.gif')}}" alt="">
                <div class="flex flex-col ml-1">
                    <div class="dos">Files Parsed:</div>
                    <div class="dos count">0</div>
                </div>
                <input id="searchToken" class="m-auto mr-1 border-1 dos w-1/2 text-center" type="text"
                       placeholder="Search..." value="{{session('keywords')}}">
            </div>

            <div class="h-12 w-full overflow-x-auto flex flex-row border-b border-white">
                @include('partials.whereListing')
            </div>
            <div class="w-full flex flex-row border-b border-white">
                <div class="dos ml-1">Browsing:</div>
                <div class="ml-1" id="channelDropdown">
                </div>
                <input type="hidden" id="filterByChannel" value="None">

            </div>

            <div id="MusicsPanel"
                 class="approvedList {{Auth::check() ? 'h-[40vh]' : 'h-[50vh]'}} flex overflow-y-auto">

                {{--

                https://stackoverflow.com/questions/6626314/center-an-item-with-position-relative
                position: relative;
                left: 50%;
                transform: translateX(-50%);

                --}}
                <!--- Écran de chargement il seras écrasé par le panneau--->
                <div class="m-auto flex flex-row">
                    <img class="w-12 mx-auto" src="{{ asset('images/hourglass.gif') }}?v=1" alt="">
                    <div class="border-3 border-solid p-1 w-100 h-12 flex flex-row">
                        <div class="h-full loadingBar" style="background-color: #F8FE50;"></div>
                    </div>
                </div>


            </div>
            @auth
                <div id="pendingTab"
                     class="h-[30vh] flex flex-col">
                    <!-- Infos  -->
                    <div class="h-12 w-full flex flex-row border-y border-white">
                        <img class="w-12" src="{{asset('images/hourglass.gif?v=1')}}" alt="">
                        <div class="flex flex-col ml-1">
                            <div class="dos">Files waiting approval:</div>
                            <div class="dos count2">0</div>
                        </div>
                    </div>

                    <div id="PendingPanel" class="pendingList flex-grow overflow-y-auto relative">
                        <!--- Écran de chargement (il seras écrasé par le panneau) --->
                        <div
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-row">
                            <img class="w-12 mx-auto" src="{{ asset('images/hourglass.gif') }}?v=1" alt="">
                            <div class="border-3 border-solid p-1 w-100 h-12 flex flex-row">
                                <div class="h-full loadingBar" style="background-color: #F8FE50;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="notificationTab"
                     class="h-[30vh] hidden flex-col">
                    <!-- Infos  -->
                    <div class="h-12 w-full flex flex-row border-y border-white">
                        <img class="w-12" src="{{asset('images/bell.png')}}" alt="">
                        <div class="flex flex-col ml-1">
                            <div class="dos">Notifications</div>
                            <div class="dos notifCount">0</div>
                        </div>
                        <button type="button"
                                class="truncate sm:ml-auto m-1 computerButton border-2 border-solid p-0.5 pl-4 pr-4 hover:cursor-pointer">
                            Read All
                        </button>
                        <button type="button"
                                class="truncate computerButton m-1 ml-0 border-2 border-solid p-0.5  pl-4 pr-4 hover:cursor-pointer">
                            Delete All
                        </button>
                        <img class="ml-0 m-auto notif-cross w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
                             src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
                    </div>

                    <div id="NotificationsPanel" class="h-[29vh] overflow-y-auto relative">
                        <!--- Écran de chargement (il seras écrasé par le panneau) --->
                        <div
                                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 flex flex-row">
                            <img class="w-12 mx-auto" src="{{ asset('images/hourglass.gif') }}?v=1" alt="">
                            <div class="border-3 border-solid p-1 w-100 h-12 flex flex-row">
                                <div class="h-full loadingBar" style="background-color: #F8FE50;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            @endauth
        </div>


        <!--- Sous-Écran 2 et 3 (Coté Droite) --->
        <div class="w-full h-[50%] md:h-full md:w-1/2 flex flex-col">

            {{-- Insertion des écrans activable --}}
            @auth
                @include('toggleableScreens.musics.import')
                @include('toggleableScreens.musics.upload')
                @include('toggleableScreens.musics.delete')
                @include('toggleableScreens.musics.edit')
                @include('toggleableScreens.musics.loading')
                @include('toggleableScreens.musics.report')
            @endauth
            @include('toggleableScreens.musics.play')
            {{-- stupide html --}}
            <div>
                <div class="flex-col bg-black flex border-3 border-solid m-0.5 p-1">
                    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
                        <img class="w-12" src="{{asset('images/book.png')}}" alt="">
                        <div class="flex flex-col ml-1">
                            <div class="dos">Filters</div>
                        </div>
                    </div>
                    <div>
                        <details>
                            <summary class="hover:cursor-pointer dos">Search by...</summary>
                            <div class="dos ml-1">To use a query, type it like this: "title:never gonna give you up"
                            </div>
                            <div class="flex flex-row w-full">
                                <div class="dos url hover:cursor-pointer ml-1 searchTitle">&#9830; Search by title</div>
                                <div class="dos mr-1 ml-auto">'title:'</div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div class="dos url hover:cursor-pointer ml-1 searchArtist">&#9830; Search by artist
                                </div>
                                <div class="dos mr-1 ml-auto">'artist:'</div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div class="dos url hover:cursor-pointer ml-1 searchGenre">&#9830; Search by genre</div>
                                <div class="dos mr-1 ml-auto">'genre:'</div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div class="dos url hover:cursor-pointer ml-1 searchYear">&#9830; Search by year</div>
                                <div class="dos mr-1 ml-auto">'year:'</div>
                            </div>
                        </details>


                        <details>
                            <summary class="hover:cursor-pointer dos">Sort by...</summary>
                            <div class="flex flex-row w-full gap-1">
                                <div class="dos url hover:cursor-pointer ml-1 toggleDirection">currently Ascending...
                                </div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div
                                        class="{{session('sortBy') == null || session('sortBy') == 'title' ? 'selected' : ''}} sorting dos url hover:cursor-pointer ml-1 sortTitle">
                                    &#9830; Sort by title
                                </div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div
                                        class="{{session('sortBy') == 'artist' ? 'selected' : ''}} sorting dos url hover:cursor-pointer ml-1 sortArtist">
                                    &#9830; Sort by artist
                                </div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div
                                        class="{{session('sortBy') == 'genre' ? 'selected' : ''}} sorting dos url hover:cursor-pointer ml-1 sortGenre">
                                    &#9830; Sort by genre
                                </div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div
                                        class="{{session('sortBy') == 'year' ? 'selected' : ''}} sorting dos url hover:cursor-pointer ml-1 sortYear">
                                    &#9830; Sort by year
                                </div>
                            </div>

                        </details>
                        @auth
                            <div class="flex flex-row w-full">
                                <div id="showMe"
                                     class="{{session('owner') == '1' ? 'selected' : ''}} dos url hover:cursor-pointer ml-1">
                                    &#9824; Show only my files
                                </div>
                            </div>
                            <div class="flex flex-row w-full">
                                <div id="showFavorite"
                                     class="{{session('favorite') == '1' ? 'selected' : ''}} dos url hover:cursor-pointer ml-1">
                                    &#9824; Show only my favorites
                                </div>
                            </div>
                        @endauth

                    </div>


                </div>

            </div>
        </div>
    </div>

    <input type="hidden" name="media_type" value="{{session('media_type') == null ? 'audios' : session('media_type')}}">


    <script>
        $(() => {

            @if(!Auth::check())
            PendingPanel.pause();

            NotificationsPanel.pause();
            @endif

            @if(!session('type') || session('type') == 'media')
            @if(App\Providers\Functions::retrieveDestinationTable() == 'audios')
            ChannelDropdown.updateOptions(['Everything', 'None', 'Christmas', 'Classical', 'Country', 'Electronic', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Metal', 'Pop', 'Rock', 'Video Game', 'Weird'])
            @else
            ChannelDropdown.updateOptions(['Everything', 'None', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Memes', 'News', 'Shows', 'Vlogs'])
            @endif
            @elseif(session('type') == 'event')
            ChannelDropdown.updateOptions(['Everything', 'Strange [4%]', 'Weird [2%]', 'Bizarre [1%]', 'Outlandish [0.4%]', 'Unfathomable [0.2%]', 'Otherworldly [0.1%]', 'Transcendental [0.04%]'])
            @else
            ChannelDropdown.updateOptions(['Everything'])
            @endif

            $('#showMe').click(function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected')
                    MusicsPanel.command("/onlyMe?value=-1");
                } else {
                    $(this).addClass('selected')
                    MusicsPanel.command("/onlyMe?value=1");
                }
            });

            $('#showFavorite').click(function () {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected')
                    MusicsPanel.command("/showFavorite?value=-1");
                } else {
                    $(this).addClass('selected')
                    MusicsPanel.command("/showFavorite?value=1");
                }
            });



        });

    </script>


@include('partials.footer')
