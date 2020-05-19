<% include BreadCrumbs %>
    
<style type="text/css" media="screen">
    
    
     .formitem-PaymentType .control-label {display:none}
     
     .formitem-PaymentType .radio.inline {margin-left: 10px;
         display:none}
     
     
     
     
</style>

<div class='bootstrap'>
    <form id='dataform' method='POST' class='form-horizontal' action='{$Link}step5
    '>    
    
    $FormHelper.DefaultFields.RAW


    <% if  $PromoCodeMessage %> 
      $PromoCodeMessage
    <% else %>

    <h2>Vielen Dank für Ihre Bestellung </h2>
   
    <% if  $record.PaymentType == "vorkasse" %>

    <div class='well'>
        <div class='space'>
            Bitte überweisen Sie den Gesamtbetrag von EUR <strong>$record.TotalPrice_str</strong> auf folgendes Konto:
            
            <div>&nbsp;</div>
            Empfänger: <strong>derdoppelstock.at</strong><br>
            Verwendungszweck: <strong>Bestellung {$record.OrderNr}</strong><br>
            BLZ: <strong>38460</strong><br>
            Konto: <strong>10400547</strong><br>
            BIC: RZSTAT2G460<br>
            IBAN: AT743846000010400547<br>
            
        </div>
    </div>        
    <% end_if %>
    
    
    <form id='dataform' method='POST' action='{$Link}checkout_finish' class='form-horizontal'>    
        
        $FormHelper.DefaultFields.RAW
     
        <div class='well formhelper group'>$record.OrderHTML.RAW
        
            <div class='mainbuttons btn-group pull-right'>
                <button class="btn btn-mini"  onClick="window.print();return false" ><i class="icon-print icon-black"></i> drucken </button>
            </div>
   
        </div>
   
   
   
        <!-- <div class='space'>
                 Wenn sie in Zukunft bestellen möchten, ohne ihre Daten erneut angeben zu müssen,
                 können sie hier ein passwort angeben:  
                 
                 <div class='well'>           
                     <label>Username:</label> <input type='text' disabled value="$FormHelper.FieldValue('BillingEmail')">
                     <label>Password:</label> <input type='password'>
                     <button class="btn btn-small btn-info" type="submit"><i class="icon-white icon-ok"></i> OK</button>
                 </div>
     
             </div>       -->  
   
   
            <% end_if %>
   
    </form>
   
</div>


<script type="text/javascript" charset="utf-8">


jQuery(document).ready(function($) {
          
    $FormHelper.validate.RAW;
    
    <% if FormHelper.FieldValue('DeliveryType')=='pickup' %>
     $('#input_PaymentTypecash').closest('label').css('display','block');
    <% else %> 
     $('#input_PaymentTypecash').remove();
    <% end_if %> 
    
     $('#input_PaymentTypevorkasse').closest('label').css('display','block');
     $('#input_PaymentTypesofort').closest('label').css('display','block');
    
    
    $('.btn-back').on('click',function(e){
        $('#dataform').attr('action','{$Link}step3'); 
    });

});

    
</script>

