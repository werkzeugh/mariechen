<?php

use SilverStripe\Control\Controller;

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\HTTPRequest;

use Tarsana\Functional as F;

class ProductVariant extends Page
{
    public function getStockStatus()
    {
        return ($this->InStock>0)?"in stock":"out of stock";
    }

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
        if ($this->Price>0) {
            return  $this->Price;
        } else {
            return  $this->Product()->Price;
        }
    }



    public function getMainImage()
    {
        $imgs=$this->getImages();
        if ($imgs->Count()>0) {
            return $imgs->First();
        }
    }

    public function getOgImageUrl()
    {
        if ($mi=$this->getMainImage()) {
            return  $mi->Link();
        }
        return parent::getOgImageUrl();
    }


    private static $db= array(
        'Text'              =>'HTMLText',
        'Price'             =>'Decimal(8,2)',
        'NewTags'           =>'Varchar(255)',
        'VariantNr'         =>'Varchar(255)',
        'InStock'           =>'IntNull',
        'EffectiveStock'    =>'IntNull',
        'ImageIds'          =>'Varchar(255)',

    );
    
    private static $defaults=array(
        'NewTags'           =>'',
    );
    
    public function Product()
    {
        return $this->Parent();
    }
    
    public function HTMLTitle()
    {
        return $this->Product()->Title." ".$this->Title." - ".$this->Product()->Config_SeoTitle;
    }

    public function getTagIdString()
    {
        return TagEngine::singleton()->getTagIdStringForPage($this->ID);
    }
    
    
    public function getImgIdString()
    {
        return F\Stream::of($this->getImages()->toArray())
        ->map(function ($img) {
            return $img->ID;
        })
        ->join(',')
        ->result();
    }
    
    public function getColorString()
    {
        $tagIds=TagEngine::singleton()->getTagIdsForPage($this->ID);
        foreach ($tagIds as $tagId) {
            $tag=TagEngine::singleton()->getTagForId($tagId);
            if ($tag->Color) {
                return $tag->Color;
            }
        }
        return '';
    }
    
    public function getColor()
    {
        return "#ddd";
    }
    
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        
        TagEngine::singleton()->handlePageTagsForProductVariantId($this->ID);
    }
    
    public function getIconForPageTree()
    {
        return 'fa fa-gift';
    }
    
    public function getImageCount()
    {
        return $this->getImages()->count();
    }
    
    
    public function getImages()
    {
        $allImages=$this->Parent()->getImages();
        $al=new ArrayList();
        
        $myImageIds=$this->MyImageIds();
        foreach ($allImages as $img) {
            if (in_array($img->ID, $myImageIds)) {
                $al->push($img);
            }
        }
        
        return $al;
    }
    public function MyImageIds()
    {
        return explode(',', $this->ImageIds);
    }
}



class ProductVariantController extends ProductPageController
{
    public function index(HTTPRequest $request)
    {
        $this->summitSetTemplateFile("Layout", "ProductPage");
        $c['SkipMainContentDiv']=1;
        
        $this->HeadAddons[]=$this->getOGHtml();
        return $c;
    }

    public function getOGHtml()
    {
        return "
            <meta property=\"product:condition\" content=\"new\">
            <meta property=\"product:availability\" content=\"{$this->StockStatus}\">
            <meta property=\"product:price:amount\" content=\"{$this->Price}\">
            <meta property=\"product:price:currency\" content=\"EUR\"> 
            ";
    }


    public function currentVariantId()
    {
        return $this->ID;
    }
}


class ProductVariantBEController extends PageBEController
{
    public function getRawTabItems()
    {
        $items=array(
            "10"    => "Product-Variant",
            // "12"     => "Product-Variants",
            "14"     => "Images",
            // "20"     => "Settings",
        );
        
        return $items;
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
        
        

        $p=array(); // ------- new field --------
        $p['fieldname']="InStock";
        $p['label']="Lagerstand";
        $p['readonly']=1;
        $this->formFields[$p['fieldname']]=$p;

        
        $p=array(); // ------- new field --------
        $p['fieldname']="URLSegment";
        $p['label']="Slug";
        $this->formFields[$p['fieldname']]=$p;
        
        
        //define all FormFields for step "Title"
        //  $p=Array(); // ------- new field --------
        //  $p['label']="Hersteller";
        //  $p['fieldname']="BrandID";
        //  $p['options']=$this->getBrands()->map();
        //  $this->formFields[$p['fieldname']]=$p;
        
        // $p=array(); // ------- new field --------
        // $p['fieldname'] = "PictureID";
        // $p['label']     = "Picture";
        //  $p['type']      = "MwFileField";
        //  $this->formFields[$p['fieldname']]=$p;
        
        
        //define all FormFields for step "Title"
        $p=array(); // ------- new field --------
        $p['fieldname']="Price <i>(optional, if price=0 the generic product-price is used)</i>";
        $p['label']="Preis";
        $this->formFields[$p['fieldname']]=$p;
        
        
        // $p=array(); // ------- new field --------
        // $p['fieldname']="NewTags";
        // $p['type']="hidden";
        // $p['default_value']=TagEngine::singleton()->getTagIdStringForPage($this->record->ID);
        // $p['label']="Tags";
        // $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='colors,usage,material' ref_id='input_NewTags'></eb-tag-editor>".TagEngine::singleton()->getCodeForBackendWidgets();
        // $this->formFields[$p['fieldname']]=$p;
    }

       
    public function step_14()
    {
        $c['VariantImages']=$this->getImages();

        $c['ProductImages']=$this->record->Product()->getImages();

        $c['RestImages']=new ArrayList();
        $myImageIds=$this->record->MyImageIds();
        foreach ($c['ProductImages'] as $img) {
            if (!in_array($img->ID, $myImageIds)) {
                $c['RestImages']->push($img);
            }
        }
        
        
        return $this->customise($c)->renderWith("Includes/ProductVariantListImages").$html;
    }
}
