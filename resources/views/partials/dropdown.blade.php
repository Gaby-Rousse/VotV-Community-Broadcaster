@php
    $table = \App\Providers\Functions::retrieveDestinationTable();
    if(session('type'))
    {
        $type = session('type');
    }
    else
    {
        $type = 'media';
    }
    if($type == 'media')
    {
        if($table == 'audios') {
        $options = ['None','Classical', 'Country', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Pop', 'Rock', 'Video Game', 'Weird'];
        }
        else {
        $options = ['None', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Memes', 'News', 'Shows', 'Vlogs'];
        }
    }
    else
    {
        if($type === 'event')
        {
           $options = ['Strange [4%]','Weird [2%]','Bizarre [1%]','Outlandish [0.4%]','Unfathomable [0.2%]', 'Otherworldly [0.1%]', 'Transcendental [0.04%]'];
        }
        else
        {
           $options = [''];
        }
    }

$i = 0

@endphp

<dropdown class="relative w-70 flex flex-col gap-0 hover:cursor-pointer">
    <div class="dropdownTitle">Select (hover me)</div>
    <div class="hidden absolute options flex-col mt-6">
        <div class="mr-auto ml-auto flex flex-col">
            @foreach($options as $option)
                @php
                    $i++;
                @endphp
                <div class="flex flex-row option">
                    <svg value="{{$option}}" class="mt-auto mb-auto" height="12.5" width="12.5"
                         xmlns="http://www.w3.org/2000/svg">
                        <line x1="0" y1="2.5" x2="8.75" y2="6.25" style="stroke:red;stroke-width:2"/>
                        <line x1="0" y1="10" x2="8.75" y2="6.25" style="stroke:red;stroke-width:2"/>
                        Sorry, your browser does not support inline SVG.
                    </svg>
                    <div>{{$i . ') '}}{{$option}}</div>
                </div>
            @endforeach
        </div>

    </div>
</dropdown>

{{--
<script>
    $(() => {

        function initialize()
        {
            invisible(svg)
            visible($(svg[0]))
        }

        function show(selector) {
            selector.removeClass('hidden');
        }

        function hide(selector) {
            selector.addClass('hidden');
        }

        function visible(selector) {
            selector.removeClass('invisible');
        }

        function invisible(selector)
        {
            selector.addClass('invisible');
        }


        let dropdown = $( "dropdown" )
        let options = $(".options")
        let option = $(".option")

        let i = 0
        let index = 0;

        dropdown.on( "click", function() {
            show(options)
        });

        dropdown.on( "mouseenter", function() {
            show(options)
            //https://developer.mozilla.org/en-US/docs/Web/API/Element/wheel_event
            $(window).on('wheel', function(e) {
                i = 0
                //https://stackoverflow.com/questions/16674963/event-originalevent-jquery
                //Il faut accéder à l'event vanilla
                i += e.originalEvent.deltaY * -0.01;
                if(i > 0)
                {
                    index--;
                    if(index < 0)
                    {
                        index = 0;
                    }
                    invisible(svg)
                    visible($(svg[index]));
                }
                else
                {
                    index++;
                    if(index > svg.length - 1)
                    {
                        index = svg.length - 1;
                    }
                    invisible(svg)
                    visible($(svg[index]))
                }
            });
        })
        dropdown.on( "mouseleave", function() {
            saveOption()
        })

        let svg = $('svg')

        option.each(function (){
            $(this).on( "mouseenter", function() {
                invisible(svg);
                visible($(this).children(":first"));
            })
        })

        option.click(function () {
            saveOption()
        })

        //https://stackoverflow.com/questions/14919459/using-jquery-to-listen-to-keydown-event
        //https://www.toptal.com/developers/keycode
        $(document).on('keydown', function(e) {
            if (e.key === 'e' || e.key === 'E') {
                saveOption();
            }
        });


        function saveOption()
        {
            hide(options)
            $(window).off('wheel')
            let value = $('svg:not(.invisible)').attr('value')
            $('.dropdownTitle').text(value);
            $('#destination').val(value)
        }

        initialize()


    })
</script>

--}}
