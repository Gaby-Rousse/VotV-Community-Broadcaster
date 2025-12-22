<!--- Écran d'upload --->
<form action="/uploadMedia" class="flex-1/2 border-3 border-solid m-0.5 flex flex-col toggleableScreen" method="post"
      enctype="multipart/form-data">
    @csrf
    <!--- Header --->
    <div class="h-12 w-full flex flex-row" style="border-bottom: solid white 1px">
        <img class="w-12" src="{{asset('images/upload.gif')}}" alt="">
        <div class="flex flex-col ml-1">
            <div class="dos">File Uploader</div>
        </div>

        <button type="button" id="openImportButton"
                class="computerButton border-2 border-solid p-0.5 m-1 ml-auto pl-4 pr-4 hover:cursor-pointer">
            Import from url
        </button>

    </div>

    <label for="fileupload" class="dos ml-1 hover:cursor-pointer ">Please upload your file(s) [Click or Drag&Drop
        here]</label>
    <input multiple
           accept="{{session('media_type')  == 'audios' || session('media_type') == null ? 'audio/*' : 'video/*' }}"
           id="fileupload" type="file" class="opacity-0 flex-grow hover:cursor-pointer" name="mediaFile[]">


    @if(session('message'))
        <label for="fileupload" class="dos ml-1 trash">{{session('message')}}</label>
    @endif
    <div id="mediaSettings" class="ml-1 flex flex-row gap-1">
        <label for="forceTranscode" class="dos hover:cursor-pointer">Compatibility mode</label>
        <input id="forceTranscode" name="forceTranscode" class="hover:cursor-pointer" type="checkbox">
    </div>

    <!--- Footer --->

    <div id="uploaderFooter" class="h-12 w-full flex flex-row mt-auto" style="border-top: solid white 1px">
        @include('partials.whereButtons')
    </div>

    <div class=" w-full min-h-12 flex flex-row" style="border-top: solid white 1px">


        <div class="flex flex-row flex-grow ml-1">
            <div class="flex flex-col">
                <div class="dos">Pending file:</div>
                <div class="flex flex-row flex-wrap max-w-[100%] gap-1" id="fileList"></div>
            </div>
            <div class="flex flex-row ml-auto">
                <button type="button" id="resetButton"
                        class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer">
                    Reset
                </button>
                <button type="button" id="uploadButton"
                        class="computerButton border-2 border-solid p-0.5 m-1 pl-4 pr-4 hover:cursor-pointer opacity-30"
                        disabled>
                    Upload!
                </button>
            </div>


        </div>
    </div>

</form>

<script>

</script>
