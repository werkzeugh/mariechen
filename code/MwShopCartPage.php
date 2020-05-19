<?php

use SilverStripe\View\Requirements;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Member;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Session;
use SilverStripe\Control\Email\Email;
//use PageController;
use SilverStripe\View\SSViewer;

class MwShopCartPage extends Page
{

 

    
}




class MwShopCartPageController extends PageController
{
    

    private static $allowed_actions = [
    'step0',
    'step1',
    'step2',
    'step3',
    'step4',
    'step5',
    'step_10',
    'step_ehp',
    'sofort_start',
    'sofort_callback',
    'sofort_return',
    'ng_cartdata',
    'ng_remove_promocode',
    'ng_check_promocode',
    'ajaxAddToCart',
    'ajaxDeleteFromCart',
      ];

    var $record, $step;
    
    
    // public function getShoppingCart4Mail()
    //    {
    //        $d= $this->Shop->getCartData4Display($this->FormHelper->FormData);
    //
    //
    //        $c=new ArrayData($d);
    //
    //
    //        return $c;
    //
    //    }
    //
   
    
    public function ngApp()
    {
        return 'mwcart';
    }
    
    public function init()
    {
        parent::init();
        // Requirements::javascript("mysite/thirdparty/ng/angular.min.js");
        Requirements::javascript("mysite/ng/mwcart/js/mwcart.js");
    }
    
    
    public function ShopSteps()
    {

        $steps=array();
        $steps[]=array('Nr'=>1,'Title'=>'Versandart','classes'=>$this->getCssClassesForStep(1));
        $steps[]=array('Nr'=>2,'Title'=>'Adresse','classes'=>$this->getCssClassesForStep(2));
        $steps[]=array('Nr'=>3,'Title'=>'Zahlungsart','classes'=>$this->getCssClassesForStep(3));
        $steps[]=array('Nr'=>4,'Title'=>'Zusammenfassung','classes'=>$this->getCssClassesForStep(4));


        return MwUtils::convertArray2ArrayList($steps);
    }
  
    public function getCssClassesForStep($nr)
    {
        $classes=array();
        if ($nr==$this->step) {
            $classes[]='active';
        }

        if ($nr<$this->step) {
            $classes[]='past';
        }
        return implode(' ', $classes);
    }
    
    
    
    
    public function step0()
    {

        return Controller::curr()->redirect($this->dataRecord->Link().'step1');
        
        // if(Member::currentUser())
        //     return Controller::curr()->redirect($this->dataRecord->Link().'step1');
        
        // $this->FormHelper->init();
        
        
        // return Array();
    }
    
    public function init_for_all_steps()
    {
    }
    
    public function step1()
    {
        $this->step=1;
        $this->FormHelper->init();
        $this->init_for_all_steps();
        
        
        return array();
    }
    
    public function step2()
    {
              $this->step=2;

        $this->FormHelper->init();
        $this->init_for_all_steps();

        if (Member::currentUser() && !$this->FormHelper->FieldValue('BillingEmail')) {
            //preload data once
            $data['BillingEmail']=Member::currentUser()->Email;
            
            $this->FormHelper->setFormData($data);
            $this->FormHelper->setupMwForm();
        }

        return array();
    }
    
   
    public function step3()
    {

        $this->step=3;

        $c=array();
        $this->FormHelper->init();
        $this->init_for_all_steps();
        
        
      
        return $c;
    }
   
   
    public function step4()
    {
              $this->step=4;

        
        $this->FormHelper->init();
        $this->init_for_all_steps();
        
        
        //make temporary order object:
        $this->record=new MysiteShopOrder();
        $this->record->update($this->FormHelper->FormData);
        $this->record->CartJSON=json_encode($this->Shop->getCartData4Display($this->FormHelper->FormData));
        
        
        $this->FormHelper->init();

        return array();
    }
    
    
   
    
    
