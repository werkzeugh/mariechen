<% include BreadCrumbs %>
    
<style type="text/css" media="screen">
    
    
     .formitem-DeliveryType .control-label {display:none}
     
     .formitem-DeliveryType .radio.inline {display:block;margin-left: 10px;}
     
</style>

<div class='bootstrap'>
  <% include MwShop_StepHeader %>

    <form id='dataform' method='POST' class='form-horizontal' action='{$Link}step3'>    
        $FormHelper.DefaultFields.RAW
   
    <h3>Rechnungsadresse:</h3>
        
    $FormHelper.Field('BillingFirstname').HTML.RAW
    $FormHelper.Field('BillingLastname').HTML.RAW
    $FormHelper.Field('BillingCompany').HTML.RAW
    
    $FormHelper.Field('BillingEmail').HTML.RAW
    $FormHelper.Field('BillingFon').HTML.RAW

    $FormHelper.Field('BillingStreet').HTML.RAW
    $FormHelper.Field('BillingZip').HTML.RAW
    $FormHelper.Field('BillingCity').HTML.RAW
    $FormHelper.Field('BillingCountry').HTML.RAW


    
    
    <% if  FormHelper.FieldValue('DeliveryType') == "delivery" %>
    $FormHelper.Field('UseDeliveryAdress').HTML.RAW
        
    <div class='deliveryform hide'>  

        <h3>Zustelladresse:</h3>

        $FormHelper.Field('DeliveryFirstname').HTML.RAW
        $FormHelper.Field('DeliveryLastname').HTML.RAW
        $FormHelper.Field('DeliveryCompany').HTML.RAW
            
          
        $FormHelper.Field('DeliveryFon').HTML.RAW
      
        $FormHelper.Field('DeliveryStreet').HTML.RAW
        $FormHelper.Field('DeliveryZip').HTML.RAW
        $FormHelper.Field('DeliveryCity').HTML.RAW
        $FormHelper.Field('DeliveryCountry').HTML.RAW
    </div>
   
    <% end_if %>
    
 
   <div class='mainbuttons btn-group pull-right'>
       <button class="btn  btn-back" type="submit" ><i class="icon-arrow-left "></i> zurück</button>
       <button class="btn btn-info" type="submit" >weiter <i class="icon-arrow-right icon-white"></i></button>
   </div>
   
   
   
   </form>
   
   
   
</div>


<script type="text/javascript" charset="utf-8">


jQuery(document).ready(function($) {
          
    $FormHelper.validate.RAW;
    
    
    $("#input_UseDeliveryAdress").change(function()
    {
        if (window.console && console.log) { console.log('chnge called');  }
         
         
        var subform=$('.deliveryform',$(this).closest('form'));

        if( ! ($(this).is(":checked") ))
        {
            subform.slideUp().addClass('ignore');
        }
        else
        {
            subform.slideDown().removeClass('ignore');   
        }
    });
    
    $("#input_UseDeliveryAdress").trigger('change');

    $('.btn-back').on('click',function(e){
        $('#dataform').attr('action','{$Link}step1'); 
    });

});

    
</script>

