<?php

use SilverStripe\Control\Controller;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use Tarsana\Functional as F;

use SilverStripe\Core\Environment;

class ProductPage extends Page
{
    private static $db= array(
        'Text'              =>'HTMLText',
        'FileFolderName'    =>'Varchar(255)',
        'ProductNr'         =>'Varchar(255)',
        'SeoTitle'         =>'Text',
        'ShortText'         =>'Text',
        'ListText'         =>'Varchar(255)',
        'Preisdarstellung'  =>"Enum('Liste,Dropdown-Box','Liste')",
        'C4Pjson_ShopItems' =>'Text',
        'C4Pjson_Pictures'  =>'Text',
        'Material'          =>'Text',
        'Dimensions'         =>'Text',
        'Weight'            =>'Text',
        'Discount'          =>'IntNull',
        'InStockType'       =>"Enum('Typ1,Typ2,Typ3','Typ1')",
        'Price'             =>'Decimal(8,2)',
        'Keywords'          =>'Varchar(255)',
        'NewTags'           =>'Varchar(255)',
    );
    
    private static $defaults=array(
        'NewTags'           =>'',
        
    );
    
    public $CachingMode='none';
    
    public function allowedChildren()
    {
        return ['ProductVariant'];
    }
    
    public function getIconForPageTree()
    {
        return 'fa fa-shopping-bag';
    }
    
    private static $has_one=array(
        //   'Brand'=>'BrandPage',
        'Picture'=>'MwFile',
        'FileRoot'=>'MwFile',
    );
    
    
    public function PriceStr()
    {
        return  $this->formatPrice($this->MyPrice());
    }
    
    
    public static function formatPrice($val)
    {
        if (strstr($val, ",")) {
            $val=str_replace(",", ".", $val);
        }
        if ($val<>0) {
            $str=number_format($val, 2, ',', '.');
            
            $str=str_replace(",00", ",-", $str);
            return "€$str";
        }
    }
    
    public function MyPrice()
    {
        return  $this->Price;
    }
    
    
    
    public function Product()
    {
        return $this;
    }
    
    public function generateKeywords()
    {
        $parts=[];
        foreach ($this->Parents() as $p) {
            if ($p->Title=='Produkte') {
                break;
            }
            if ($p->ID!=$this->ID) {
                array_push($parts, $p->Title);
            }
        }
        $str=implode(',', $parts);
        $bn=$this->BrandName();
        if ($bn) {
            $str="$bn | $str";
        }
        return $str;
    }

    public function AllProductPictures()
    {
        $al=new ArrayList();
        $record['PictureID']=$this->PictureID;
        $p=new ProductPage_C4P_Picture($this, 'C4Pjson_Pictures', 'dummy', $record);
        $al->push($p);
        $pp=$this->C4P->getAll_Pictures;
        if ($pp && $pp->count()) {
            $al->merge($pp);
        }
    
        return $al;
    }