    public function sofort_start()
    {
        
        require_once(Director::baseFolder().'/mysite/thirdparty/sofortlib/sofortLib.php');
               
        $checkoutpageurl="http://{array_get($_SERVER,'HTTP_HOST')}/".MwShop::conf('CheckoutPage');

        $paymentSecret = md5(mt_rand().microtime());
     

        $Sofort = new SofortLib_Multipay(MwShop::conf('SofortConfigKey'));
        $Sofort->setSofortueberweisung();
        $Sofort->setAmount(round($this->record->TotalPrice, 2));
        $Sofort->setReason("Bestellung #".$this->record->OrderNr, "www.derdoppelstock.at");
       // $Sofort->setSenderAccount('88888888', '12345678', 'Max Mustermann');
        $Sofort->setEmailCustomer($this->record->BillingEmail);
        if ($this->record->BillingFon) {
            $Sofort->setPhoneNumberCustomer($this->record->BillingFon);
        }
        
        $Sofort->setSuccessUrl($checkoutpageurl.'/sofort_return?sofortaction=success&sofortcode='.$this->record->TransactionID);
        $Sofort->setAbortUrl($checkoutpageurl.'/sofort_return?sofortaction=abort&sofortcode='.  $this->record->TransactionID);
        $Sofort->setTimeoutUrl($checkoutpageurl.'/sofort_return?sofortaction=timeout&sofortcode='.$this->record->TransactionID);
        $Sofort->setNotificationUrl($checkoutpageurl.'/sofort_callback?paymentSecret='.$paymentSecret.'&action=multipay&sofortcode='.$this->record->TransactionID);
        $Sofort->sendRequest();
       
        if ($Sofort->isError()) {
            die("\n\n<pre>mwuits-debug 14:41:25 : ".print_r(0, 1));
            //PNAG-API didn't accept the data
            $this->record->add2Log("sofortüberweisungsrequest error", array("error"=>$Sofort->getError()));
            echo $Sofort->getError();
        } else {
            //buyer must be redirected to $paymentUrl else payment cannot be successfully completed!
            $this->record->PaymentTransactionID=$Sofort->getTransactionId();
            $this->record->PaymentSecret=$paymentSecret;
            
            $this->record->add2Log("sofortüberweisungsrequest started, got ID: ".$this->record->PaymentTransactionID);
            
            $paymentUrl = $Sofort->getPaymentUrl();
            header('Location: '.$paymentUrl);
        }
        die();
    }
    
    
    public function sofort_callback()
    {
        
        $this->record=MwShop::getOrderByTransactionID(array_get($_REQUEST, 'sofortcode'));
        if ($this->record) {
            if ($this->record->PaymentSecret==array_get($_REQUEST, 'paymentSecret')) {
                $this->record->PaymentStatus='paid';
                $this->record->add2Log("sofortüberweisungsrequest callback success", $_REQUEST);
            } else {
                $this->record->add2Log("sofortüberweisungsrequest callback received", $_REQUEST);
            }
        }
        die('OK');
    }
    
    public function sofort_return()
    {
        $this->record=MwShop::getOrderByTransactionID(array_get($_REQUEST, 'sofortcode'));
        
        
        $_SESSION['FormHelperTransactionID']=array_get($_REQUEST, 'sofortcode');
        
        //
        // if(array_get($_GET,'d') || 1 ) { $x=$_REQUEST; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
        //
        // if(array_get($_GET,'d') || 1 ) { $x=$this->record; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }


        if (array_get($_REQUEST, 'sofortaction')=='success') {
            usleep(10000); //avoid race-conditions between success-url and notification and needless error-mails
            return Controller::curr()->redirect(MwShop::conf('CheckoutPage').'/step5');
        } else {
            return Controller::curr()->redirect(MwShop::conf('CheckoutPage').'/step4');
        }
    }

    public function ng_cartdata()
    {
        $this->FormHelper->init();
        $ret=$this->FormHelper->FormData;
        if (!$ret) {
            $ret=array();
        }
        
       
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        die();
    }

    public function ng_remove_promocode()
    {
        $this->FormHelper->setFormDataField('promocode', null);
        $this->FormHelper->write();
        
        
        header('content-type: application/json; charset=utf-8');
        $ret=array('status'=>'ok');
        echo json_encode($ret);
        die();
    }
    
