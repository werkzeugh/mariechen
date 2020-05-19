<style>
  .itemlist li {list-style:none;}
  div.actions {display:none}
</style>


<div class='formsection'>
    <table class='table table-condensed table-striped' >
  <tr>
      <th>Kategorie-Rabatte:</th>
      <th>Rabatt</th>
      <th>MaximalRabatt</th>
      <th>KennenlernRabatt</th>
   </tr>
   <% if  $record.Parent.ClassName=='ShopCategoryPage' %>
   
    <% with  $record.Parent %>
    <tr>
        <td>$Title</td>
        <td>$FallbackedDiscountValueExplained('Discount')</td>
        <td>$FallbackedDiscountValueExplained('MaxDiscount')</td>
        <td>$FallbackedDiscountValueExplained('KennenlernDiscount')</td>
     </tr>
    <% end_with %>


   
   <% end_if %>
   <tr>
       <td style='padding-left:20px'><strong>$record.Title</strong></td>
       <td>$record.FallbackedDiscountValueExplained('Discount')</td>
       <td>$record.FallbackedDiscountValueExplained('MaxDiscount')</td>
       <td>$record.FallbackedDiscountValueExplained('KennenlernDiscount')</td>
    </tr>
</table>
</div>
<div class='space'>
 <a href='$CurrentURL?addItem=1' class='button'><span class='tinyicon  ui-icon-plus'></span>$getText(additem)</a>
</div>

$FilterHTML


<% if EHP %>

     <div class='formsection' style="max-width:800px"><div id='ehp'></div></div>

      <script type="text/javascript">

        // include ehp-widget ---------- BEGIN
        

    

        $('#ehp').EHP({
          'type':'listing',
          'baseurl':'/BE/Pages/edit/$ID/ehp',
          'listparams':{},
          'whole_row_dblclickable':1,
          'pagesize':30,
          'use_bootstrap_css':1,
          'showCheckboxes':true,
          'checkboxActions':{
              'setInStockType':{label:'Lagerstands-Typ setzen'},
              'setDiscount':{label:'Rabatt setzen'},
              'setMaxDiscount':{label:'Maximalrabatt setzen'},
              'setKennenlernDiscount':{label:'Kennenlernrabatt setzen'}
          },
          'checkboxActions_beforeSubmit':function(params,dd) {
              if(!params)
                return false;

              if(params.multiaction=='setInStockType') {
                return window.top.callAngularFunction('app.showModal','SetInStockType',{'baseUrlForController':'/mysite/ng/backend',msg:'error-test'});
              } else if(params.multiaction.match(/set/)) {
                  var fieldname=params.multiaction.replace(/set/,'');
                  var addon='';
                  params.new_value=prompt('neuer Wert für '+fieldname+addon);
                  if(params.new_value!==null) {
                      return true;
                  }
              } 
              return false;  
          },
          'texts':{
            'add_text':'none'
          },$myEHP_JsonAddonParams
          'columns':
            $EHP.getJSONColumnDefinitions 

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

