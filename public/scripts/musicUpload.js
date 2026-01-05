/**
 * @author: Gabrielle Rousse
 * Code poorly structured that does everything.
 * Initially called musicUpload, it's actually (almost) every javascript related actions here
 */

let MusicsPanel;

let PendingPanel;

let NotificationsPanel;

let DestinationDropdown;

let DestinationBatchDropdown

let FrequencyBatchDropdown

let ChannelDropdown;

let WatchingChannelsDropdown


$(() => {


    const uploadButton = $('#uploadButton')

    //### Global functions

    /**
     * Shows an element.
     * @function
     * @param {selector} selector - The element to update
     * @param {string} cssClass - The class to add
     */
    function show(selector, cssClass = 'flex') {
        selector.removeClass('hidden');
        selector.addClass(cssClass);
    }

    /**
     * Hides an element.
     * @function
     * @param {selector} selector - The element to update
     * @param {string} cssClass - The class to remove
     */
    function hide(selector, cssClass = 'flex') {
        selector.addClass('hidden');
        selector.removeClass(cssClass);
    }

    function visible(selector) {
        selector.removeClass('invisible');
    }

    function invisible(selector) {
        selector.addClass('invisible');
    }


    /**
     * Makes the uploadButton blink and fully visible
     * @function
     */
    function enableButton() {
        uploadButton.removeClass('opacity-30')
        //https://stackoverflow.com/questions/13626517/how-to-remove-disabled-attribute-using-jquery
        uploadButton.prop("disabled", false)
        blink = true;
        blinking(uploadButton)
    }

    /**
     * Stop the uploadButton blinking and reduce its opacity
     * @function
     */
    function disableButton() {
        uploadButton.addClass('opacity-30')
        uploadButton.prop("disabled", true)
        blink = false;
    }

    /**
     * Imitate the way a radio input works.
     * The selected button gets a teal color
     * The other all gets the default color
     * @function
     * @param {selector} disable - The element(s) to restore to their initial style (unselect all)
     * @param {string} target - The element to make 'selected'
     */
    function toggleButton(disable, target) {
        disable.removeClass('selected')
        target.addClass('selected');
    }


    //https://developer.mozilla.org/en-US/docs/Web/API/Window/clearTimeout
    let timeoutID;
    const sfx_click = new Audio('https://votvbroadcast.com/sfx/sfx_click.mp3?v=2')
    const sfx_error = new Audio('https://votvbroadcast.com/sfx/sfx_error.mp3')

    /**
     * Adds a pop-up to the right
     * @function
     * @param {string} type - The icon to show
     * @param {string} message - The message to show
     */
    function popin(type, message) {
        //clearTimeout(timeoutID)
        //$('.popup').remove();
        let icon = type.toLowerCase();
        let html = `<div class="popup gap-2 inline-flex flex-row"><img class="size-8 my-auto" src="https://votvbroadcast.com/images/${icon}.png" > <div class="mt-auto mb-auto dos !text-white">${message}</div></div>`
        if (icon == 'info')
            sfx_click.play();
        else
            sfx_error.play();
        clearTimeout(timeoutID)
        $('#popups').append(html);
        let popup = $('.popup')
        setTimeout(function () {
            popup.addClass('popout');
            timeoutID = setTimeout(function () {
                popup.remove();
            }, 3000);
        }, 3000);
    }


    let toggleableScreen = $('.toggleableScreen')

    /**
     * Open a '.toggleableScreen' according to the target
     * @function
     * @param {selector} target - The target to display
     */
    function toggleScreen(target) {

        $('select').prop('selectedIndex', 0);
        //Hide all screens
        hide(toggleableScreen);
        //Show a single screen
        show(target);

        //Remove all enter listener
        //https://stackoverflow.com/questions/209029/best-way-to-remove-an-event-handler-in-jquery
        $(document).off('keypress')

        //Make a new enter listener depending on the window that is opened
        //If you're uploading, pressing enter will upload
        //If you're editing, pressing enter will edit
        //etc...

        if (target.is($("form[action|='/uploadMedia']"))) {
            toggleButton(where, whereMusics)
            typeInput.val('media');
            //https://stackoverflow.com/questions/979662/how-can-i-detect-pressing-enter-on-the-keyboard-using-jquery
            $(document).one('keypress', function (e) {
                if (e.which === 13) {
                    uploadMedia();
                }
            });
        } else if (target.is($("form[action|='/updateMetadata']"))) {
            $(document).on('keypress', function (e) {
                if (e.which === 13) {
                    updateMetadata();
                }
            });
        } else if (target.is($("form[action|='/deleteMedia']"))) {
            $("input[name|='confirm']").val(null);
            $(document).on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    deleteMedia();
                }
            });
        } else if (target.is($("form[action|='/importMedia']"))) {
            $(document).on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    importMedia();
                }
            });
        } else if (target.is($("form[action|='/updateBatch']"))) {
            $(document).on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    submitBatchEditForm()
                }
            });
        } else if (target.is($("form[action|='/deleteBatch']"))) {
            $(document).on('keypress', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    submitBatchDeleteForm()
                }
            });
        }
    }

    let keywords = '';

    /**
     * Checks if your session contain data that should be loaded.
     * See below for the usage
     * Mainly used for shared files
     * @function
     */
    function ajaxGetSessionValues() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/getSession', //Appel
                type: 'GET', //En utilisant GET
                dataType: 'json', //Interprète les données en json
                success: function (response) {
                    console.log(response)
                    keywords = response;
                    return resolve(true);
                }, error: function (xhr) {
                    popin('Warning', JSON.parse(xhr.responseText).message)
                }
            });
        });
    }

    ajaxGetSessionValues().then(() => {
            if (keywords) {
                if (keywords.startsWith('filename:')) {
                    ajaxRequestPlayMedia(keywords.substring('filename:'.length))
                    toggleScreen($('#playScreen'));
                }
            }
        }
    )

    //### Buttons
    let favorite = $('.favorite')
    let report = $('.report')


    /**
     * When clicking on the favorite icon, add or remove the file from your favorite
     * @event
     */
    favorite.on("click", function () {
        MusicsPanel.command('/toggleFavorite?filename=' + encodeURIComponent(lastFilename));
        if ($(this).attr('src') === 'https://votvbroadcast.com/images/empty_heart.png') {

            $(this).attr('src', 'https://votvbroadcast.com/images/filled_heart.png')
        } else {

            $(this).attr('src', 'https://votvbroadcast.com/images/empty_heart.png')
        }
    })

    /**
     * Paste in your clipboard a url that loads the file in the player when opened.
     * @event
     */
    $('.share').click(function () {

        //https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/encodeURI
        navigator.clipboard.writeText(encodeURI('https://votvbroadcast.com/' + route + '?keywords=filename:') + encodeURIComponent(lastFilename));
        popin('Info', `URL copied into clipboard!`);

    });

    /**
     * Each cross close the window, which opens the upload window
     * @event
     */
    $('.cross').click(() => {
        toggleScreen($("form[action|='/uploadMedia']"))
    })

    /**
     * Submit the update form
     * @event
     */
    $('#updateButton').click(() => {
        updateMetadata();
    })


    /**
     * Submit the upload form
     * @event
     */
    uploadButton.click(() => {
        uploadMedia();
    })


    MusicsPanel = new AutoRefreshedPanel("MusicsPanel", "/getMedias", 5, attachEvent)

    PendingPanel = new AutoRefreshedPanel("PendingPanel", "/getPendingMedias", 5, attachEvent)

    NotificationsPanel = new AutoRefreshedPanel("NotificationsPanel", "/getNotifications", 5, attachNotificationEvent)

    /**
     * eventListeners from a refresh panel must be added this way, otherwise the listener dies
     * These are events for the Notification autoRefreshPanel
     * @function
     */
    function attachNotificationEvent() {
        /**
         * Clicking on a notification toggle it
         * @event
         */
        $(".notification").click(function () {
            let id = $(this).attr("idnotif")
            NotificationsPanel.command('/notificationAction?id=' + id);
        });
    }

    let selectedFiles = [];
    let selectedCount = $('.selectedCount');
    let batchMenu = $('#batchMenu');

    /**
     * eventListeners from a refresh panel must be added this way, otherwise the listener dies
     * These are events for both the approved files and the pending files
     * @function
     */
    function attachEvent() {

        /**
         * Open the file in the editing tab
         * @event
         */
        let editAction = $(".editAction");
        editAction.off('click');
        editAction.on("click", function () {
            let filename = $(this).attr("filename")
            ajaxRequestMedia(filename);
        });

        /**
         * Open the file in the deleting tab
         * @event
         */
        let deleteAction = $(".deleteAction");
        deleteAction.off('click');
        deleteAction.on("click", function () {
            let filename = $(this).attr("filename")
            toggleScreen($("form[action|='/deleteMedia']"))
            $("#hiddenFileToDelete").val(filename);
            $("#confirmationLabel").text('Confirm that you want to delete: ' + filename + ' (y/n)')
            $("#confirm").focus();
        });

        /**
         * Open the file in the media player
         * @event
         */
        let playAction = $(".playAction")
        playAction.off('click');
        playAction.on("click", function () {
            let filename = $(this).attr("filename")
            toggleScreen($('#playScreen'))
            //Requête ajax seulement si la musique n'est pas déjà chargé
            if (audio.attr('src') !== '/uploads/musics/' + filename && audio.attr('src') !== '/temp_uploads/musics/' + filename) {
                ajaxRequestPlayMedia(filename);
            }
        });

        /**
         * Approve a file
         * @event
         */
        let approveAction = $(".approveAction")
        approveAction.off('click');
        approveAction.on("click", function () {
            let filename = $(this).attr("filename")
            toggleScreen($("#loadingScreen"))
            approveMedia(filename).then(() => {
                MusicsPanel.refresh(true);
                PendingPanel.refresh(true);
                toggleScreen($('#playScreen'));
                popin('info', `${filename} approved!`)
            });
        });

        /**
         * Mark a file as selected
         * @event
         */
        let selectAction = $('.selectAction')
        selectAction.off('click');
        selectAction.on('click', function () {
            let prev = $(this).prev();
            let filename = $(this).attr("filename")
            if (prev.hasClass('hidden')) {
                selectedFiles.push(filename);
                console.log(selectedFiles)
                prev.removeClass('hidden');
            } else {
                //https://stackoverflow.com/questions/5767325/how-can-i-remove-a-specific-item-from-an-array-in-javascript
                let index = selectedFiles.indexOf(filename)
                if (index > -1) { // only splice array when item is found
                    selectedFiles.splice(index, 1); // 2nd parameter means remove one item only
                }
                console.log(selectedFiles)
                prev.addClass('hidden');
            }
            selectedCount.html(selectedFiles.length)

            if (selectedFiles.length === 1) {
                show(batchMenu)
            } else if (selectedFiles.length < 1) {
                closeBatchMenu()
            }

        });

        remarkAsSelected()
    }

    //*** BATCH RELATED SECTION ***//
    function remarkAsSelected() {
        selectedFiles.forEach((file, index) => {
            $(`img[filename="${file}"]`).prev().removeClass('hidden');
        })
    }

    function unmarkEverything() {
        selectedFiles = [];
        $(`.cover`).prev().addClass('hidden');
    }

    function closeBatchMenu() {
        toggleScreen($('#playScreen'))
        hide(batchMenu)
        unmarkEverything()
    }

    $('.closeSelected').on('click', () => {
        closeBatchMenu()
    })

    $('.generateList').on('click', () => {
        popin('info', 'Generating online.txt, please wait...')
        let data = new FormData();
        data.append("filenames", selectedFiles)

        ajaxGenerateAndDownloadOnlineTXT(data).then(() => {
            popin('info', 'online.txt generated!')
        })
    })

    $('.downloadFiles').on('click', () => {
        popin('info', 'Generating a .zip file, please wait...')
        let data = new FormData();
        data.append("filenames", selectedFiles)

        ajaxDownloadAllSelectedFiles(data).then(() => {
            popin('info', '.zip file generated!')
        })
    })

    let batchDeleteForm = $("form[action|='/deleteBatch']")
    $('.deleteFiles').on('click', () => {
        batchDeleteForm[0].reset();
        toggleScreen(batchDeleteForm);
    })

    //edit files form
    let batchEditForm = $("form[action|='/updateBatch']")
    $('.editFiles').on('click', () => {
        batchEditForm[0].reset();
        toggleScreen(batchEditForm);
        $('#b_currentCover').attr('src', '');
        $('#destinationBatchDropdown' + ' .dropdownTitle').text('Unchanged');
        $('#frequencyBatchDropdown' + ' .dropdownTitle').text('Unchanged');

        $('#batchDestination').val('Unchanged')
        $('#batchFrequency').val('Unchanged')
    })


    function ajaxGenerateAndDownloadOnlineTXT(data) {
        console.log(data)
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/generateOnlineTXT',
                method: 'POST',
                processData: false,
                contentType: false,
                data: data,
                success: (response) => {
                    let file = new File([response], "online.txt", {type: "text/plain;charset=utf-8"});
                    saveAs(file);
                    resolve(true)
                },
                error: () => {
                    resolve(true);
                }
            });
        })
    }

    function ajaxDownloadAllSelectedFiles(data) {
        console.log(data)
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/downloadAllSelectedFiles',
                method: 'POST',
                processData: false,
                contentType: false,
                data: data,
                xhrFields: {
                    responseType: 'blob'
                },
                success: (response) => {
                    let file = new File([response], "files.zip", {type: 'application/zip'});
                    saveAs(file);
                    resolve(true)
                },
                error: () => {
                    resolve(true);
                }
            });
        })
    }


    //### Overlay (this is the pop-up that appears when not connected)
    let overlay = $('#overlay')
    if (overlay) {
        /**
         * Closes the pop-up
         * @event
         */
        overlay.click((e) => {
            //Do not close the popup if pressing a URL (<a>)
            if (e.target.tagName !== 'A') {
                hide(overlay);
            }
        })
    }

    //### Identify if the user is in audio or video mode
    let media_input = $("input[name|='media_type']");
    let media_type = media_input.val();

    let route
    if (media_type === 'videos') {
        route = 'uploadVideo';
    } else {
        route = 'uploadAudio';
    }


    //### Get values

    //Is the file in temp_uploads?
    let isFilePending

    /**
     * Tells if a file is pending
     * @function
     * @param {string} filename - The filename to check
     */
    function ajaxIsPending(filename) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/isPending',
                type: 'GET',
                data: {filename: filename},
                dataType: 'json',
                success: function (response) {
                    isFilePending = response;
                    return resolve(true);

                }, error: function (xhr) {
                    popin('Warning', JSON.parse(xhr.responseText).message)
                }
            });
        })
    }

    //Does the user owns the file?
    let isOwner

    /**
     * Tells if the user owns the file
     * @function
     * @param {string} filename - The filename to check
     */
    function ajaxIsOwner(filename) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/isOwner',
                type: 'GET',
                data: {filename: filename},
                dataType: 'json',
                success: function (response) {
                    isOwner = response;
                    return resolve(true);

                }, error: function (xhr) {
                    popin('Warning', JSON.parse(xhr.responseText).message)
                }
            });
        })
    }


    //### Uploading section
    const fileInput = $('#fileupload');
    const uploaderFooter = $('#uploaderFooter');
    const resetButton = $('#resetButton')
    const jqueryFilename = $('#filename');
    let fileInputList = $('#fileList')

    /**
     * Empty the file input.
     * @event
     */
    resetButton.click(() => {
        fileInput.val(null);
        jqueryFilename.text('None');
        fileInputList.html('')
        hide(fileInputList)
        disableButton();
        log("Reset successfully.")
    })

    function updateFileList(target) {
        let fileList = target.files
        let count = fileList.length
        Array.from(fileList).forEach((file, index) => {
            fileInputList.append(generateFile(file.name, index, count))
        })
        show(fileInputList)
        $('.removeFile').on('click', function () {
            removeFileFromFileList(parseInt($(this).data('id')))
        });
        return count;
    }

    /**
     * Add files in the list of file you're about to upload.
     * @event
     */
    fileInput.on('change', function () {
        fileInputList.html('')
        let count = updateFileList($(this)[0])
        //https://stackoverflow.com/questions/25333488/why-isnt-the-filelist-object-an-array
        if (count > 1) {
            log("Files added successfully.")
        } else {
            log("File added successfully.")
        }

        enableButton();

    })


    /**
     * Removes a file from the list of file about to be uploaded.
     * @function
     * @param {int} index - Position of the file in the list
     * This comes from here: https://stackoverflow.com/questions/3144419/how-do-i-remove-a-file-from-the-filelist
     */

    function removeFileFromFileList(index) {
        const dt = new DataTransfer();
        const {files} = document.getElementById('fileupload')

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (index !== i) {
                dt.items.add(file); // here you exclude the file. thus removing it.
            }
        }

        /* Assigning data transfer object files to the 'input' variable will not write the data transfer files to it because it doesn't have the reference to the element: Instead write, */
        document.getElementById('fileupload').files = dt.files; // Assign the updates list
        fileInputList.html('')
        if (dt.files.length <= 0) {
            hide(fileInputList)
            disableButton()
        } else {
            updateFileList(document.getElementById('fileupload'))
        }
    }

    /**
     * HTMLHelper that creates a "file" to display in the list.
     * @function
     * @param {string} filename - Text to write
     * @param {int} id - Identifier used when removing one file from the list
     * @param {int} maxIndex - Manage width by knowing how many file is there
     */

    function generateFile(filename, id, maxIndex = 1) {
        let width = maxIndex
        if (maxIndex > 10) {
            width = 10;
        }
        return `<file class="flex flex-row max-w-1/${width} gap-0.5">
        <img data-id="${id}" class="removeFile border-1 computerButton mt-auto hover:cursor-pointer mb-auto size-4" src="https://dev.votvbroadcast.com/images/cross.png">
        <div class=" line-clamp-1 dos">${filename}</div></file>`
    }


    let blink = false;

    /**
     * The server removes problematic characters from the file.
     * However, when a user upload a file, the client fetch the editor windows using the filename that you provided earlier
     * Therefore it must be formatted like the server did.
     * @event
     */
    function formatFilename(filename) {
        //J'HAIT REGEX JE COMPRENDS RIEN
        const problematicCharacters = ['"', '#', '/', '?']
        problematicCharacters.forEach((item) => {
            filename = filename.replaceAll(item, '')
        })
        return filename;
    }

    /**
     * Send the file to the server
     * @function
     */
    function uploadMedia() {
        toggleScreen($("#loadingScreen"))
        let formMedia = $("form[action|='/uploadMedia']")
        let formData = new FormData(formMedia[0]);
        let filename = formData.get("mediaFile[]").name;
        console.log(formData);
        //Quand la fameuse promesse est retourné, ouvre le metadata editor
        PendingPanel.postCommand('/uploadMedia', formData).then(() => {
            let fileWithoutExt = filename.split('.')[0];
            if (media_type === 'videos') {
                filename = fileWithoutExt + '.mp4'
            } else {
                filename = fileWithoutExt + '.mp3'
            }


            fileInput.val(null);
            fileInputList.html('')
            $('#filename').text('None');
            disableButton();
            filename = formatFilename(filename);
            console.log(filename);
            ajaxIsOwner(filename).then(() => {
                if (isOwner === true) {
                    ajaxRequestMedia(filename)
                } else {
                    toggleScreen(formMedia)
                    log(isOwner);
                    if (isOwner === 'A file with this name already exists.') {
                        searchToken.val('filename:' + filename)
                        MusicsPanel.command("/setSearchKeywords?keywords=filename:" + encodeURIComponent(filename));
                        log('If you believe this is an error, rename your file.')
                    }
                }


            })


        })
    }


    /**
     * Shows an alert in the upload window
     * @function
     * @param {string} text - The text to show
     */
    function log(text) {
        $('#mediaSettings').before(`<label for="fileupload" class="dos ml-1 trash">${text}</label>`);
        if ($('.trash').length >= 4) {
            $('.trash').first().remove();
        }
    }

    /**
     * Wait an amount of ms.
     * @function
     * @param {number} ms - The amount of ms to wait
     */
    const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

    /**
     * Blink the border of a target
     * @function
     * @param {selector} target - The target to blink
     */
    const blinking = async (target) => {
        while (blink) {
            await delay(500);
            target.addClass('red');
            await delay(500);
            target.removeClass('red');
        }
        target.removeClass('red');
    };

    //### Metadata editing

    let fileType;
    const explicitInput = document.querySelector('#explicit')

    /**
     * Open the metadata editor with the requested file loaded.
     * @function
     * @param {string} filename - The filename to edit
     */
    function ajaxRequestMedia(filename) {
        $.ajax({
            url: '/getMedia', //Appel
            type: 'GET', //En utilisant GET
            data: {filename: filename}, //Envoi ?filename=
            dataType: 'json', //Interprète les données en json
            success: function (response) {
                if (response.error) {
                    toggleScreen($("form[action|='/uploadMedia']"))
                    log("Err.")
                } else {
                    let coverPath = '/uploads/covers/';
                    fileType = response.type;
                    hide(channelDiv);
                    if (response.type === 'media') {
                        toggleButton(where, whereMusics)
                        typeInput.val('media');
                        if (media_type === 'audios') {
                            DestinationDropdown.updateOptions(['None', 'Christmas', 'Classical', 'Country', 'Electronic', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Metal', 'Pop', 'Rock', 'Video Game', 'Weird'])
                        }
                        if (media_type === 'videos') {
                            DestinationDropdown.updateOptions(['None', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Memes', 'News', 'Shows', 'Vlogs'])
                        }
                        channelText.text('Channel:')
                        show(channelDiv);
                    } else if (response.type === 'event') {
                        toggleButton(where, whereEvents)
                        typeInput.val('event');
                        DestinationDropdown.updateOptions(['Strange [4%]', 'Weird [2%]', 'Bizarre [1%]', 'Outlandish [0.4%]', 'Unfathomable [0.2%]', 'Otherworldly [0.1%]', 'Transcendental [0.04%]'])
                        channelText.text('Frequency:')
                        show(channelDiv);
                    } else if (response.type === 'ad') {
                        toggleButton(where, whereAds)
                        typeInput.val('ad');
                    } else if (response.type === 'segue') {
                        toggleButton(where, whereSegues)
                        typeInput.val('segue');
                    }


                    explicitInput.checked = false;


                    $('#title').val(response.title);
                    $('#artist').val(response.artist);
                    $('#genre').val(response.genre);
                    $('#year').val(response.year);
                    $('#description').val(response.description);
                    $('#destination').val(response.destination);
                    if (response.explicit === 1) {
                        explicitInput.checked = true;
                    }
                    $('#destinationDropdown .dropdownTitle').text(response.destination)

                    $('#currentCover').attr('src', coverPath + response.cover);
                    $('#editing_filename').text(filename);
                    $("#hiddenFileName").val(filename);
                    $('#cover').val(null);

                    toggleScreen($("form[action|='/updateMetadata']"))
                }


            }, error: function (xhr) {
                popin('Warning', JSON.parse(xhr.responseText).message)
            }
        });
    }

    /**
     * Update the metadata of a file according to a form
     * @function
     */
    function updateMetadata() {
        //https://developer.mozilla.org/en-US/docs/Web/API/FormData/FormData

        toggleScreen($("#loadingScreen"))

        const formData = new FormData($("form[action|='/updateMetadata']")[0]);
        formData.set('media_type', media_type);
        let filename = formData.get('filename')

        ajaxIsPending(filename).then(() => {
            if (isFilePending) {
                PendingPanel.postCommand('/updateMetadata', formData).then(() => {
                    ajaxRequestMedia(filename);
                });
            } else {
                MusicsPanel.postCommand('/updateMetadata', formData).then(() => {
                    ajaxRequestMedia(filename);
                });
            }

        })


    }


    //### Delete section

    /**
     * Deletes a media
     * @function
     */
    function deleteMedia() {
        toggleScreen($("#loadingScreen"))
        const formData = new FormData($("form[action|='/deleteMedia']")[0]);
        formData.set('media_type', media_type);
        ajaxIsPending(formData.get('filename')).then(() => {
            if (isFilePending) {
                PendingPanel.postCommand('/deleteMedia', formData).then(() => {
                    toggleScreen($("form[action|='/uploadMedia']"))
                    PendingPanel.refresh(true)
                })
            } else {
                MusicsPanel.postCommand('/deleteMedia', formData).then(() => {
                    MusicsPanel.refresh(true)
                    toggleScreen($("form[action|='/uploadMedia']"))
                });
            }

        })
    }

    //### Script approval

    //Mutex

    let approvingFiles = {}

    /**
     * Approve a file
     * @function
     * @param {string} filename - The filename to approve
     */
    function approveMedia(filename) {
        //Is it currently getting approved?
        //This safety measure has been implemented because for some reason it always sends the approval twice.
        if (approvingFiles[filename]) {
            return
        }

        //Locked until the end
        approvingFiles[filename] = true;

        return new Promise((resolve, reject) => {
            $.ajax({
                url: '/approveMedia',
                type: 'GET',
                data: {filename: filename, media_type: media_type},

                success: function () {
                    return resolve(true);
                }, error: function () {
                    return resolve(true);
                },
                complete: function () {
                    //Unlock
                    approvingFiles[filename] = false;
                }
            });
        })
    }


    //### Media Player

    let autoplay = false;
    let audio = $('audio');
    if (media_type === 'videos') {
        audio = $('video');
    } else if (media_type === 'audios') {
        audio = $('audio');
    }

    //Vanilla javascript audio
    const vAudio = audio[0]

    const volume = $('#volume');
    const volumeValue = $('#audioValue');


    let storageVolume = parseFloat(localStorage.getItem("volume"));
    if (storageVolume) {
        volume.val(storageVolume)
        volumeValue.text(storageVolume);
        sfx_click.volume = storageVolume / 100;
    } else {
        volume.val(50);
        sfx_click.volume = 50 / 100;
    }

    /**
     * Resume or stop playback
     * @event
     */

    $("#playPause").click(() => {

        if (vAudio.duration > 0 && !vAudio.paused) {
            vAudio.pause();
            $("#playPause").attr('src', '/images/play.png')

        } else {
            vAudio.play();
            $("#playPause").attr('src', '/images/pause.png')
        }
    })


    let lastFilename;


    /**
     * Opens a file in the media player
     * @function
     * @param {string} filename - The filename of the file to play.
     */
    function ajaxRequestPlayMedia(filename) {
        $.ajax({
            url: '/getMedia',
            type: 'GET',
            data: {filename: filename},
            dataType: 'json',
            success: function (response) {

                $('#report_reason').val(response.reportReason)

                if (favorite) {
                    if (response.isFavorite) {
                        favorite.attr('src', 'https://votvbroadcast.com/images/filled_heart.png')
                    } else {
                        favorite.attr('src', 'https://votvbroadcast.com/images/empty_heart.png')
                    }
                }

                if (favorite) {
                    if (response.isReported) {
                        report.attr('src', 'https://votvbroadcast.com/images/flag.png')
                    } else {
                        report.attr('src', 'https://votvbroadcast.com/images/empty_flag.png')
                    }
                }

                if (lastFilename === filename) {
                    return;
                }

                lastFilename = filename;

                $('#duration').text(convertSeconds(response.seconds));
                $('#seeking').attr('max', response.seconds);
                let isPending = response.isPending;
                let coverPath = '/uploads/covers/';
                $('#ptitle').text(response.title);
                $('#partist').text(response.artist);
                $('#pgenre').text(response.genre);
                $('#pyear').text(response.year);
                $('#pdescription').text(response.description);
                $('title').text(response.artist + ' - ' + response.title);
                if (media_type === 'audios') {


                    let musicPath = '/uploads/audios/medias/';
                    if (isPending) {
                        musicPath = '/temp_uploads/pending/';
                    } else {
                        let type = response.type;
                        if (type === 'event') {
                            musicPath = '/uploads/audios/events/';
                        } else if (type === 'ad') {
                            musicPath = '/uploads/audios/advertisements/';
                        } else if (type === 'segue') {
                            musicPath = '/uploads/audios/segues/';
                        }
                    }
                    audio.attr('src', musicPath + filename);

                    vAudio.volume = volume.val() / 100;
                } else if (media_type === 'videos') {
                    let isPending = response.isPending;
                    coverPath = '/uploads/covers/';
                    let videoPath = '/uploads/videos/medias/';
                    if (isPending) {
                        videoPath = '/temp_uploads/pending/';
                    } else {
                        let type = response.type;
                        if (type === 'event') {
                            videoPath = '/uploads/videos/events/';
                        } else if (type === 'ad') {
                            videoPath = '/uploads/videos/advertisements/';
                        }
                    }

                    $('video').attr('src', videoPath + filename);
                }


                $('.playing_file').text(filename);


                $('.pcurrentCover').attr('src', coverPath + response.cover);
                if (storageAutoplay === "1") {
                    vAudio.play();
                    $("#playPause").attr('src', '/images/pause.png')
                } else {
                    $("#playPause").attr('src', '/images/play.png')
                }


            }, error: function (xhr) {
                popin('Warning', JSON.parse(xhr.responseText).message)
            }
        });
    }


    /**
     * Checks whenever the volume is updated and changes the playback volume
     * @event
     */
    volume.on('input', () => {
        let volumeNumber = volume.val() / 100;
        volumeValue.text(volume.val());
        vAudio.volume = volumeNumber;
        sfx_click.volume = volumeNumber
        localStorage.setItem("volume", volume.val().toString());
    })

    const seeking = $('#seeking')
    const elapsed = $('#elapsed')

    let isSeeking = false;

    /**
     * Shows the playback time
     * @event
     */
    audio.on('timeupdate', () => {
        if (!isSeeking) {
            seeking.val(vAudio.currentTime);
            elapsed.text(convertSeconds(vAudio.currentTime));
        }
    });

    /**
     * Restore the play icon whenever playback finishes
     * @event
     */
    audio.on('ended', () => {
        $("#playPause").attr('src', '/images/play.png')
    });


    /**
     * Tells where your cursor is while seeking
     * @event
     */
    seeking.on('input', () => {
        isSeeking = true;
        elapsed.text(convertSeconds(seeking.val()));
    });

    /**
     * Update the playback to the seeking position
     * @event
     */
    seeking.on('change', () => {
        isSeeking = false;
        vAudio.currentTime = seeking.val();
    });

    /**
     * Converts the seconds from a probed duration into a human-readable format
     * @function
     * @param {number} seconds - The amount of seconds probed
     */
    function convertSeconds(seconds) {
        let formattedHours = String(Math.floor(seconds / 3600)).padStart(2, '0');
        let formattedMinutes = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
        let formattedSeconds = String(Math.floor(seconds % 60)).padStart(2, '0');

        return `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;

    }

    //### Currently Broadcasting
    let everything = 0;
    let sfwChannel = 21
    WatchingChannelsDropdown = new VotvDropdown('watchingChannels', 'monitoringChannel', 'updateMonitoringChannel', 20)
    if (media_type === 'audios') {
        everything = 0;
        sfwChannel = 21;
        WatchingChannelsDropdown.updateOptions(['Everything', 'SFW', 'Christmas', 'Classical', 'Country', 'Electronic', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Metal', 'Pop', 'Rock', 'Video Game', 'Weird'])
    } else if (media_type === 'videos') {
        everything = 1;
        sfwChannel = 22;
        WatchingChannelsDropdown.updateOptions(['Everything', 'SFW', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Lt30secs', 'Lt5mins', 'Memes', 'News', 'Shows', 'Vlogs'])
    }


    //https://www.w3schools.com/jsref/met_win_setinterval.asp

    fetchMetadata();


    //Chaque 5s.
    let monitoringInterval = setInterval(fetchMetadata, 5000);
    //TODO: Find a way to not hardcode these number.
    const channelsNumber = {
        'Everything': everything,
        'Animations': 2,
        'Christmas': 3,
        'Classical': 4,
        'Country': 5,
        'Documentaries': 6,
        'Electronic': 7,
        'Hip Hop': 8,
        'Horror': 9,
        'Instrumental': 10,
        'Jazz': 11,
        "Let's Plays": 12,
        'Lt30secs': 13,
        'Lt5mins': 14,
        'Mariachi': 15,
        'Memes': 16,
        'Metal': 17,
        'News': 18,
        'Pop': 19,
        'Rock': 20,
        'SFW': sfwChannel,
        'Video Game': 23,
        'Vlogs': 24,
        'Weird': 25,
    }

    const monitoringChannel = $('#monitoringChannel')

    /**
     * Monitor another channel
     * @event
     * @listens 'updateMonitoringChannel' is a custom trigger sent to the dropdown. Whenever the dropdown changes this event is triggered.
     */
    monitoringChannel.on('updateMonitoringChannel', function () {
        clearInterval(monitoringInterval)
        fetchMetadata(channelsNumber[monitoringChannel.val()]);
        monitoringInterval = setInterval(() => fetchMetadata(channelsNumber[monitoringChannel.val()]), 5000);
    });

    let currentlyPlayingTitle;

    /**
     * Retrieve the metadata of the currently playing file (artist - title)
     * @function
     * @param {number} source - The source to fetch from.
     */
    function fetchMetadata(source = everything) {
        $.ajax({
            type: 'GET',
            url: 'https://radio.votvbroadcast.com/status-json.xsl',
            dataType: 'json',
            success: function (data) {
                let str = data.icestats.source[source].title;
                $(".metadata").text(str);
                //https://bobbyhadz.com/blog/javascript-get-substring-before-specific-character#get-substring-before-the-last-occurrence-of-a-specific-character
                currentlyPlayingTitle = str;
            },
            error: function () {
                $(".metadata").text("Err")
            }
        });
    }


    /**
     * @deprecated FINALLY YAAAAAAAAAAAAAAAAAAAAY!!!!!!!!!!!!!!
     * Retrieve the filename from using the title
     * @function
     * @param {string} title - The title to which we need to find the original filename
     */
    //TODO: While this currently works, it will fails badly soon enough
    //You should use the artist too.
    function ajaxRequestFilename(title) {
        $.ajax({
            url: '/getFilename', //Appel
            type: 'GET', //En utilisant GET
            data: {title: title}, //Envoi ?filename=
            dataType: 'json', //Interprète les données en json
            success: function (response) {
                ajaxRequestPlayMedia(response)
                toggleScreen($('#playScreen'));
            }, error: function (xhr) {
                popin('Warning', JSON.parse(xhr.responseText).message)
            }
        });
    }

    const broadcasting = $('#broadcasting')

    //https://api.jquery.com/children/
    /**
     * Makes the currently broadcasting title blue when hovering
     * @event
     */
    broadcasting.on("mouseenter", function () {
        $(this).children().addClass('selected');
        $(this).children().children().addClass('selected');
    })

    /**
     * Restore the initial currently broadcasting style when leaving the hover
     * @event
     */
    broadcasting.on("mouseleave", function () {
        $(this).children().removeClass('selected');
        $(this).children().children().removeClass('selected');
    })

    /**
     * Open the media player with the currently broadcasting file
     * @function
     */
    broadcasting.click(function () {
        ajaxRequestPlayMedia(currentlyPlayingTitle)
        toggleScreen($('#playScreen'))
    })


    //### Import

    /**
     * Opens the import screen
     * @event
     */
    $('#openImportButton').click(() => {
        toggleScreen($("form[action|='/importMedia']"))
    })

    /**
     * Begin the importing process
     * @event
     */
    $('#importButton').click(() => {
        importMedia();
    })

    /**
     * Remove the url from the form of the importing screen
     * @event
     */
    $('#resetImportButton').click(() => {
        $('#url').val('');
    })

    /**
     * Import a file from a url
     * @function
     */
    function importMedia() {
        toggleScreen($("#loadingScreen"))
        const formData = new FormData($("form[action|='/importMedia']")[0]);
        PendingPanel.postCommand('import', formData).then(() => {
            $('#url').val('');
            toggleScreen($("form[action|='/importMedia']"))
        })
    }

    //### Settings
//Settings are stored in localStorage. There isn't much yet.

    let storageAutoplay = localStorage.getItem("autoplay");

    if (!storageAutoplay) {
        localStorage.setItem("autoplay", "0");
    }

    let autoplaySetting = $('#autoplaySetting');

    if (storageAutoplay === "1") {
        autoplaySetting.attr('checked', true)
    }

    /**
     * Updates the loading prop
     * @event
     */
    autoplaySetting.click(function () {
        if ($(this).prop('checked')) {
            localStorage.setItem("autoplay", "1");
        } else {
            localStorage.setItem("autoplay", "0");
        }

    })

    let storageLoading = localStorage.getItem("loading");

    if (!storageLoading) {
        localStorage.setItem("loading", "0");
    }

    let loadingSetting = $('#loadingSetting');

    if (storageLoading === "1") {
        loadingSetting.attr('checked', true)
    }

    /**
     * Updates the autoplay prop
     * @event
     */
    loadingSetting.click(function () {
        if ($(this).prop('checked')) {
            localStorage.setItem("loading", "1");
        } else {
            localStorage.setItem("loading", "0");
        }

    })


    let settingScreen = $('#settings')

    /**
     * Opens the setting tab
     * @event
     */
    $('#settingButton').click(function () {
        show(settingScreen);
    });

    /**
     * Close the setting tab and apply the settings
     * @event
     */
    $('#backButton').click(function () {
        hide(settingScreen);
        storageAutoplay = localStorage.getItem("autoplay");
        storageLoading = localStorage.getItem("loading");
    })


    //### Queries
    const searchToken = $('#searchToken');

    var timeout = null;
    /**
     * When done typing in the searchbar send the query
     * @event
     */
    searchToken.keyup(function () {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            MusicsPanel.command("/setSearchKeywords?keywords=" + encodeURIComponent($(this).val()));
            window.history.replaceState(null, "", route + "?keywords=" + encodeURIComponent($(this).val()));
        }, 1000);
    });


    /**
     * Sends 'title:' in the searchbar and focus it.
     * @event
     */
    $('.searchTitle').click(() => {
        searchToken.val('title:')
        searchToken.focus()
    })

    /**
     * Sends 'artist:' in the searchbar and focus it.
     * @event
     */
    $('.searchArtist').click(() => {
        searchToken.val('artist:')
        searchToken.focus()
    })

    /**
     * Sends 'genre:' in the searchbar and focus it.
     * @event
     */
    $('.searchGenre').click(() => {
        searchToken.val('genre:')
        searchToken.focus()
    })

    /**
     * Sends 'year:' in the searchbar and focus it.
     * @event
     */
    $('.searchYear').click(() => {
        searchToken.val('year:')
        searchToken.focus()
    })

    //### Sorting

    /**
     * Updates the sorting
     * @function
     * @param {string} sortBy - The value to sort by
     * @param {int} desc - Is it descending?
     */
    function setSorting(sortBy, desc) {
        let descValue = desc ? 1 : -1
        //desc = 1 -> true
        MusicsPanel.command("/setSorting?sortBy=" + sortBy + "&desc=" + descValue);
    }

    let currentSorting;
    let direction = false;

    const sorting = $('.sorting')

    /**
     * Sort by title the approved medias
     * @event
     */
    //https://stackoverflow.com/questions/12481439/jquery-this-keyword
    $('.sortTitle').click(function () {
        setSorting('title', direction)
        currentSorting = 'title'
        toggleButton(sorting, $(this))
    })

    /**
     * Sort by artist the approved medias
     * @event
     */
    $('.sortArtist').click(function () {
        setSorting('artist', direction)
        currentSorting = 'artist'
        toggleButton(sorting, $(this))
    })

    /**
     * Sort by genre the approved medias
     * @event
     */
    $('.sortGenre').click(function () {
        setSorting('genre', direction)
        currentSorting = 'genre'
        toggleButton(sorting, $(this))
    })

    /**
     * Sort by year the approved medias
     * @event
     */
    $('.sortYear').click(function () {
        setSorting('year', direction)
        currentSorting = 'year'
        toggleButton(sorting, $(this))
    })
    const toggleDirection = $('.toggleDirection')

    /**
     * Update the direction (asc or desc) of the sorting
     * @event
     */
    toggleDirection.click(() => {
        direction = !direction;
        setSorting(currentSorting, direction)
        if (direction) {
            toggleDirection.text('currently Descending...')
        } else {
            toggleDirection.text('currently Ascending...')
        }
    })


    //### Notifications

    const notificationTab = $('#notificationTab')
    const pendingTab = $('#pendingTab')
    const notificationButton = $('#notificationButton');

    /**
     * Hides the pending list and opens the notification list
     * @event
     */
    notificationButton.click(() => {
        hide(pendingTab);
        show(notificationTab);
    })

    /**
     * Close the pending list and opens the pending list
     * @event
     */

    $('.notif-cross').click(() => {
        hide(notificationTab);
        show(pendingTab);
    })

    /**
     * Marks all notifications as read
     * @event
     */
    $('button:contains("Read All")').click(() => {
            NotificationsPanel.command('/notificationAction?id=readAll')
        }
    )

    /**
     * Delete all notifications
     * @event
     */

    $('button:contains("Delete All")').click(() => {
            NotificationsPanel.command('/notificationAction?id=deleteAll')
        }
    )

    //### Folder

    /**
     * Shows the files according to the folder to display
     * @function
     * @param {string} type - The folder to display (medias, events, ads, segues)
     */
    function setListing(type) {
        console.log('setType:' + type)
        MusicsPanel.command("/setType?type=" + type);
    }


    const listWhere = $('.listWhere');

    ChannelDropdown = new VotvDropdown('channelDropdown', 'filterByChannel', 'sendChannel')

    const channelDiv = $('#channelDiv');
    const channelText = $('#channelText');

    /**
     * Shows only medias
     * @event
     */
    $('#listWhere button:contains("Musics"), #listWhere button:contains("Videos")').click(function () {
        toggleButton(listWhere, $(this))
        setListing("media")

        if (media_type === 'audios') {
            ChannelDropdown.updateOptions(['Everything', 'None', 'Christmas', 'Classical', 'Country', 'Electronic', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Metal', 'Pop', 'Rock', 'Video Game', 'Weird'])
        } else if (media_type === 'videos') {
            ChannelDropdown.updateOptions(['Everything', 'None', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Memes', 'News', 'Shows', 'Vlogs'])
        }

    })
    /**
     * Show only events
     * @event
     */
    $('#listWhere button:contains("Events")').on('click', function () {
        toggleButton(listWhere, $(this))
        setListing("event")
        ChannelDropdown.updateOptions(['Everything', 'Strange [4%]', 'Weird [2%]', 'Bizarre [1%]', 'Outlandish [0.4%]', 'Unfathomable [0.2%]', 'Otherworldly [0.1%]', 'Transcendental [0.04%]'])
    })
    /**
     * Show only events
     * @event
     */
    $('#listWhere button:contains("Ads")').on('click', function () {
        toggleButton(listWhere, $(this))
        setListing("ad")
        ChannelDropdown.updateOptions(['Everything'])
    })
    /**
     * Show only segues
     * @event
     */
    $('#listWhere button:contains("Segues")').on('click', function () {
        toggleButton(listWhere, $(this))
        setListing("segue")
        ChannelDropdown.updateOptions(['Everything'])
    })


    //### Publish toward...
    const typeInput = $("input[name|='type']")
    const where = $('.where');

    const whereMusics = $('.whereButtons button:contains("Musics"), .whereButtons button:contains("Videos")');
    const whereEvents = $('.whereButtons button:contains("Events")');
    const whereAds = $('.whereButtons button:contains("Advertisements")');
    const whereSegues = $('.whereButtons button:contains("Segues")');

    /**
     * Update destination folder to Medias
     * @event
     */
    whereMusics.click(function () {
        toggleButton(where, whereMusics)
        typeInput.val('media');
    });

    /**
     * Update destination folder to Events
     * @event
     */
    whereEvents.click(function () {
        toggleButton(where, whereEvents)
        typeInput.val('event');
    });

    /**
     * Update destination folder to Ads
     * @event
     */
    whereAds.click(function () {
        toggleButton(where, whereAds)
        typeInput.val('ad');
    });

    /**
     * Update destination folder to Segues
     * @event
     */
    whereSegues.click(function () {
        toggleButton(where, whereSegues)
        typeInput.val('segue');
    });


    //### Fonctionnement dropdown

    DestinationDropdown = new VotvDropdown('destinationDropdown', 'destination')

    //### Reporting

    /**
     * Open the report window
     * @event
     */
    report.click(function () {
        toggleScreen($("form[action|='/reportMedia']"));
        $('#hiddenFileToReport').attr('value', lastFilename);
    })

    /**
     * Report a file
     * @event
     */
    $('#reportButton').click(function () {
        reportFile();
    })

    /**
     * Report the file loaded in the media player
     * @function
     */
    function reportFile() {
        //https://developer.mozilla.org/en-US/docs/Web/API/FormData/FormData

        toggleScreen($("#loadingScreen"))
        let playScreen = $('#playScreen')


        const formData = new FormData($("form[action|='/reportMedia']")[0]);
        let filename = formData.get('filename')
        formData.set('media_type', media_type);

        ajaxIsPending(filename).then(() => {
            if (isFilePending) {
                PendingPanel.postCommand('/reportMedia', formData).then(() => {
                    ajaxRequestPlayMedia(filename)
                    toggleScreen(playScreen)
                });
            } else {
                MusicsPanel.postCommand('/reportMedia', formData).then(() => {
                    ajaxRequestPlayMedia(filename)
                    toggleScreen(playScreen)
                });
            }
            //popin('Info', `${filename} reported successfully!`)

        })


    }

    //### Infinite scrolling

    let mutexApproved = false;
    let approvedMediaPage = 1;
    let approvedMaxMediaPage = $('#approvedMaxPages').val()
    $('#MusicsPanel').on('scroll', function () {
        if (approvedMediaPage >= approvedMaxMediaPage) {

            return;
        }

        let scrollTop = $(this).scrollTop();
        let containerHeight = $(this).innerHeight();
        let totalContentHeight = this.scrollHeight;
        let oldScrollTop = $(this).scrollTop();
        if (scrollTop + containerHeight >= totalContentHeight - 1) {
            if (mutexApproved)
                return;
            mutexApproved = true;
            approvedMediaPage++;
            if (storageLoading === "1")
                addLoadingScreen($(this))
            MusicsPanel.contentServiceURL = "/getMedias?page=" + approvedMediaPage;
            MusicsPanel.refresh(true).then(() => {
                $('#MusicsPanel').scrollTop(oldScrollTop);
                mutexApproved = false;
            })
        }
    });

    let mutexPending = false;
    let pendingMediaPage = 1;
    let pendingMaxMediaPage = $('#pendingMaxPages').val()

    $('#PendingPanel').on('scroll', function () {
        if (pendingMediaPage >= pendingMaxMediaPage) {

            return;
        }
        let scrollTop = $(this).scrollTop();
        let containerHeight = $(this).innerHeight();
        let totalContentHeight = this.scrollHeight;
        let oldScrollTop = $(this).scrollTop();
        if (scrollTop + containerHeight >= totalContentHeight - 1) {
            if (mutexPending)
                return;
            mutexPending = true;
            pendingMediaPage++;
            if (storageLoading === "1")
                addLoadingScreen($(this))
            PendingPanel.contentServiceURL = "/getPendingMedias?page=" + pendingMediaPage;
            PendingPanel.refresh(true).then(() => {
                $('#PendingPanel').scrollTop(oldScrollTop);
                mutexPending = false;
            });
        }
    });


    function addLoadingScreen(target) {
        target.html(` <div class="m-auto flex flex-row">
                    <img class="w-12 mx-auto" src="https://votvbroadcast/images/hourglass.gif?v=1" alt="">
                    <div class="border-3 border-solid p-1 w-100 h-12 flex flex-row">
                        <div class="h-full loadingBar" style="background-color: #F8FE50;"></div>
                    </div>
                </div>`)
    }

    const filterByChannel = $('#filterByChannel')

    filterByChannel.on("sendChannel", () => {
        $('#MusicsPanel').scrollTop(0);
        approvedMediaPage = 1;
        MusicsPanel.contentServiceURL = "/getMedias?page=" + approvedMediaPage;
        mutexApproved = true;
        MusicsPanel.command("/setChannel?value=" + filterByChannel.val()).then(() => {
            mutexApproved = false;
        });
    })

    //I'm pissed, 1 nanoseconds between the actual value and the new channels, FUCK THIS.
    //https://developer.mozilla.org/en-US/docs/Web/API/MutationObserver
    const targetNode = document.getElementById("approvedMaxPages");

    // Options for the observer (which mutations to observe)
    const config = {attributes: true, childList: true, subtree: true};

    // Callback function to execute when mutations are observed
    const callback = (mutationList, observer) => {
        for (const mutation of mutationList) {
            if (mutation.type === "attributes") {
                approvedMaxMediaPage = parseInt(targetNode.value)
            }
        }
    };

    // Create an observer instance linked to the callback function
    const observer = new MutationObserver(callback);

    // Start observing the target node for configured mutations
    observer.observe(targetNode, config);


    //#### Batch editing


    DestinationBatchDropdown = new VotvDropdown('destinationBatchDropdown', 'batchDestination', null, 20, "Unchanged")
    if (media_type === 'audios') {
        DestinationBatchDropdown.updateOptions(['Unchanged', 'Everything', 'SFW', 'Christmas', 'Classical', 'Country', 'Electronic', 'Hip Hop', 'Instrumental', 'Jazz', 'Mariachi', 'Metal', 'Pop', 'Rock', 'Video Game', 'Weird'])
    } else if (media_type === 'videos') {
        DestinationBatchDropdown.updateOptions(['Unchanged', 'Everything', 'SFW', 'Animations', 'Documentaries', 'Horror', "Let's Plays", 'Lt30secs', 'Lt5mins', 'Memes', 'News', 'Shows', 'Vlogs'])
    }

    FrequencyBatchDropdown = new VotvDropdown('frequencyBatchDropdown', 'batchFrequency', null, 10, "Unchanged")
    FrequencyBatchDropdown.updateOptions(['Unchanged', 'Strange [4%]', 'Weird [2%]', 'Bizarre [1%]', 'Outlandish [0.4%]', 'Unfathomable [0.2%]', 'Otherworldly [0.1%]', 'Transcendental [0.04%]'])

    $('#updateBatchButton').on('click', () => {
        submitBatchEditForm()
    })

    function submitBatchEditForm() {
        toggleScreen($("#loadingScreen"))
        const formData = new FormData($("form[action|='/updateBatch']")[0]);
        formData.set('filenames', selectedFiles);
        hide(batchMenu)
        MusicsPanel.postCommand('/updateBatch', formData).then(() => {
            PendingPanel.refresh(true);
            toggleScreen($("#playScreen"))
            closeBatchMenu();
        })
    }

    function submitBatchDeleteForm() {
        toggleScreen($("#loadingScreen"))
        const formData = new FormData($("form[action|='/deleteBatch']")[0]);
        formData.set('filenames', selectedFiles);
        hide(batchMenu)
        MusicsPanel.postCommand('/deleteBatch', formData).then(() => {
            PendingPanel.refresh(true);
            toggleScreen($("#playScreen"))
            closeBatchMenu();
        })
    }

    //### Image preview
    function updateCover(target, file) {
        target.attr('src', window.URL.createObjectURL(file));
    }

    $('#cover').on('change', function () {
        updateCover($('#currentCover'), $(this)[0].files[0]);
    })

    $('#b_cover').on('change', function () {
        updateCover($('#b_currentCover'), $(this)[0].files[0]);
    })

})
