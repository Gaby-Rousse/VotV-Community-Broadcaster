@include('partials/header')
@include('partials/nav')

<div class="flex flex-col">
    {{--
        sm = smallscreen, md = medium screen, lg = large screen. Permet d'être adaptatif.

        sm:	640px
        md:	768px
        lg:	1024px
        xl:	1280px
        2xl: 1536px
        Tailwind c'est du code destiné pour mobile de base.
        En dessous de 640px, c'est la valeur sans mention
        Ici text-xl.

        https://tailwindcss.com/docs/responsive-design

    --}}
    <h1 class="ml-auto mr-auto title text-2xl sm:text-4xl md:text-5xl lg:text-6xl">VotV Community Broadcaster</h1>
    <div class="flex flex-row flex-wrap-reverse mt-15 mb-10 ml-0 sm:ml-20 mr-auto">
        <div class="flex flex-col max-w-150 bg-black/80 p-4 rounded-lg">
            <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Description
            </div>
            <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline">
                <div class="w-[70%]">VotV Community Broadcaster is a fan made website and is not affiliated with
                    MrDrNose.
                </div>
                <div class="w-[70%]">Its purpose is to provide an in-game radio station and tv station that broadcasts
                    medias shared by the community.
                </div>
                <br>
                <div class="w-[70%]">Don't know how to add the streams to your online.txt? Check out the
                    <a href="/help" class="subtitle url">Help</a>
                    page.
                </div>
                <div class="flex flex-row gap-4 mt-4">

                </div>
            </div>

            <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Features</div>
            <div class="flex flex-col gap-2 mt-2 mb-2 items-center sm:items-baseline">
                <div class="w-[70%]">Access a radio and tv station both available 24/7</div>
                <div class="w-[70%]">Upload files that will be automatically parsed into these stations</div>
                <div class="w-[70%]">Adjust file metadata</div>
                <div class="w-[70%]">Download files uploaded by the community</div>
            </div>
        </div>
        <div class="lg:ml-25 mx-auto max-w-150 p-4 bg-black/80 flex flex-col">
            <div class="text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Links</div>
            <div class="flex relative flex-col gap-4 mt-2 mb-6 items-center sm:items-baseline">
                <a class="p-2 w-90 home" href="/changelog">Changelog</a>
                <a class="p-2 w-90 home" href="/credits">Credits</a>
                <a class="p-2 w-90 home" href="/history">History</a>
                <a class="p-2 w-90 home" href="/help">Help</a>
                <div class="flex flex-row w-full justify-between">
                    <a class="p-2 w-40 home" href="https://votvbroadcast.com/txt/radio.txt">Radios Streams</a>
                    <a class="p-2 w-40 home" href="https://votvbroadcast.com/txt/tv.txt">TV Streams</a>
                </div>
                <div class="flex flex-row w-full justify-between">
                    <a class="p-2 w-40 home" href="/suggestions">Suggestions</a>
                    <a class="p-2 w-40 home" href="/bugs">Bugs</a>
                </div>

                <div class="text-lg sm:text-xl md:text-2xl lg:text-3xl subtitle">External</div>
                <div class="flex flex-row w-full justify-between">
                    <a class="p-2 w-40 home" href="https://votv.dev">votv.dev</a>
                    <a class="p-2 w-40 home" href="https://assets.votvbroadcast.com/">Assets Hub</a>
                </div>

                <a class="p-2 w-90 home"
                   href="https://github.com/Foxaryse/ArchiveCommunityBranch/tree/main/linux/scripts/onlinevideoworkaround">onlinevideoworkaround
                    <br> (TV streams on Linux!)</a>

            </div>

@include('partials/footer')
