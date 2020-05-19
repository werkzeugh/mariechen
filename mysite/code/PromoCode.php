<?php
    
use SilverStripe\ORM\DataObject;

      
class PromoCode extends DataObject
{
    
    
    private static $db=Array(
      'Code'=>'Varchar(255)',
      'Type'=>'Varchar(255)',
      'ValidUntil'=>'DBDatetime',
      'IssueDate'=>'DBDatetime',
      'IssueNote'=>'Varchar(255)',
      'RedeemDate'=>'DBDatetime',
      'RedeemNote'=>'Varchar(255)',
      'Note'=>'Varchar(255)',
      'DiscountValue1'=>'Varchar(255)',
      'MinimalValue1'=>'Varchar(255)',
      'DiscountValue2'=>'Varchar(255)',
      'MinimalValue2'=>'Varchar(255)',
      'Art'=>'Varchar(255)',
      'Email'=>'Varchar(255)',
    );
    
    private static $has_one=Array(
      'Order'=>'MysiteShopOrder'  
    );

    private static $indexes=Array(
          "CodeUnique"  => [
            'type'    => 'unique',
            'columns' => ['Code'],
          ]
    );
    
    static public function getByCode($code)
    {
        return DataObject::get('PromoCode')->filter('Code',$code)->First();
    }

    static public function getByEmail($code)
    {
        return DataObject::get('PromoCode')->filter('Email',$code)->First();
    }
     
     
    public function alreadyUsed()
    {
        return ($this->OrderID?TRUE:FALSE);
    }
    
    
    public function useForOrder($order)
    {
        if($order && $order->ID)
        {
            $this->OrderID=$order->ID;
            $this->RedeemDate=Datum::mysqlDate(time());
            $this->write();
        }
    }
    
    public function getRabattTyp()
    {

        if(strstr($this->DiscountValue1,'%'))
            return 'percentage';
        if($this->DiscountValue1>1)
            return 'value';
    }
    
    
    
