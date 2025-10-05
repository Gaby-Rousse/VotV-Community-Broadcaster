
<div class="flex flex-col ml-1">
    <div class="dos">Click on a unread notification to mark as read.</div>
    <div class="dos">Click on a read notification to delete it.</div>
    <div class="flex flex-row gap-2">
        <div class="dos">[yyyy-MM-dd HH:mm:ss]</div>
        <div class="dos flex-grow">Text Block</div>
    </div>
    @foreach($notifications as $notification)
        <div idnotif="{{$notification->id}}" class="flex flex-row gap-2 notification hover:cursor-pointer">
            <div class="dos whitespace-nowrap {{$notification->seen == 0 ? 'selected' : '' }}">[{{$notification->created_at}}]</div>
            <div
                class="dos {{$notification->seen == 0 ? 'selected' : '' }}">{{$notification->from . ' ' . $notification->content}}</div>
        </div>

    @endforeach
</div>


<script>
    $(() => {
        $('.notifCount').text({{$count}});
        @if(session('play_mailSFX'))
        const sfx_mail = new Audio('https://votvbroadcast.com/sfx/sfx_email.ogg');
        sfx_mail.play();
        @endif
    })

</script>
