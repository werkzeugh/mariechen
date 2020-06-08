<?php

use SilverStripe\ORM\DataObject;

use SilverStripe\View\Requirements;

class ProductCategoryPage extends GenericHolderPage
{
    private static $db= array(
        'ShortText'          =>'Text',
        'ShortText_en'       =>'Text',

    );

    public function allowedChildren()
    {
        return [
                'ProductPage',
                // 'ProductCategoryPage'
        ];
    }

    private static $has_one=array(
    //   'Brand'=>'BrandPage',
      'Picture'=>'MwFile',
    );
    
    public function getIconForPageTree()
    {
        return 'fa fa-folder-o';
    }
    
     
    public function LeftnavLink($action = null)
    {
        if ($this->C4P->getAll_TopSlider->count()>0
           || $this->Products() && $this->Products()->count() >0) {
            return $this->Link();
        } else {
            return "#";
        }
    }
    
    public function myShortText()
    {
        return nl2br($this->ShortText);
    }
    public $subclass='ProductPage';


    public function allowedChildren()
    {
        return array($this->ClassName,$this->subclass,'ProductPageAlias','MwShopCartPage','Article','RedirectionPage');
    }

    public function Products()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=$this->UnHiddenChildren("ProductPage");
        }
        return $this->cache[__FUNCTION__];
    }

    public function SubCategories()
    {
        return $this->UnHiddenChildren("ProductCategoryPage|Article|RedirectionPage");
    }

    public function UnHiddenChildren($filter = null)
    {
        if (!$filter) {
            return $this->SubCategories();
        } else {
            return parent::UnHiddenChildren($filter);
        }
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();

        if ($this->BrandID) {
            $this->Title=$this->getBrandTitle();
            $this->MenuTitle=$this->Title;
            $this->URLSegment=$this->generateURLSegment($this->Title);
        }
    }
    
    
    public function getBrandTitle()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $this->cache[__FUNCTION__]=$this->Brand()->Title;
        }
        return $this->cache[__FUNCTION__];
    }


    public function myMenuTitle()
    {
        if ($this->BrandID) {
            return _t('passend_fuer', 'passend für')." ".$this->BrandTitle;
        } else {
            return $this->Title;
        }
    }


    public function myTitle()
    {
        if ($this->BrandID) {
            return $this->Parent()->Title." - "._t('passend_fuer', 'passend für')." ".$this->BrandTitle;
        } else {
            return $this->Title;
        }
    }
}




class ProductCategoryPageController extends GenericHolderPageController
{
    private static $allowed_actions = [
        'ex_template',
    ];

    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        return $this->redirect($this->dataRecord->Parent()->Link());
    }


    public function ex_template(SilverStripe\Control\HTTPRequest $request)
    {
        $c['Layout']="###CONTENT###";
        return $c;
    }
}


class ProductCategoryPageBEController extends GenericHolderPageBEController
{
    public function init()
    {
        $this->texts['additem']="add Product";
        parent::init();
        //
        // Requirements::customCSS("
        //  .custom {border:4px dashed #999}
        //
        //  ");
    }
    

    public function getRawTabItems()
    {
        $items=FrontendPageBEController::getRawTabItems();
        $items["10"]="Products";
        // $items["14"]="Settings";
      

        return $items;
    }

    public function getAllTagTypes()
    {
        return TagEngine::singleton()->getAllTagTypesString();
    }

    public function step_10()
    {
        $this->NoDataForm=true;
            
        if ($_POST['SortedIds']) {
            foreach (explode(",", $_POST['SortedIds']) as $n=>$id) {
                $id=(int) $id;
                if ($id) {
                    $record=DataObject::get_by_id("ProductPage", $id);
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
            
        $c['Variants']=$this->record->Children();
            
        return $this->customise($c)->renderWith("Includes/ProductCategoryPageListProducts").$html;
    }

    public function step_14()
    {
        


        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Title";
        $p['fieldname']="Title";
        
        if ($this->record->BrandID) {
            $p['tag_addon']=' disabled="1" ';
        }
        
        $this->formFields[$p['fieldname']]=$p;
        
        
        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Title <em class='lang'>en</em>";
        $p['fieldname']="Title_en";
        
        if ($this->record->BrandID) {
            $p['tag_addon']=' disabled="1" ';
        }
        
        $this->formFields[$p['fieldname']]=$p;
        
        
        //define all FormFields for step "Title"
        // $p=Array(); // ------- new field --------
        // $p['label']="passend für (Marke)";
        // $p['note']="macht diese Kategorie zur Marken-Kategorie";
        // $p['fieldname']="BrandID";
        // $p['options']=$this->getBrands()->map();
        // $this->formFields[$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['fieldname'] = "PictureID";
        $p['label']     = "Picture";
        $p['type']      = "MwFileField";
        $this->formFields[$p['fieldname']]=$p;
        
        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['fieldname']="MaxDiscount";
        $p['label']="MaximalRabatt <i>in %</i>";
        $p['styles']="width:80px";
        $p['tag_addon']=" placeholder=\"default: ".$this->record->FallbackedDiscountValue($p['fieldname'])." \" ";
        $this->formFields[$p['fieldname']]=$p;


        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['fieldname']="Discount";
        $p['label']="Rabatt <i>in %</i>";
        $p['styles']="width:80px";
        $p['tag_addon']=" placeholder=\"default: ".$this->record->FallbackedDiscountValue($p['fieldname'])." \" ";
        $this->formFields[$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['fieldname']="KennenlernDiscount";
        $p['label']="KennenlernRabatt <i>in %</i>";
        $p['styles']="width:80px";
        $p['tag_addon']=" placeholder=\"default: ".$this->record->FallbackedDiscountValue($p['fieldname'])." \" ";
        $this->formFields[$p['fieldname']]=$p;
        

        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Short Text";
        $p['type']="textarea";
        $p['styles']="height:100px;width:500px";
        $p['fieldname']="ShortText";
        // $p['rendertype']='beneath';
        $this->formFields[$p['fieldname']]=$p;
        

        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['label']="Short Text <em class='lang'>en</em>";
        $p['type']="textarea";
        $p['styles']="height:100px;width:500px";
        $p['fieldname']="ShortText_en";
        // $p['rendertype']='beneath';
        $this->formFields[$p['fieldname']]=$p;
    }
    

    
    public function array_unshift_assoc(&$arr, $key, $val)
    {
        $arr = array_reverse($arr, true);
        $arr[$key] = $val;
        $arr = array_reverse($arr, true);
    }
}
