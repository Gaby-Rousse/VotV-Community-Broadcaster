@if(session('media_type') == null || session('media_type') == 'audios')
    <div id="listWhere" class=" flex m-auto">
        <button
            class="{{session('type') == null || session('type') == 'media' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Musics
        </button>
        <button
            class="{{session('type') == 'event' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Events
        </button>
        <button
            class="{{session('type') == 'ad' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Ads
        </button>
        <button
            class="{{session('type') == 'segue' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Segues
        </button>
    </div>
@elseif(session('media_type') == 'videos')
    @php
        if(session('type')){
           if(session('type') == 'segue') {
               session(['type' => 'media']);
           }
        }
    @endphp
    <div id="listWhere" class=" flex m-auto">
        <button
            class="{{session('type') == null || session('type') == 'media' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Videos
        </button>
        <button
            class="{{session('type') == 'event' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Events
        </button>
        <button
            class="{{session('type') == 'ad' ? 'selected' : ''}} w-28 h-8 listWhere computerButton hover:cursor-pointer border-2 border-solid p-0.5 mt-1 mb-1 ml-1 pl-4 pr-4">
            Ads
        </button>
    </div>
@endif
