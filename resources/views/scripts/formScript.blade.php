<script>
    let delay = 500;
    @if($errors->any() || session('error'))
        delay = 200
    @endif
    $(() => {
        //Tout les enfants du formulaire
        //Sauf le bouton caché (le bouton caché permet d'envoyer en pressant enter)
        //https://www.w3schools.com/jquery/traversing_slice.asp
        //Tout sauf le premier et dernier
        const lines = $('form').children().slice(1, -1);
        lines.hide();

        //https://api.jquery.com/jQuery.each/
        $.each(lines, function (index, value) {
            $(value).delay(delay * index).show(0);
        });


        //À la fin de l'affichage:
        //Focus le premier input (invite l'usager à type directement.)
        setTimeout(() => {
            //skip le csrf input
                $('input').eq(1).focus();
            },
            delay * lines.length
        )
    });
</script>