    public function ng_check_promocode()
    {
        $this->FormHelper->init();
                                
        $ret=array();
        $ret['status']='err';
        $ret['msg']='unknown';

        $jsonInput = file_get_contents('php://input');
        if ($jsonInput) {
            $q=json_decode($jsonInput, 1);
            if ($q['promocode']) {
                $pc=PromoCode::getByCode($q['promocode']);
                if ($pc) {
                    if ($pc->alreadyUsed()) {
                        $ret['status']='err';
                        $ret['msg']='already_used';
                    } else {
                        $ret['status']='ok';
                        $this->FormHelper->setFormDataField('promocode', $pc->Code);
                        $this->FormHelper->write();
                        $ret['msg']='ok ..pc='.$this->FormHelper->FieldValue('promocode');
                    }
                } else {
                    $ret['msg']='not_found';
                }
            }
        }

        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        return "";
    }

    
    public function checkPromoCode($code)
    {
        
        $code=trim(Convert::raw2xml($code));
        
        $promocode=PromoCode::getByCode($code);
        if ($promocode) {
            if ($promocode->RedeemDate) {
                return array('error'=>'already_used','msg'=>'Der Gutschein-Code "'.$code.'" wurde bereits eingelöst');
            }

            return array('ok'=>true,'type'=>'PromoCode','PromoCode'=>$promocode);
        }


        return array('error'=>'not_found','msg'=>"Der Gutschein-Code '{$code}' ist nicht gültig.");
    }
    
    public function step5()
    {
        MwShop::setConf('hideSidebarCartHTML', 1);
                
        $this->init_for_all_steps();
        
        $c=array();
        // finish order
        $this->FormHelper->init();

        //make temporary order object:
 
        
             

 
        // write sessiondata to record ---------- BEGIN
        $data=$this->FormHelper->getFormData();
        if ($data) {
            $tid=$this->FormHelper->CurrentTransactionID;
            
            //find in DB ?
            $this->record=MwShop::getOrderByTransactionID($tid);
            
            if(!$this->record) {
                $this->record=new MysiteShopOrder;
            }

            // check promocode again on order-completion ---------- BEGIN
            $code=$this->FormHelper->FieldValue('promocode');
            if ($code) {
                //$codeCheck=$this->checkPromoCode($code);
            
                if (is_array($codeCheck) && $codeCheck['error']) {
                        $c['PromoCodeMessage']="<div class='alert alert-error'><i class='icon-warning-sign'></i> ".$codeCheck['msg']."</div>";

                    $this->FormHelper->setFormDataField('PromoCode', '');
                    $this->FormHelper->write();
                    $this->FormHelper->setupMwForm();
                    return $c;
                } else {
                    $PromoCode=$codeCheck['PromoCode'];
                }
            }
            // check promocode again on order-completion ---------- END

            $this->record->update($this->FormHelper->FormData);
            $this->record->TransactionID=$tid;
            $this->record->CartJSON=json_encode($this->Shop->getCartData4Display($this->FormHelper->FormData));
            $this->record->update($data);
            $this->record->write();
            $this->record->write();//set ordernr on 2nd write

            if ($PromoCode) {
                //mark PromoCode as used
                $PromoCode->useForOrder($this->record);
            }
        
            // write sessiondata to record ---------- END
        } else {
            //data was already deleted (on reload maybe)
            $tid=$this->FormHelper->CurrentTransactionID;
            if ($tid) {
                //try to find saved record by transactionid
                $this->record=DataObject::get('MysiteShopOrder')->filter('TransactionID', $tid)->First();
            }
        }
        
        
      
        
        
        // check if payment is needed ---------- BEGIN

        if ($this->record  && $this->record->PaymentType=='sofort' && $this->record->PaymentStatus!='paid') {
            return $this->sofort_start();
        }


        // check if payment is needed ---------- END
        if ($this->record) {
            $this->record->finalizeOrder();
            $this->record->sendEmailToMerchant();
            $this->record->sendEmailToCustomer();
        }

        //remove cart from session
        $this->FormHelper->resetFormDataSession();
        Mwerkzeug\MwSession::set('FormHelperTransactionID', "");
        //clear session somehow
        $_SESSION['shop_order']=array();


        return $c;
    }
    
    // include formhelper (FormHelper) stuff ---------- BEGIN

