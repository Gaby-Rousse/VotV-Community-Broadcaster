@include('partials/header')
@include('partials/nav')
<div class="flex flex-col gap-2">

    <h1 class="ml-auto mr-auto title text-2xl sm:text-4xl md:text-5xl lg:text-6xl">Help</h1>
    <fieldset
            class="mx-auto flex flex-col gap-2 flex-wrap border-3 py-6 pt-4 px-5 border-[#04a96c] w-full max-w-140 bg-black/80 ">
        <legend>Subjects</legend>
        <a class="p-2 w-40 h-12 home" href="/help#how-to">How to</a>
        <a class="p-2 w-60 h-12 home" href="/help#online-txt-generator">Online.txt generator</a>
        <a class="p-2 w-60 h-12 home" href="/help#common-issues">Common issues</a>
    </fieldset>
    <div id="how-to" class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle mx-auto">How to</div>
    <div class="mx-auto mb-10 w-3/4 gap-1 flex flex-col">
        <div class="subtitle text-center text-xl">
            Paths
        </div>
        <div class="flex flex-col gap-3">
            <div><span class="subtitle">Windows (Radio):</span>%LOCALAPPDATA%\VotV\Assets\radio</div>
            <div><span class="subtitle">Windows (TV):</span>%LOCALAPPDATA%\VotV\Assets\tv</div>
            <div><span class="subtitle">Linux_steam (Radio):</span>/home/{user}/.steam/steam/steamapps/compatdata/{id_given_for_votv}/pfx/drive_c/users/steamuser/AppData/Local/VotV/Assets/radio
            </div>
            <div><span class="subtitle">Linux_steam (TV):</span>/home/{user}/.steam/steam/steamapps/compatdata/{id_given_for_votv}/pfx/drive_c/users/steamuser/AppData/Local/VotV/Assets/tv
            </div>

        </div>
        <div class="subtitle text-center my-2 text-xl">How to add the streams to your online.txt</div>
        <div>Every <span class="subtitle">odd</span> line is an "label", the name that you want to
            give
            to the stream in-game
        </div>
        <div>Every <span class="subtitle">even</span> line is the url toward the stream</div>
        <img class="mx-auto my-2 lg:w-3/4" src="/images/ex.png">
        <div>Once done don't forget to <span class="subtitle">SAVE!</span></div>

        <div id="online-txt-generator" class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle mx-auto">Online.txt
            generator
        </div>
        <window class="m-auto w-full flex flex-col">
            <div class="flex flex-row">
                <button id="showRadio" class="pl-3 pr-3 pt-1 pb-1 active flex-grow">Radio</button>
                <button id="showTV" class="pl-3 pr-3 pt-1 pb-1 flex-grow">TV</button>
            </div>
            <div class="w-full flex header h-8">
                <div id="title" class="m-auto">Radio</div>
            </div>
            <div id="radio-container" class="flex flex-col"></div>
            <div id="tv-container" class="hidden flex-col"></div>
            <div class="mt-auto flex flex-row">
                <button id="generate" class="ml-auto pl-8 pr-8 pt-1 pb-1">Generate!</button>
            </div>
        </window>
        <div id="common-issues" class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle mt-2 mx-auto">Common issues
        </div>
        <div class="subtitle my-2 text-center text-xl">
            List is empty, I don't see the files I added to online.txt
        </div>
        <div>First, make sure you saved your file by doing CTRL+S or File -> Save</div>
        <div>Once done, try refreshing assets directly in the in-game settings</div>
        <div class="subtitle my-2 text-center text-xl">
            Status stuck to none
        </div>
        <div>Make sure the TV is plugged in</div>
        <div class="subtitle my-2 text-center text-xl">
            Status stuck to failed
        </div>
        <div>VotV Community Broadcaster might be down or blocked where you live.</div>
        <div>Try accessing the streams directly in your browser, try those ones:</div>
        <br>
        <a class="!text-left subtitle url" href="https://radio.votvbroadcast.com/votv.mp3">VotV Community Radio</a>
        <a class="!text-left subtitle url" href="https://tv.votvbroadcast.com/votv.mp4">VotV Community TV</a>
        <div class="subtitle my-2 text-center text-xl">
            Linux shenanigans
        </div>
        <div>Make sure you have this launch option first:</div>
        <div class="subtitle">WINE_DO_NOT_CREATE_DXGI_DEVICE_MANAGER=1 %command%</div>
        <div>This makes the radio works flawlessly. As for the TV you will hear the audio but see nothing.</div>
        <div>To make the TV work on Linux please use this <a class="subtitle url"
                                                             href="https://github.com/Foxaryse/ArchiveCommunityBranch/tree/main/linux/scripts/onlinevideoworkaround">workaround</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const radioContainer = $('#radio-container');
        const tvContainer = $('#tv-container');
        const title = $("#title");

        const radio = {
            "VotV Community Broadcast": "https://radio.votvbroadcast.com/votv.mp3",
            "VCB SFW": "https://radio.votvbroadcast.com/votv_sfw.mp3",
            "VCB Country": "https://radio.votvbroadcast.com/votv_country.mp3",
            "VCB Classical": "https://radio.votvbroadcast.com/votv_classical.mp3",
            "VCB Electronic": "https://radio.votvbroadcast.com/votv_electronic.mp3",
            "VCB Hip Hop": "https://radio.votvbroadcast.com/votv_hiphop.mp3",
            "VCB Instrumental": "https://radio.votvbroadcast.com/votv_instrumental.mp3",
            "VCB Jazz": "https://radio.votvbroadcast.com/votv_jazz.mp3",
            "VCB Mariachi": "https://radio.votvbroadcast.com/votv_mariachi.mp3",
            "VCB Metal": "https://radio.votvbroadcast.com/votv_metal.mp3",
            "VCB Pop": "https://radio.votvbroadcast.com/votv_pop.mp3",
            "VCB Rock": "https://radio.votvbroadcast.com/votv_rock.mp3",
            "VCB Video Game": "https://radio.votvbroadcast.com/votv_video_game.mp3",
            "VCB Weird": "https://radio.votvbroadcast.com/votv_weird.mp3"
        };
        const tv = {
            "VotV Community Broadcast": "https://tv.votvbroadcast.com/votv.mp4",
            "VCB SFW": "https://tv.votvbroadcast.com/votv_sfw.mp4",
            "VCB Animations": "https://tv.votvbroadcast.com/votv_animations.mp4",
            "VCB Documentaries": "https://tv.votvbroadcast.com/votv_documentaries.mp4",
            "VCB Horror": "https://tv.votvbroadcast.com/votv_horror.mp4",
            "VCB Let's plays": "https://tv.votvbroadcast.com/votv_letsplays.mp4",
            "VCB Memes": "https://tv.votvbroadcast.com/votv_memes.mp4",
            "VCB Shows": "https://tv.votvbroadcast.com/votv_shows.mp4",
            "VCB Vlogs": "https://tv.votvbroadcast.com/votv_vlogs.mp4",
            "VCB News": "https://tv.votvbroadcast.com/votv_news.mp4",
            "VCB < 5min": "https://tv.votvbroadcast.com/votv_lt5min.mp4",
            "VCB CHAOTIC": "https://tv.votvbroadcast.com/votv_lt30sec.mp4"
        };

        Object.entries(radio).forEach(([name, url]) => {
            const div = document.createElement('div');
            div.className = "settingItem h-8 flex flex-row";
            div.innerHTML = `
                <div class="mt-auto mb-auto ml-2">${name}</div>
                <label class="ml-auto checkContainer">
                    <input name="${name}" class="radio-station" type="checkbox" value="${url}">
                    <span class="checkmark"></span>
                </label>`;
            radioContainer[0].appendChild(div);
        });

        let radioToggle = document.createElement('div');
        radioToggle.className = "settingItem h-8 flex flex-row";
        radioToggle.innerHTML = `
            <div class="mt-auto mb-auto ml-2">Toggle All</div>
            <label class="ml-auto checkContainer">
                <input  class="toggleAllRadios" type="checkbox">
                <span class="checkmark"></span>
            </label>`;
        radioContainer[0].appendChild(radioToggle);

        $('.toggleAllRadios').on("change", function () {
            $('.radio-station').prop('checked', this.checked);
        });

        Object.entries(tv).forEach(([name, url]) => {
            const div = document.createElement('div');
            div.className = "settingItem h-8 flex flex-row";
            div.innerHTML = `
                <div class="mt-auto mb-auto ml-2">${name}</div>
                <label class="ml-auto checkContainer">
                    <input name="${name}" class="tv-station" type="checkbox" value="${url}">
                    <span class="checkmark"></span>
                </label>`;
            tvContainer[0].appendChild(div);
        });

        let tvToggle = document.createElement('div');
        tvToggle.className = "settingItem h-8 flex flex-row";
        tvToggle.innerHTML = `
            <div class="mt-auto mb-auto ml-2">Toggle All</div>
            <label class="ml-auto checkContainer">
                <input class="toggleAllTVs" type="checkbox">
                <span class="checkmark"></span>
            </label>`;
        tvContainer[0].appendChild(tvToggle);

        $('.toggleAllTVs').on("change", function () {
            $('.tv-station').prop('checked', this.checked);
        });

        function show(selector, cssClass = 'flex') {
            selector.removeClass('hidden').addClass(cssClass);
        }

        function hide(selector, cssClass = 'flex') {
            selector.addClass('hidden').removeClass(cssClass);
        }

        const showTV = $("#showTV");
        const showRadio = $("#showRadio");

        showTV.on("click", () => {
            show(tvContainer);
            hide(radioContainer);
            showTV.addClass('active');
            showRadio.removeClass('active');
            title.html("TV")
            resetCheckboxes()
        });

        showRadio.on("click", () => {
            hide(tvContainer);
            show(radioContainer);
            showRadio.addClass('active');
            showTV.removeClass('active');
            title.html("Radio")
            resetCheckboxes()
        });

        function resetCheckboxes() {
            $(':checkbox').each(function () {
                this.checked = false;
            });
        }

        $("#generate").on("click", () => {
            let content = "";
            $(':checkbox:checked').each(function () {
                content += $(this).attr("name");
                content += "\n"
                content += $(this).val();
                content += "\n"
            });
            const link = document.createElement("a");
            const file = new Blob([content], {type: 'text/plain'});
            link.href = URL.createObjectURL(file);
            link.download = "online.txt";
            link.click();
            URL.revokeObjectURL(link.href);
        })
    });
</script>

@include('partials.footer')