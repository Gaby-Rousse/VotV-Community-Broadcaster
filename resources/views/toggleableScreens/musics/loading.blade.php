

<!--- Écran de chargement --->
<div class="flex-1/2 border-3 border-solid m-0.5 hidden flex-col toggleableScreen" id="loadingScreen" >

    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-12" src="{{asset('images/hourglass.gif')}}?v=1" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">Loading</div>
        </div>
    </div>

    <div class="m-auto border-3 border-solid p-1 w-100 h-10">
        <div class="h-[100%] loadingBar" style="background-color: #F8FE50;"></div>
    </div>

</div>
