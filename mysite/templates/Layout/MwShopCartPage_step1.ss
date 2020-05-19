<% include BreadCrumbs %>

<style type="text/css" media="screen">


 .formitem-DeliveryType .control-label {display:none}

 .formitem-DeliveryType .radio.inline {display:block;margin-left: 10px;}

</style>

<div class='bootstrap'>
  <% include MwShop_StepHeader %>
  <form id='dataform' method='POST' class='form ' action='{$Link}step2'>    
    $FormHelper.DefaultFields.RAW

    <h2>Versandart</h2>

    <div class='space'>Bitte wählen Sie eine Versandart</div>


    $FormHelper.Field("DeliveryType").HTML.RAW



    <div class='mainbuttons btn-group pull-right'>
     <button class="btn btn-info" type="submit" >weiter <i class="icon-arrow-right icon-white"></i></button>

   </div>
   
   
   
 </form>



</div>

<script type="text/javascript" charset="utf-8">

  jQuery(document).ready(function($) {

    $FormHelper.validate.RAW;
    

    $('.btn-back').on('click',function(e){
      $('#dataform').attr('action','{$Link}step2'); 
    });

  });


</script>
