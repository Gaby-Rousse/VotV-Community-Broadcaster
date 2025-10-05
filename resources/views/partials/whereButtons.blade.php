@if(session('media_type') == null || session('media_type') == 'audios')
<div class="whereButtons flex flex-row overflow-x-auto flex-grow ml-1">
    <div class="flex flex-col">
        <div class="dos">Where?</div>
    </div>
    <button type="button" class="where sm:ml-auto ml-2 computerButton selected border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
        Musics
    </button>
    <button type="button" class="where computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
        Events
    </button>
    <button type="button" class="where computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
        Advertisements
    </button>
    <button type="button" class="where computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
        Segues
    </button>
    <input type="hidden" value="media" name="type" />
</div>
@elseif(session('media_type') == 'videos')
    <div class="whereButtons flex flex-row overflow-x-auto flex-grow ml-1">
        <div class="flex flex-col">
            <div class="dos">Where?</div>
        </div>
        <button type="button" class="where sm:ml-auto ml-2 computerButton selected border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
            Videos
        </button>
        <button type="button" class="where computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
            Events
        </button>
        <button type="button" class="where computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
            Advertisements
        </button>
        <input type="hidden" value="media" name="type" />
    </div>
@endif
