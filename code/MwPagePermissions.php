<?php 
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBText;


class FakeUser {

  var $Email='fakeuser';
  public function inGroup($name)
  {
    if ($name=='any_or_none') {
      return true;
    }
    return false;
  }

}

class MwPagePermissions extends DataExtension {


    private static $db = array(
          'C4Pjson_PagePermissions'    => DBText::class
      );

    public function C4P_Place_PagePermissions()
    {
        $conf['min']  = 1;
        $conf['allowed_types']  = Array();
        $conf['allowed_types']['C4P_PagePermissionRule']['label']     = "PagePermission";
        $conf['max_width']                                            = 900;
        return $conf;
    }

    public function getC4P()
    {
      if(!isset($this->owner->cache[__FUNCTION__]))
      {
        $this->owner->cache[__FUNCTION__]=new C4P($this->owner);
      }
      return $this->owner->cache[__FUNCTION__];
    }


    public function getPagePermissionGroupMap()
    {

      $permissions=$this->owner->getCumulativeElementsForPlace('PagePermissions');

      $map=array();
      foreach ($permissions as $p) {
        $map[$p->Right][$p->Group]=Array(
            'Type'=>$p->Type,
            'Src'=>$p->Mainrecord->MenuTitle
        );
      }

      return $map;      
    }

    public function getFakeUser()
    {
      static $fakeUser;
      if(!$fakeUser) {
          $fakeUser=new FakeUser();
      }
      return $fakeUser;
      
    }

    public function userCanDoActionOnPage($user,$actionName,$context=array(),$defaultValue=NULL)
    {

      if(!$user) {
        $user=$this->getFakeUser();
      }

      if (preg_match('/(hide|publish|rename|drag|createSubPage|delete|Visibility)/i',$actionName)) {
        $actionName='modifyPage'; // todo: make smarter
      }
      $ret=$defaultValue;
      $permissions=$this->owner->getCumulativeElementsForPlace('PagePermissions');
      // foreach ($permissions as $p) {
      //   echo "\n<li>".$p->getBEPreviewHtml()." via ".$p->Mainrecord->Title;
      // }


      foreach ($permissions as $p) {
        if ($p->Right==$actionName &&  $user->inGroup($p->Group)) {
          $ret=$p->Type;
        }
      }

       // echo "\n<li>".$user->Email." can do $actionName : $ret";
      return $ret;
    }


    public function getCumulativeElementsForPlace($placename,$customMax=NULL,$p=Array())
    {
      if(!isset($this->owner->cache[__FUNCTION__][$placename]))
      {
      // echo "<li>getRecursiveElementsForPlace called for $placename on {$this->record->Link()}";
        $elements=$this->owner->C4P->getElementsForPlace($placename,$customMax,$p);
        $parent=$this->owner->getParent();
        if($parent) {
          if($parent->C4P) {
            $parentElements=$parent->getCumulativeElementsForPlace($placename,$customMax,$p);
            if ($parentElements && $parentElements->count()>0) {
              $parentElements->merge($elements);
              $elements=$parentElements;
            }
            

          }
        }
        $this->owner->cache[__FUNCTION__][$placename]= $elements;
      }

      return $this->owner->cache[__FUNCTION__][$placename];

    }

}





