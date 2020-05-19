<% if items %>
<style type="text/css" media="screen">

</style>

<div class='header-cart'>

    <div class='header-cart-inner bootstrap'>
        <img src="/mysite/images/cart_icon_small_black.png" width="24" height="24" alt="Cart Icon" class='header-cart-icon'>
        $total_items Produkt<% if total_items == 1 %><% else %>e<% end_if %> im <a href="$Top.Shop.CheckoutPage.Link"><u>Warenkorb</u></a>  <!-- <a class="btn btn-mini btn-inverse pull-right" href="$Top.Shop.CheckoutPage.Link"><i class="icon-arrow-right icon-white"></i> zur Kasse</a> -->
    </div>
   
</div>

<% else %>
<!-- empty cart -->

<% end_if %>
