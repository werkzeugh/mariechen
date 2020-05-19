       // ugly global access:
var LastUsedIVGField;


// widget skeleton based on: http://bililite.com/blog/extending-jquery-ui-widgets/
// http://jqueryui.com/docs/Developer_Guide
 (function($) {
   
    $.ui.widget.subclass('ui.IVGFieldBase', {
        options: {
          log: 1,
          test: 5,
          availableTypes:{'image':'Image','video':'Video','gallery':'Gallery'},
          ShowChooseButton: true,
          texts: {
            RemoveButtonText: 'Remove File',
            AreYouSure: 'are you sure ?',
            etype_image: 'Image',
            etype_video: 'Video',
            etype_gallery: 'Gallery'
          },
          ThumbnailFormat: {
            format: 'CroppedImage',
            arg1: 90,
            arg2: 90
          }
        },
        currentval:{},
        _init: function() {
            this.redraw();
        },
        getIVGFieldDIV: function()
        {
            return this.element.next('.IVGFieldDIV');
        },
        loadcurrentval:function()
        {
          if(this.element.val())
          {
            this.currentval=$.evalJSON(this.element.val());
          }
        },
        savecurrentval:function()
        {
            this.element.val($.toJSON(this.currentval));
        },
        get_etype:function()
        {
          if(this.currentval.etype)
          {
            return this.currentval.etype;
          }
        },
        redraw: function() {
            var self = this;
            this.loadcurrentval();
            //remove existing stuff
            var existingDIV = this.getIVGFieldDIV();
            if (existingDIV)
            existingDIV.remove();
            //create new HTML
            this.newHTML = $("<div class='IVGFieldDIV'></div>");

            //buttons
            var $typechooser=this.getTypeChooserHTML();
            this.newHTML.append($typechooser);
            
            var etype=this.get_etype();
            if(!etype)
            {
              etype='image';
            }
            
             if (window.console && console.log) { console.log(etype);  }
            if(etype=='image')
            {
              this.redraw_image();
            }
            if(etype=='video')
            {
              this.redraw_video();
            }
            if(etype=='gallery')
            {
              this.redraw_gallery();
            }

            // $newHTML.find('div._buttons').append('<div>').append(this.getUploadButton());

            // if(this.options.ShowChooseButton)
            // {
            //   $newHTML.find('div._buttons').append('<div>').append(' ').append(this.getChooseButton());
            // }
            
            // if (this.element.val() > 0)
            // {
            //     //preview
            //     $newHTML.find('div._previewplace').html(this.getLoadingHTML());
                   
            //     $.post('/BE/IVG/jsonGetInfo/' + this.element.val(),this.getOptionsForPreview(),
            //     function(data) {
            //         $newHTML.find('div._previewplace').hide().html(self.createFilePreviewHTML(data)).fadeIn();
            //     },'json');

            // }
            
            
            this.element.after(this.newHTML);
        },
        redraw_image:function()
        {
          this.newHTML.append($('<div>i am the image</div>'));
        },
        redraw_video:function()
        {
          
        },
        redraw_gallery:function()
        {
          
        },
        getTypeChooserHTML:function()
        {
          var self=this;
          var $dd=$("<select class='etypechooser'>");
            
            $.each(this.options.availableTypes,function (key,value) {
                 var text=self.options.texts['etype_'+value];
                 var $option=$("<option value=\""+value+"\">"+text+"</option>");
                 $dd.append($option);
            });
            
            $dd.val(this.get_etype());
            
          $dd.change(function(){
            self.currentval.etype=$(this).val();
             if (window.console && console.log) { console.log(self.currentval);  }
            self.savecurrentval();
            self.redraw();
          });
          return $dd;
        },
        log: function(msg) {
            if (!this.options.log) return;

            if (window.console && console.log) {
                // firebug logger
                console.log('IVGField: ' + msg);
            }
        },

        destroy: function() {
          $.Widget.prototype.destroy.apply(this, arguments); // default destroy
        }

    });

    $.ui.IVGFieldBase.subclass ('ui.IVGField', {
            // 
            // getChooseButton: function() {
            //   return this._super().text('me too !'); 
            //        
            // },
      
    });


})(jQuery);
/* END */



