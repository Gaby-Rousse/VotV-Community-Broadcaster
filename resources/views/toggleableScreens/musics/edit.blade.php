<!--- Écran éditeur de métadonnées --->
<form  action="/updateMetadata" class="flex-1/2 editingScreen border-3 border-solid m-0.5 hidden flex-col toggleableScreen" method="post" enctype="multipart/form-data" >
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-11 ml-1 mt-auto mb-auto" src="{{asset('images/editor.png')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">Metadata Editor</div>
        </div>
        <img class="m-auto cross w-11 border-2 border-solid hover:cursor-pointer mr-0.5" src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
    </div>

    <div class="flex flex-row">
        <label for="title" class="dos ml-1 hover:cursor-pointer ">Title: </label>
        <input id="title" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="title">
    </div>

    <div class="flex flex-row">
        <label for="artist" class="dos ml-1 hover:cursor-pointer ">Artist: </label>
        <input id="artist" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="artist">
    </div>

    <div class="flex flex-row">
        <label for="genre" class="dos ml-1 hover:cursor-pointer ">Genre: </label>
        <input id="genre" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="genre">
    </div>

    <div class="flex flex-row">
        <label for="year" class="dos ml-1 hover:cursor-pointer ">Year: </label>
        <input id="year" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="year">
    </div>

    <div class="flex flex-row">
        <label for="cover" class="dos ml-1 hover:cursor-pointer ">Cover: </label>
        <input id="cover" type="file" accept="image/*" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="cover">
    </div>

    <div class="flex flex-row">
        <label for="explicit" class="dos ml-1 hover:cursor-pointer ">Explicit: </label>
        <input id="explicit" type="checkbox" class="dos border-none hover:cursor-pointer ml-0.5" name="explicit">
    </div>
    <div class="flex flex-row">
        <label for="description" class="dos ml-1 hover:cursor-pointer ">Description: </label>
        <textarea id="description" type="text" class="dos border-none hover:cursor-pointer flex-grow ml-0.5 h-5" name="description"></textarea>
    </div>


    <div class="mt-auto">



        <div id="channelDiv" class="ml-1 mt-auto flex flex-row gap-1">
            <div id="channelText" class="dos">Channel: </div>
            <div id="destinationDropdown"></div>
        </div>

        <div class="h-12 w-full flex flex-row" style="border-top: solid white 1px">
            @include('partials.whereButtons')
        </div>

    </div>



    <input id="destination" type="hidden" name="destination" value="None">






    <!--- Footer --->
    <div id="pendingFile" class="h-12 w-full flex flex-row " style="border-top: solid white 1px">


        <div class="flex flex-row flex-grow">
            <img id="currentCover" class="w-12 cover" alt="">
            <div class="hidden xl:flex flex-col ml-1">
                <div class="dos">Editing:</div>
                <div class="dos line-clamp-1" id="editing_filename">something.mp3</div>
            </div>
            <button type="button" class="cross computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
                Cancel
            </button>
            <button type="button" id="updateButton" class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
                Update
            </button>

        </div>
    </div>

    <input type="hidden" id="hiddenFileName" name="filename">

</form>

<script>
    $(() => {
        const select = $('select');
        //https://stackoverflow.com/questions/10502093/how-to-reset-a-select-element-with-jquery

        select.change(function() {
            //https://stackoverflow.com/questions/10659097/jquery-get-selected-option-from-dropdown
            $('#genre').val(select.find(":selected").text());
        });
    })


</script>

