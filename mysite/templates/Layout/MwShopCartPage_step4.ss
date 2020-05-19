<% include BreadCrumbs %>
    
<style type="text/css" media="screen"></
    
    
     .formitem-PaymentType .control-label {display:none}
     
     .formitem-PaymentType .radio.inline {margin-left: 10px;
         display:none}
     
     
     
     
</style>

<div class='bootstrap'>
  <% include MwShop_StepHeader %>

    <form id='dataform' method='POST' class='form' action='{$Link}step5'>    
    
    $FormHelper.DefaultFields.RAW

    <div class='space'>Bitte überprüfen Sie hier nochmal ihre Angaben:</div>
   
     <div class='well formhelper'>$record.OrderHTML</div>
   
 
     

     <label class='checkbox'><input type='checkbox' id='agb_accepted'> Ich akzeptiere die Allgemeinen Geschäftsbedingungen (<a href='/de/kundenservice/agb/' target='_blank'>⤻ AGB in neuem fenster öffnen</a>)</label>         
    
     <div>&nbsp;</div>
     
   <div class='mainbuttons btn-group pull-right'>
       <button class="btn  btn-back" type="submit" ><i class="icon-arrow-left "></i> zurück</button>
       <button class="btn btn-info btn-submit" type="submit" ><i class="icon-arrow-right icon-ok icon-white"></i> Zahlungspflichtig bestellen</button>
   </div>
   
   
   
   </form>
   
   <div>&nbsp;</div>
   <div>&nbsp;</div>
   
   
</div>


<script type="text/javascript" charset="utf-8">


jQuery(document).ready(function($) {
          
    $FormHelper.validate.RAW;
    
    
    $('.btn-submit').on('click',function(e){
        if(! $('#agb_accepted').is(':checked') )
        {
            alert('Sie müssen die Allgemeinen Geschäftsbedingungen  akzeptieren, um die Bestellung absenden zu können');
            
            e.preventDefault();
        }
    });
    
    $('.btn-back').on('click',function(e){
        
        $('#dataform').attr('action','{$Link}step3'); 
    });

});

    
</script>

