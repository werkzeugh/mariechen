<h1>Tags</h1>

<div class='group'>

  <div class="leftmenu">
   
    <div class="leftmenu_inner">

      <ul class="menu">
        <% loop pageNavItems %>
   
          <li class="$LinkingMode"><a href="$Link">$Title</a></li>
   
        <% end_loop %>
      </ul>

    </div>

  </div>

  <% if  CurrentTagGroupKey %>
  <div style='margin-left:220px' class='bootstrap'>



  <div id='ehp'></div>

  <script type="text/javascript">

    // include ehp-widget ---------- BEGIN
    
    $('#ehp').EHP({
      'type':'listing',
      'baseurl':'/BE/FreshTag/ehp',
      'listparams':{'tag_group':'$CurrentTagGroupKey'},
      'texts':{
        'add_text':'add Tag'
      },
      'showCheckboxes':true,
      'checkboxActions':{
          'delete':     {label:'delete selected tags'}
      },
      'defaultSortBy':{'TagKey':'asc'},
      'columns':{
        'TagKey':{'label':'Key','sortable':true,'filter':{'type':'auto'} },
        'Title':{'label':'Name','sortable':true,'filter':{'type':'auto'} }
        }
    });
    // include ehp-widget ---------- END


    $(document).on('keyup',function(e){
       
        if(e.keyCode == 13 && e.srcElement.nodeName=='BODY') 
        {
             $('a.EHP_additem:visible').trigger('click'); 
        }
        
    });
    
  </script>



  </div>
  <% end_if %>
  




</div>