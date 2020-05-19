<div class='space CElementTopButtons'>
  <% if SortMode %>
   <div style='margin-left:20px'><strong><% _t('SortHeadline','Sortieren') %>:</strong>
     <div class='space'> 
       <% _t('SortText','Sie können nun die Absätze mittels drag & drop verschieben.') %>
        
     </div>  
   <a href='#' class='button saveSortCElements' ><span class='tinyicon ui-icon-check'></span><% _t('SaveLink','Speichern') %></a>
   <a href='#' class='button cancelSortCElements' ><span class='tinyicon ui-icon-close'></span><% _t('CancelLink','Abbrechen') %></a>
   </div>
  <% else  %>
           <a href='#' class='button sortCElements celement_sortlink' ><span class='tinyicon ui-icon-arrow-2-n-s'></span><% _t('sortlink','sortieren') %></a>
  <% end_if %>
</div>

<ul class='CElementListUL bootstrap' id='CElementListUL-$Fieldname'>
   <% loop Items %>
    <li id='$CssID' class='celement-li'>
          $BackendItemHtml
    </li>
  <% end_loop %>
</ul>

<% if SortMode %>
<% else %>
<div class='space'>
  <a href='#' class='button addCElement' ><span class='tinyicon ui-icon-plus'></span><% _t('addNewCElement','add Item') %></a>
</div>
<% end_if %>

<script type="text/javascript" charset="utf-8">
  
  $(document).ready(function() {
    
    $('a.addCElement').click(function (event) {
      event.preventDefault();
      $(this).closest('.CElementList').CElement('additem');
    });

    $('a.sortCElements').click(function (event) {
      event.preventDefault();
      $(this).closest('.CElementList').CElement('sortelements');
    });

    $('a.cancelSortCElements').click(function (event) {
      event.preventDefault();
      $(this).closest('.CElementList').CElement('list');
    });

    $('a.saveSortCElements').click(function (event) {
      event.preventDefault();
      $(this).closest('.CElementList').CElement('savesortelements');
    });

    CElement_edit=function(element)
    {
      $(element).closest('.CElementList').CElement('edit', $(element).closest('li') );
      return false;
    };

    CElement_duplicate=function(element)
    {
      $(element).closest('.CElementList').CElement('duplicate', $(element).closest('li') );
      return false;
    };
    
    CElement_remove=function(element)
    {
      $(element).closest('.CElementList').CElement('remove', $(element).closest('li') );
      return false;
    };

    CElement_settings=function(element)
    {
        existings=$(element).parent().find('.celement_settings');
        if(existings.length)
        {
            $(element).parent().find('.celement_settings').slideUp(200,function(){ $(this).remove();});
        }
        else
        {
            var newdiv=$("<div class='celement_settings'></div>").hide();
            var txt=$(element).closest('.CElement').hasClass('hidden')?'publish':'hide';
            txt+=' now';
            newdiv.append("<a href='#' class='button' onClick='return CElement_hide_unhide(this)'><span class='tinyicon ui-icon-cancel'></span>"+txt+"</a>");
            newdiv.append("<div class='space'>hide on <input type='text'></div>");
            newdiv.append("<div class='space'>publish on <input type='text'></div>");
      
            txt=$(element).closest('.CElement').hasClass('archived')?'unarchive':'archive';
            txt+=' now';
            newdiv.append("<a href='#' class='button' onClick='return CElement_archive_unarchive(this)'><span class='tinyicon ui-icon-lightbulb'></span>"+txt+"</a>");
            $(element).after(newdiv);
            //      $('a',newdiv).wrap('<div></div>');
            newdiv.slideDown();    
            //$(element).closest('.CElementList').CElement('remove', $(element).closest('li') );
        }
        return false;
    };


    CElement_hide_unhide=function(element)
    {
      $(element).closest('.CElementList').CElement('toggle_hide', $(element).closest('li') );
      return false;
    };

    CElement_archive_unarchive=function(element)
    {
      $(element).closest('.CElementList').CElement('toggle_archive', $(element).closest('li') );
      return false;
    };


    CElement_cancel=function(element)
    {
        $(element).closest('.CElementList').CElement('show', $(element).closest('li') );
        
    }
    
    CElement_submit=function(element)
    {
      
      $(element).closest('form').find('input[name=settings_json]').remove();
      var hiddenfield = $('<input>',{'type':'hidden','name':'settings_json'});
      var settings=$(element).closest('.celement-li').data('settings');
      hiddenfield.val($.toJSON(settings));
      $(element).closest('form').append(hiddenfield);


      var cssid = $(element).closest('.CElement').closest('li').attr('id');
      $(element).closest('form').find('input[name=cssid]').remove();
      var hiddenfield2 = $('<input>');
      hiddenfield2.attr('type','hidden');
      hiddenfield2.attr('name','cssid');
      hiddenfield2.val(cssid);
      $(element).closest('form').append(hiddenfield2);
      
      
      if($(element).closest('.CElement').closest('li').attr('insertafter'))
      {
        $(element).closest('form').find('input[name=insertAfter]').remove();
        var hiddenfield3 = $('<input>');
        hiddenfield3.attr('type','hidden');
        hiddenfield3.attr('name','insertAfter');
        hiddenfield3.val($(element).closest('.CElement').closest('li').attr('insertafter'));
        $(element).closest('form').append(hiddenfield3);
      }
      
      $(element).closest('form').submit();      
      
      return false;
    };

    <% if SortMode %>
    
      $('#CElementListUL-$Fieldname .editbuttons').hide();
      $('#CElementListUL-$Fieldname').sortable();
    
    <% end_if %>
    

   });

  
</script>

