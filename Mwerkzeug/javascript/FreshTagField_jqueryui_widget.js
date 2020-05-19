// ugly global access:
var LastUsedFreshTagField;


// widget skeleton based on: http://bililite.com/blog/extending-jquery-ui-widgets/
// http://jqueryui.com/docs/Developer_Guide
 (function($) {
   
    $.ui.widget.subclass('ui.FreshTagFieldBase', {
        options: {
          types: {}
        },
        tagCache: {},
        tags: undefined,
        popupwin:{},
        _init: function() {
            var self=this;
            this.element.change(function(){
              self.updateFromMainField();
            });
            this.redraw();
        },

        getFreshTagFieldDIV: function()
        {
            return this.element.next('.FreshTagFieldDIV');
        },
        updateFromMainField: function()
        {
          var taglist  = [];
          var self     = this;
          var mainval  = this.element.val();
          
          var taglist1 = mainval.split(/[, ]*tag_/);
          
          $.each(taglist1,function(idx,val){
            val=$.trim(val);
            if(val) taglist.push(val);
          });
          // console.log(taglist);
          this.onTagsLoaded(function(){
            self.updatePreview(taglist);
          });
          
          this.updateQuickedit(taglist);
          
        },
        updateQuickedit: function(tagList)
        {
           var qu=this.getQuickEdit();
           qu.val(tagList.join(' '));
        },
        getTagFromStr: function(str)
        {
          var ret={'key':str,'name':''};
          if(this.tagCache[str])
            ret.name=this.tagCache[str].Title;
          else
          {
            $.each(this.tags,function(typekey,tags4type){
              if(tags4type[str])
              {
                ret.name=tags4type[str];
              }
            });
          }          
          
          return ret;
        },
        updatePreview: function(tagList)
        {
           var self=this;
           var $pp= $('._previewplace',this.getFreshTagFieldDIV());
           $pp.html('');
          
          $.each(tagList,function(idx,tagstr)
          {
            var tag=self.getTagFromStr(tagstr);
            if(tag)
            {
              var $span=$('<span class="freshtag"><b>'+tag.key+'</b>&nbsp;'+tag.name+'</span>');
              $.getJSON('/BE/FreshTag/jsonTagInfo', {'key':tagstr},
                function(data) {
                  if(data && data.Title)
                  {
                   self.tagCache[data.TagKey]=data;
                   $span.html("<b>"+data.TagKey+"</b>&nbsp;"+data.Title);
                  }
                 });
               $pp.append($span);
            }
          });
        },
        getTagChooserHtml: function()
        {
          var self=this;
          var $chooser4Type;
          var $html=$('<div class="FreshTagChooser">');
          var $closebutton=$('<a href="#" class="button closebutton"><span class="tinyicon ui-icon-close"></span>close</a>').click(function(e){
            e.preventDefault();
            $(this).closest('._chooseplace').slideUp();
          });
          
          $html.append($closebutton);
          
          $.each(this.options.types, function(typekey,label) { 
            $chooser4Type=$('<div></div>');
            $chooser4Type.append('<div><b>'+label+':</b></div>');
            
            $.each(self.tags[typekey], function(tagkey,tagname) { 
               $chooser4Type.append(' <a href="'+tagkey+'" class="freshtag"><b>'+tagkey+'</b> '+tagname+'</a> ');
            });

             $createButton=self.getTagCreateButton(typekey);

             $chooser4Type.append($('<div>').append($createButton));            
            
            $html.append($chooser4Type);
          });
          
          return $html;
        },
        getTagCreateButton:function(typekey)
        {
            var self=this;
            var $button=$("<a href='#' class='button createnewtag'><span class='tinyicon ui-icon-plus'></span>create new tag</a>");
            $button.click(function(e)
          {
                e.preventDefault();
                //create new tag form:
                var $form=$("<form>");
                $form.append("<label>Key:</label>");                
                $form.append("<input type='text' name='tagkey' value='' style='width:40px'>");                
                $form.append("<input type='hidden' name='tagtype' value='"+typekey+"' >");                
                $form.append("&nbsp;<label>Title:</labe>");                
                $form.append("<input type='text' name='tagname' value='' style='width:100px'>");                
                $form.append("&nbsp;<a href='#' class='button createtag'><span class='tinyicon ui-icon-check'></span></a>");                
                $form.append("&nbsp;<a href='#' class='button cancel'><span class='tinyicon ui-icon-cancel'></span></a>");                
                $('a.cancel',$form).click(function(e){
                      e.preventDefault();
                      $form.fadeOut();
                });
                $('a.createtag',$form).click(function(e){
                      e.preventDefault();
                      $form.parent().load('/BE/FreshTag/ajaxCreateTagInline',$form.serialize(),function(){
                        
                        //make new tags clickable:
                           
                        $('a.freshtag',this).live('click',function(e)
                        {
                          e.preventDefault();
                          self.addTag2QuickEdit($(this).attr('href'));
                        });

                      });
                });
                $(this).after($('<div style="padding:5px 0px">').append($form));
          });
            return $button;

        },
        redraw: function() {
            var self = this;
            
            //remove existing stuff
            var existingDIV = this.getFreshTagFieldDIV();
            if (existingDIV) {
              existingDIV.remove();
            }
            //create new HTML
            var $newHTML = $("<div class='FreshTagFieldDIV'></div>");
            $newHTML.append("<div class='_editplace'></div>");
            $newHTML.append("<div class='_previewplace'></div>");
            $newHTML.append("<div class='_chooseplace'></div>");
           
            var $input =$('<input class="quicktagedit">');
             
            $input.change(function(){ 
                self.quicktageditChanged(this);
              });
            $button=$('<a href="#" class="quicktagchoose button"><span class="tinyicon ui-icon-help"></span></a>');
            $button.click(function(e){ 
                e.preventDefault();
                self.showQuickTagChooser();
              });
            
            
            $('._editplace',$newHTML).append($input);
            $('._editplace',$newHTML).append('&nbsp;');
            $('._editplace',$newHTML).append($button);
            this.element.after($newHTML);
            this.element.change(); // trigger preview of tags

        },
        quicktageditChanged: function(el)
        {
          //console.log(el);
          //console.log('quicktagedit was changed');
          //change master fields
          
          // split quicktag 
          var val=$(el).val();
          //console.log(val);
          
          var foundTags=val.split(/[, ]/);

          //update master field
          var mainval='';
          $.each(foundTags, function(i,tag)
          {
            if(tag.length>0)
            {
              mainval+='tag_'+tag+' ';
            }
          });
          this.element.val(mainval);
          this.element.change();
          
        },
        onTagsLoaded:function(callback)
        {
           var self=this;
           if(!self.tags)
           {
            $.getJSON('/BE/FreshTag/jsonTagList', {'types':this.options.types},
            function(data) {
               self.tags=data;
               callback();
             });
           }
           else
           {
             callback();
           } 
        },
        getQuickEdit:function()
        {
          return $('.quicktagedit',this.getFreshTagFieldDIV());
        },
        addTag2QuickEdit: function(tag)
        {
          var inp = this.getQuickEdit();
          inp.val(inp.val()+' '+tag);
          inp.change();
        },
        showQuickTagChooser:function()
        {
          //get data
          var self=this;
          this.onTagsLoaded(function()
          {
            var mydiv=$('._chooseplace',self.getFreshTagFieldDIV());
            mydiv.hide();
            mydiv.html(self.getTagChooserHtml());
            
            $('a.freshtag',mydiv).click(function(e)
            {
              e.preventDefault();
              self.addTag2QuickEdit($(this).attr('href'));
            });
            
            mydiv.slideDown();
          });
        },
        getOptionsForPreview: function()
        {
            //set options to pass to the preview-json-url, to get a appropriate preview-image
            
            var opts = {
                Thumbnail: this.options.ThumbnailFormat
            };
            return opts;
        },
        createLinkPreviewHTML: function(data)
        {
            var $html=$("<div class='_preview'>");
            // $previewbuttons=$("<div class='_previewbuttons'>").append(this.getRemoveButton());
            // $html.append($previewbuttons);
            
            $html.append("<div style='margin:5px 0px'><span class='_FreshTag' style='background:#ddd;border:1px solid #999;padding:2px 10px;width:auto;margin-right:auto;line-height:12px;font-size:12px' title='"+this.element.val()+"'><b>" + data.Title + "</b> " + data.ReadableUrl + data.PreviewHtml + "</span></div>");
            return $html;
        },
        getChooseButton: function() {
          var self = this;
          return button = $("<a href='#' class='button _choose'><span class='tinyicon ui-icon-folder-open'></span><i>" + this.options.texts.ChooseButtonText + "</i></a>").click(function(event) {
            event.preventDefault();
            self.chooseTarget();
          });
        },
        chooseTarget: function() {
            var chooserUrl = '/BE/FreshTag/chooser';
            if(this.options.ChooserUrl)
            {
              chooserUrl=this.options.ChooserUrl;
            }
            
            chooserUrl+='?FreshTag=' + escape(this.element.val());
            
            var iframe = $('<iframe src="' + chooserUrl + '" style="width:700px;height:500px" ></iframe>');
            // FileBrowserReturnValue = 0;
            var self = this;
            LastUsedFreshTagField = this;
            //store this FreshTagField
            // var popupwin = window.open(chooserUrl, 'FreshTagFieldPopup', 'width=800,height=500,scrollbars=1');
            // popupwin.focus();

            this.popupwin = new Boxy(iframe, {
                            title: this.options.texts.ChooseButtonText,
                            afterHide: function() {
                              
                            },
                            modal: true,
                            x: 50,
                            y:jQuery(document).scrollTop() + 30
                        });
        },
        removeFile: function(event) {
            this.element.val('');
            this.element.change();
            var self=this;
            this.getFreshTagFieldDIV().find('._previewplace').slideUp('slow',
            function() {
                self.redraw();
            });
            
        },
        getLinkData: function(callback) {
          var self=this;
          $.post('/BE/FreshTag/jsonGetInfo/' + this.element.val(),this.getOptionsForPreview(),callback,'json');
        },
        updateIDFromPopupWindow: function(FreshTag)
        {
            this.element.val(FreshTag);
            //this.updateCopyrightField();
            $.post('/BE/FreshTag/notifyTargetObject/',{'FreshTag':FreshTag});
            this.popupwin.hide();
            this.redraw();
        },
        log: function(msg) {
            if (!this.options.log) return;

            if (window.console && console.log) {
                // firebug logger
                console.log('FreshTagField: ' + msg);
            }
        },

        destroy: function() {
          $.Widget.prototype.destroy.apply(this, arguments); // default destroy
        }

    });

    $.ui.FreshTagFieldBase.subclass ('ui.FreshTagField', {
            // 
            // getChooseButton: function() {
            //   return this._super().text('me too !'); 
            //        
            // },
      
    });


})(jQuery);
/* END */



