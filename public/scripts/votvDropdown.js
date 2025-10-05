/**
 * @author: Gabrielle Rousse
 * Generates a VotV-Like dropdown that works similarly to how it would work in-game
 */

class VotvDropdown {
    /** Create a VotVDropdown
     * @param {string} dropdownId - The id of the DOM element that will have it's content replaced
     * @param {string} saveId - The input that needs it data to be updated
     * @param {string} functionToTrigger - A custom event trigger
     * @param {number} z - Dropdown are above the content, if 2 dropdown are close they will fight for their z-index and have a bugged display.
     */
    constructor(dropdownId, saveId, functionToTrigger = null, z = 10) {
        this._z = z;
        const self = this;
        this.functionToTrigger = functionToTrigger;
        this.dropdownId = '#' + dropdownId;
        this.generateDropdown();
        this.saveId = '#' + saveId;
        this.dropdown = $(this.dropdownId);
        this.index = 0;
        this.options = $(this.dropdownId + " .options");

        this.initializeDropdown();


        /**
         * When clicking on the dropdown, opens it (mainly for mobile users, since they can't hover.)
         * @event
         */
        this.dropdown.on("click", function() {
            self.showEl(self.options, "block");
        });

        /**
         * When hovering above the dropdown, open it.
         * @event
         */
        this.dropdown.on("mouseenter", function() {
            /**
             * While hovering, pressing E will save the dropdown value
             * @event
             */
            $(document).on('keydown', function(e) {
                if (e.key === 'e' || e.key === 'E') {
                    self.saveOption();
                }
            });
            self.showEl(self.options, "block");

            /**
             * Scrolling changes the cursor position in the dropdown
             * @event
             */
            $(window).on('wheel', function(e) {
                let i = 0;
                i += e.originalEvent.deltaY * -0.01;
                if (i > 0) {
                    self.index--;
                    if (self.index < 0) {
                        self.index = 0;
                    }
                    self.invisibleEl(self.svg);
                    self.visibleEl($(self.svg[self.index]));
                } else {
                    self.index++;
                    if (self.index > self.svg.length - 1) {
                        self.index = self.svg.length - 1;
                    }
                    self.invisibleEl(self.svg);
                    self.visibleEl($(self.svg[self.index]));
                }
            });
        });

        /**
         * Leaving the dropdown closes it and disable the keydown 'E' listener
         * @event
         */
        this.dropdown.on("mouseleave", function() {
            self.closeOption();
            $(document).off('keydown')
        });
    }

    /**
     * Generate the initial content of a dropdown
     * @function
     */
    generateDropdown() {
        $(this.dropdownId).html(`
            <dropdown class="relative z-${this._z} flex flex-col w-70 gap-0 hover:cursor-pointer">
                <div class="dropdownTitle">Select (hover me)</div>
                <div class="hidden absolute options flex-col mt-6">
                </div>
            </dropdown>
        `);
    }

    /**
     * Things to be done to assure that the dropdown works.
     * @function
     */
    initializeDropdown() {
        this.index = 0;
        this.svg = $(this.dropdownId + ' svg');
        this.option = $(this.dropdownId + " .option");
        this.invisibleEl(this.svg);
        this.visibleEl($(this.svg[0]));
        const self = this;
        this.option.on('click', function(e) {
            e.stopPropagation();
            self.saveOption();
        });

        this.option.each(function () {
            $(this).on("mouseenter", function () {
                self.invisibleEl(self.svg);
                self.visibleEl($(this).children(":first"));
            });
        });

        //Default Dropdown title, this could clearly be a parameter
        //TODO: Transform this into a parameter
        $(this.dropdownId + ' .dropdownTitle').text('Everything');
        //https://stackoverflow.com/questions/1948332/detect-all-changes-to-a-input-type-text-immediately-using-jquery
        //Triggers a custom EventListener
        //Updating a node doesn't trigger either 'input' or 'change'
        if(this.functionToTrigger)
        {
            $(this.saveId).val('Everything').trigger(this.functionToTrigger);
        }
        else {
            $(this.saveId).val('Everything')
        }
    }

    /**
     * Save the selected option inside the specified input
     * @function
     */
    saveOption() {
        this.closeOption();
        let value = $(this.dropdownId + ' svg:not(.invisible)').attr('value');
        $(this.dropdownId + ' .dropdownTitle').text(value);
        if(this.functionToTrigger)
        {
            $(this.saveId).val(value).trigger(this.functionToTrigger);
        }
        else
        {
            $(this.saveId).val(value)
        }

    }

    /**
     * Hide the options
     * @function
     */
    closeOption() {
        this.hideEl(this.options, "block");
        $(window).off('wheel');
    }

    /**
     * Generate the options provided to the dropdown
     * @function
     * @param {array} array - An array containing the various options to display
     */
    updateOptions(array) {
        let optionsContent = '<div class="mr-auto ml-auto flex flex-col">';
        array.forEach((item,index) => {
            optionsContent += `
                <div class="flex flex-row option">
                    <svg value="${item}" class="mt-auto mb-auto" height="12.5" width="12.5"
                            xmlns="http://www.w3.org/2000/svg">
                        <line x1="0" y1="2.5" x2="8.75" y2="6.25" style="stroke:red;stroke-width:2"/>
                        <line x1="0" y1="10" x2="8.75" y2="6.25" style="stroke:red;stroke-width:2"/>
                        Sorry, your browser does not support inline SVG.
                    </svg>
                    <div>${index + 1}) ${item}</div>
                </div>`;
        });
        optionsContent += '</div>';
        this.options.html(optionsContent);
        this.initializeDropdown();
    }

    /**
     * Show element
     * @function
     */
    showEl(selector, classe = 'flex') {
        selector.removeClass('hidden');
        selector.addClass(classe);
    }

    /**
     * Hide element
     * @function
     */
    hideEl(selector, classe = 'flex') {
        selector.addClass('hidden');
        selector.removeClass(classe);
    }

    /**
     * Makes an element visible
     * @function
     */
    visibleEl(selector) {
        selector.removeClass('invisible');
    }

    /**
     * Makes an element invisible
     * @function
     */
    invisibleEl(selector) {
        selector.addClass('invisible');
    }
}
