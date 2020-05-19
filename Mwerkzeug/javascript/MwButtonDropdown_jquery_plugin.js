    (function ($) {
        $.fn.MwButtonDropdown = function (settings) {
            settings = $.extend({
                preserveButtonTitle:false
                }, settings);
            
            
            
            var setName=function( $element,str)
            {
              if(!settings.preserveButtonTitle)
              {
                $('button', $element ).html(str+'&nbsp;<span class="caret"></span>');
              }
            };

            var setHiddenField=function( $element,str)
            {
                str=str.replace(/#/,'');
                $("input[type='hidden']", $element ).val(str);
            };
            
            var updateFromHiddenField=function($element)
            {
                 var currentKey='#'+getValueFromHiddenField($element);
                 $('.dropdown-menu > li > a',$element).each(function(){
                     if($(this).attr('href')==currentKey)
                     {
                       var newname=$(this).text().trim();
                       setName($element,newname);
                       return newname;
                     }
                  });
            };
            
            var getValueFromHiddenField=function($element)
            {
                return $("input[type='hidden']", $element ).val();
            };

            return this.each(
                function () {
                    var $element = $(this);

                    $('.dropdown-menu li a',$element).unbind('click').bind('click',function(e)
                    {
                        e.preventDefault();
                        //e.stopPropagation();
                        setHiddenField($element,$(this).attr('href'));
                        var newname=updateFromHiddenField($element);

                        var data={key:getValueFromHiddenField($element), value:newname};
                        $element.trigger($.Event( {type:'btn_change', target:$element[0]}),data);
                    });
                              
                    updateFromHiddenField($element);                            
                }
            );
            
            
        };
    })(jQuery);
    
    
        
    
    
    