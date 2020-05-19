<?php

use SilverStripe\Control\Controller;

//use PageController;

class RedirectionPage extends Page
{
    private static $db=array(
        'RedirectionTarget' => "Enum('_self,_blank','_self')",
        'RedirectionType' => "Enum('first_subpage,random_subpage','first_subpage')",
        'TargetMwLink' => "Varchar(255)",
    );



    public function getIconForPageTree()
    {
        return 'fa fa-arrow-circle-right';
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

      
    public function Link($action = null)
    {
        return $this->getTargetURL();
    }
      
    public function getTargetURL()
    {
        if ($this->TargetMwLink) {
            $obj=MwLink::getObjectForMwLink($this->TargetMwLink);

            if ($obj) {
                if ($obj->ID==$this->ID) {
                    echo('redirection-page cannot redirect to itself !');
                    return "#redirect_loop_error";
                }
                $target_url=$obj->Link();
            }
        }

        if (!$target_url) {
            $sourcePage=$this;
            if ($this->AliasPage) {
                $sourcePage=$this->AliasPage;
            }

            switch ($this->RedirectionType) {
            
            case 'first_subpage':
            $firstpage=$sourcePage->UnHiddenChildren()->First();
            if ($firstpage) {
                $target_url=$firstpage->Link();
            }
            break;
            default:
            if ($this->RedirectURL) {
                $target_url=$this->RedirectURL;
            }
            break;
        }
        }

        if (array_get($_GET, 'preview')) {
            $target_url.="?preview=".array_get($_GET, 'preview');
        }
        return $target_url;
    }


    public function isCurrent()
    {
        if (Controller::curr()->MwLink==$this->TargetMwLink) {
            return true;
        }
        return false;
    }
}
  

class RedirectionPageController extends PageController
{
    public function index(SilverStripe\Control\HTTPRequest $request)
    {
        $target_url=$this->getTargetURL();

        if ($target_url) {
            Controller::curr()->redirect($target_url);
        } else {
            die($this->minimalPageHeader()."<div class='info'><h1>sorry!</h1>no target page found</div>");
        }
    }
}


class RedirectionPageBEController extends BpMysitePageController
{
    public $myClass='RedirectionPage';

    
    public function getRawTabItems()
    {
        $items=array();
        $items['18']="Settings";
        return $items;
    }

    public function step_18()
    {

    //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Page-Types";
        $p['fieldname']="ClassName";
        $p['type']="select";
        $p['options']=$this->getAllowedPageClasses();
        $p['no_empty_option']=1;
        $this->formFields[$p['fieldname']]=$p;

        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Title";
        $p['fieldname']="Title";
        $this->formFields[$p['fieldname']]=$p;




        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Page-Status";
        $p['fieldname']="Hidden";
        $p['type']="radio";
        $p['options']=array('0' => 'published','1' => 'hidden');
        $this->formFields[$p['fieldname']]=$p;

        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Show Page In Menus";
        $p['fieldname']="ShowInMenus";
        $p['type']="checkbox";
        $this->formFields[$p['fieldname']]=$p;
       


        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Menu-Title<i>if different from page-title</i>";
        $p['fieldname']="MenuTitle";
        $this->formFields[$p['fieldname']]=$p;


        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="URLSegment";
        $p['fieldname']="URLSegment";
        $this->formFields[$p['fieldname']]=$p;

        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Redirect to here";
        $p['fieldname']="TargetMwLink";
        $p['addon_classes']="MwLinkField";
        $p['type']="hidden";
        $this->formFields[$p['fieldname']]=$p;


        $p=array(); // ------- new field --------
        $p['label']="Redirect to Sub-Pages";
        $p['fieldname']="RedirectionType";
        $p['type']="select";
        $p['options']=singleton($this->myClass)->dbObject($p['fieldname'])->enumValues();
        $this->formFields[$p['fieldname']]=$p;
       

        //define all FormFields for step "Title"
    $p=array(); // ------- new field --------
    $p['label']="Redirection-Target";
        $p['fieldname']="RedirectionTarget";
        $p['type']="select";
        $p['options']=singleton($this->myClass)->dbObject($p['fieldname'])->enumValues();
        $this->formFields[$p['fieldname']]=$p;
    }
}
