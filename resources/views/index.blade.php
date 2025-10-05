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
    <div class="sm:mt-20 mt-10 mb-10 ml-0 sm:ml-20 mr-auto">
        <div class="text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Links</div>
        <div class="flex flex-col gap-4 mt-2 mb-6 items-center sm:items-baseline">
            <a class="p-2 w-40 home" href="https://votv.dev" >votv.dev</a>
            <a class="p-2 w-40 home" href="/changelog" >Changelog</a>
            <a class="p-2 w-40 home" href="/credits" >Credits</a>
            <a class="p-2 w-40 home" href="/suggestions" >Suggestions</a>
            <a class="p-2 w-40 home" href="/bugs" >Bugs</a>
        </div>

        <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Description</div>
        <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline">
            <div class="w-[70%]">VotV Community Broadcaster is a fan made website and is not affiliated with MrDrNose.</div>
            <div class="w-[70%]">Its purpose is to provide an in-game radio station and tv station that broadcasts medias shared by the community.</div>
            <div class="w-[70%]">For the radio, add the following url: <a class="url" href="https://radio.votvbroadcast.com/votv.mp3">https://radio.votvbroadcast.com/votv.mp3</a> to online.txt</div>
            <div class="w-[70%]">For the tv, add the following url: <a class="url" href="https://tv.votvbroadcast.com/votv.mp4">https://tv.votvbroadcast.com/votv.mp4</a> to online.txt</div>
            <div class="flex flex-row gap-4 mt-4">
                <a class="p-2 w-40 home" href="https://votvbroadcast.com/txt/radio.txt" >Radios Streams</a>
                <a class="p-2 w-40 home" href="https://votvbroadcast.com/txt/tv.txt" >TV Streams</a>
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

</div>

@include('partials/footer')
