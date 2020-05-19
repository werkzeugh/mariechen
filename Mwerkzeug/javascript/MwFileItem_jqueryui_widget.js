// ugly global access:
var MwFileItem_LastUsed;
var MwFileItem_Current = 0;

// widget skeleton based on: http://bililite.com/blog/understanding-jquery-ui-widgets-a-tutorial/
// http://jqueryui.com/docs/Developer_Guide
(function ($) {

    $.widget("ui.MwFileItem", {
        options: {
            log: 1,
            ChooserMode: null,
            test: 5,
            texts: {
                AreYouSure: 'are you sure ?',
                PreviewButtonText: 'preview',
                EditButtonText: 'edit metadata',
                ChooseButtonText: 'use this file',
                RemoveButtonText: 'remove file'
            },
            removeUrl: "/BE/MwFile/ajaxRemoveFile/",
            moveUrl: "/BE/MwFile/ajaxMoveFile/",
            editUrl: "/BE/MwFile/iframeEdit/",
            itemHTMLUrl: "/BE/MwFile/ajaxGetItemHTML/",
            chooseFile: function () {
                alert('override me !');
            }
        },
        getDBId: function () {
            return this.element.attr('dbid');
        },
        _init: function () {
            this.decorate();
        },
        redraw: function () {
            url = this.options.itemHTMLUrl + this.getDBId();
            var self = this;
            this.element.load(url, null,
                function () {
                    self.element.fadeOut('fast').fadeIn('fast');
                    self.decorate();
                    self.element.trigger('mouseover');
                });
        },
        decorate: function () {
            //buttons
            var $decorationHTML = $("<div class='_buttons _hover'></div>");
            if (this.options.ChooserMode) {
                $decorationHTML.append($('<div>').append(this.getPreviewButton()));
                $decorationHTML.append($('<div>').append(this.getChooseButton()));
            } else {
                $decorationHTML.append($('<div>').append(this.getEditButton()));
                $decorationHTML.append($('<div>').append(this.getPreviewButton()));
                $decorationHTML.append($('<div>').append(this.getRemoveButton()));
            }
            this.element.find('._thumbnail').append($decorationHTML);
            var self = this;
            this.element.bind('mouseover',
                function () {
                    self.mouseover();
                });
        },
        mouseover: function () {
            if (MwFileItem_Current != 0 && MwFileItem_Current != this) {
                MwFileItem_Current.element.find('._hover').fadeOut();
            }
            this.element.find('._hover').fadeIn();
            MwFileItem_Current = this;
        },
        getChooseButton: function () {
            var self = this;
            return button = $("<a href='#' class='iconbutton _choose' title='" + this.options.texts.ChooseButtonText + "'><span class='tinyicon ui-icon-arrowthick-1-e'></span><i>" + this.options.texts.ChooseButtonText + "</i></a>").click(function (event) {
                event.preventDefault();
                var callback = self.options.chooseFile;
                if ($.isFunction(callback)) callback(self);
            });
        },
        getEditButton: function () {
            var self = this;
            return button = $("<a href='#' class='iconbutton _edit' title='" + this.options.texts.EditButtonText + "'><span class='tinyicon ui-icon-pencil'></span></a>").click(function (event) {
                event.preventDefault();
                self.editMetadata();
            });
        },
        getPreviewButton: function () {
            var self = this;
            return button = $("<a href='" + this.element.attr('fileurl') + "' class='iconbutton _preview' target='_new' title='" + this.options.texts.PreviewButtonText + "'><span class='tinyicon ui-icon-zoomin'></span></a>").click(function (event) {
                event.preventDefault();
                self.previewFile();
            });
        },
        getRemoveButton: function () {
            var self = this;
            return $("<a href='#' class='iconbutton _remove' title='" + this.options.texts.RemoveButtonText + "'><span class='tinyicon ui-icon-trash'></span></a>").click(function (event) {
                event.preventDefault();
                if (confirm(self.options.texts.AreYouSure)) {
                    self.removeFile();
                }
            });
        },
        removeFile: function () {
            var self = this;
            url = this.options.removeUrl + this.getDBId();
            $.getJSON(url, {},
                function (data) {
                    self.element.fadeOut();
                });
        },
        moveFile: function (targetID) {
            var self = this;
            url = this.options.moveUrl + this.getDBId() + '/' + targetID;
            $.getJSON(url, {},
                function (data) {
                    if (data.error) {
                        alert(data.error.msg);
                    } else {
                        self.element.fadeOut();
                    }
                });
        },
        previewFile: function () {
            window.open(this.element.attr('fileurl'), '_blank', 'width=800,height=600,menubar=1,resizable=1,scrollbars=1,status=0,titlebar=0,toolbar=0');
        },
        editMetadata: function () {
            var self = this;

            url = this.options.editUrl + this.getDBId();
            iframe = $('<iframe src="' + url + '" style="width:800px;height:600px" ></iframe>');
            this.options.editWindow = new Boxy(iframe, {
                title: this.options.texts.EditButtonText,
                afterHide: function () {
                    self.afterEditMetadata();
                },
                modal: true,
                center: false,
                x: 50,
                y: jQuery(document).scrollTop() + 30
            });

        },
        afterEditMetadata: function () {
            this.redraw();
        },
        log: function (msg) {
            if (!this.options.log) return;

            if (window.console && console.log) {
                // firebug logger
                console.log('MwFileItem: ' + msg);
            }
        },
        destroy: function () {
            $.Widget.prototype.destroy.apply(this, arguments); // default destroy
        }

    });

    $.extend($.ui.MwFileItem, {
        version: "1.0",
    });

})(jQuery);
/* END */
