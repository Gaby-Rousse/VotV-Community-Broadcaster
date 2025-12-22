<!--- Écran d'upload --->
<form action="/importMedia" class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen"
      enctype="multipart/form-data">
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-12" src="{{asset('images/satellite.png')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">File Importer</div>
        </div>
        <button type="button"
                class="cross computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
            Upload from computer
        </button>
    </div>

    <div class="flex flex-row ml-1 w-full flex-grow">
        <label for="url" class="dos hover:cursor-pointer ">URL(s) (Seperate urls by a comma to import several files at
            once.):</label>
        <textarea wrap="soft" class="dos flex-grow resize-none mr-1" id="url" type="text" name="url"></textarea>
    </div>

    <!--- Footer --->
    <div class="h-12 w-full flex flex-row mt-auto" style="border-top: solid white 1px">
        @include('partials.whereButtons')
    </div>
    <div class="h-12 w-full flex flex-row" style="border-top: solid white 1px">
        <button type="button" id="resetImportButton"
                class="computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
            Reset
        </button>
        <button type="button" id="importButton"
                class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
            Import!
        </button>


    </div>
</form>

