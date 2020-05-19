       // ugly global access:
       var LastUsedMwFileField;


       // widget skeleton based on: http://bililite.com/blog/extending-jquery-ui-widgets/
       // http://jqueryui.com/docs/Developer_Guide
       (function ($) {

         $.ui.widget.subclass('ui.MwFileFieldBase', {
           options: {
             log: 1,
             test: 5,
             ShowChooseButton: true,
             ShowUploadButton: true,
             texts: {
               ChooseButtonText: 'Media-Archive',
               UploadButtonText: 'Upload File',
               UploadBrowseButtonText: 'Choose File',
               RemoveButtonText: 'Remove File',
               AreYouSure: 'are you sure ?'
             },
             ThumbnailFormat: {
               format: 'SetSize',
               arg1: 180,
               arg2: 90
             }
           },

           _init: function () {
             this.redraw();
           },

           getMwFileFieldDIV: function () {
             return this.element.next('.MwFileFieldDIV');
           },

           redraw: function () {
             var self = this;

             //remove existing stuff
             var existingDIV = this.getMwFileFieldDIV();
             if (existingDIV)
               existingDIV.remove();
             //create new HTML
             var $newHTML = $("<div class='MwFileFieldDIV'><div class='_previewplace'></div><div class='_uploadplace'></div><div class='_buttons'></div></div>");

             //buttons

             if (this.options.ShowUploadButton) {
               $newHTML.find('div._buttons').append('<div>').append(this.getUploadButton());
             }

             if (this.options.ShowChooseButton) {
               $newHTML.find('div._buttons').append('<div>').append(' ').append(this.getChooseButton());
             }

             if (this.element.val() > 0) {
               //preview
               $newHTML.find('div._previewplace').html(this.getLoadingHTML());

               $.post('/BE/MwFile/jsonGetInfo/' + this.element.val(), this.getOptionsForPreview(),
                 function (data) {
                   $newHTML.find('div._previewplace').hide().html(self.createFilePreviewHTML(data)).fadeIn();
                 }, 'json');

             }


             this.element.after($newHTML);


           },

           getLoadingHTML: function () {
             return "loading...";
           },
           getOptionsForPreview: function () {
             //set options to pass to the preview-json-url, to get a appropriate preview-image

             var opts = {
               Thumbnail: this.options.ThumbnailFormat
             };
             return opts;
           },
           createFilePreviewHTML: function (data) {
             var $html = $("<div class='_preview'>");
             $previewbuttons = $("<div class='_previewbuttons'>").append(this.getRemoveButton());
             $html.append($previewbuttons);

             var isSvg = false;
             if (data.record.Filename.match(/\.svg$/)) {
               isSvg = true;
             }

             if (data.ThumbnailURL) {
               $html.append($("<img src=" + data.ThumbnailURL + " title='" + data.record.Filename + "' style='max-width:120px'>"));
             }
             $html.append("<div class='_subline'>" + data.Filename + ", " + data.FilesizeStr + "</div>");
             return $html;
           },
           getChooseButton: function () {
             var self = this;
             return $("<a href='#' class='iconbutton _choose'><span class='tinyicon ui-icon-folder-open'></span><i>" + this.options.texts.ChooseButtonText + "</i></a>").click(function (event) {
               event.preventDefault();
               self.chooseFile();
             });
           },
           getUploadButton: function () {
             var self = this;
             return $("<a href='#' class='iconbutton _upload'><span class='tinyicon ui-icon-arrowthick-1-n'></span><i>" + this.options.texts.UploadButtonText + "</i></a>").click(function (event) {
               event.preventDefault();
               self.uploadFile();
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

           uploadFile: function () {
             var uploadInterfaceUrl = '/BE/MwFile/uploadSingleFile';
             var self = this;
             $('.MwFileFieldDIV ._uploadplace').html(); //clear other ._uploadplaces on this page (there can be only one at the moment)
             this.getMwFileFieldDIV().find('._uploadplace').html("<iframe src='" + uploadInterfaceUrl + "'></iframe>");
             LastUsedMwFileField = this;
             //store this MwFileField
           },
           chooseFile: function () {
             type = 'image';
             var chooserUrl = '/BE/MwFileFieldChooser/?type=' + escape(type) + '&id=' + this.element.val();
             // iframe = $('<iframe src="' + chooserUrl + '" style="width:700px;height:500px" ></iframe>');
             // FileBrowserReturnValue = 0;
             var self = this;
             LastUsedMwFileField = this;
             //store this MwFileField
             var popupwin = window.open(chooserUrl, 'MwFileFieldPopup', 'width=800,height=500,scrollbars=1');
             popupwin.focus();

             // popupWindow = new Boxy(iframe, {
             //                 title: this.options.texts.ChooseButtonText,
             //                 afterHide: function() {
             //                     if (FileBrowserReturnValue > 0)
             //                     {
             //                         self.element.val(FileBrowserReturnValue);
             //                         self.element.change();
             //                         self.redraw();
             //                         //trigger change handler
             //                     }
             //
             //                 },
             //                 modal: true,
             //                 x: 50,
             //                 y: 50
             //             });
           },
           removeFile: function (event) {
             this.element.val('');
             this.element.change();
             var self = this;
             this.getMwFileFieldDIV().find('._previewplace').slideUp('slow',
               function () {
                 self.redraw();
               });

           },
           getImageData: function (callback) {
             var self = this;
             $.post('/BE/MwFile/jsonGetInfo/' + this.element.val(), this.getOptionsForPreview(), callback, 'json');
           },
           updateCopyrightField: function () {
             var self = this;
             var callback = function (data) {
               $('input#input_Copyright').val(data.record.Copyright);
             };

             this.getImageData(callback);
           },
           updateIDFromPopupWindow: function (id) {
             this.element.val(id);
             this.updateCopyrightField();
             this.redraw();
           },
           log: function (msg) {
             if (!this.options.log) return;

             if (window.console && console.log) {
               // firebug logger
               console.log('MwFileField: ' + msg);
             }
           },

           destroy: function () {
             $.Widget.prototype.destroy.apply(this, arguments); // default destroy
           }

         });

         $.ui.MwFileFieldBase.subclass('ui.MwFileField', {
           // 
           // getChooseButton: function() {
           //   return this._super().text('me too !'); 
           //        
           // },

         });


       })(jQuery);
       /* END */
