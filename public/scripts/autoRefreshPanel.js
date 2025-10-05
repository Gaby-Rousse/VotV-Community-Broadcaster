/**
 * @author: Nicolas Chourot
 * Script for ASP.NET, severely edited by Gabrielle Rousse for PHP and with additionnal features such as promises
 *
 * An AutoRefreshedPanel is a section of a page that refreshes partially (usually a section) using Ajax,
 * allowing users to see updates in live without the need to refresh.
 * It can retrieve a View, send POST and GET request all using AJAX.
 */

class AutoRefreshedPanel {

    /** Create an AutoRefreshPanel
     * @param {string} panelId - The id of the DOM element that will have it's content replaced
     * @param {string} contentServiceURL - The url that returns a partial view
     * @param {number} refreshRate - The amount of time, in seconds, before the refreshPanel tries to refresh
     * @param postRefreshCallback - EventListeners attached to the panel (if not specified here, they break.)
     */
    constructor(panelId, contentServiceURL, refreshRate, postRefreshCallback = null) {
        this.contentServiceURL = contentServiceURL;
        this.panelId = panelId;
        this.postRefreshCallback = postRefreshCallback;
        this.refreshRate = refreshRate * 1000;
        this.paused = false;
        this.refresh(true);
        setInterval(() => {
            this.refresh()
        }, this.refreshRate);
    }

    /**
     * Pause the refreshing, the panel won't ever refresh while paused
     * @function
     */
    pause() {
        this.paused = true;
    }

    /**
     * Resume the refreshing
     * @function
     */
    restart() {
        this.paused = false
    }

    /**
     * Replace the content inside the refreshPanel
     * @function
     * @param {string} htmlContent - The html code to display
     */
    replaceContent(htmlContent) {
        if (htmlContent !== "") {
            $("#" + this.panelId).html(htmlContent);
            if (this.postRefreshCallback != null) this.postRefreshCallback();
        }
    }

    /**
     * Updates the refresh panel
     * @function
     * @param {boolean} forced - Forced happens when the user refresh the page manually
     */
    refresh(forced = false) {
        if (!this.paused) {
            $.ajax({
                url: this.contentServiceURL + (forced ? (this.contentServiceURL.indexOf("?") > -1 ? "&" : "?") + "forceRefresh=true" : ""),
                dataType: "html",
                success: (htmlContent) => {
                    this.replaceContent(htmlContent)
                },
            })
        }
    }

    /**
     * Sends a GET request
     * @function
     * @param {string} url - The endpoint for the request
     * @param params - No idea, I never use this
     * @param moreCallBack - I don't know
     * @return Promise - Tells you when the ajax request is done
     */
    command(url) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                method: 'GET',
                success: (response) => {
                    this.refresh(true);
                    if(response)
                    {
                        this.popin(`<div class="popup gap-2 inline-flex flex-row text-nowrap"><img src="https://votvbroadcast.com/images/error.png" > <div class="mt-auto mb-auto dos !text-white">` + response + `</div></div>`);
                    }
                    resolve(true);

                },
                error: () => {
                    resolve(true);
                }
            });
        })
    }

    /**
     * Script directly from musicUpload.js
     * There must be a way to use it without rewriting it here
     * Adds a pop-up to the right
     * @function
     * @param {string} html - The html to show
     */
    popin(html) {
        //clearTimeout(timeoutID)
        //$('.popup').remove();

        $('#popups').append(html);
        let popup = $('.popup')
        setTimeout(function () {
            popup.addClass('popout');
            setTimeout(function () {
                popup.remove();
            }, 3000);
        }, 3000);
    }


    /**
     * Sends a GET request
     * @function
     * @param {string} url - The endpoint for the request
     * @param {FormData} data - The submitted data
     * @return Promise - Tells you when the ajax request is done
     * This was initially intended for json data but was converted to receive a FormData Object inspired by this:
     * https://stackoverflow.com/questions/6974684/how-to-send-formdata-objects-with-ajax-requests-in-jquery
     */
    postCommand(url, data) {
        return new Promise((resolve, reject) => {

            $.ajax({
                url: url,
                method: 'POST',
                processData: false,
                contentType: false,
                data: data,
                success: (response) => {
                    this.refresh(true);
                    if (response)
                        this.parsedResponse = JSON.parse(response);
                    if (this.parsedResponse) {
                        if (this.parsedResponse.database) {
                            this.popin(`<div class="popup gap-2 inline-flex flex-row text-nowrap"><img src="https://votvbroadcast.com/images/error.png" > <div class="mt-auto mb-auto dos !text-white">` + this.parsedResponse.txt + `</div></div>`);
                            this.popin(`<div class="popup gap-2 inline-flex flex-row text-nowrap"><img src="https://votvbroadcast.com/images/warning.png" > <div class="mt-auto mb-auto dos !text-white">` + 'Database updated! However no data has been written in the file itself.' + `</div></div>`);
                        } else {
                            this.popin(`<div class="popup gap-2 inline-flex flex-row text-nowrap"><img src="https://votvbroadcast.com/images/error.png" > <div class="mt-auto mb-auto dos !text-white">` + response + `</div></div>`);
                        }
                    }


                    resolve(true);

                },
                error: () => {
                    resolve(true);
                }
            });

        });
    }

}
