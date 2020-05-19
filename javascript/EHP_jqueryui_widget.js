

// widget skeleton based on: http://bililite.com/blog/extending-jquery-ui-widgets/
// http://jqueryui.com/docs/Developer_Guide
 (function($) {
    
    
    $.ui.widget.subclass('ui.EHPBase', {
        options: {
          types: {},
          type:'listing',
          columnChooser:0,
          showCheckboxes:0,
          allowMultiEdits:1,
          submitOnEnter:1,
          dragdrop_sort:0,
          use_bootstrap_css:1,
          texts:{
            'Testme': 'testme_en',
            'AreYouSure': 'are you sure ?'
          }
        },
        loadCount:0,
        currentMode:'show',
        currentFilterData:{},
        currentSortData:{},
        currentColumnData:{},
        _init: function() {
          
            this.widgetEventPrefix='ehp';
            this.element.addClass('ehpdiv');            

            if(this.options.dragdrop_sort)
            {
              this.element.addClass('ehp-dragdrop_sort');            
            }

            if(this.options.type=='listing')
            {
                if(this.options.defaultSortBy) this.currentSortData=this.options.defaultSortBy;             
                if(this.options.defaultFilterData) this.currentFilterData=this.options.defaultFilterData;             
                this.listingInit();
                if(!this.options.noInitialLoad)
                {
                  this.loadList();        
                }
                       
            }
            var self=this;
            
            
            $(this.element).on('click','a.EHP_additem',function(e){
                e.preventDefault();
                self.addItem(this);
            });
            $(this.element).on('click','a.EHP_saveitem',function(e){
                e.preventDefault();
                $(this).html('<img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">');
                $(this).removeClass('EHP_saveitem');
                self.saveItem(this);
            });
            $(this.element).on('click','a.EHP_cancelitem',function(e){
                e.preventDefault();
                self.reloadItem(this);
            });
             $(this.element).on('click','a.EHP_deleteitem',function(e){
                e.preventDefault();
                if(confirm(self.options.texts.AreYouSure))
                {
                 self.deleteItem(this);
                }
            });
            
            $(this.element).on('click','a.EHP_duplicateitem',function(e){
                e.preventDefault();
                self.duplicateItem(this);
            });
            $(this.element).on('click','a.EHP_toggle_hidden',function(e){
                e.preventDefault();
                self.toggleHidden(this);
            });
            $(this.element).on('click','a.EHP_toggle_archive',function(e){
                e.preventDefault();
                self.toggleArchive(this);
            });
            $(this.element).on('click','a.EHP_edititem',function(e){
                e.preventDefault();
                self.editItem(this);
            });
            if(self.options.whole_row_clickable)
            {
              $(this.element).on('click','tr td:not(.ehp_rowbuttons,.checkbox)',function()
              {
                //find first button and click it, if its classname contain the term 'edit'
                var clickedbutton= $('td.ehp_rowbuttons a:first',$(this).closest('tr'));
                if(clickedbutton.length>0)
                {
                  if(clickedbutton.attr('class').match(/(edit|show|view)/))
                  {
                  
                    clickedbutton.click();
                  }
                
                }
              });
            }

            if(self.options.whole_row_dblclickable)
            {
              $('tr td:not(.ehp_rowbuttons,.ehp-checkbox)',this.element).live('dblclick',function()
              {
                //find first button and click it, if its classname contain the term 'edit'
                var clickedbutton= $('td.ehp_rowbuttons a:first',$(this).closest('tr'));
                if(clickedbutton.length>0)
                {
                  if(clickedbutton.attr('class').match(/(edit|show|view)/))
                  {
                    if(clickedbutton.attr('href').length>1)
                      window.location=clickedbutton.attr('href');
                    else
                      clickedbutton.trigger('click');
                  }
                
                }
              });
            }

  
            $('tr',this.element).live('mouseenter',function()
            {
                if(self.currentMode=='show')
                {     
                  $('tr.active',self.element).removeClass('active');
                  $(this).addClass('active');
                }

            });
            this._trigger("init",{},{widget:this});
            
        },

        listingInit: function() {
        },
        post_to_url: function(url, params, target) {
            target = target || "_blank"; 
            var form = $('<form />').hide();
            form.attr('action', url)
                .attr('method', 'POST')
                .attr('target', target);
                
            function addParam(name, value, parent) {
                var fullname = (parent.length > 0 ? (parent + '[' + name + ']') : name);
                if(value instanceof Object) {
                    for(var i in value) {
                        addParam(i, value[i], fullname);
                    }
                }
                else $('<input type="hidden" />').attr({name: fullname, value: value}).appendTo(form);
            };

            addParam('', params, '');

            $('body').append(form);
            form.submit();
        },
        setMode: function(newmode)
        {
            this.currentMode  =newmode;
            $('tr.err_row',this.element).remove();
            if(newmode=='edit')
            {
              $('.btn',this.element).not('.active *').css('visibility','hidden');
              $('tr:not(.active) .ehp_rowbuttons *',this.element).css('visibility','hidden');
              
              $('tr.editmode input:first',this.element).focus();
              $('tr.editmode .focus-on-load',this.element).focus();
               if (window.console && console.log) { console.log('call mwfilefield',null);  }
              $('.MwFileField',this.element).MwFileField();
              
            }
            if(newmode=='show')
            {
              $('.btn',this.element).not('.active *').css('visibility','visible');
              $('tr:not(.active) .ehp_rowbuttons *',this.element).css('visibility','visible');
            }
            
            
        },
        addItem:function(caller) {
            var url=this.options.baseurl;
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'action':'add'
                    };

            var self=this;
            $.post(url,params,function(html){
                
                var lastrow=$('.ehptable tr.actionfooter',self.element);
              
                var newrow=$(html);
                if(lastrow.length)
                 lastrow.before(newrow);
                else
                 $('.ehptable',self.element).append(newrow);

                self.setupForm(newrow);
                
                self.setMode('edit');
            });  

        },
        editItem:function(caller) {
            var url=this.options.baseurl;
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'action':'edit',
                        'dbid':$(caller).closest('tr').attr('dbid')
                    };

            var self=this;
            
            if(! (this.options.allowMultiEdits) ) //close other rows in editmode 
            {
              $('tr.editmode td:first',this.element).each(function(){
                   self.reloadItem(this);
              });
            }
            
            $.post(url,params,function(html){
                var tr=$(caller).closest('tr');
                tr.replaceWith(html);
                
                self.setupForm(tr);
                
                if (typeof setup_datepickers !== 'undefined') {
                  setup_datepickers();
                }
                
                self.setMode('edit');
                // $('.ehptable td.ehp-checkbox input',self.element).remove();
            });  

        },
        setupForm:function(el)
        {
          if(this.options.submitOnEnter) {

          /* setup submit on enter: */
          $('input[type="text"]',el).on('keyup',function(e){
            if(e.keyCode == 13)
            {
              $('a.EHP_saveitem',$(this).closest('tr')).trigger('click');
            }
          });
          
          }
            
        },
         deleteItem:function(caller,custom_args) {
            var url=this.options.baseurl;
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'custom_options':custom_args,
                        'action':'delete',
                        'dbid':$(caller).closest('tr').attr('dbid')
                    };

            var self=this;
            $.post(url,params,function(html){
                $(caller).closest('tr').fadeOut('slow',function(){
                    $(this).remove();
                    self.setMode('show');
                    self._trigger("listchange",{},{widget:this});
                });
            });  

        },
        toggleHidden:function(caller) {
            var url=this.options.baseurl;
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'action':'toggle_hidden',
                        'dbid':$(caller).closest('tr').attr('dbid')
                    };

            var self=this;
            $.post(url,params,function(data){
                if(data.hidden)
                {
                    $(caller).closest('tr').addClass('ishidden');
                }
                else
                {
                    $(caller).closest('tr').removeClass('ishidden');
                }
            },'json');  
        },
        toggleArchive:function(caller) {
            var url=this.options.baseurl;
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'action':'toggle_archive',
                        'dbid':$(caller).closest('tr').attr('dbid')
                    };

            var self=this;
            $.post(url,params,function(data){
                if(data.archived)
                {
                    $(caller).closest('tr').addClass('archived');
                }
                else
                {
                    $(caller).closest('tr').removeClass('archived');
                }
            },'json');  
        },
        duplicateItem:function(caller) {
          var url=this.options.baseurl;
          params={
                      'options':this.cleanUpJson4Ajax(this.options),
                      'action':'duplicate',
                      'dbid':$(caller).closest('tr').attr('dbid')
                  };

          var self=this;
          $.post(url,params,function(html){
              $(caller).closest('tr').after($(html));
              self._trigger("listchange",{},{widget:this});
          });  
        },
        reloadItem:function(caller) {
            caller=this.resolveCaller(caller);
            var url=this.options.baseurl;
            var dbid=$(caller).closest('tr').attr('dbid');
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'action':'show',
                        'dbid':dbid
                    };

            var self=this;
            $(caller).closest('tr').css('background','yellow').fadeOut().fadeIn();
            $.post(url,params,function(html2){
                var tr=$(caller).closest('tr');
                tr.replaceWith(html2);
                self.setMode('show');
                self.redrawCheckbox(dbid);
                self._trigger("listchange",{},{widget:this});
            });  
        },
        saveItem:function(caller) {
            var url=this.options.baseurl;
            var dbid=$(caller).closest('tr').attr('dbid');
            params={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'formdata':$(caller).closest('form').serialize(),
                        'action':'save'
                    };

            var self=this;
            $.post(url,params,function(html){
              
                $('tr.err_row',$(caller).closest('table')).remove();
                self._trigger("listchange",{},{widget:this});
                if(html.match(/SAVE_OK/))
                {
                    $(caller).closest('tr').replaceWith(html);
                    self.setMode('show');
                    self.redrawCheckbox(dbid);
                }
                else
                {
                  var errorRow=$("<tr class='err_row'></tr>");
                  var colspan=$('thead tr:last th',$(caller).closest('table')).length;
                  var errorTD=$("<td colspan='"+colspan+"'></td>");
                  errorTD.html(html);
                  errorRow.append(errorTD);
                  
                  $(caller).closest('tr').before(errorRow);
                  
                }
                
            });  

        },
        cleanUpJson4Ajax: function(arg)
        {
          //removes functions from all top-level-keys of argument
          // converts top-level-values for true & false to 1 and 0
          if(arg)
          {
            var arg2={};
            $.each(arg,function (key,val)
            {
              if(!$.isFunction(val))
              {
                if(val === true)
                  arg2[key]=1;
                else if(val === false)
                  arg2[key]=0;
                else if(val === null)
                  arg2[key]='';
                else
                  arg2[key]=val;
              }
            });
            return arg2;
          }
          else
           return arg;
        },
        loadList: function(arg) {
            
            var url=this.options.baseurl;
            var self=this;
            var postparams;
            var mypostparams={
                        'options':this.cleanUpJson4Ajax(this.options),
                        'loadcount':this.loadCount++,
                        'action':'listing'
                    };
            mypostparams.sortby=this.currentSortData;
            mypostparams.columndata=this.currentColumnData;
            mypostparams.filter=this.currentFilterData;

            if(arg && arg.postparams)
            {
              postparams=$.extend({},  mypostparams, arg.postparams);
            }
            else
              postparams=mypostparams;
            
            if($('table.ehptable',this.element).length>0)
            {
              $('table.ehptable tbody',this.element).empty();
              var colspan=$('.ehptable thead tr:last th',this.element).length-1;
              $('table.ehptable tbody',this.element).after('<tr><td colspan="'+colspan+'"><center><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></center></td></tr>');

            }
            else
              this.element.html('<center><img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></center>');
            
            
            if(this.options.preloadResultCounts)
            {
              $tc=$('.ehp-totalcount',this.element);
              $tc.html('<img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading">');
              postparams.action='totalcount';
              $tc.load(url,postparams);
              postparams.action='listing';
            }
            
            this.element.load(url,postparams,function(){
              self.afterloadList();
            });  
            

        },
        resolveCaller:function(caller)
        {
          
          if(!isNaN(parseFloat(caller)) && isFinite(caller))
          {
            var newcaller=$("tr[dbid=\'"+caller+"\']",self.element)[0];
            return newcaller;
          }
          return caller;
        },
        redrawCheckbox:function(row)
        {
          row=this.resolveCaller(row);
          if(this.options.showCheckboxes)
          {
             $('td.ehp-checkbox',row).each(function() {
               if($('input',this).length == 0)
               {
                 $(this).append('<input type="checkbox" class="cb">');
               }
             });
          }
        },
        handleCheckboxes:function()
        {
          var self=this;
          
          if(this.options.showCheckboxes && $("tr[dbid]",self.element).length>0)
          {
             $('.ehptable td.ehp-checkbox',this.element).each(function() {
               if($('input',this).length == 0)
               {
                 $(this).append('<input type="checkbox" class="cb">');
               }
             });
             
             var colspan=$('.ehptable tr:last td',this.element).length-1;
  
             $('.ehptable',this.element).append(
               '<tr class="plain actionfooter"><td class="ehp-checkbox"><input type="checkbox" class="check_all" title="check all/none"></td><td colspan="'+colspan+'" class="ehp_actions"></td></tr>'
             );

             $('.ehptable th.ehp-checkbox ',this.element).append('<input type="checkbox" class="check_all" title="check all/none">');
             
             if(this.options.checkboxActions)
             {
               $('.ehptable td.ehp_actions',this.element).append(this.getActionDD());
             }

             $('.ehptable .check_all',this.element).click(function(e){
                  if($(this).is(':checked'))
                  {
                    $('.ehptable tr .ehp-checkbox input',self.element).attr('checked','checked');
                  }
                  else
                  {
                    $('.ehptable tr .ehp-checkbox input',self.element).removeAttr('checked');                    
                  }
             });
             
          }
        },
        getActionDD:function()
        {
          var self=this;
          var dd=$('<select class="form-control set-inline">');
          
          dd.append('<option value="">actions:</option>');
          
          var defaultActions={'delete':{'label':'delete'}};          
          $.each(this.options.checkboxActions,function(key,action)
          {
            if(action===true)
            {
              action=defaultActions[key];
            }
            dd.append('<option value="'+key+'"> - '+action.label+'</option>');
          });

          // start action:
          dd.change(function(e){

            var dd=this;
            var action=$(dd).val();
            if(action)
            {
              var selectedIds=[];
              //get all selected ids
              $('.ehptable td.ehp-checkbox input:checked',self.element).each(function(){
                selectedIds.push($(this).closest('tr').attr('dbid'));
              });
              
              var angularPromise=null;
              if(selectedIds.length==0)
              {
                alert('you have to select some items first');
                $(dd).val(0);
              }
              else
              {
                
                
                
                var params={
                            'options':self.cleanUpJson4Ajax(self.options),
                            'action':'multi_action',
                            'multiaction':action,
                            'ids':selectedIds
                        };
                        
                if(self.options.checkboxActions_beforeSubmit)
                {
                  var res=self.options.checkboxActions_beforeSubmit.apply(this, [params,dd]);
                  if ('function' === typeof res.then) {
                    angularPromise=res;
                  }

                  if(res===false)
                  {
                    $(dd).val(0);
                    return false;
                  }
                }
                else
                {
                  if(action=='delete')
                  {
                    if(!confirm("are you sure ?"))
                    {
                      $(dd).val(0);
                      return false;
                    }
                  }
                  
                }
                
                
                $(dd).parent().find('.ehp_actionresult').remove();
                var txt="performing action '"+action+"' on "+selectedIds.length+" item(s) ...";
                var infospan=$('<span class="ehp_actionresult"> '+txt+' <img src="/Mwerkzeug/images/loading.gif" width="16" height="11" alt="Loading"></span>');
                infospan.insertAfter(dd);
                var url=self.options.baseurl;
                
                $(dd).attr('disabled',1);                
                
                
                if(angularPromise) {
                  angularPromise.then(function(data){

                    data.payload.then(function(data2) {
                      if (window.console && console.log) { console.log('➜➜ got data from showModal',data2);  }
                        params.payload=data2;
                        infospan.load(url,params,function() {
                          $(dd).val(0);
                          $(dd).removeAttr('disabled');                
                        });
                    },function(faildata){
                     $(dd).val(0);
                     $(dd).removeAttr('disabled');   
                     infospan.empty();  
                   });

                  });

                } else {
                  infospan.load(url,params,function()
                  {
                    $(dd).val(0);
                    $(dd).removeAttr('disabled');                
                  });
                }
                   
                
              }
            }
             return true; 
              
          });
          
          return dd;
        },
        handlePaging:function()
        {
          var self=this;
          $('.ehptable .ehp_pagination a',this.element).click(function(e) {
             e.preventDefault();
             var pagenum=$(this).attr('href');
             pagenum=pagenum.replace(/[^0-9]/g, '');
             self.loadList({'postparams':{'start':pagenum}});
          });
        },
        setCurrentFilterData:function(data) {
          this.currentFilterData=data;
        },
        getCurrentSortData:function()
        {
          return this.currentSortData;
        },
        getCurrentColumnData:function()
        {
          return {'fieldnames':$('.ehp-active-columns', this.element).val()};          
        },
        handleFilter:function()
        {
          var self=this;
        
          var filterform;
          if(this.options.filterform_selector)
          {
            filterform=$(this.options.filterform_selector);
          }
          else
          {
            filterform=$('form.ehpform',self.element);
          }
          
          $('.submit_filter',filterform).unbind('click.ehp').bind('click.ehp',function(e) {            
             e.preventDefault();
             self.currentFilterData=self.serializeForm(filterform);
             self.loadList();
          });


          // $('form.ehpform tr.editmode input',this.element).live('keypress',function(e){
          //         if(e.which == 13){
          //           e.preventDefault();
          //           self.saveItem(this);
          //         }
          //       });
          
          $('input',filterform).unbind('keypress.ehp').bind('keypress.ehp',function(e){
            if(e.which == 13){
              e.preventDefault();
              $('.submit_filter',filterform).trigger('click');
            }
          });
        },
        handleColumnChooser:function()
        {
          var self=this;
          $('.ehptable .ehp-columnchooser-apply',this.element).on('click',function(e)
          {
             e.preventDefault();
             var data=$("input[type='hidden']",$(this).closest('.MwCheckboxDropdown')).val();
             self.currentColumnData.fieldnames=data;
             self.loadList();
          });
        },
        handleSorting:function()
        {
          var self=this;
          $('.ehptable th.sortable_col',this.element).each(function(){
            self.showSortColumnMarker(this);
            
            $(this).click(function(e){
              
              var newsort=$(this).data('sort')==='asc'?'desc':'asc';
              $(this).data('sort',newsort);
              if (!e.shiftKey)
              {
                self.currentSortData={};
                self.showSortColumnMarker(this);
                self.loadList();
              }
              else
              {
                self.showSortColumnMarker(this);
              }
             
            });
          });
          
        },
        showSortColumnMarker:function(th)
        {
          var iconclass;
          $('.sortlabel',th).remove();
          if($(th).data('sort')==='asc')  iconclass='icon-arrow-down';
          if($(th).data('sort')==='desc')  iconclass='icon-arrow-up';
          if(iconclass)
          {
            var count = 0;
            var key=$(th).data('fieldname');
            this.currentSortData[key]=$(th).data('sort');

            for (i in this.currentSortData) {
                if (this.currentSortData.hasOwnProperty(i)) {
                    count++;
                    if(key==i) break;
                }
            }
            
            if(count==1) count='';
            
            $(th).append("<span class='sortlabel label label-success pull-right'><i class='"+iconclass+" icon-white'></i>"+count+"</span>");
          }
          
        },
        serializeForm: function(e)
           {
              var o = {};
              var a = e.serializeArray();
              $.each(a, function() {
                this.name=this.name.replace(/filter\[(.*)\]/,'$1');
                  if (o[this.name]) {
                      if (!o[this.name].push) {
                          o[this.name] = [o[this.name]];
                      }
                      o[this.name].push(this.value || '');
                  } else {
                      o[this.name] = this.value || '';
                  }
              });
              return o;
           },
        afterloadList:function()
        {
          if($('table.ehptable td.ehp_reloadmarker',this.element).length>0) //reload list, if reloadmarker is present
          {
             this.loadList();      
          }
          this.handleCheckboxes();
          this.handlePaging();
          this.handleFilter();
          this.handleSorting();
          this.handleColumnChooser();
          if(this.options.width)
          {
            $('table.ehptable',this.element).width(this.options.width);
          }

          if(this.options.dragdrop_sort)
          {
            // Return a helper with preserved width of cells
            var sortHelper = function(e, ui) {
                ui.children().each(function() {
                    $(this).width($(this).width());
                });
                return ui;
            };
            var self=this;
            $("table.ehptable tbody",this.element).sortable({
              items: "tr[dbid]",
              handle:'.sortgrip',
              helper: sortHelper,
              stop: function(event, ui) { self.afterSort(event,ui); }
            });            
          }
          if(this.options.afterloadList)
          {
              arguments={};
              this.options.afterloadList.apply(this, arguments);
          }
          
          this._trigger("listchange",{},{widget:this});
            
        },
        afterSort:function(event,ui)
        {
          var dbid=ui.item.attr('dbid');
          //save whole list for resorting

          var url=this.options.baseurl;
          var idlist=[];
          $(".ehptable tr[dbid]",this.element).each(function(){
            idlist.push($(this).attr('dbid'));
          });
          params={
                      'options':this.cleanUpJson4Ajax(this.options),
                      'action':'savesort',
                      'ids':idlist
                  };

          var self=this;
          $.post(url,params,function(html){
               if (window.console && console.log) { console.log('sorting finished');  }
               self._trigger("sortfinish",{},{widget:this});
               self._trigger("listchange",{},{widget:this});
                });  
           
           
           
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

    $.ui.EHPBase.subclass('ui.EHP', {
            // 
            // getChooseButton: function() {
            //   return this._super().text('me too !'); 
            //        
            // },
    });
    
    




})(jQuery);



/* END */



