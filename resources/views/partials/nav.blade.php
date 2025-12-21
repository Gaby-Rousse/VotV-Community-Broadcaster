{{--
Quel bourbier...
Les burger menu, c'est pas autant facile qu'avec bootstrap
En plus javascript n'est pas inclut.

Le code provient d'ici:

https://tailwindcss.com/plus/ui-blocks/application-ui/navigation/navbars

Pour être sincère, il y a certains trucs beaucoup trop compliqué pour rien.
Les svg sont construit directement. C'est juste fou. Une image ça aurait été ben en masse.
C'était toute du relatif pis du absolute, like si t'a compris.
J'ai essayé de mon mieux de convertir en flex.

--}}


<nav>
    <div>
        <div class="flex flex-row h-16 ">
            <div class="ml-auto flex items-center sm:hidden">
                <!-- Mobile menu button-->
                <button type="button"
                        class=" mr-4 relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:ring-2 focus:ring-white focus:outline-hidden focus:ring-inset"
                        aria-controls="mobile-menu" aria-expanded="false">
                    <!--
                      Icon when menu is closed.

                      Menu open: "hidden", Menu closed: "block"
                    -->
                    <svg class="block size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                         aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                    </svg>
                    <!--
                      Icon when menu is open.

                      Menu open: "block", Menu closed: "hidden"
                    -->
                    <svg class="hidden size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                         aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="hidden sm:flex flex-row gap-4 mr-4 ml-auto">
                <a class="mt-auto mb-auto p-2 w-28" href="/">Home</a>
                <a class="mt-auto mb-auto p-2 w-28" href="https://radio.votvbroadcast.com/votv.mp3">Radio</a>
                <a class="mt-auto mb-auto p-2 w-28" href="https://tv.votvbroadcast.com/votv.mp4">TV</a>
                <a class="mt-auto mb-auto p-2  w-28" href="/upload">Upload</a>
                @auth
                    <a class="mt-auto mb-auto p-2  w-28" href="/account">Account</a>
                @else
                    <a class="mt-auto mb-auto p-2  w-28" href="/signin">Login</a>
                @endauth

            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="sm:hidden" id="mobile-menu">
        <div class="hidden flex-col gap-2 " id="contentToShow">
            <a class="ml-auto mr-auto p-2 w-36" href="/">Home</a>
            <a class="ml-auto mr-auto p-2 w-36" href="https://radio.votvbroadcast.com/votv.mp3">Radio</a>
            <a class="ml-auto mr-auto p-2 w-36" href="https://tv.votvbroadcast.com/votv.mp4">TV</a>
            <a class="ml-auto mr-auto p-2 w-36" href="/upload">Upload</a>
            @auth
                <a class="ml-auto mr-auto p-2 w-36" href="/account">Account</a>
            @else
                <a class="ml-auto mr-auto p-2 w-36" href="/signin">Login</a>
            @endauth

        </div>
    </div>
</nav>

<script>
    $(() => {

        const button = $('button');
        const icones = $('svg');
        //en jquery, si tu fait juste "icones[0]" tu obtient le dom. Les fonction JQUERY ne seront plus disponible.
        const burger = $(icones[0]);
        const x = $(icones[1]);
        const menu = $('#contentToShow');

        button.click(function () {

            if (burger.css('display') === 'block') {
                burger.css({'display': 'none',});
                x.css({'display': 'block',});
                menu.addClass('rightToLeft')
                menu.removeClass('leftToRight')
            } else {
                burger.css({'display': 'block',});
                x.css({'display': 'none',});
                menu.addClass('leftToRight')
                menu.removeClass('rightToLeft')
            }
        });


    });
</script>
