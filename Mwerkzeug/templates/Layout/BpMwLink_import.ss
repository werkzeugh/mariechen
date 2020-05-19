<style>
 body {background:#FFF;margin:0px;font-size:13px}

  h2 {font-size:17px;margin-bottom:15px;color:#555;}
 
</style>

<h2>data-import</h2>


<div class='space'>
    <a href='#' class='button importdata'><span class='tinyicon ui-icon-check'></span>Import selected Data</a>
</div>

<% loop ImportData %>

<div style='margin:5px 20px'>
    
    <label for="cb_$Pos"><input type='checkbox' id="cb_$Pos" value='$Key' class='cb $Key' <% if Checked %>checked<% end_if %>
        >$Label:


        <textarea style='display:none' id='val_$Key'>$Value</textarea>


        <% if Image %>
        $Image.SetFittedSize(100,100)
        <% else %>
        $Value
        <% end_if %>
        
        </label>
</div>

<% end_loop %>



<script>

jQuery(document).ready(function($) {
          
    $('.cb').click(function(e)
    {
        var key=$(this).val();
        $('.cb.'+key+':checked').not(this).attr('checked',false); 
    });
          
    $('a.importdata').click(function(e){
        e.preventDefault();
        
        $('.cb:checked').each(function(){
            
            var key=$(this).val();
           var value=
           $('textarea',$(this).closest('div')).val();
    
           //find element in parent and set that val !
           if(key=='PictureID') {
               var field=parent.jQuery('input[name="fdata['+key+']"]');
               if(field)
               {
                    if (window.console && console.log) { console.log(field);  }
                   field.MwFileField('updateIDFromPopupWindow',value);
               }
           } else {
             if (window.console && console.log) { console.log('input[name="fdata['+key+']"]',value);  }
             parent.jQuery('*[name="fdata['+key+']"]').val(value);
           }
        });
        
        parent.popupWindow.hide();
        
    });

 });




function setMwLink(mwlink) {
    if (window.opener) {
      //popup version (tinymce)
      if (window.opener.LastUsedMwLinkField) {
        window.opener.LastUsedMwLinkField.updateIDFromPopupWindow(mwlink);
        window.close();
      } else alert('error: cannot find file-chooser field in parent window.');
    } else if (parent) {
      //boxy version
//      console.log(parent.LastUsedMwLinkField);
      parent.LastUsedMwLinkField.updateIDFromPopupWindow(mwlink);
    }

  }


$(document).ready(function() {

   

    $("a.mwlink").live('click', function(e) {
        e.preventDefault();
        var mwlink = $(this).attr('mwlink');
        setMwLink(mwlink);
    });


});

</script>
