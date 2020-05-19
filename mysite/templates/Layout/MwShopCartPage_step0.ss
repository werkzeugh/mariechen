<% include BreadCrumbs %>
    
<style type="text/css" media="screen">
    
    
     .formitem-DeliveryType .control-label {display:none}
     
     .formitem-DeliveryType .radio.inline {display:block;margin-left: 10px;}
     
</style>

<div class='bootstrap'>
    <form id="dataform" action="/User/login" method="post" autocomplete="off">
        $FormHelper.DefaultFields.RAW
    
    <input class="hidden"  name="BackURL" value="$CurrentURL" type="hidden">
   
   <h2>Ihre Bestellung</h2>
   
   
   <div class='row'>
       <div class='span4 ' style='border-right:1px solid #999;padding-right:10px'> 
           <div class='space' >
           
           
            <div>&nbsp;</div>   
            Wenn Sie einen Account in unserem Shop besitzen, können Sie sich nun hier anmelden:  
               
           
           </div>
      
      
   
           <div class='MwUserLoginBoxField'> 
               <label>e-Mail Adresse</label>
               <input type='text' name='fdata[Username]' id='inputUsername'>
           </div>
   
           <div class='MwUserLoginBoxField'> 
               <label>Passwort</label>
               <input name='fdata[Password]' type='password'>
           </div>
       
       
           <button class="btn btn-primary btn-info" type="submit"><i class="icon-white  icon-ok"></i> OK</button>
      
           <div class='space'><a href='/User/lostpassword'>&raquo;Passwort vergessen ? </a></div>
           
           
        </div>   
   
       <div class='span4' >
       
   
<div style='height:200px'>&nbsp;</div>
      
           <div class='mainbuttons btn-group pull-right'>
               <a class="btn btn-info" href='/de/cart/step1'><i class="icon-arrow-right icon-white"></i> ohne Anmeldung fortsetzen</a>
           </div>
       
       </div>
   </div>   
   
   
   </form>
   
   
   
</div>

