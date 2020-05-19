<?php 
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBVarchar;
use SilverStripe\View\ArrayData;
use SilverStripe\ORM\DataExtension;


class MwLanguage extends DataExtension {


    private static $db = array(
        'LanguageVersionsJSON'    => DBVarchar::class
    );

    function registerLanguageVersionOfThisPage($languagepage)
    {
        if($languagepage->Level(1)->ID == $this->owner->Level(1)->ID)
        {
            $language=$languagepage->myLanguage();
            if($language!=$this->owner->myLanguage())
            {
                //add versions on both pages:
                $this->owner->setLanguageVersion($languagepage);
                $languagepage->setLanguageVersion($this->owner);
            }  
        }
        
    }

    function myLanguage()
    {
        if(!isset($this->owner->cache[__FUNCTION__]))
        {
      
            if($lp=$this->owner->Level(2))
            {
                if(($lp->ClassName=='LanguagePortalPage' || $lp->IsLanguagePortal ) && strlen($lp->URLSegment)==2)
                    $l=$lp->URLSegment;
                }

            $this->owner->cache[__FUNCTION__]=$l;
        }
        return $this->owner->cache[__FUNCTION__];
    }
    
    

    public function getLanguageVersions()
    {
        if(!isset($this->owner->cache[__FUNCTION__]))
        {
            $json=$this->owner->LanguageVersionsJSON;
            $langs=Array();
            if($json)
            {
                $ids=json_decode($json,1);
                if(is_array($ids))
                {
                    foreach ($ids as $lang => $pageid) {
                        $page=DataObject::get_by_id(SiteTree::class,$pageid);
                        if($page)
                            $langs[$lang]=$page;
                    }
                }
            }
            $this->owner->cache[__FUNCTION__]=$langs;
        }
        return $this->owner->cache[__FUNCTION__];
  
        
    }
    
    public function getLanguageVersionsForTemplate()
    {
        $arr=$this->owner->getLanguageVersions();
        $al=new ArrayList();
        $mylang=$this->owner->myLanguage();
        foreach ($arr as $lang => $page) {
            $al->push(new ArrayData( Array( 'isCurrentLanguage'=>($mylang==$lang),'Language' => $lang, 'Page'=>$page ) ) );
        }
        return $al;
        
    }
    
    public function setLanguageVersion($langpage)
    {
        $lang=$langpage->myLanguage();
        if($lang)
        {
        
            if($this->owner->LanguageVersions)
                $existing_versions=json_decode($this->owner->LanguageVersionsJSON,1);
            else
                $existing_versions=Array();
            $existing_versions[$lang]=$langpage->ID;
            $this->owner->LanguageVersionsJSON=json_encode($existing_versions);
            $this->owner->write();
            $this->owner->cache=Array(); //clear cache
        }
    }

    public function getLanguageVersion($l)
    {
        
        if(strlen($l)==2) {
            if(($this->owner->ClassName=='LanguagePortalPage' || $this->owner->IsLanguagePortal ) && strlen($this->owner->URLSegment)==2) {
                // find sibling language

                $otherlang=SiteTree::get_by_link('/'.trim($this->owner->Parent()->RawLink())."/$l");
                return $otherlang;
                
            } else if($langs=$this->owner->getLanguageVersions()) {
                // if(array_get($_GET,'d') || 1 ) { $x=$langs; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
                if($langs[$l]) {
                    return $langs[$l];
                }
            }
        }
            
    }

    public function getPageRootForLanguage($l)
    {
        //find parent page
        $parent=$this->owner->Parent();
        if($parent)
        {
            // echo "<li>parent:{$parent->Link()}";
            //find language version of paren
            $lvp=$parent->getLanguageVersion($l);
            if($lvp)
            {
                // echo "<li>parent ($l): {$lvp->Link()}";
                return $lvp;
            }
        }
    }

    function  getAvailableLanguages()
    {
     
        if(!isset($this->owner->cache[__FUNCTION__]))
        {
            $portalpage=$this->owner->Level(1);
            if($portalpage)
            {

                $childs=$portalpage->Children();
                foreach ($childs as $c) {
                    if(( $c->ClassName=='LanguagePortalPage' || $c->IsLanguagePortal) && strlen($c->URLSegment)==2)
                        $languages[$c->URLSegment]=$c->URLSegment;
                }
        
            }
        
           $this->owner->cache[__FUNCTION__]=$languages;
        }
        return $this->owner->cache[__FUNCTION__];
        
        
    }




}

