<style type="text/css" media="screen">


table.cartitems th.right {text-align:right}
table.cartitems td.right {text-align:right}

.bootstrap .cartitems.table td  {vertical-align:top}

.cartitem-removed td {visibility:hidden}
.cartitem-removed td {padding:0px;visibility:hidden}

.bootstrap .cartitem-amount input {width:30px;margin:2px;text-align:right}
 
</style>

<% include BreadCrumbs %>
    
<h1>Warenkorb</h1>



<div class='bootstrap'>

<% if items %>
   <form method='POST' id="transportform"><input type="hidden"  id="payload" name="payload"></form>
   <form method='POST' id="cartform" ng-submit="submitForm()">
        
       <div class='space group'>
           <small class='pull-right'>Alle Preise in EUR inkl. 20% Mwst.</small>
       </div>
       
       <table class='cartitems table table-condensed table-striped'>
   
   
           <tr >
               <th class='right'>Stk.</th>
               <th>Artikel</th>
               <th>Design</th>
               <th class='right'>Listenpreis</th>
               <th class='right'>Einzelpreis</th>
               <th class='right'>Gesamtpreis</th>
               <th class='right'></th>
           </tr>    
           <% loop items %>
   
           <tr class='cartitem' valign='top'>
               <td class='right cartitem-amount' data-name="fdata[cartitems][$key][amount]" data-instock="$in_stock">$amount</td>
               <td><% if  product_link %><a href='$product_link'><% end_if %>$product_title<% if  product_link %></a><% end_if %> <div>$variant_title</div></td>
               <td >
               
               <a href="https://engine.slide-it.net/editor/$did" title="design bearbeiten"><div style="background-image:url('https://engine.slide-it.net/editor/png/$did')" class="designpreview"></div></a>

               </td>
               <td class='right'>$baseprice_str</td>
               <td class='right'>$singleprice_str</td>
               <td class='right'>$price_str</td>
               <td class='cartitem-button'>
                   <button class="btn btn-mini btn-remove-item" style="display:none"><i class="icon-trash"></i></button>
               </td>
           </tr>
   
           <% end_loop %>
           
           <tr class='cartitem' valign='top'>
               <td class='' colspan='1'><strong>&nbsp;</strong></td>
               <td class='' colspan='4'><strong>Total</strong></td>
               <td class='right'><strong>$gesamtbrutto_str</strong></td>
               <td class='cartitem-button'>&nbsp;</td>
       
           </tr>
       </table>
   

       <div class='group'>  
           <div class='mainbuttons btn-group pull-right'>
 
               <button class="btn btn-edit-cart"><i class="icon icon-pencil"></i> Warenkorb bearbeiten</button>
               <% if  BackLink %>
               <a class="btn" href="$BackLink"><i class="icon icon-arrow-left"></i> zurück zum Produkt</a>
               <% end_if %>
            
               <button class="btn btn-info btn-do-checkout" ><i class="icon-white icon-arrow-right"></i> weiter zur Kassa</button> 
           </div>
       </div>
       
       <div class='group'>
           <div>&nbsp;</div>
           <div>&nbsp;</div>
           <div ng-switch on="app.hasPromocode()">
       
               <div ng-switch-when='no'> 
                   <div>
                       
                       Haben Sie einen Rabattcode ?
            
                       <div  class="input-append" >
                           <input type='text' name='rabattcode' ng-model='tmp.promocode'><button class="btn" type="button" ng-click="checkPromoCode()"><i class="icon-white icon-ok"></i></button>
                       </div>
                       
                       <div class='alert alert-danger' ng-show='errormsg'>
                           <i class="icon-white warning-sign" ></i>
                           <span ng-switch on='errormsg' >
                               <span ng-switch-when='not_found'>Der Rabattcode konnte nicht gefunden werden</span> 
                               <span ng-switch-when='already_used'>Dieser Rabattcode wurde bereits eingelöst</span> 
                               <span ng-switch-when='unknown'>Fehler beim Ermitteln des Rabattcodes</span> 
                           </span>
                       </div>               
                   </div>
               </div> 
               <div ng-switch-when='yes'> 
                   
                   <span class='alert alert-success pull-right' >
                       <i class='icon-info-sign'></i> aktueller PromoCode: {{app.cart.promocode}}
                       <a style='position:relative;left:20px' title='Promocode entfernen' href="javascript:void(0)" ng-click="removePromoCode()"><i class='icon-remove'></i></a>
                   </span>                

               </div>
                
           </div>
           
           
       </div>
   
   
   <div class='savebuttons btn-group  hide'>
           <button class="btn btn-info btn-save-changes" type='submit'><i class="icon icon-white icon-ok"></i> Änderungen speichern</button>
           <button class="btn btn-cancel-changes"><i class="icon icon-remove"></i> Abbrechen</button>
   </div>
</form>
<% else %>

<div>&nbsp;</div>
    <div class='alert alert-info'>Derzeit befinden sich  keine Artikel im Warenkorb</div>
<% end_if %>
</div>



<script type="text/javascript" charset="utf-8">
    
jQuery(document).ready(function($) {


    $('.btn-edit-cart').on('click',function(e){
        
        e.preventDefault();
        
        $('.mainbuttons').hide();
        $('.savebuttons').show();
        
        $('.cartitem').each(function()
        {
            var val=$('.cartitem-amount',this).text();
            var name=$('.cartitem-amount',this).data('name');
            var instock=$('.cartitem-amount',this).data('instock');
          
            if(instock >0 ) {
              var selectboxhtml="<select  name='"+name+"'>";
              for (var i = 1; i <= instock; i++) {
                if (i>100) {
                  break;
                } 
                selectboxhtml+="<option>"+i+"</option>";
              }
              selectboxhtml+="</select>";
             $('.cartitem-amount',this).html(selectboxhtml); 
             $('.cartitem-amount select',this).val(val).css('width','80px');
            } else{
             $('.cartitem-amount',this).html("<input type='text' name='"+name+"' value='"+val+"'>"); 
            }

           $('.cartitem-button button',this).fadeIn(); 

        });
        
    });
    
    
    $('.btn-cancel-changes').on('click',function(e){
        e.preventDefault();
        document.location.href='$CurrentURL';
    });
    
    
    $('.btn-do-checkout').on('click',function(e){
        e.preventDefault();
        document.location.href='{$CurrentURL}step0';
    });
    
 
    
    $('.btn-remove-item').on('click',function(e){
        e.preventDefault();
        
        var cartitem=$(this).closest('tr');
        $('.cartitem-amount input',cartitem)
             .appendTo( $(this).closest('form') )
             .val(0)
             .hide();
        
        cartitem.children('td').wrapInner('<div>');
        
        $('td > div',cartitem).slideUp(300,function(e)
        {
            cartitem.remove();
        });
        
    });
    
    
    
    
 });




</script>
