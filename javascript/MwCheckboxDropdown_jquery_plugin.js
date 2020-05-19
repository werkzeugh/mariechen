    (function ($) {
        $.fn.MwCheckboxDropdown = function (settings) {
            settings = $.extend({
                preserveButtonTitle:false
            }, settings);
            
            var setName=function( _this,str)
            {
              if(!settings.preserveButtonTitle)
              {
                $('button', _this ).html(str+'&nbsp;<span class="caret"></span>');
              }
            };

            var setHiddenField=function( _this,str)
            {
                str=str.replace(/#/,'');
                $("input[type='hidden']", _this ).val(str);
            };
            
            var updateFromHiddenField=function(_this)
            {
                 var val=getValueFromHiddenField(_this);
                 var names=[];
                 var arr=val.split(',');
                 $.each(arr,function(key,value){
                     var cb=$('input[value="'+value+'"]',_this);
                     cb.attr('checked',1);
                     names.push(cb.closest('li').text().trim()); 
                  });
                 setName(_this,names.join(', '));
                                
            };
            
            var getValueFromHiddenField=function(_this)
            {
                return $("input[type='hidden']", _this ).val();
            };


            var setHiddenFieldFromDropdowns=function(_this)
            {
                var newval=[];
                $('.dropdown-menu li a input:checked',_this).each(function(){
                    newval.push($(this).val());
                });
                
               $("input[type='hidden']", _this ).val(newval.join(','));
               
            };
            
            return this.each(
                function () {
                    var _this = $(this);

                    $('.dropdown-menu li a input',_this).unbind('change').bind('change',function(e){
                        setHiddenFieldFromDropdowns(_this);
                        updateFromHiddenField(_this);
                    });
                
                    $('.dropdown-menu li a input',_this).unbind('click').bind('click',function(e){
                        e.stopPropagation();
                        
                    }); 

                    $('.dropdown-menu li a',_this).unbind('click').bind('click',function(e)
                    {
                        e.preventDefault();
                        e.stopPropagation();
                        $('input',this).trigger('click').trigger('change');
                    });
                                                          
                    updateFromHiddenField(_this);
                }
            );
            
            
        };
    })(jQuery);
    
    
        
    
    
    