    public function getFormHelper() //FormHelper
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new FormHelper($this);
        }
        return $this->cache[__FUNCTION__];
    }


    function FormHelper_config()
    {
        $c['MailTemplateName']='Includes/'.$this->ClassName.'_checkout_mail';
    
        return $c;
    }

    public function getCountries()
    {
        
        return singleton('MysiteShopOrder')->getCountries();
    }
      
      
    function FormHelper_setFields()
    {
    
        $p=array(); // ------- new field --------
        $p['label']="Zustellart";
        $p['fieldname']="DeliveryType";
        $p['validation']="required";
          
        $p['type']='radio';
        $p['options']=$this->Shop->getDeliveryTypes($this->FormHelper->FormData);
        $fields[$p['fieldname']]=$p;
    
        $p=array(); // ------- new field --------
        $p['label']="Bezahlvariante";
        $p['fieldname']="PaymentType";
        $p['options']=$this->Shop->getPaymentTypes($this->FormHelper->FormData);
          
        if ($p['options']['vorkasse']) {
            $p['options']['vorkasse'].=" (weitere 0,5% Rabatt)";
        }
              
        $p['validation']="required";
        $p['type']='radio';
         
        $fields[$p['fieldname']]=$p;
              
    
        $p=array(); // ------- new field --------
        $p['label']="Rabatt-Code";
        $p['fieldname']="PromoCode";
        $fields[$p['fieldname']]=$p;
    
    
        $p=array(); // ------- new field --------
        $p['label']="Vorname";
        $p['fieldname']="Firstname";
        $p['validation']="required";
        $adressfields[$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['label']="Nachname";
        $p['fieldname']="Lastname";
        $p['validation']="required";
        $adressfields[$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['label']="Firmenname";
        $p['fieldname']="Company";
        $adressfields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']="Adresse";
        $p['fieldname']="Street";
        $p['validation']="required";
        $adressfields[$p['fieldname']]=$p;
          
          
          
        
        $p=array(); // ------- new field --------
        $p['label']="PLZ";
        $p['fieldname']="Zip";
        $p['validation']="required:true,number: true,min:1000,max:99999";
        $adressfields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']="Ort";
        $p['fieldname']="City";
        $p['validation']="required";
        $adressfields[$p['fieldname']]=$p;
   
        $p=array(); // ------- new field --------
        $p['label']="Land";
        $p['fieldname']="Country";
        $p['validation']="required";
        $p['options']=$this->getCountries();

        $adressfields[$p['fieldname']]=$p;
   
        $p=array(); // ------- new field --------
        $p['label']='Email';
        $p['fieldname']='Email';
        $p['validation']="'required':true,'email':true";
        $adressfields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['label']='Telefon';
        $p['fieldname']="Fon";
        // $p['validation']="required";
        $p['tag_addon']=" placeholder=\"+43-XXX-XXX XX XX\" ";
        $adressfields[$p['fieldname']]=$p;
        
        foreach ($adressfields as $fieldname => $conf) {
            $newfieldname='Billing'.$fieldname;
            $conf['fieldname']=$newfieldname;
            $billingfields[$newfieldname]=$conf;
        }
          
        foreach ($adressfields as $fieldname => $conf) {
            $newfieldname='Delivery'.$fieldname;
            $conf['fieldname']=$newfieldname;
            $deliveryfields[$newfieldname]=$conf;
        }
              
        unset($deliveryfields['DeliveryEmail']);
        
        $fields= $fields + $deliveryfields + $billingfields;
        
        $p=array(); // ------- new field --------
        $p['label']='Bitte um Zustellung an eine andere Anschrift';
        $p['fieldname']="UseDeliveryAdress";
        $p['type']="checkbox";
        $fields[$p['fieldname']]=$p;
        
        
        return $fields;
    }
    

    // include formhelper (FormHelper) stuff ---------- END
    
    
    
    
    


    
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        
        $this->FormHelper->init();
        

        if ($_POST) {
            $this->handleIncomingValues();
        }
        $c=$this->Shop->getCartData4Display($this->FormHelper->FormData);
        $c['items']=MwUtils::convertArray2ArrayList($c['items']);

        $c['BackLink']=array_get($_SERVER, 'HTTP_REFERER');
        
        return $c;
    }


    public function handleIncomingValues($incoming = null)
    {
        if (array_get($_POST, 'payload')) {
            parse_str(array_get($_POST, 'payload'), $post);
        } else {
            $post=$_POST;
        }

        $incomingItems=$post['fdata']['cartitems'];
        $cart=$this->Shop->getCart();
        if (!$incomingItems) {
              $incomingItems=array();
        }


        if ($cart['items']) {
            foreach ($cart['items'] as $key => $value) {
                if (!array_key_exists($key, $incomingItems)) {
                    $cart['items'][$key]['amount']=0;
                }
            }
        }
        
        if ($incomingItems) {
            foreach ($incomingItems as $key => $item) {
                if ($cart['items'][$key]) {
                     $cart['items'][$key]['amount']=$item['amount'];
                }
            }
        }

        // if(array_get($_GET,'d') || 1 ) { $x=$incomingItems; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
        // if(array_get($_GET,'d') || 1 ) { $x=$cart['items']; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
            
        $this->Shop->saveCart($cart);
    }

    
    public function ajaxAddToCart()
    {
        
        $items=array_get($_POST, 'items');
            
        if (is_array($items)) {
            $this->Shop->addToCart($items);
        }
        header('content-type: application/json; charset=utf-8');
        echo('{"status":"ok"}');
        die();
    }
        
        
    public function ajaxDeleteFromCart()
    {
        $key=$this->urlParams['ID'];
        $cart=$this->Shop->Cart;
        $cart['items'][$key]['amount']=0;
        $cart=$this->Shop->saveCart($cart);
        
        return $this->Shop->SidebarCartHTML();
    }
}


