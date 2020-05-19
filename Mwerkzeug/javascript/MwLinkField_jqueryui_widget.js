// ugly global access:
var LastUsedMwLinkField;
var popupWindow;

// widget skeleton based on: http://bililite.com/blog/extending-jquery-ui-widgets/
// http://jqueryui.com/docs/Developer_Guide
(function ($) {

  $.ui.widget.subclass('ui.MwLinkFieldBase', {
    options: {
      log: 1,
      test: 5,
      ShowChooseButton: true,
      texts: {
        ChooseButtonText: 'Choose Target',
        ImportButtonText: 'import texts/imgs'
      }
    },
    popupwin: {},
    _init: function () {
      this.redraw();
    },

    getMwLinkFieldDIV: function () {
      return this.element.next('.MwLinkFieldDIV');
    },

    redraw: function () {
      var self = this;

      //remove existing stuff
      var existingDIV = this.getMwLinkFieldDIV();
      if (existingDIV) existingDIV.remove();
      //create new HTML
      var $newHTML = $("<div class='MwLinkFieldDIV'></div>");
      $newHTML.append("<div class='_previewplace'></div>");

      //buttons

      if (this.options.ShowChooseButton) {
        $newHTML.append('<div>').append(' ').append(this.getChooseButton());
      }


      if (this.element.val() !== '') {
        //preview
        $newHTML.find('div._previewplace').html(this.getLoadingHTML());

        $.post('/BE/MwLink/jsonGetInfo/?MwLink=' + escape(this.element.val()), this.getOptionsForPreview(),
          function (data) {
            $newHTML.find('div._previewplace').hide().html(self.createLinkPreviewHTML(data)).fadeIn();
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
    importToTeaserEnabled: function () {
      if (this.options.importToTeaserEnabled)
        return true;
      else if (this.element.closest('.c4p-itemform').length > 0) {
        return true;
      } else if (this.element.closest('.CElement.editmode').length > 0) {
        return true;
      }

    },
    createLinkPreviewHTML: function (data) {
      var self = this;
      var $html = $("<div class='_preview'>");
      // $previewbuttons=$("<div class='_previewbuttons'>").append(this.getRemoveButton());
      // $html.append($previewbuttons);
      var prevhtml = data.PreviewHtml;
      if (!prevhtml || prevhtml == 'undefined')
        prevhtml = '';

      $html.append("<div style='margin:5px 0px'><span class='_mwlink' style='display:inline-block;background:#ddd;border:1px solid #999;padding:2px 10px;width:auto;margin-right:auto;line-height:12px;font-size:12px' title='" + this.element.val() + "'>➔ <b>" + data.Title + "</b><br> " + data.ReadableUrl + prevhtml + "</span></div>");
      if (this.importToTeaserEnabled()) {
        var $importlink = $("<a href='#' class='iconbutton'><span class='tinyicon ui-icon-arrow-1-s'></span>" + this.options.texts.ImportButtonText + "</a>");
        $importlink.click(function (e) {
          e.preventDefault();

          var importUrl = '/BE/MwLink/import';
          if (self.options.importUrl) {
            importUrl = self.options.importUrl;
          }

          importUrl += '?MwLink=' + escape(self.element.val());
          var iframe = $('<iframe src="' + importUrl + '" style="width:500px;height:400px" ></iframe>');

          self.popupwin = new Boxy(iframe, {
            title: self.options.texts.ImportButtonText,
            afterHide: function () {

            },
            modal: true,
            x: 50,
            y: jQuery(document).scrollTop() + 30
          });

          popupWindow = self.popupwin;


        });
        $('._mwlink', $html).append($importlink);

      }
      return $html;
    },
    getChooseButton: function () {
      var self = this;
      return jQuery("<a href='#' class='iconbutton _choose'><span class='tinyicon ui-icon-folder-open'></span><i>" + this.options.texts.ChooseButtonText + "</i></a>").click(
        function (event) {
          event.preventDefault();
          self.chooseTarget();
        });
    },
    chooseTarget: function () {
      var chooserUrl = '/BE/MwLink/chooser';
      if (this.options.ChooserUrl) {
        chooserUrl = this.options.ChooserUrl;
      }

      chooserUrl += '?MwLink=' + escape(this.element.val());
      var iframe = $('<iframe src="' + chooserUrl + '" style="width:700px;height:600px" ></iframe>');
      // FileBrowserReturnValue = 0;
      var self = this;
      LastUsedMwLinkField = this;
      //store this MwLinkField
      if (this.options.usePlainPopup) {
        this.popupwin = window.open(chooserUrl, 'MwLinkFieldPlainPopup', 'width=800,height=600,scrollbars=1');
        this.popupwin.focus();
      } else {

        this.popupwin = new Boxy(iframe, {
          title: this.options.texts.ChooseButtonText,
          afterHide: function () {

          },
          modal: true,
          x: 50,
          y: jQuery(document).scrollTop() + 30
        });
      }

    },
    removeFile: function (event) {
      this.element.val('');
      this.element.change();
      var self = this;
      this.getMwLinkFieldDIV().find('._previewplace').slideUp('slow',
        function () {
          self.redraw();
        });

    },
    getLinkData: function (callback) {
      var self = this;
      $.post('/BE/MwLink/jsonGetInfo/' + this.element.val(), this.getOptionsForPreview(), callback, 'json');
    },
    updateIDFromPopupWindow: function (mwlink) {
      this.element.val(mwlink);
      //this.updateCopyrightField();
      $.post('/BE/MwLink/notifyTargetObject/', {
        'MwLink': mwlink
      });
      if (this.popupwin.hide)
        this.popupwin.hide();
      this.redraw();
    },
    log: function (msg) {
      if (!this.options.log) return;

      if (window.console && console.log) {
        // firebug logger
        console.log('MwLinkField: ' + msg);
      }
    },

    destroy: function () {
      $.Widget.prototype.destroy.apply(this, arguments); // default destroy
    }

  });

  $.ui.MwLinkFieldBase.subclass('ui.MwLinkField', {
    // 
    // getChooseButton: function() {
    //   return this._super().text('me too !'); 
    //        
    // },

  });


})(jQuery);
/* END */
