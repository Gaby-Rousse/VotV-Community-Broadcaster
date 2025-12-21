<!--- Écran poubelle --->
<form action="/deleteMedia" class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" method="post"
      enctype="multipart/form-data">
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-11 ml-1 mt-auto mb-auto" src="{{asset('images/trash_screen.png')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">Trash</div>
        </div>
        <img class="m-auto mr-0.5 cross w-11 border-2 border-solid hover:cursor-pointer"
             src="{{asset('images/cross.png')}}" alt="" style="border-color:#F8FE50">
    </div>

    <div class="flex flex-row">
        <label for="confirm" id="confirmationLabel" class="dos ml-1 hover:cursor-pointer ">Please confirm you want to
            delete: (y/n) </label>
        <input id="confirm" type="text" class="dos border-none flex-grow hover:cursor-pointer ml-0.5" name="confirm">
    </div>

    @if(Auth::user()->isAdmin())
        <div class="flex flex-grow flex-row">
            <label for="reason" class="dos ml-1 hover:cursor-pointer ">Reason: </label>
            <textarea id="reason" type="text" class="dos border-none hover:cursor-pointer flex-grow ml-0.5"
                      name="reason"></textarea>
        </div>
    @endif

    <input type="hidden" id="hiddenFileToDelete" name="filename">

</form>
