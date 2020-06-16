<?php

use SilverStripe\ORM\FieldType\DBBoolean;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Session;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\View\ViewableData;

class MwShop_Order extends DataObject
{
    private static $db=array(

        "TransactionID"             => "Varchar(255)",
        "OrderNr"                   => "Varchar(255)",

        "Status"                    => "Varchar(255)",
        "PaymentStatus"             => "Varchar(255)",
        "PaymentTransactionID"      => "Varchar(255)",
        "PaymentSecret"             => "Varchar(255)",
    
        "DeliveryType"              => "Varchar(255)",
        "PaymentType"               => "Varchar(255)",

        "BillingFirstname"          => "Varchar(255)",
        "BillingLastname"           => "Varchar(255)",
        "BillingCompany"            => "Varchar(255)",
        "BillingStreet"             => "Varchar(255)",
        "BillingZip"                => "Varchar(255)",
        "BillingCity"               => "Varchar(255)",
        "BillingCountry"            => "Varchar(255)",
        'BillingEmail'              => "Varchar(255)",
        "BillingFon"                => "Varchar(255)",
        "BillingComment"            => "Varchar(255)",
        "BillingDonation"           => "Varchar(255)",
        "BillingUseDeliveryAdress"  => "Varchar(255)",

        "DeliveryFirstname"         => "Varchar(255)",
        "DeliveryLastname"          => "Varchar(255)",
        "DeliveryCompany"           => "Varchar(255)",
        "DeliveryStreet"            => "Varchar(255)",
        "DeliveryZip"               => "Varchar(255)",
        "DeliveryCity"              => "Varchar(255)",
        "DeliveryCountry"           => "Varchar(255)",
        'DeliveryEmail'             => "Varchar(255)",
        "DeliveryFon"               => "Varchar(255)",
        "DeliveryUseDeliveryAdress" => "Varchar(255)",


        "UseDeliveryAdress"         => DBBoolean::class,

        "CartJSON"                  => "Text",
        "TotalItems"                => "Int",
        "TotalPrice"                => "Float",
        "PromoCode"                 => "Varchar(255)",
        "LogJSON"                   => "Text",

    );
    
    
    public function add2Log($msg, $data = null)
    {
        $data2log=array();
        $data2log['time']=Date('Y-m-d H:i:s');
        $data2log['msg']=$msg;
        $data2log['ip']=array_get($_SERVER, 'REMOTE_ADDR');
        $data2log['url']=array_get($_SERVER, 'REQUEST_URI');
              
        if ($data) {
            $data2log['data']=$data;
        }
                    
        $json=json_encode($data2log);
        $this->LogJSON.="\n^^^\n".$json;
        $this->write();
    }
    
    public function getLogData()
    {
        $all_lines=array();
        
        if ($this->LogJSON) {
            foreach (explode("\n^^^\n", $this->LogJSON) as $line) {
                $linedata=json_decode($line, 1);
                if ($linedata) {
                    $all_lines[]=$linedata;
                }
            }
        }
        
        return $all_lines;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        
        if ($this->ID && !$this->OrderNr) {
            $this->OrderNr=$this->createNewOrderNr();
        }
        
        //write cart-data to main-record
        if ($this->CartJSON) {
            $cartdata=$this->CartData;

                    
            $this->TotalItems=$cartdata['total_items'];
            $this->TotalPrice=$cartdata['gesamtbrutto'];
        }
    }
    
    public function createNewOrderNr()
    {
        return Date("ym").sprintf("%05d", $this->ID);
    }
    
    
    public function getCartData()
    {
        $cdata=json_decode($this->CartJSON, 1);
        return $cdata;
    }
    
    public function getCartData4Template()
    {
        $cdata=$this->getCartData();
        $cdata['items']=MwUtils::convertArray2ArrayList($cdata['items']);
       
        $c=new ArrayData($cdata);
       
        return $c;
    }
    
    public function OrderHTML()
    {
        $html=$this->renderWith("Includes/MwShopOrder_OrderHTML");
        return $html;
    }
    
    
    public function getPaymentType_Str()
    {
        $arr=$this->getShop()->getPaymentTypes();
        return $arr[$this->PaymentType];
    }

    public function getDeliveryType_Str()
    {
        $arr=$this->getShop()->getDeliveryTypes();
        return $arr[$this->DeliveryType];
    }

    public function getBillingCountry_Str()
    {
        $c=$this->getCountries();
        return $c[$this->BillingCountry];
    }
    
    public function getDeliveryCountry_Str()
    {
        $c=$this->getCountries();
        return $c[$this->DeliveryCountry];
    }

