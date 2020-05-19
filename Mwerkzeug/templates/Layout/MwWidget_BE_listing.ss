<h1>Widgets</h1>

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

  <% if  CurrentCategoryID %>
  <div style='margin-left:220px'>



  <div id='ehp'></div>

  <script type="text/javascript">

    // include ehp-widget ---------- BEGIN
    
    $('#ehp').EHP({
      'type':'listing',
      'baseurl':'/BE/MwWidget/ehp',
      'listparams':{'widget_category':'$CurrentCategoryID'},
      'texts':{
        'add_text':'add Widget'
      },
      'columns':[
        {'label':'Name'},
        {'label':'Type'}
      ]
    });
    // include ehp-widget ---------- END

  </script>



  </div>
  <% end_if %>
  




</div>