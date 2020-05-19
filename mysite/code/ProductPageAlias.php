<?php

class ProductPageAlias extends ProductPage
{
    var $cache;
    private static $db= Array(
    );
    
    private static $has_one=Array(
      'ProductPage'=>'ProductPage',
    );
    
    
    static function createAlias($prod)
    {
        if($prod->ID)
        {
            $p=new ProductPageAlias();
            $p->Hidden=1;
            $p->ParentID=$prod->ParentID;
            $p->ProductPageID=$prod->ID;
            $p->write();
            return $p;
        }
    }
    
    public function getBETitle()
    {
        return "Alias for {$this->Title}";
    }
    
    
    public function getMenuTitle()
    {
        return "Alias for {$this->Title}";
    }
    
    public function __get($field)
    {
    
        if(!preg_match('#^(ID|MenuTitle|BETitle|Hidden|ClassName|ParentID|ProductPageID|Product|Created|LastEdited|.*On|URLSegment|ShowInMenus)$#',$field)
            && $this->Product)
        {
           // echo "<li>get $field";
                return $this->Product->$field;
        }
            
        
           return parent::__get($field);
    }

    
    public function getProduct()
    {
        if(!isset($this->cache[__FUNCTION__]))
        {
            $p=$this->ProductPage();
            if(!$p)
                $this->delete(); //delete alias if target was not found
            
            $this->cache[__FUNCTION__]=$p;
        }
        return $this->cache[__FUNCTION__];

    }

}



class ProductPageAliasController extends ProductPageController
{
    
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        
        return $this->redirect($this->dataRecord->Product->Link());
    }


}


class ProductPageAliasBEController extends ProductPageBEController
{



    public function getRawTabItems()
     {
       $items=Array(
         "10"                        => "Basics",
         "20"                        => "Settings",
         );

       return $items;
     }

  
  
     public function step_10()
      {


        //define all FormFields for step "Title"
        $p=Array(); // ------- new field --------
        $p['label']="Alias for";
        $p['fieldname']="Title";
        $p['type']="html";
        $p['html']=$this->record->MenuTitle.
            '
            <script type="text/javascript" charset="utf-8">
              top.frames["leftframe"].jQuery("a.reloadtree").trigger.click();
            </script>    
            ';
        
        $this->formFields[$p['fieldname']]=$p;


    }

}



