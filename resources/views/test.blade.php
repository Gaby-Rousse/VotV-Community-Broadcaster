@include('partials/header')

<div class="mb-20"></div>
<window class="m-auto mr-0 w-[50vw] h-[50vh] flex flex-col">
    <div class="flex flex-row">
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">Display</button>
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">Playlists</button>
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
        <button class="pl-3 pr-3 pt-1 pb-1 flex-grow">N/A</button>
    </div>
    <div class="w-full text-center header">
        Display
    </div>
</window>

@include('partials.dropdown')
<!--- https://tailwindcss.com/docs/pointer-events --->
<div class="flex flex-row fixed w-[20vw] h-full top-0 right-0 z-10 pointer-events-none">
    <div class="popup w-76 gap-2 inline-flex flex-row text-nowrap"><img src="{{asset('images/help.png')}}" > <div class="mt-auto mb-auto dos !text-white">Text copied into clipboard!</div></div>
</div>

<div>Hello I shouldn't move. ...</div>




@include('partials/footer')
