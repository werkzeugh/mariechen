<style>
  .itemlist li {list-style:none;}
  div.actions {display:none}
</style>


<div class='space'>
 <a href='$CurrentURL?addItem=1' class='iconbutton'><span class='tinyicon  ui-icon-plus'></span>$getText(additem)</a>
</div>

$FilterHTML

<% if EHP %>

     <div class='bootstrap'><div id='ehp'></div></div>

      <script type="text/javascript">

        // include ehp-widget ---------- BEGIN
        
        $('#ehp').EHP({
          'type':'listing',
          'baseurl':'/BE/Pages/edit/$ID/ehp',
          'listparams':{},
          'whole_row_dblclickable':1,
          'pagesize':50,
          'use_bootstrap_css':1,
          'texts':{
            'add_text':'none'
          },$myEHP_JsonAddonParams.RAW
          'columns':
             $EHP.getJSONColumnDefinitions.RAW 

        });
        // include ehp-widget ---------- END

      </script>

<% else %>

<!-- old style:
 -->

<div class='itemlist'>  
  
  $getPaging(Items)

    <% loop Items  %>
  

    <li class='space'>
      <a href='/BE/Pages/edit/$ID' style='display:block' class='simpleitem $cmsCssClass'>
        <div class='group'>
          $cmsThumbnail
          <strong>$cmsTitle</strong><br>
          <small>$cmsShortText</small>
        </div>  
      </a>
    </li>
    <% end_loop %>

  $getPaging(Items)
</div>

<% end_if %>