    public function getShop()
    {
        static $shop;
        if (!$shop) {
            $shop=new MwShop(Controller::curr());
        }
        return $shop;
    }
    
    // public function sendEmailToCustomer()
    //   {
    //       # code...
    //   }

    public function finalizeOrder()
    {
        // to override
    }

    public function sendEmailToMerchant()
    {
        $to=MwShop::conf('MerchantEmail');
        $subject="Neue Bestellung von {$this->BillingEmail} #{$this->OrderNr}";

        $html="Neue Bestellung: <div>&nbsp;</div><div>&nbsp;</div> {$this->OrderHTML()} ";
        $mailhtml= MysiteMail::makeNiceHTML($html);
               
        $mail = Mwerkzeug\MwEmail::create(MwShop::conf('MerchantEmail'), $to, $subject, $mailhtml);
        $mail->send();
        MwMailLog::add($mail); // log this mail
    }
    
    
    public function sendEmailToCustomer()
    {
        $to=$this->BillingEmail;

        $subject="Ihre Bestellung auf {array_get($_SERVER,'HTTP_HOST')} - #{$this->OrderNr}";
      
        $html="<strong>Vielen Dank für ihren Einkauf auf {array_get($_SERVER,'HTTP_HOST')} </strong>

        <div>&nbsp;</div>
          Hier Ihre Bestelldaten zu Ihrer Information:
        
         <div>&nbsp;</div><div>&nbsp;</div> 
         
         {$this->OrderHTML()} 
         
         ";
        $mailhtml= MysiteMail::makeNiceHTML($html);

        $mail = Mwerkzeug\MwEmail::create(MwShop::conf('MerchantEmail'), $to, $subject, $mailhtml);
        $mail->send();
        MwMailLog::add($mail); // log this mail
    }
    
    public function TotalPrice_str()
    {
        return MwShop::formatPrice($this->TotalPrice);
    }


    public function getCountries()
    {
        return array(

            'at' => 'Österreich',
            'de' => 'Deutschland',
            'ch' => 'Schweiz',
            'be' => 'Belgien',
            'bg' => 'Bulgarien',
            'dk' => 'Dänemark',
            'ee' => 'Estland',
            'fi' => 'Finnland',
            'fr' => 'Frankreich',
            'gr' => 'Griechenland',
            'ie' => 'Irland',
            'it' => 'Italien',
            'lv' => 'Lettland',
            'lt' => 'Litauen',
            'lu' => 'Luxemburg',
            'mt' => 'Malta',
            'nl' => 'Niederlande',
            'no' => 'Norwegen',
            'pl' => 'Polen',
            'pt' => 'Portugal',
            'ro' => 'Rumänien',
            'sk' => 'Slovakei',
            'si' => 'Slowenien',
            'es' => 'Spanien',
            'se' => 'Schweden',
            'cz' => 'Tschechien',
            'hu' => 'Ungarn',
            'uk' => 'United Kingdom',
            'cy' => 'Zypern',
        );
    }
}

class MwShop extends ViewableData
{
    public $Controller;
    public $cartrecord;
    
    public static $conf;
    
    public static function conf($key)
    {
        return self::$conf[$key];
    }
    
    public static function setConf($key, $value)
    {
        self::$conf[$key]=$value;
    }
    
    public static function getOrderByTransactionID($tid)
    {
        if ($tid) {
            return DataObject::get('MysiteShopOrder')->filter('TransactionID', $tid)->First();
        }
    }
    
    
    public static function getDeliveryTypes($currentCart = null)
    {
        return array(
            'pickup'   => 'Abholung im Geschäft',
            'delivery' => 'Zustellung',
        );
    }

    public function getPaymentTypes($currentCart = null)
    {
        $ret= array(
            'cash'       => 'Barzahlung bei Abholung',
            'vorkasse'   => 'Vorkasse',
            'rechnung'   => 'Rechnung',
            'sofort'     => 'Sofortüberweisung',
            'creditcard' => 'Kreditkarte',
        );
        return $ret;
    }
    

    
   
    
    public function __construct($Controller)
    {
        $this->Controller=$Controller;
    }
    
    public function getCart()
    {
        if (!isset($this->cartrecord)) {
            $this->cartrecord=Mwerkzeug\MwSession::get('shop_order');
        }
        return $this->cartrecord;
    }

