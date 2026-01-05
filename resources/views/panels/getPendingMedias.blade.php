<div>

    @foreach($medias as $media)
        <div class=" h-12 w-full flex flex-row " style="border-bottom: solid white 1px">
            <div class="flex-shrink-0 w-12 h-12 relative">
                <div class="absolute hidden pointer-events-none w-full h-full bg-[#0000004d] top-0 left-0 ">
                    <div class="absolute -top-1 right-0.5 dos">*</div>
                </div>

                <img filename="{{$media->filename}}" class="w-12 h-12 hover:cursor-pointer selectAction cover "
                     loading="lazy"
                     src="{{asset('uploads/covers/' . $media->cover)}}"
                     alt="cover">
            </div>
            {{-- Pour permettre un flex-grow de réduire en dessous de la taille de son contenu, min-width = 0 --}}
            <div class="ml-1 flex flex-col flex-grow min-w-0">
                <div class="dos truncate">{{$media->artist}}</div>
                <div class="dos truncate">{{$media->title}}</div>
            </div>
            <div class="m-auto mr-1 gap-1 flex flex-row flex-shrink-0">
                <img filename="{{$media->filename}}"
                     class="size-10 border-1 border-solid hover:cursor-pointer pendingFile editAction"
                     style="border-color: #F8FE50" src={{asset('images/pencil.png')}} alt="edit">
                <img filename="{{$media->filename}}"
                     class="size-10 border-1 border-solid hover:cursor-pointer pendingFile deleteAction"
                     style="border-color: #F8FE50" src={{asset('images/trash.png')}} alt="delete">
                <img filename="{{$media->filename}}"
                     class="size-10 border-1 border-solid hover:cursor-pointer pendingFile playAction"
                     style="border-color: #F8FE50" src={{asset('images/play.png')}} alt="play">
                @if(Auth::user()->isAdmin())
                    <img filename="{{$media->filename}}"
                         class="size-10 border-1 border-solid hover:cursor-pointer pendingFile approveAction"
                         style="border-color: #F8FE50" src={{asset('images/check.png?v=1')}} alt="approve">
                @endif
            </div>
        </div>
    @endforeach


</div>

<script>

    $('.count2').text({{$count}});
    $('#pendingMaxPages').val({{ceil($count /10)}})

</script>


