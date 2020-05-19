// ugly:
var popupWindow;
var FileBrowserReturnValue;

 (function($) {

    var settings = {
        buttonText: 'Bild auswählen',
        removeButtonText: 'Bild entfernen',
    };

    var methods = {

        init: function(options) {
            return this.each(function() {
                if (options) {
                    $.extend(settings, options);
                }

                data=$(this).data('alreadyInitialized');
                if (!data)
                {
                    var $this=$(this);
                    $(this).data('alreadyInitialized', 1);
                    button = $("<a href='#' class='button'><span class='tinyicon ui-icon-pencil'></span>" + settings.buttonText + "</a>");
                    $(this).after(button);
                    button.wrap('<div>');
                    button.click(function(event) {
                        event.preventDefault();
                        console.dirxml($this[0]);
                        type = 'image';
                        var chooserUrl = '/BE/MwFileFieldChooser/?type=' + escape(type) + '&id=' + $this.val();
                        iframe = $('<iframe src="' + chooserUrl + '" style="width:700px;height:500px" ></iframe>');
                        FileBrowserReturnValue = 0;
                        popupWindow = new Boxy(iframe, {
                            title: settings.buttonText,
                            afterHide: function() {
                                if (FileBrowserReturnValue > 0)
                                {
                                    $this.val(FileBrowserReturnValue);
                                    $this.change();
                                    //trigger change handler
                                }

                            },
                            modal: true,
                            x: 50,
                            y: 50
                        });
                    });

                    if ($(this).val() > 0)
                    {
                        removebutton = $("<a href='#' class='button'><span class='tinyicon ui-icon-trash'></span>" + settings.removeButtonText + "</a>");
                        $(this).after(removebutton);
                        removebutton.wrap('<div>');
                        removebutton.click(function(event) {
                            event.preventDefault();
                            $this.val('');
                            $this.change();
                            //trigger change handler
                        });

                    }
                }
            });

            return this;
        },
    };

    $.fn.MwFileField = function(method) {

        // Method calling logic
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.MwFileField');
        }

    };

})(jQuery);
