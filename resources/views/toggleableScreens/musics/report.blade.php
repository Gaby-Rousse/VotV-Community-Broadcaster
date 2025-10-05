<!--- Écran poubelle --->
<form action="/reportMedia" class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" method="post"
      enctype="multipart/form-data">
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-11 ml-1 mt-auto mb-auto" src="{{asset('images/restrict.png')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">Report</div>
        </div>
        <img class="m-auto mr-0.5 cross w-11 border-2 border-solid hover:cursor-pointer"
             src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
    </div>

    <div class="flex flex-grow flex-row">
        <label for="report_reason" class="dos ml-1 hover:cursor-pointer ">Please provide the reason of your report: </label>
        <textarea id="report_reason" type="text" class="dos border-none hover:cursor-pointer flex-grow ml-0.5"
                  name="reason"></textarea>
    </div>

    <div id="pendingFile" class="h-12 w-full flex flex-row mt-auto" style="border-top: solid white 1px">
        <div class="flex flex-row flex-grow">
            <img class="w-12 cover pcurrentCover" alt="">
            <div class="hidden xl:flex flex-col ml-1">
                <div class="dos">Reporting:</div>
                <div class="dos line-clamp-1 playing_file" >something.mp3</div>
            </div>
            <button type="button"
                    class="cross computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
                Cancel
            </button>
            <button type="button" id="reportButton"
                    class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
                Report
            </button>

        </div>
    </div>

        <input type="hidden" id="hiddenFileToReport" name="filename">

</form>