    public function getPercentageDiscount($sum,$percentage)
    {
        $p=str_replace('%','',$percentage)*1;
        
        if($p)
        {
            $ret=$sum*($p/100);
            return $ret;
        }
        
        return $p;
    }
    
    
    public function augmentCart($cart)
    {


        if($this->Type=='Kennenlerneinkauf')
        {
            $discount=0;
            foreach ($cart['items'] as $item) {
                
                $discount4product=$item['singleprice']-$item['kennenlernprice'];
                            
                if($discount4product>0 && $item['kennenlernprice'])                        
                  $discount+=$discount4product*$item['amount'];
                            

            }
            $text="Kennenlerneinkaufs-Rabatt";
            
        }
        elseif($this->RabattTyp=='percentage')
        {
        
            
            if($this->DiscountValue1)
            {
                if($cart['summebrutto']>=$this->MinimalValue1)
                {
                    $discount=0;
                    $numSonderartikel=0;
                    foreach ($cart['items'] as $key=>$item) {

                            
                            $discount4product=$this->getPercentageDiscount($item['baseprice'],$this->DiscountValue1);
                            
                            $maxDiscount4product=($item['baseprice']-$item['maxdiscountedprice']);
                            

                            // echo "<li> <strong>{$item['product_title']}</strong>:";
                            // echo "<li> discount4product: $discount4product";
                            // echo "<li> maxDiscount4product: $maxDiscount4product";
                            if($maxDiscount4product > 0 && $discount4product>$maxDiscount4product)
                            {
                                 $discount4product=$maxDiscount4product;
                                 $numSonderartikel++;
                                 $addon=" <span class='sonderprodukt'>*Sonderprodukt max.Rabatt ".$item['maxdiscount'].'%</span>';
                            
                            $cart['items'][$key]['variant_title'].=$addon;
                            
                        }

                            if($discount4product>0 && $item['baseprice']>$item['singleprice']) // bereits rabattiert ?
                            {
                                $alreadydiscounted4Product=($item['baseprice']-$item['singleprice']);
                                // echo "<li> alreadydiscounted4Product: $alreadydiscounted4Product";
                                
                                $discount4product-= $alreadydiscounted4Product;
                                $numSonderartikel++;
                                
                            }
                            
                            // echo "<li> discount4product: $discount4product";
                            
                            $discount4row=$discount4product*$item['amount'];
                            if($discount4row>0)
                            {
                                $discount+=$discount4row;
                            }

                            // echo "<li> discount4row: $discount4row";
                            // echo "<li> discount: $discount";
                            

                    }
                    $text="{$this->DiscountValue1} Gutschein-Rabatt";
                    if($numSonderartikel)
                    $text.="<br><small>(Sonderprodukte sowie bereits bestehende Aktionen und Rabatte sind vom Gutschein-Rabatt ausgenommen)</small>";
                }
            }

              
                       
        }
        elseif($this->RabattTyp=='value')
        {
        
             $maximalrabatt=0;
             $alreadydiscounted=0;
            foreach ($cart['items'] as $item) {
            
                // $maxRabatt4Product=($item['baseprice']-$item['maxdiscountedprice']);
//    
//                 if($maxRabatt4Product>0)
//                 {
//                     $maximalrabatt+=($maxRabatt4Product*$item['amount']);
//                 }

                if($item['baseprice']>$item['singleprice']) //skip already discounted products
                {
                    $numSonderartikel++;
                    continue;
                }
            
                $unrabattierte_summe+=$item['baseprice']*$item['amount'];
                
            }
            
            // echo "<li>maximalrabatt= $maximalrabatt";
//             echo "<li>alreadydiscounted= $alreadydiscounted";

            // if( $discount > $maximalrabatt )
  //           {
  //               $discount=$maximalrabatt;
  //               $numSonderartikel=1;
  //           }
        
        

            if($this->DiscountValue1 && $this->MinimalValue1)
            {
                $text=MwShop::formatPrice($this->DiscountValue1)." Gutschein-Rabatt bei Einkauf ab ".MwShop::formatPrice($this->MinimalValue1);

                if($unrabattierte_summe>=$this->MinimalValue1)
                {
                    $discount=$this->DiscountValue1;
                }
            }

            if($this->DiscountValue2 && $this->MinimalValue2)
            {
                $text=MwShop::formatPrice($this->DiscountValue1) ." oder ".MwShop::formatPrice($this->DiscountValue2)." Gutschein-Rabatt bei Einkauf ab ".MwShop::formatPrice($this->MinimalValue1)." oder ".MwShop::formatPrice($this->MinimalValue2);


                if($unrabattierte_summe>=$this->MinimalValue2)
                {
                    $discount=$this->DiscountValue2;
                }
            }
            
        
            
            if($numSonderartikel)
            {
                $text.="<br><small>(Sonderprodukte sowie bereits bestehende Aktionen und Rabatte sind vom Gutschein-Rabatt ausgenommen)</small>";
    
            }            
            
        
        
            
        }


        if($discount<0)
            $discount=0;

        if($discount>0 || $numSonderartikel)
        {
            $rabattitem['product_title']=$text;
            $rabattitem['amount']="1";
            $rabattitem['price']=-$discount;
            $rabattitem['price_str']=MwShop::formatPrice($rabattitem['price']);
            if($rabattitem['price_str']=="")
                $rabattitem['price_str']='0,00';
        }
                
        if($rabattitem)
        {
            $cart['items'][]=$rabattitem;
            $cart['summebrutto']+=$rabattitem['price'];
        }        
                
        return $cart;
        
    }
    
    
    static public function createKennenlernCodeForEmail($email)
    {

        $email=trim($email);
        $email2find=strtolower($email);
        if($email)
        {
            $already_exists=PromoCode::getByEmail($email2find);
            if($already_exists)
            {
                return "already_sent";
            }
            
        }
        
        $newcode=new PromoCode;
        $newcode->Code=PromoCode::generateKennenlernCode();
        $newcode->Type='Kennenlerneinkauf';
        $newcode->Email=$email2find;
        $newcode->write();
        if($newcode->ID)
        {
            $from=MwShop::conf('MerchantEmail');
            $subject="Dein Kennenlern-Einkauf bei www.derdoppelstock.at";

            $html="Hier ist Dein Gutschein-Code für Deinen Kennenlern-Einkauf auf www.derdoppelstock.at: 
            <div>&nbsp;</div> 
            <b>{$newcode->Code}</b>
            <div>&nbsp;</div>
            Viel Vergnügen beim Einkauf !
            ";
            $mailhtml= MysiteMail::makeNiceHTML($html);
               
            $mail = new Email($from, $email ,$subject,$mailhtml);
            $mail->send();
            MwMailLog::add($mail); // log this mail
            return 'ok';
        }
        
        return 'error';
    
        
    }
    
    
    public function generateKennenlernCode()
    {

            $characters = '1234567890ABCDEFGHIJKLMNPQRST';
            $len=4;
            $newcode = '';
            for ($i = 0; $i < $len; $i++) {
                $newcode .= $characters[rand(0, strlen($characters) - 1)];
            }
            $newcode=Date('Ym').$newcode;
 
        return $newcode;
    }
}



class PromoCodeController extends  FrontendPageController
{
    function ng_request_kennenlerncode()
    {
        
        header('content-type: application/json; charset=utf-8');
        $jsonInput = file_get_contents('php://input');
        
        if($jsonInput)
        {
            $args=json_decode($jsonInput,1);
            if($args)
            {
                $email=$args['email'];
            }
        }
        
        $ret=Array();
        $ret['status']=PromoCode::createKennenlernCodeForEmail($email);

        //$ret['status']='already_sent';
        
        header('content-type: application/json; charset=utf-8');
        echo json_encode($ret);
        die('');
    }
    
    
    
}



