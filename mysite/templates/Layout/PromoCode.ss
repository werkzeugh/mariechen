<style type="text/css" media="screen">

.filter input {width:20px;}     
    
</style>

<div class='bootstrap'>
    <h1>Rabatt-Codes</h1>
    <div id='ehp'></div>
</div>

<script type="text/javascript">



jQuery(document).ready(function($) {


    // include ehp-widget ---------- BEGIN
  
    $('#ehp').EHP({
      'type':'listing',
      'baseurl':'/BE/PromoCode/ehp',
      'whole_row_dblclickable':1,
      'texts':{
        'add_text':'neuen Gutschein erstellen'
      },
      'defaultSortBy':{'ID':'asc'},
      'columnChooser':true,
      'columns':{
          {$getEHP.getJSONColumnDefinitions}
        }
    });
    // include ehp-widget ---------- END


    $(document).on('keyup',function(e){
       
        if(e.keyCode == 13 && e.srcElement.nodeName=='INPUT') 
        {
            var tr=$(e.srcElement).closest('tr');
            $('a.EHP_saveitem',tr).trigger('click');
        }
        
       
        if(e.keyCode == 27 && e.srcElement.nodeName=='INPUT') 
        {
            var tr=$(e.srcElement).closest('tr');
            $('a.EHP_cancelitem',tr).trigger('click');
        }
        
        
    });
          
 });


</script>