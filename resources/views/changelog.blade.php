@include('partials/header')
@include('partials/nav')

<h1 class="ml-auto mr-auto title text-2xl sm:text-4xl md:text-5xl lg:text-6xl">Changelog</h1>
<div class="sm:mt-20 mt-10 mb-10 ml-auto sm:ml-20 mr-auto w-[70%]">
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">Planned</div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>(Do not mind the order of this list)</li>
            <li>Allow multiple query when searching for uploaded files</li>
            <li>Clarify what users are allowed to submit (approvals are currently handled according to EternityDev Games
                <a class="url hover:!text-blue-500 !text-blue-400" href="https://discord.com/invite/eternitydevgames"
                   target="_blank">discord</a> rules)
            </li>
            <li>Fix display for tablet users</li>
            <li>Allow users to create a custom broadcast/playlist (?!)</li>
            <li>Segmented programmes</li>
            <li>Avoiding Repetitions (p84 of cookbook)(not soon)</li>
            <li>Segmented uploader (solve 100mb limit)</li>
            <li>Voting system</li>
            <li>Rework suggestions and bugs forms</li>
            <li>Status page for streams</li>
            <li>Metadata for video using custom  <a class="url hover:!text-blue-500 !text-blue-400" href="https://www.liquidsoap.info/doc-dev/icy_metadata.html"
                                                    target="_blank">icy_metadata</a></li>
            <li>Batch editing</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.5 (10/25/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
           <li>Batch uploading</li>
           <li>Batch downloading</li>
           <li>Fixed a bug related to favorites</li>
           <li>Files that are converted now have their metadata automatically parsed</li>
           <li>Introduced compatibility mode, might help uploading some files that doesn't work</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4.5 (09/21/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Replaced PHP-Audio with GetID3 to parse and write metadata in files</li>
            <li>Now writes metadata in UTF-8 (for real this time) </li>
            <li>Replaced yt-dlp with a self hosted instance of Cobalt</li>
            <li>Importing from an url is now faster!</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4.4 (09/20/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Various bug fixes</li>
            <li>Added PHPDoc and JSDoc everywhere making the code more readable (1000+ lines of comments have been added)</li>
            <li>Fixed a bug with the channel monitoring feature</li>
            <li>Improved greatly the video media player (users can now adjust their playback volume + see the metadata of the video</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4.3 (09/07/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Various bug fixes (mainly for non-connected user)</li>
            <li>Users can now change which channels they are monitoring</li>
            <li>I now receive a notification whenever a bug or suggestion is added</li>
            <li>Admins receive a notification whenever a report is made</li>
            <li>Fixed dropdown display issues</li>
            <li>If writing to a file you're editing fails, the database will update nonetheless</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4.2 (09/01/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Fixed small display issues</li>
            <li>Attempt at managing corrupted media (if a media can't be probed, it will be deleted.)</li>
            <li>Allow users to filter by channel/frequency</li>
            <li>Fixed event frequency on video streams</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4.1 (08/30/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Added mail sfx when receiving a new notification</li>
            <li>Reporting feature added</li>
            <li>"Currently broadcasting" removed from tv section</li>
            <li>Fixed various display issues</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.4 (08/21/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>It is now possible to move already uploaded files</li>
            <li>Optimized javascript (reduce drastically the network usage of the upload page)</li>
            <li>Video streams are back online</li>
            <li>Clicks on the dropdown now works, the bug has been fixed</li>
            <li>Reworked event frequency system (Instead of playing an event every 100 songs, events has a chance to
                occur according to a percentage.) *Note: Only available for audios currently
            </li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.3.1 (08/17/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Browser remember the last volume input you used for playback</li>
            <li>Pop-up volume is identical to the playback volume</li>
            <li>Clicking the "currently broadcasting" opens the audio currently playing. radio.votvbroadcast.com still
                redirects you to the broadcast.
            </li>
            <li>Explicit tag has been added</li>
            <li>Event frequency has been added</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.3 (08/09/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>New settings tab (doesn't redirect to account anymore but rather to a new tab that allows user to
                customize their experience)
            </li>
            <li>New alerting system, similar to how VotV makes a popup in game</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.2 (08/08/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Added various TV Broadcasts channels (<a class="url hover:!text-blue-500 !text-blue-400"
                                                         href="https://votvbroadcast.com/txt/tv.txt">Available
                    Channels</a>)
            </li>
            <li>New dropdown menu</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.1 (08/05/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Files are now converted on the fly if they aren't mp3 or mp4. Therefore the server now supports more
                file types.
            </li>
            <li>Covers pictures are now lazy loading (very helpful on slow networks when pictures are not cached)</li>
            <li>Added a share button</li>
            <li>Added favorites, user can add music to their favorite and filter only their favorites</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.3.0 (07/30/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Thanks to the incredible help provided by Questwalker, the TV stream is online</li>
            <li>Added buttons on the main page that shows all available streams</li>
            <li>Instead of cancelling when receiving problematic files, the server now corrects the name</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.8 (07/28/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Ditched Cloudflared in favor of NGINX. Users can expect a slightly more stable broadcast.</li>
            <li>Seperated radio stations by genres, users can listen to a filtered radio station that broadcast one
                precise genre
            </li>
            <li>These various streams are listed <a class="hover:!text-blue-500 !text-blue-400 hover:cursor-pointer"
                                                    href="https://radio.votvbroadcast.com/">here</a></li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.7 (07/26/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Errors are alerted to the user whenever they happen and are more clear than before</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.6 (07/19/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>When importing, users can now decide of the destination of the file</li>
            <li>Added suggestion form</li>
            <li>Added bug form</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.5 (07/18/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Added remember me feature, stay connected for 7 days by checking the 'remember me' button when signing
                in
            </li>
            <li>Delete all + Read all notifications buttons added to the notifications tab</li>
            <li>Metadata encoding fixed, shoutout to questwalker for their precious help on the matter!</li>
            <li>Notification display fixed</li>
            <li>Media player display fixed</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.4.3 (07/17/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Added a filter that show only your uploads</li>
            <li>Click on "Currently broadcasting" redirects to the radio station</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.4.2 (07/17/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Prevent users from uploading problematic files. (File that start with '#' or contains quotation mark.)
            </li>
            <li>Description of media now also appear when playing a file.</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.4.1 (07/15/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>User sessions are not lost in the void anymore</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.4 (07/15/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Users can now upload events, advertisements and segues</li>
            <li>Users can also listen to uploaded events, advertisements or segues</li>
            <li>It's now possible to add a description to an audio file</li>
            <li>Added a link toward the home page when inside the upload page (home button)</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.3 (07/12/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Users can download media from another website (such as youtube) with the use of YT-DLP.</li>
            <li>Note: Adding media using yt-dlp can take up to a full minute. Will try to optimize this.</li>
            <li>Users can sort the available tracks using various filters</li>
        </ul>
    </div>
    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.2 (07/11/2025)
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>A notification tab has been added</li>
            <li>Deleting your account requires you to confirm</li>
            <li>Users can change their username</li>
            <li>Added audio normalization for broadcast audios + Fading transitions between audios</li>
            <li>Attempt to fix stuttering</li>
        </ul>
    </div>

    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2.1</div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Added proper credits</li>
            <li>Added segues to the radio station</li>
        </ul>
    </div>

    <div class=" text-xl sm:text-2xl md:text-3xl lg:text-4xl subtitle sm:text-left text-center">v0.2 Public Release
    </div>
    <div class="flex flex-col gap-2 mt-2 mb-6 items-center sm:items-baseline ">
        <ul>
            <li>Basic Account Feature</li>
            <li>Media must be approved before being broadcasted publicly</li>
            <li>No more refresh between actions inside the upload page</li>
        </ul>
    </div>


</div>

@include('partials/footer')
