@include('partials/header')
@include('partials/nav')

<div class="ml-auto mb-8 mt-8 mr-auto title text-2xl sm:text-4xl md:text-5xl lg:text-6xl">Credits</div>
<div class="w-[70%] sm:w-auto ml-auto mr-auto sm:text-md md:text-lg lg:text-xl">
    Here are some users who helped make this website even better!
</div>

<div class="mt-10 mb-10 ml-auto sm:ml-20 mr-auto w-[70%]">
    <div class="flex flex-col gap-2 mt-2 mb-2 items-center sm:items-baseline">

        <div class="text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Thanks to:</div>
        <div class="sm:text-md md:text-lg lg:text-xl">
            RendRover88, AshTheOrca and MapleCreature for their great voice acting for the radio segues.
        </div>
        <div class="sm:text-md md:text-lg lg:text-xl">
            Mr.SpookyPumpkinHead for their custom events and providing various scripts for the segues.
        </div>
        <div class="sm:text-md md:text-lg lg:text-xl">
            A5TR0spud for designing the awesome <a class="hover:!text-blue-500 !text-blue-400 hover:cursor-pointer" href="{{ asset('images/VOTV Community Radio Poster.png') }}">VOTV Community Radio poster</a>.
        </div>
        <div class="sm:text-md md:text-lg lg:text-xl">
            QuestWalker for their outstanding help on debugging various issues!
        </div>
        <div class="sm:text-md md:text-lg lg:text-xl">
            You, the whole community for providing files to be broadcasted.
        </div>
    </div>
</div>

@include('partials/footer')
