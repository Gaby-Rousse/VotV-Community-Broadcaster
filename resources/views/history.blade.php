@include('partials.header')
<div class="md:border-3 border-solid h-screen bg-black flex flex-col p-0.5">
    @include('partials.navPC')
</div>
<script>
    let delay = 500;
    $(() => {
        //Tout les enfants du target
        //https://www.w3schools.com/jquery/traversing_slice.asp
        const lines = $('#history').children()
        lines.hide();

        //https://api.jquery.com/jQuery.each/
        $.each(lines, function (index, value) {
            $(value).delay(delay * index).show(0);
        });

    });
</script>
@include('partials.footer')