class MwShopCartPageBEController extends PageBEController
{
  
  
  

    public function getRawTabItems()
    {
         $items=parent::getRawTabItems();
         $items['10']='Orders';
         return $items;
    }
     
    public function step_10()
    {

        EHP::includeRequirements();
        $this->includePartialBootstrap();
    
        $tpl=SSViewer::fromString($this->getTemplateHtml());
        return $this->renderWith($tpl);
    }
  
    public function getTemplateHtml()
    {
        return <<<'HTML'


        <div class='bootstrap'><div id='ehp'></div></div>

         <script type="text/javascript">

           // include ehp-widget ---------- BEGIN
        
           $('#ehp').EHP({
             'type':'listing',
             'baseurl':'/BE/Pages/edit/$ID/ehp',
             'listparams':{},
             'afterloadList':function(){
                                var self=this;
                                /*
                                var exportbutton=$("<a href='#' ><i class='icon-arrow-down'></i> export as .XLS</a>").css('display','block').click(function(e)
                                                                {
                                                                    var url=self.options.baseurl;
                                                                    var postparams={
                                                                        'options':self.options,
                                                                        'action':'xls_export',
                                                                        'sortby':self.currentSortData,
                                                                        'filter':self.currentFilterData
                                                                    };
                                                                    self.post_to_url(url,postparams);
                                                                });
                                                                $('tr.summary td:first div',this.element).append(exportbutton);
                                */
                            },
             'pagesize':100,
             'use_bootstrap_css':1,
             'texts':{
               'add_text':'none'
             },
             'columns':
               $EHP.getJSONColumnDefinitions.RAW

           });
           // include ehp-widget ---------- END

         </script>
        
        
HTML;
    }
  
    // include ehp stuff ---------- BEGIN

    public function getEHP()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new EHP($this);
        }
        return $this->cache[__FUNCTION__];
    }


    function step_ehp()
    {
        echo $this->EHP->dispatch();
        exit();
    }
      //
      // public function EHP_Items($options=NULL)
      // {
      //   return $this->record->Items();
      // }

    public function EHP_getSortSQL()
    {
        return array("ID"=>"ID desc");
    }

      // public function EHP_roweditHTML($record,$params)
      //      {
      //
      //        return "
      //        <td>
      //         not available
      //            <!-- <input type='text' name='fdata[Title]' value='{$record->Title}'> -->
      //        </td>
      //        ";
      //      }
    
    public function EHP_Columns()
    {
        return explode(',', 'OrderNr,Created,BillingEmail,BillingCity,BillingCountry,TotalPrice');
    }

    public function EHP_getRecordClass()
    {
        return "MwShop_Order";
    }

      
    public function EHP_roweditTpl($record, $params)
    {

        $tpl= '
          <td colspan="11">
             
             $OrderHTML
             
          </td>
          ';

        return $tpl;
    }



    public function EHP_rowButtons()
    {
         return implode("\n", array($this->EHP->defaultButton('inlineedit')));
    }


    public function EHP_rowTpl()
    {
        return '
<td>$OrderNr</td>
<td>$Created</td>
<td>$BillingEmail</td>
<td>$BillingCity</td>
<td>$BillingCountry</td>
<td>$TotalPrice</td>
        ';
    }
      
      
    public function EHP_rowExportArr(&$item)
    {
          
          
        $fields=explode(',', '"   
OrderNr,
Created,
BillingEmail,
BillingCity,
BillingCountry,
TotalPrice       
');
        $row=array();
        foreach ($fields as $key) {
            $key=trim($key);
            $row[$key]=$item->$key;
        }
        return $row;
    }
      

    // include ehp stuff ---------- END
}
