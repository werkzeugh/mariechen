<?php


use SilverStripe\ORM\DataObject;

class MysiteShop extends MwShop
{



    public function getPaymentTypes($currentCart = array())
    {
      

        $ret= array('cash'=>'Barzahlung bei Abholung',
                              'vorkasse' => 'Vorkasse',
                              // 'rechnung' => 'Rechnung',
                              'sofort' => 'Sofortüberweisung',
                              'creditcard' => 'Kreditkarte',
                          );

        if ($currentCart['BillingCountry']!='at') {
            unset($ret['rechnung']);
        }

        return $ret;
    }


    public function saveCart($cart)
    {
        

        // if (true || array_key_exists('d', $_GET)) {
        //     $x=$cart;
        //     $x=htmlspecialchars(print_r($x, true));
        //     echo "\n<li>mwuits: <pre>$x</pre>";
        // }
        //check in-stock-values

        if ($cart['items']) {
            foreach ($cart['items'] as $key => $row) {
                list($product_id,$variantid)=explode("^", $key);
                $p=DataObject::get_by_id('ProductPage', $product_id);
                if ($p) {
                    $v=$p->getVariant($variantid);
                    $instock=$v->InStock;

                    if ($instock<1) {
                        if ($p->InStockType=='Typ2' || $p->InStockType=='Typ3') {
                            $instock=5;
                        }
                    }


                    if ($row['amount']>$instock) {
                        $cart['items'][$key]['amount']=$instock;
                    }
                }
            }
        }


        parent::saveCart($cart);
    }
    


    public function getCartData4Display($fdata = array())
    {
        
        $mycart=array();
        $mycart['items']=array();
       
        $cart=$this->Cart;
           
           
        if ($cart['items']) {
            foreach ($cart['items'] as $key => $item) {
                if ($item['amount']<1) {
                    continue;
                }
                [$product_id,$variantid,$did]=explode("^", $key);
                $item['key']=$key;

                $p=DataObject::get_by_id('ProductPage', $product_id);
                $item['product_id']=$p->ID;
                $item['product_title']=$p->Title;
                $item['product_link']=$p->Link();

                $v=$p->getVariant($variantid);
                $item['variant_title']=$v->Title;
                $item['did']=$did;
                $item['variant_number']=$v->Number;
                if ($v) {
                    $item['singleprice']=$v->myDiscountedPrice();
                    $item['baseprice']=$v->myBasePrice();
                    $item['maxdiscountedprice']=$v->myMaxDiscountedPrice();
                    $item['kennenlernprice']=$v->KennenlernPrice;
                    $item['discount']=$v->Mainrecord->myDiscount();
                    $item['maxdiscount']=$v->myMaxDiscount();
                    $item['singleprice_str']=$this->formatPrice($item['singleprice']);
                    $item['baseprice_str']=$this->formatPrice($item['baseprice']);
                    
                      
                    $item['price']=$item['singleprice']*$item['amount'];
                    $item['price_str']=$this->formatPrice($item['price']);
                    $item['in_stock']=$v->InStock;
                }
                $summebrutto+=$item['price'];
                $total_items+=$item['amount'];
               
                $mycart['items'][]=$item;
                
                // if($fdata['PromoCodeType']=='KennenlernRabatt')
  //               {
  //
  //                   $rabattitem=Array();
  //
  //                   $kennenlernrabatt=$p->myKennenlernDiscount();
  //                   if($kennenlernrabatt)
  //                   {
  //                       $rabattitem['product_title']="-{$kennenlernrabatt}% Kennenlernrabatt";
  //
  //                       $rabattitem['amount']="1";
  //
  //                       $rabattitem['singleprice']=-$item['singleprice']*($kennenlernrabatt/100);
  //                       $rabattitem['singleprice_str']=$this->formatPrice($rabattitem['singleprice']);
  //
  //                       $rabattitem['price']=$rabattitem['singleprice']*$item['amount'];
  //                       $rabattitem['price_str']=$this->formatPrice($rabattitem['price']);
  //
  //                       $mycart['items'][]=$rabattitem;
  //
  //                       $summebrutto+=$rabattitem['price'];
  //                   }
  //
  //               }
            }
            $mycart['total_items']=$total_items;


            $mycart['summebrutto']=$summebrutto;

            
            if ($fdata['promocode']) {
                $promocode=PromoCode::getByCode($fdata['promocode']);

                if ($promocode) {
                    $mycart['promocode']=$promocode;
                    $mycart=$promocode->augmentCart($mycart);

                    $summebrutto=$mycart['summebrutto'];
                }
            }
                

            if ($fdata['PaymentType']=='vorkasse') {
                    $rabattitem=array();
                    
                    $rabattitem['product_title']='-0.5% Rabatt bei Zahlungsart "Vorkasse" ';

                    $rabattitem['amount']="1";
                    
                    
                    $rabattitem['price']=-$summebrutto*0.005;
                    $rabattitem['price_str']=$this->formatPrice($rabattitem['price']);
                    
                    $mycart['items'][]=$rabattitem;
                    
                    $summebrutto+=$rabattitem['price'];
            }
            
            
    


            if ($fdata['DeliveryType']=='delivery') {
                $deliverycountry_field=$fdata['UseDeliveryAdress']?'DeliveryCountry':'BillingCountry';
                
                switch ($fdata[$deliverycountry_field]) {
                    case 'de':
                        $versandkosten = 12.90;
                        break;
                    case 'at':
                        $versandkosten = 6.90;
                        break;
                    case 'no':
                        $versandkosten = 85;
                        break;
                    case 'ch':
                        $versandkosten = 65;
                        break;
                    default:
                        $versandkosten = 35;
                        break;
                }
            
                $mycart['versandkosten']=$versandkosten;
                $mycart['versandkosten_str']=$this->formatPrice($versandkosten);
            }
    

          
            $mycart['summebrutto']=$summebrutto;
    
            $mycart['gesamtbrutto']=$mycart['summebrutto']+$mycart['versandkosten'];
            $mycart['steueranteil']= $mycart['gesamtbrutto']-($mycart['gesamtbrutto']/1.20);
            $mycart['gesamtnetto']=$mycart['gesamtbrutto']-$mycart['steueranteil'];



            $mycart['steueranteil_str']=$this->formatPrice($mycart['steueranteil']);
            $mycart['gesamtnetto_str']=$this->formatPrice($mycart['gesamtnetto']);
            $mycart['gesamtbrutto_str']=$this->formatPrice($mycart['gesamtbrutto']);
            $mycart['summebrutto_str']=$this->formatPrice($mycart['summebrutto']);
        }
        
        
        return $mycart;
    }
}

//extends basic order-record

class MysiteShopOrder extends MwShop_Order
{


    public function finalizeOrder()
    {


        if ($this->Status!='created') {
            $this->Status="created";

            $cart=$this->getCartData();
            if ($cart['items']) {
                foreach ($cart['items'] as $item) {
                    list($product_id,$variantid)=explode("^", $item['key']);
                    if ($product_id) {
                        $p=DataObject::get_by_id('ProductPage', $product_id);
                        if ($p) {
                            $v=$p->getVariant($variantid);
                            if ($v && $item['amount']) {
                                $v->decrementInStockValue($item['amount']);
                            }
                        }
                    }
                }
            }
          // //decrement instock values of cart
          // if($_GET[d] || 1 ) { $x= $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
          // die("<pre>mwuits-debug 22:04:54 : finalizeOrder".print_r(0,1));
            $this->write();
        }
    }
}
