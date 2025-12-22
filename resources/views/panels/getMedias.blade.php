@php use App\Providers\Functions;
     $mediaType = Functions::retrieveDestinationTable();
@endphp
<div class="w-full">

    @foreach($medias as $media)
        @php
            if($media->type == 'media') {
                $folder = 'medias';
            } elseif ($media->type == 'event') {
                $folder = 'events';
            } elseif($media->type == 'ad') {
                $folder = 'advertisements';
            } elseif($media->type == 'segue') {
                $folder = 'segues';
            }
        @endphp
        <div class=" h-12 w-full flex flex-row " style="border-bottom: solid white 1px">
            <img class="w-12 cover" loading="lazy" src="{{asset('uploads/covers/' . $media->cover)}}" alt="cover">
            {{-- Pour permettre un flex-grow de réduire en dessous de la taille de son contenu, min-width = 0 --}}
            <div class="ml-1 flex flex-col flex-grow min-w-0">
                <div class="dos truncate">{{$media->artist}}</div>
                <div class="flex flex-row">
                    <div class="dos truncate">{{$media->title}}</div>
                    @if($media->explicit == 1)
                        <div class="dos purple">[E]</div>
                    @endif
                    @if($media->explicit == 2)
                        <div class="ml-1 dos selected">[DEBUG]</div>
                    @endif
                </div>

            </div>
            <div class="m-auto mr-1 gap-1 flex flex-row flex-shrink-0">
                @auth
                    @if(Auth::user()->isAdmin() || Auth::id() == $media->ownerId)
                        <img filename="{{$media->filename}}"
                             class="size-10 border-1 border-solid hover:cursor-pointer editAction"
                             style="border-color: #F8FE50" src={{asset('images/pencil.png')}} alt="edit">
                        <img filename="{{$media->filename}}"
                             class="size-10 border-1 border-solid hover:cursor-pointer deleteAction"
                             style="border-color: #F8FE50" src={{asset('images/trash.png')}} alt="delete">
                    @endif
                @endauth
                <img filename="{{$media->filename}}"
                     class="size-10 border-1 border-solid hover:cursor-pointer playAction" style="border-color: #F8FE50"
                     src={{asset('images/play.png')}} alt="play">
                <a href="/uploads/{{$mediaType}}/{{$folder}}/{{$media->filename}}" download>
                    <img class="size-10 border-1 border-solid hover:cursor-pointer" style="border-color: #F8FE50"
                         src={{asset('images/download.png')}} alt="download">
                </a>

            </div>
        </div>
    @endforeach
</div>

<script>


        $('.count').text({{$count}});
        $('#approvedMaxPages').val({{ceil($count /10)}})

</script>


