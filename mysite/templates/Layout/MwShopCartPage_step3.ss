<% include BreadCrumbs %>
    
<style type="text/css" media="screen">
    
    
     .formitem-PaymentType .control-label {display:none}
     
     .formitem-PaymentType .radio.inline {margin-left: 10px;
         display:none}
     
     
     
     
</style>

<div class='bootstrap'>
  <% include MwShop_StepHeader %>

    <form id='dataform' method='POST' class='form' action='{$Link}step4'>    
        $FormHelper.DefaultFields.RAW
   
    <h3>Zahlungsart:</h3>
        
    <div class='space'>Bitte wählen Sie eine Bezahlvariante</div>
   
   
    <div class='group'>
            $FormHelper.Field("PaymentType").HTML.RAW

    </div>
    

    
   <div class='mainbuttons btn-group pull-right'>
       <button class="btn  btn-back" type="submit" ><i class="icon-arrow-left "></i> zurück</button>
       <button class="btn btn-info btn-next" type="submit" >weiter <i class="icon-arrow-right icon-white"></i></button>
   </div>
   
   
   
   </form>
   
   
   
</div>


<div id="sofort-infobox" style="display:none" >
    
    <div class="sofort-infotext well span5">
        <img src="/mysite/images/sofortueberweisung_100x38.png" width="100" height="38" class="sofort-icon" alt="Sofortüberweisung" align='left'>
      
        <strong>SOFORT &Uuml;berweisung - Einfach sicher zahlen.</strong>
        <p>Mit dem T&Uuml;V-zertifiziertem Bezahlsystem SOFORT &Uuml;berweisung k&ouml;nnen Sie dank PIN&amp;TAN, ohne Registrierung, einfach und sicher mit Ihren gewohnten Online-Banking-Daten zahlen. <a href="https://documents.sofort.com/de/sue/kundeninformationen/" target="_blank">Mehr hier</a>.</p>
      
    </div>
    
    <br clear='all'>
</div>

<div id="creditcard-infobox" style="display:none" >

    <div class="creditcard-infotext  span5">
        <strong><s>Kreditkarte</s></strong>
        <p>Geben Sie gerne ihr Geld Kreditinstituten? Wir auch nicht! Danke dass ihr unsere Zahlungsmethoden akzeptiert!</p>
  
    </div>
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
     $('#input_PaymentTyperechnung').closest('label').css('display','block');
     $('#input_PaymentTypecreditcard').closest('label').css('display','block');
     $('#input_PaymentTypecreditcard').attr('disabled',1);
     
     $('#input_PromoCode').on('change',function(e){
         $('#dataform').attr('action','{$Link}step3'); 
     });
    
    $('.btn-back').on('click',function(e){
        $('#dataform').attr('action','{$Link}step2'); 
    });
    
    
    $('span',$('#input_PaymentTypesofort').parent()).html($('#sofort-infobox').html());
    $('span',$('#input_PaymentTypecreditcard').parent()).html($('#creditcard-infobox').html());

});

    
</script>