class PromoCodeBEController extends  BackendPageController
{
  
  
  public function init()
  {
      
      if(!Permission::check("ENTER_BE"))
          Security::permissionFailure();
      parent::init();
      
      //$this->includePartialBootstrap();
      
  }
  
  public function index(SilverStripe\Control\HTTPRequest $request)
  {
      EHP::includeRequirements(Array("skip_bootstrap_setup"=>0));
      
      
      return Array();
  }
  
  
  
  // include ehp stuff ---------- BEGIN

   public function getEHP()
   {
     if(!isset($this->cache[__FUNCTION__]))
     {
       $this->cache[__FUNCTION__]=new EHP($this);
     }
     return $this->cache[__FUNCTION__];
   }


   function ehp()
   {
       echo  $this->EHP->dispatch();
       exit();
   }
   

   
   // public function EHP_BaseItems($options=NULL)
   // {
   //       return DataObject::get('PromoCode');
   // }
   // 
  
   function EHP_getRecordClass()
   {
      
       return 'PromoCode';
   }

   function EHP_getJSONColumnDefinitions()
   {
       return trim("
       'ID':{label:'Nr','sortable':1,filter:{'type':'auto'},hide_on_load:1},
       'Code':{sortable:1,filter:{'type':'auto'}},
       'Email':{sortable:1,filter:{'type':'auto'}},
       'Type':{sortable:1,filter:{'type':'auto'},hide_on_load:1},
       'DiscountValue1':{sortable:1,filter:{'type':'auto'}},
       'MinimalValue1':{sortable:1,filter:{'type':'auto'}},
       'DiscountValue2':{sortable:1,filter:{'type':'auto'}},
       'MinimalValue2':{sortable:1,filter:{'type':'auto'}},
       'ValidUntil':{sortable:1,hide_on_load:1},
       'IssueDate':{sortable:1,hide_on_load:1},
       'IssueNote':{sortable:1,filter:{'type':'auto'}},
       'RedeemDate':{sortable:1,filter:{'type':'auto'}},
       'RedeemNote':{sortable:1,hide_on_load:1,filter:{'type':'auto'}},
       'Note':{sortable:1,filter:{'type':'auto'}},
       'Art':{sortable:1,hide_on_load:1},
       ",", ");
       
   }
   
   
   
   // include ehp stuff ---------- END
   
   
   public function EHP_roweditFormFields($record,$params)
   {
   
       return $fields;
   }


   public function EHP_initFormFields($record,&$formfields,$params)
   {
     
       // $p=Array();
       // $p['fieldname']='Price2';
       // $p['label']='Preis / Packung (20stk)';
       // $formfields[$p['fieldname']]=$p;
       // 
       // 
       // $p=Array();
       // $p['fieldname']='BaseUnitID';
       // $p['label']='Basis-Einheit';
       // $p['options']=DataObject::get('PromoCode')->filter('Multiplier','1')->map();
       // $formfields[$p['fieldname']]=$p;
       
       
   }

   public function EHP_roweditTpl($record,$params)
   {
       return <<<'HTML'
       <td colspan="$ColCount">
           <div class="formgroup">
               <ul>
                   <li>$record.ID</li>
                   <li>$FormField('Code').HTML</li>
                   <li>$FormField('Type').HTML</li>
                   </ul><ul>
                   <li>$FormField('DiscountValue1').HTML</li>
                   <li>$FormField('MinimalValue1').HTML</li>
                   <li>$FormField('DiscountValue2').HTML</li>
                   <li>$FormField('MinimalValue2').HTML</li>
                   </ul><ul>
                   <li>$FormField('ValidUntil').HTML</li>
                   <li>$FormField('IssueDate').HTML</li>
                   <li>$FormField('IssueNote').HTML</li>
                   <li>$FormField('RedeemDate').HTML</li>
                   <li>$FormField('RedeemNote').HTML</li>
                   <li>$FormField('Note').HTML</li>
                   <li>$FormField('Art').HTML</li>
               </ul>
           </div>
       </td>
HTML;
       
   }
   
   
 
   public function EHP_rowButtons()
   {
      return implode("\n",Array($this->EHP->defaultButton('inlineedit'),$this->EHP->defaultButton('hide_unhide'),$this->EHP->defaultButton('delete'),$this->EHP->defaultButton('duplicate')));
   }
 
   
   
   public function EHP_columnTemplates()
   {

       return Array(
           // 'tag'        => '<small>$splitted(tag)</small>',
           //   'BaseUnitID'   => '<small>$BaseUnit.Title</small>',
  //          'StartDate'  => '<small>$Datum("StartDate").GermanDate("d.m.Y")</small>',
  //          'EndDate'    => '<small>$Datum("EndDate").GermanDate("d.m.Y")</small>',
  //          'created_at' => '<small>$Datum("created_at").GermanDate("d.m.Y")</small>',
  //          'updated_at' => '<small>$Datum("updated_at").GermanDate("d.m.Y")</small>',
          );  
       
   }
   
   // include ehp stuff ---------- END
  
  
  
}
    
    
    
    
    
    