    public function saveCart($cart)
    {
        if (!$cart) {
            $cart=$this->Cart;
        } else {
            $this->cartrecord=$cart;
        }
        
        Mwerkzeug\MwSession::set('shop_order', $cart);
        if (! Mwerkzeug\MwSession::get('FormHelperTransactionID')) {
            Mwerkzeug\MwSession::set('FormHelperTransactionID', md5(time()."_".rand(1, 500)));
        }

        Mwerkzeug\MwSession::save();
    }
    
    
    public function addToCart($items)
    {
        $cart=$this->Cart;
        if (is_array($items)) {
            foreach ($items as $item) {
                $cart['items'][$item['articleid']]['amount']+=$newamount;
               
                if ($cart['items'][$item['articleid']]['amount']==0) {
                    $cart['items'][$item['articleid']]['amount']=1;
                }
            }
        }

        $this->saveCart($cart);

        return true;
    }
    
    
    
    public function SidebarCartHTML()
    {
        if (!MwShop::conf('hideSidebarCartHTML')) {
            $c=$this->getCartData4Display();
            return Controller::curr()->customise($c)->renderWith('Includes/MwShop_SidebarCart');
        }
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
                $item['did']=$did;

                $v=$p->getVariant($variantid);
                $item['variant_title']=$v->Title;
                $item['variant_number']=$v->Number;
                
                if ($v) {
                    $item['singleprice']=$v->myDiscountedPrice();
                    $item['singleprice_str']=$this->formatPrice($item['singleprice']);
                    
                    $item['price']=$item['singleprice']*$item['amount'];
                    $item['price_str']=$this->formatPrice($item['price']);
                }
                
                $summebrutto+=$item['price'];
                $total_items+=$item['amount'];
                
              

                $mycart['items'][]=$item;
                
                if ($fdata['PromoCodeType']=='KennenlernRabatt') {
                    $rabattitem=array();
                    
                    $kennenlernrabatt=$p->myKennenlernDiscount();
                    if ($kennenlernrabatt) {
                        $rabattitem['product_title']="-{$kennenlernrabatt}% Kennenlernrabatt";

                        $rabattitem['amount']="1";
                    
                        $rabattitem['singleprice']=-$item['singleprice']*($kennenlernrabatt/100);
                        $rabattitem['singleprice_str']=$this->formatPrice($rabattitem['singleprice']);
                    
                        $rabattitem['price']=$rabattitem['singleprice']*$item['amount'];
                        $rabattitem['price_str']=$this->formatPrice($rabattitem['price']);
                    
                        $mycart['items'][]=$rabattitem;
                    
                        $summebrutto+=$rabattitem['price'];
                    }
                }
            }
            $mycart['total_items']=$total_items;


            
            if ($fdata['PromoCodeType']=='PromoCode') {
                $promocode=PromoCode::getByCode($fdata['PromoCode']);

                if ($promocode) {
                    $mycart['promocode']=$promocode;
                    $rabattitem=$promocode->getRabattItem($summebrutto);

                    if ($rabattitem) {
                        $mycart['items'][]=$rabattitem;
                        $summebrutto+=$rabattitem['price'];
                    }
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
            
            
    
            $mycart['summebrutto']=$summebrutto;
            $mycart['summebrutto_str']=$this->formatPrice($mycart['summebrutto']);


            if ($fdata['DeliveryType']=='delivery') {
                $versandkosten = 6.90; // versandkostenpauschale
                
                
                $deliverycountry_field=$fdata['UseDeliveryAdress']?'DeliveryCountry':'BillingCountry';
                
                if ($fdata[$deliverycountry_field]!='at') {
                    $versandkosten = 12.90; // versandkostenpauschale .de
                }
            
                $mycart['versandkosten']=$versandkosten;
                $mycart['versandkosten_str']=$this->formatPrice($versandkosten);
            }
    
            $mycart['gesamtbrutto']=$summebrutto+$versandkosten;
            $mycart['gesamtbrutto_str']=$this->formatPrice($mycart['gesamtbrutto']);


            $mycart['steueranteil']= $mycart['gesamtbrutto']-($mycart['gesamtbrutto']/1.20);
            $mycart['steueranteil_str']=$this->formatPrice($mycart['steueranteil']);

            $mycart['gesamtnetto']=$mycart['gesamtbrutto']-$mycart['steueranteil'];
            $mycart['gesamtnetto_str']=$this->formatPrice($mycart['gesamtnetto']);
        }
        
        
        return $mycart;
    }
      
    
    public function CheckoutPage()
    {
        return SiteTree::get_by_link(MwShop::conf('CheckoutPage'));
    }
    
    public static function formatPrice($val)
    {
        if (strstr($val, ",")) {
            $val=str_replace(",", ".", $val);
        }
        if ($val<>0) {
            $str=number_format($val, 2, ',', '.');
            
//            $str=str_replace(",00",",-",$str);
            return $str;
        }
    }
}
