<!--- Écran éditeur de métadonnées --->
<form action="/updateBatch"
      class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" method="post"
      enctype="multipart/form-data">
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-11 ml-1 mt-auto mb-auto" src="{{asset('images/editor.png')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos flex gap-1 flex-row">
                <div class="dos red">Batch</div>
                Metadata Editor
            </div>
        </div>
        <img class="m-auto cross w-11 border-2 border-solid hover:cursor-pointer mr-0.5"
             src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
    </div>

    <div class=" ml-1 mt-1 gap-1 flex flex-row"><img class="size-6" src="{{asset('/images/info.png')}}">
        <div class="dos !text-white">Empty input will keep the original data</div>
    </div>

    <div class="flex flex-row">
        <label for="b_artist" class="dos ml-1 hover:cursor-pointer ">Artist: </label>
        <input id="b_artist" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="artist">
    </div>

    <div class="flex flex-row">
        <label for="b_genre" class="dos ml-1 hover:cursor-pointer ">Genre: </label>
        <input id="b_genre" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="genre">
    </div>

    <div class="flex flex-row">
        <label for="b_year" class="dos ml-1 hover:cursor-pointer ">Year: </label>
        <input id="b_year" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="year">
    </div>

    <div class="flex flex-row">
        <label for="b_cover" class="dos ml-1 hover:cursor-pointer ">Cover: </label>
        <input id="b_cover" type="file" accept="image/*" class="dos border-none flex-grow hover:cursor-pointer ml-0.5"
               name="cover">
    </div>

    <div class="flex flex-row">
        <label for="b_description" class="dos ml-1 hover:cursor-pointer ">Description: </label>
        <textarea id="b_description" type="text" class="dos border-none hover:cursor-pointer flex-grow ml-0.5 h-5"
                  name="description"></textarea>
    </div>

    <div id="channelDiv" class="ml-1 flex flex-row gap-1">
        <div id="channelText" class="dos">Channel:</div>
        <div id="destinationBatchDropdown"></div>
    </div>
    <div class="ml-1 flex flex-row gap-1">
        <div class="dos">Frequency:</div>
        <div id="frequencyBatchDropdown"></div>
    </div>


    <input id="batchDestination" type="hidden" name="destination" value="Unchanged">
    <input id="batchFrequency" type="hidden" name="frequency" value="Unchanged">


    <!--- Footer --->
    <div class="h-12 w-full flex flex-row mt-auto " style="border-top: solid white 1px">


        <div class="flex flex-row flex-grow">
            <img id="currentCover" class="w-12 cover" alt="">
            <div class="hidden xl:flex flex-col ml-1">
                <div class="dos">Editing:</div>
                <div class="flex gap-1 flex-row">
                    <div class="dos selectedCount line-clamp-1"></div>
                    <div class="dos"> files</div>
                </div>

            </div>
            <button type="button"
                    class="cross computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
                Cancel
            </button>
            <button type="button" id="updateBatchButton"
                    class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
                Update
            </button>

        </div>
    </div>

</form>