    public function myBrand()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            if ($this->BrandID) {
                $brand=$this->Brand();
            } else {
                $brand=null;
            }
            $this->cache[__FUNCTION__]=$brand;
        }
    
        return $this->cache[__FUNCTION__];
    }

    public function BrandName()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $name="";
            if ($this->myBrand()) {
                $name=$this->myBrand()->Title;
            }
            $this->cache[__FUNCTION__]=$name;
        
            //debug mwuits
            if (!$this->PriceMax) {
                $this->write();
            }
        }
    
        return $this->cache[__FUNCTION__];
    }

    // include c4p stuff ---------- BEGIN

    public function getC4P()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=new C4P($this);
        }
        return $this->cache[__FUNCTION__];
    }

    // include c4p stuff ---------- END
    public function C4P_Place_ShopItems()
    {
        $conf['allowed_types']= array();
        $conf['allowed_types']['ProductPage_C4P_ShopItem']['label'] = "Shop-Item";
        return $conf;
    }

    public function C4P_Place_Pictures()
    {
        $conf['allowed_types']= array();
    
        $conf['allowed_types']['ProductPage_C4P_Picture']['label'] = "Shop-Item";
        return $conf;
    }


    public function onAfterWrite()
    {
        parent::onAfterWrite();
    
        TagEngine::singleton()->handlePageTagsForProductId($this->ID);
    }

    public function getBETitle()
    {
        return $this->Title;
    }


    public function myDiscount()
    {
        //rabatt-value ohne rabatteg, mit berücksichtigung von max-limits
    
        $discount=$this->owner->FallbackedDiscountValue('Discount');
        $maxdiscount=$this->owner->FallbackedDiscountValue('MaxDiscount');
    
        if ($discount>$maxdiscount) {
            $ret=$maxdiscount;
        }
    
        $ret=$discount;
        if ($ret!=$this->DiscountCache) {
            $this->DiscountCache=$ret;
            $this->write();
        }
    
        return $discount;
    }


    // -----------------------------------

    // -----------------------------------


    public function BasePriceStr()
    {
        $str=MwShop::formatPrice($this->PriceMin);
    
        if ($this->PriceMin<$this->PriceMax) {
            $str="ab $str";
        }
        return $str;
    }


    public function getProductVariants()
    {
        return $this->UnHiddenChildren();
    }


    public function getUnHiddenProductVariants()
    {
        return $this->UnHiddenChildren();
    }

    public function getImageFolderPath()
    {
        return "/products/".$this->Parent()->URLSegment."/".$this->getImageFolderName();
    }

    public function getImageFolder()
    {
        $f= MwFile::getByFilename($this->getImageFolderPath());
    
        if ($f && $f->ID!=$this->FileRootID) {
            $this->FileRootID=$f->ID;
            $this->write();
            echo "\n<li>update filerootid";
        }
        return $f;
    }

    public function getImageFolderName()
    {
        if ($this->FileFolderName) {
            return $this->FileFolderName;
        }
        return $this->URLSegment;
    }

    public function getMainImage()
    {
        return $this->getImages()->First();
    }

    public function getImages()
    {
        $f=$this->getImageFolder();
        if ($f) {
            return $f->getSortedChildren();
        }
        return new ArrayList();
    }

    public function currentVariantId()
    {
        $v=$this->getUnHiddenProductVariants();
        return $v->First()->ID;
    }

    public function getImagesWithTagIds()
    {
        $imgs=F\Stream::of($this->getImages()->toArray())
    ->map(function ($file) {
        $img = $file->CroppedImage(310*3, 390*3);
        $bigimg = $file->CroppedImage(310*4.5, 390*4.5);
        $smallimg = $file->CroppedImage(600, 755);
        
        return  [
            'img' => $img,
            'bigimg' => $bigimg,
            'smallimg' => $smallimg,
            'tagIdString'=>TagEngine::singleton()->getTagIdStringForFile($img->ID)
        ];
    })
    ->result();
    
        return new ArrayList($imgs);
    }


    public function getMaterialString()
    {
        return F\Stream::of(TagEngine::singleton()->getTagIdsForPage($this->ID))
    ->map(function ($id) {
        return TagEngine::singleton()->getTagForId($id);
    })
    ->filter(function ($tag) {
        return $tag && ($tag->getTagType()->URLSegment=='material');
    })
    ->map(function ($tag) {
        return $tag->Title;
    })
    ->join(", ")
    ->result();
    }

    public function getUnHiddenProductVariantsWithColors()
    {
        $variants=F\Stream::of($this->getUnHiddenProductVariants()->toArray())
    ->map(function ($variant) {
        return  ['variant' => $variant,
        // 'tagIdString'=>TagEngine::singleton()->getTagIdStringForPage($variant->ID),
        'imgIdString'=>$variant->getImgIdString(),
        'colorString'=>$variant->getColorString(),
    ];
    })->result();

        return new ArrayList($variants);
    }
    public function getVariant($variantID)
    {
        if (!isset($this->cache[__FUNCTION__][$variantID])) {
            $variants=$this->C4P->getAll_ShopItems;
            foreach ($variants as $v) {
                if ($v->Number==$variantID) {
                    $this->cache[__FUNCTION__][$variantID]=$v;
                }
            }
            if (!$this->cache[__FUNCTION__][$variantID]) {
                $this->cache[__FUNCTION__][$variantID]=null;
            }
        }
        return $this->cache[__FUNCTION__][$variantID];
    }

    public function getcmsTitle()
    {
        return $this->Title;
    }

    public function HTMLTitle()
    {
        return $this->getField('Title')." - ".$this->getField('SeoTitle');
    }

    public function myShortText()
    {
        if ($this->ShortText) {
            return nl2br($this->ShortText);
        } else {
            return $this->ListText;
        }
    }




    // public function isVisibleInBpPageTree()
    // {
    //     if ($this->ID == Controller::curr()->CurrentPageID) {
    //         return true;
    //     }
        
    //     return false;
    // }
        
    public function getPicture()
    {
        return $this->Picture();
    }
        
    public function getPictures()
    {
        $ret= $this->C4P->getAll_Pictures;
            
            
        return $ret;
    }
        
        
    public function getShopItems()
    {
        $ret= $this->C4P->getAll_ShopItems;
            
        if (!$ret) {
            $ret=[];
        }
        return $ret;
    }
        
    public function AvailabilityInfoForZeroStock()
    {
        $arr=[
                'Typ1'=>'<a href="mailto:office@derdoppelstock.at">Bitte per E-Mail anfragen</a>',
                'Typ2'=>'Versandfertig in 3-5 Werktagen',
                'Typ3'=>'Versandfertig in 1-2 Wochen',
            ];
            
        return $arr[$this->InStockType];
    }
        
    public function preloadImages()
    {
        // $d=Environment::getEnv('ASSETS_DIR');
        // die("\n\n<pre>mwuits-debug 2019-11-23_16:38 ".print_r($d, true));
            
        foreach ($this->getImages() as $file) {
            echo "\n\n - {$file->Filename}";
                
                
            echo "\n   - ".basename($file->CroppedImage(600, 755)->Link()); //baglist
                echo "\n   - ".basename($file->CroppedImage(310*3, 390*3)->Link()); //bagdetail
                echo "\n   - ".basename($file->CroppedImage(310*4.5, 390*4.5)->Link()); //bagdetail
                echo "\n   - ".basename($file->SetFittedSize(100, 100)->Link()); //backend
        }
    }
        
    public function getTranslated($name)
    {
        return parent::getTranslated($name);
    }
}
    
    
    
    class ProductPageController extends PageController
    {
        public function index(SilverStripe\Control\HTTPRequest $request)
        {
            
            // Requirements::javascript("bower_components/angular-14/angular.min.js");
            
            Requirements::javascript("node_modules/swiper/js/swiper.min.js");
            Requirements::CSS("node_modules/swiper/css/swiper.min.css");
            
            
            return parent::index($request);
        }
        
        
        
        
        
        public function getCodeForProductDetailWidgets()
        {
            
            // $settings['baseurl']=$this->Link();
            $settings['liveApiUrl']='https://'.(array_key_exists('HTTP_X_ORIGINAL_HOST', $_SERVER)?$_SERVER['HTTP_X_ORIGINAL_HOST']:$_SERVER['HTTP_HOST'])."/ex";
            $settings['lang']=$this->CurrentLanguage();
            $settingsAsJson=json_encode($settings);
            
            
            return '
            <script>
            window.vueAppConf='.$settingsAsJson.';
            </script>
            <script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/bagdetail/js/chunk-vendors.js').'"></script>
            <script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/bagdetail/js/app.js').'"></script>';
        }
        
        public function getShopItems()
        {
            $items=[];
            foreach ($this->dataRecord->C4P->getAll_ShopItems as $si) {
                $item=array(
                    'title'=>$si->Title,
                    'nr'=>$si->Number,
                    'articleId'=>$si->ArticleID,
                    'maxAmount'=>0
                );
                
                
                
                $item['inStock']=$si->InStock*1;
                if ($si->InStock>0) {
                    $item['maxAmount']=$si->InStock;
                } elseif ($si->maxForDropdownField()) {
                    $item['maxAmount']=$si->maxForDropdownField();
                }
                
                $item['maxAmount']= (int) $item['maxAmount'];
                $items[]=$item;
            }
            
            return $items;
        }
        
        
        public function quotesafe($str)
        {
            return str_replace("'", '&#27;', $str);
        }
        
        public function settingsAsJson()
        {
            $ret['shopItems']=$this->getShopItems();
            $ret['isHidden']=($this->dataRecord->Hidden)?true:false;
            $ret['zeroStockInfo']=$this->dataRecord->AvailabilityInfoForZeroStock();
            $ret['productId']=$this->dataRecord->ID;
            $ret['baseurl']=$this->Link();
            return $this->quotesafe(json_encode($ret));
        }
    }
    
    
    class ProductPageBEController extends PageBEController
    {
        public function getRawTabItems()
        {
            $items=array(
                "10"    => "Product",
                "12"     => "Product-Variants",
                "14"     => "Images",
                "16  "     => "ImagesNeu",
                "23"                        => "SEO",
                
                // "20"     => "Settings",
            );
            
            return $items;
        }
        
        
        
        public function getAllTagTypes()
        {
            return TagEngine::singleton()->getAllTagTypesString();
        }
        
        public function step_makealias()
        {
            $alias=ProductPageAlias::createAlias($this->record);
            if ($alias->ID) {
                $this->redirect($alias->EditLink());
            }
        }
        
        public function step_12()
        {
            $this->NoDataForm=true;
            
            if ($_POST['SortedIds']) {
                foreach (explode(",", $_POST['SortedIds']) as $n=>$id) {
                    $id=(int) $id;
                    if ($id) {
                        $record=DataObject::get_by_id("ProductVariant", $id);
                        if ($record) {
                            $record->Sort=($n+1)*10;
                            // echo "\n<li>".$record->Title." - ".$record->Sort;
                            $record->write();
                        }
                    }
                }
            }
            
            Requirements::javascript("mysite/thirdparty/html5sortable/dist/html5sortable.min.js");
            
            $html=TagEngine::getCodeForBackendWidgets();
            if ($_POST['taggable_ids']) {
                TagEngine::singleton()->updateTags($_POST['taggable_ids'], $_POST['add_tags'], $_POST['remove_tags']);
            }
            
            $c['Variants']=$this->record->getProductVariants();
            $c['ProductTagsIdString']=TagEngine::singleton()->getTagIdStringForPage($this->record->ID);
            
            return $this->customise($c)->renderWith("Includes/ProductPageListVariants").$html;
        }

        
        public function step_16()
        {
            $html=$this->getCodeForBackendWidgets();

            return "
            <vbe-imgfolder class='vueapp-vbe' path='/products/{$this->ID}'></vbe-imgfolder>".$html;
        }
        
        public function step_14()
        {
            if ($_POST['SortedIds']) {
                foreach (explode(",", $_POST['SortedIds']) as $n=>$id) {
                    $id=(int) $id;
                    if ($id) {
                        $record=DataObject::get_by_id("MwFile", $id);
                        if ($record) {
                            $record->Sort=($n+1)*10;
                            // echo "\n<li>".$record->Title." - ".$record->Sort;
                            $record->write();
                        }
                    }
                }
            }
            
            $this->NoDataForm=true;
            Requirements::javascript("mysite/thirdparty/html5sortable/dist/html5sortable.min.js");
            
            $html=TagEngine::getCodeForBackendWidgets();
            if ($_POST['taggable_ids']) {
                TagEngine::singleton()->updateTags($_POST['taggable_ids'], $_POST['add_tags'], $_POST['remove_tags']);
            }
            
            $c['Variants']=$this->record->getImages();
            
            $c['ProductTagsIdString']=TagEngine::singleton()->getTagIdStringForPage($this->record->ID);
            $c['VariantTagsIdString']=TagEngine::singleton()->getTagIdStringForChildren($this->record->ID);
            
            
            
            
            $this->formFields[$p['fieldname']]=$p;
            
            return $this->customise($c)->renderWith("Includes/ProductPageListImages").$html;
        }
        
        public function step_10()
        {
            
            // FreshTagBase::includeRequirements();
            BackendHelpers::includeTinyMCE();
            
            //define all FormFields for step "Title"
            $p=array(); // ------- new field --------
            $p['fieldname']="Title";
            $p['label']="Name";
            $this->formFields[$p['fieldname']]=$p;
            
            
            $p              = [];
            $p['fieldname'] = "Config_GoogleTitle";
            $p['label']     = "Google Title";
            $this->formFields[$p['fieldname']]=$p;
            
            $p=array(); // ------- new field --------
            $p['fieldname']="URLSegment";
            $p['label']="Slug";
            $this->formFields[$p['fieldname']]=$p;
            
            
            // $p=array(); // ------- new field --------
            // $p['fieldname']="Material";
            // $p['label']="Material";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            // $p=array(); // ------- new field --------
            // $p['fieldname']="Dimensions";
            // $p['label']="Dimensions";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            // $p=array(); // ------- new field --------
            // $p['fieldname']="Weight";
            // $p['label']="Weight";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            
            
            
            
            //define all FormFields for step "Title"
            $p=array(); // ------- new field --------
            $p['label']="Price <i>(EUR, incl.VAT)</i>";
            $p['type']="text";
            $p['fieldname']="Price";
            $this->formFields[$p['fieldname']]=$p;
            
            
            //define all FormFields for step "Title"
            //  $p=Array(); // ------- new field --------
            //  $p['label']="Hersteller";
            //  $p['fieldname']="BrandID";
            //  $p['options']=$this->getBrands()->map();
            //  $this->formFields[$p['fieldname']]=$p;
            
            
            
            //define all FormFields for step "Title"
            $p=array(); // ------- new field --------
            $p['fieldname']="ProductNr";
            $p['label']="Product-Nr:";
            $this->formFields[$p['fieldname']]=$p;
            
            
            
            // $allTagTypes=TagEngine::singleton()->getAllTagTypesString();
            // $p=array(); // ------- new field --------
            // $p['fieldname']="NewTags";
            // $p['type']="hidden";
            // $p['default_value']=TagEngine::singleton()->getTagIdStringForPage($this->record->ID);
            // $p['label']="Tags";
            // $p['note']="(gelten für alle Produkt-Varianten)";
            // $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='$allTagTypes' ref_id='input_NewTags'></eb-tag-editor>";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            // $TagIdString=TagEngine::singleton()->getTagIdStringForChildren($this->record->ID);
            
            // $p=array(); // ------- new field --------
            // $p['fieldname']="DummyTags";
            // $p['type']="html";
            // $p['label']="Tags der einzelnen Produktvarianten";
            // $p['note']="(diese sollten dann nicht bei den normalen Eigenschaften angegeben werden)";
            // $p['html']="<eb-tag-viewer class='vueapp-eb_backend' tagids='$TagIdString'></eb-tag-viewer>".TagEngine::singleton()->getCodeForBackendWidgets();
            // $this->formFields[$p['fieldname']]=$p;
            
            //define all FormFields for step "Title"
            $p=array(); // ------- new field --------
            $p['fieldname']="ListText";
            $p['label']="List Text <i>used in Listings of this product</>";
            $p['type']="textarea";
            $p['styles']="height:100px";
            // $p['rendertype']='beneath';
            $this->formFields[$p['fieldname']]=$p;

            $p=array(); // ------- new field --------
            $p['fieldname']="ShortText";
            $p['type']="textarea";
            $p['styles']="height:100px";
            $p['label']="Short Text <i> if not defined, the List-Text is used</i>";
            // $p['rendertype']='beneath';
            $this->formFields[$p['fieldname']]=$p;
            
            
            
            // //define all FormFields for step "Title"
            // $p=array(); // ------- new field --------
            // $p['label']="MaximalRabatt <i>in %</i>";
            // $p['styles']="width:80px";
            // $p['tag_addon']=" placeholder=\"default\" ";
            // $p['fieldname']="MaxDiscount";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            // $p=array(); // ------- new field --------
            // $p['label']="Verhalten bei Lagerstand=0";
            // $p['options']=$this->getInStockTypes();
            // $p['default']="Typ1";
            // $p['fieldname']="InStockType";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            // //define all FormFields for step "Title"
            // $p=array(); // ------- new field --------
            // $p['label']="Rabatt <i>in %</i>";
            // $p['styles']="width:80px";
            // $p['tag_addon']=" placeholder=\"default\" ";
            // $p['fieldname']="Discount";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            // //define all FormFields for step "Title"
            // $p=array(); // ------- new field --------
            // $p['label']="KennenlernRabatt <i>in %</i>";
            // $p['styles']="width:80px";
            // $p['tag_addon']=" placeholder=\"default\" ";
            // $p['fieldname']="KennenlernDiscount";
            // $this->formFields[$p['fieldname']]=$p;
            
            
            
            
            //define all FormFields for step "Title"
            $p=array(); // ------- new field --------
            $p['label']="Description";
            $p['type']="textarea";
            $p['styles']="height:300px;width:500px";
            $p['fieldname']="Text";
            $p['addon_classes']="tinymce";
            // $p['rendertype']='beneath';
            $this->formFields[$p['fieldname']]=$p;
            
            
            
            // //define all FormFields for step "Title"
            // $p=array(); // ------- new field --------
            // $p['fieldname']="FileFolderName";
            // $p['label']="Image-Folder-Name <i>nur setzen wenn file-folder anders als URL heisst</i>";
            // ;
            // $this->formFields[$p['fieldname']]=$p;
        }
        
        public function getInStockTypes()
        {
            return [
                'Typ1'=>'Typ 1: bitte per E-Mail anfragen ',
                'Typ2'=>'Typ 2: Versandfertig in 3-5 Werktagen (Lagerstand im Front-End: 5 )',
                'Typ3'=>'Typ 3: Versandfertig in 1-2 Wochen (Lagerstand im Front-End: 5 )',
            ];
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
    }
    
    
    
    class ProductPage_C4P_ShopItem extends C4P_Element
    {
        public function formattedPrice($value)
        {
            return MwShop::formatPrice($value);
        }
        
        
        public function getArticleID()
        {
            return $this->Mainrecord->ID."^".$this->Number;
        }
        
        public function getArrForMax($m)
        {
            $arr=array();
            $n=1;
            $max=$m;
            
            if ($max>10) {
                $max=10;
            }
            
            while ($n<=$max) {
                $arr[$n]=$n;
                $n++;
            }
            return $arr;
        }
        
        public function maxForDropdownField()
        {
            $max=$this->InStock;
            if ($max==0 && $this->Mainrecord->InStockType!='Typ1') {
                $max=5;
            }
            return $max;
        }
        
        public function getAmountDropDownField()
        {
            $p=array(); // ------- new field --------
            $p['fieldname']      = "amount-".$this->ArticleID;
            $p['addon_classes']  = "amount_dd";
            $p['type']           = "select";
            $p['text_options']   = $this->getArrForMax($this->maxForDropdownField());
            $f=new MwFormField($p);
            return $f;
        }
        
        public function myAmountsDD()
        {
            $options=array();
            foreach ($this->myAmounts() as $row) {
                $options[$row['amount']]=$row['amount'];
            }
            return $options;
        }
        
        public function myAmountsJSON()
        {
            foreach ($this->myAmounts() as $row) {
                $prices[$row['amount']]=Controller::curr()->Shop->formatPrice($row['price']);
            }
            
            return json_encode($prices);
        }
        
        
        
        public function myBasePrice()
        {
            if (!isset($this->cache[__FUNCTION__])) {
                $p=$this->record['Price'];
                if (strstr($p, ",")) {
                    $p=str_replace(",", ".", $p);
                }
                $this->cache[__FUNCTION__]=$p;
            }
            return $this->cache[__FUNCTION__];
        }
        
        public function myDiscountedPrice()
        {
            if (!isset($this->cache[__FUNCTION__])) {
                $p=$this->myBasePrice();
                
                if ($this->Mainrecord->myDiscount()) {
                    $p=$this->myBasePrice()-($this->myBasePrice() * ($this->Mainrecord->myDiscount()/100));
                }
                
                $this->cache[__FUNCTION__]=$p;
            }
            return $this->cache[__FUNCTION__];
        }
        
        public function myMaxDiscount()
        {
            return $this->Mainrecord->FallbackedDiscountValue('MaxDiscount');
        }
        
        public function myMaxDiscountedPrice()
        {
            if (!isset($this->cache[__FUNCTION__])) {
                $p=$this->myBasePrice();
                
                $maxdiscount=$this->myMaxDiscount();
                
                if ($maxdiscount) {
                    $p=$this->myBasePrice()-($this->myBasePrice() * ($maxdiscount/100));
                }
                
                $this->cache[__FUNCTION__]=$p;
            }
            return $this->cache[__FUNCTION__];
        }
        
        
        
        
        
        public function myAmounts()
        {
            $amounts=$this->Amounts;
            $amounts=explode("\n", $amounts);
            $rows=array();
            foreach ($amounts as $line) {
                $cols=explode(';', $line);
                $row['amount']=trim($cols[0]);
                if ($cols[1]) {
                    $row['price']=$cols[1];
                }
                
                if ($row['amount']) {
                    $rows[]=$row;
                }
            }
            
            return $rows;
        }
        
        public function setFormFields()
        {
            $p=array(); // ------- new field --------
            $p['fieldname'] = "Title";
            $p['label']     = "Artikel-Zusatz";
            $this->formFields['left'][$p['fieldname']]=$p;
            
            
            
            $p=array(); // ------- new field --------
            $p['fieldname'] = "Number";
            $p['label']     = "Artikel-Nr";
            $this->formFields['left'][$p['fieldname']]=$p;
            
            
            $p=array(); // ------- new field --------
            $p['fieldname'] = "Price";
            $p['label']     = "Preis (EUR)";
            $this->formFields['left'][$p['fieldname']]=$p;
            
            
            $p=array(); // ------- new field --------
            $p['fieldname'] = "InStock";
            $p['label']     = "Lagerstand";
            $p['styles']='width:50px';
            $this->formFields['left'][$p['fieldname']]=$p;
            
            // $p=Array(); // ------- new field --------
            // $p['label']="Bestellmengen;Preis";
            // $p['note']='<i>jeweils 1 Bestellmenge; Preis pro Zeile</i>';
            // $p['type']='textarea';
            // $p['styles']='height:300px';
            // $p['fieldname']="Amounts";
            // $this->formFields['right'][$p['fieldname']]=$p;
        }
        
        public function getDefaultRecord()
        {
            return array("InStock"=>1);
        }
        
        
        public function MainPicture()
        {
            if ($this->PictureID) {
                return $this->Picture->CroppedImage(940, 300);
            }
        }
        
        public function PreviewPicture()
        {
            if ($this->PictureID) {
                return $this->Picture->CroppedImage(940/2, 300/2);
            }
        }
        public function PreviewTpl()
        {
            return '<strong>$Title</strong> <span>$Number</span> <br>
            Preis: <i>$Price</i><br>
            Lagerstand:$InStock
            
            ';
        }
        
        public function decrementInStockValue($amount)
        {
            $this->InStock=$this->InStock-$amount;
            
            if ($this->InStock<0) {
                $this->InStock=0;
            }
            $this->write();
        }
    }
    
    
    
    class ProductPage_C4P_Picture extends C4P_Element
    {
        public function setFormFields()
        {
            $p=array(); // ------- new field --------
            $p['fieldname'] = "PictureID";
            $p['label']     = "Image";
            $p['type']      = "MwFileField";
            $this->formFields['left'][$p['fieldname']]=$p;
        }
        
        
        public function MainPicture()
        {
            if ($this->PictureID) {
                return $this->Picture->SetFittedSize(270, 170);
            }
        }
        
        public function PreviewPicture()
        {
            if ($this->PictureID) {
                return $this->Picture->SetFittedSize(270, 170);
            }
        }
        public function PreviewTpl()
        {
            return '$PreviewPicture';
        }
    }
