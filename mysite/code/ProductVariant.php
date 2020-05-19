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
    public function getPrice()
    {
        return $this->Parent->Price;
    }

    public function getStockStatus()
    {
        return ($this->InStock>0)?"in stock":"out of stock";
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
        'Title_de'           =>'Varchar(255)',
        'Text'              =>'HTMLText',
        'Text_de'           =>'HTMLText',
        'Price'             =>'Decimal(8,2)',
        'NewTags'           =>'Varchar(255)',
        'VariantNr'         =>'Varchar(255)',
        'InStock'           =>'IntNull',
        'EffectiveStock'    =>'IntNull',

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
        
        $myImgs=[];
        $commonImgs=[];
        $commonTagId=TagEngine::singleton()->getTagIdForSlug("common");
        $variantTagIds=TagEngine::singleton()->getTagIdsForPage($this->ID);
        if (array_key_exists('coro', $_GET)) {
            if (true || array_key_exists('d', $_GET)) {
                $x= $variantTagIds;
                $x=htmlspecialchars(print_r($x, true));
                echo "\n<li>mwuits: <pre>$x</pre>";
            }
        }
        foreach ($allImages as $img) {
            $imgTagIds=TagEngine::singleton()->getTagIdsForFile($img->ID);
            
            $containsAllValues = !array_diff($variantTagIds, $imgTagIds);
            
            if ($containsAllValues) {
                $addImg=true;
                $img->isListImage=true;
                array_push($myImgs, $img);
                continue;
            }
            if (in_array($commonTagId, $imgTagIds)) {
                array_push($commonImgs, $img);
            }
        }
        
        return new ArrayList(array_merge($myImgs, $commonImgs));
    }


    public function getField($name)
    {
        if ($GLOBALS['CurrentLanguage']=='de') {
            switch ($name) {
                case 'Title':
                case 'Text':
                $val=parent::getField($name."_de");
                    if ($val) {
                        return $val;
                    }
                
            break;
            }
        }
        return parent::getField($name);
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
            // "14"     => "Images",
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
        $p['fieldname']="Title_de";
        $p['label']="Name <i>(german, optional)</i>";
        $this->formFields[$p['fieldname']]=$p;
        
        $p=array(); // ------- new field --------
        $p['fieldname']="VariantNr";
        $p['label']="Texcom Bezeichnung";
        $this->formFields[$p['fieldname']]=$p;

        // $p=array(); // ------- new field --------
        // $p['fieldname']="InStock";
        // $p['label']="Lagerstand";
        // $p['readonly']=1;
        // $this->formFields[$p['fieldname']]=$p;

        
        $p=array(); // ------- new field --------
        $p['fieldname']="URLSegment";
        $p['label']="URL <i>Feld leer lassen ➜ automatische URL wird generiert</i>";
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
        
        
        // //define all FormFields for step "Title"
        // $p=array(); // ------- new field --------
        // $p['fieldname']="ProductNr";
        // $p['label']="Produkt-Nr:";
        // $this->formFields[$p['fieldname']]=$p;
        
        
        $p=array(); // ------- new field --------
        $p['fieldname']="NewTags";
        $p['type']="hidden";
        $p['default_value']=TagEngine::singleton()->getTagIdStringForPage($this->record->ID);
        $p['label']="Tags";
        $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='colors,usage,material' ref_id='input_NewTags'></eb-tag-editor>".TagEngine::singleton()->getCodeForBackendWidgets();
        $this->formFields[$p['fieldname']]=$p;
    }
}
