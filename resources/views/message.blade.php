@include('partials/header')
@include('partials/nav')
<h1 class="ml-auto mr-auto title text-2xl sm:text-4xl md:text-5xl lg:text-6xl">{{$title}}</h1>
<div class="text-center mt-2 sm:text-md md:text-lg lg:text-xl">
    {{$description}}
</div>
<form method="post" action="/sendMessage"
      class="flex flex-col m-auto mt-10 w-full sm:w-2/3 md:w-1/2 rounded-sm border-2 p-1"
      style="border-color: #2b2d30; background-color:#1e1f22">
    @csrf
    <div>
        <label for="description">Message: </label>
        <textarea class="w-full h-[200px] " id="description" name="message"
                  type="text" {{session('connectedUser') ? '' : 'disabled'}} >{{session('connectedUser') ? '' : 'Please authenticate.'}}</textarea>

    </div>
    <button type="submit" class="ml-auto mr-auto rounded-sm border-2 hover:cursor-pointer"
            style="border-color: #4d4e51; background-color:#2b2d30">Submit!
    </button>
    <input type="hidden" name="table" value="{{$table}}">
</form>
<div class="mt-10 mb-10 text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle text-center">{{$current}}</div>

<div class="h-[30vh] overflow-y-auto flex flex-col gap-10 mb-10">
    @foreach($messages as $message)
        <form method="post" action="/updateMessage"
              class="flex flex-col m-auto w-full sm:w-2/3 md:w-1/2 rounded-sm border-2 p-1"
              style="border-color: #2b2d30; background-color:#1e1f22">
            @csrf
            <div>
                <div>
                    {{$message->time}}
                </div>
                <div class="flex flex-row">
                    <div>From:</div>
                    <div>{{$message->from}}</div>
                </div>

                <div class="flex flex-row">
                    <div>Message:</div>
                    <div class="break-keep" >{{$message->content}}</div>
                </div>

                <div class="flex flex-row">
                    <div>Seen:</div>
                    <div>{{$message->seen == 1 ? 'yeah' : 'nope'}}</div>
                </div>

            </div>
            @if(session('connectedUser'))
                <div class="ml-auto mr-auto">
                    @if(\App\Providers\Queries::isOwnerOfMessage($message->id, $table) || session('connectedUser')->isAdmin())
                        <button type="submit" name="delete" value="{{$message->id}}"
                                class=" rounded-sm border-2 hover:cursor-pointer"
                                style="border-color: #4d4e51; background-color:#2b2d30">Delete!
                        </button>
                    @endif
                    @if(session('connectedUser')->isAdmin())
                        <button type="submit" name="seen" value="{{$message->id}}"
                                class=" rounded-sm border-2 hover:cursor-pointer"
                                style="border-color: #4d4e51; background-color:#2b2d30">Check!
                        </button>
                    @endif
                </div>
            @endif
            <input type="hidden" name="table" value="{{$table}}">
        </form>
    @endforeach
</div>


@include('partials/footer')
