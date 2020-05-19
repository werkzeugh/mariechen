<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\Control\Controller;
use SilverStripe\Security\PermissionProvider;

class HelpTip extends DataObject {

  private static $db=Array(
    "Title"=>"Varchar(255)",
    "CharID"=>"Varchar(50)",
    "Text"=>"HTMLText",
    );

  static public function get_by_charID($charID)
  {
    return DataObject::get_one(get_class(),"CharID='$charID'");
  }

  static public function getHtml($charID)
  {

    if($ht=self::get_by_charID($charID))
    {
      $txt=$ht->Text;
      $txt.=$ht->getEditLink();
      return self::wrapInHtml($txt);
    }
    else
    {
      return  self::getAddLink($charID);

    }
  }

  static public function getAddLink($charID)
  {

    if(!Permission::check("EDIT_HELPTIP"))
      return "";

    return "<a href='/BE/HelpTip/add/$charID' class='helptipbutton iframepopup' afterHide='reloadWindow'>+</a>";
  }

  public function getEditLink()
  {

    if(!Permission::check("EDIT_HELPTIP"))
      return "";

    return "<a href='/BE/HelpTip/edit/{$this->ID}' class='helptipbutton iframepopup' afterHide='reloadWindow'>edit</a>";
  }

  static public function wrapInHtml($txt)
  {
    return "<div class='helpbubble typography'>$txt</div>";
  }

}


class HelpTipController extends BackendPageController implements PermissionProvider {

     var $myClass="HelpTip";
     var $mwForm;

     function providePermissions() {
       return array(
         "EDIT_HELPTIP" => "Edit Help-Tips",
         );
     }

    function  add()
    {
      $charID=Controller::curr()->urlParams['ID'];

      $this->record= new HelpTip;
      $this->record->CharID=$charID;
      $this->record->write();

      Controller::curr()->redirect("/BE/HelpTip/edit/".$this->record->ID);
    }

    public function edit($value='')
    {
      $this->loadRecord();
      $this->summitSetTemplateFile("main","BackendPage_iframe");
      $this->mwForm=new mwForm;

      BackendHelpers::includeTinyMCE();

          $p=Array(); // ------- new field --------
          $p['preset']=$this->record->toMap();
          $p['label']="Text";
          $p['type']='textarea';
          $p['fieldname']="Text";
          $p['addon_classes']="tinymce";
          $p['rendertype']='beneath';
          $this->formFields[$p['fieldname']]=$p;
      $field=$this->mwForm->render_naked_field($p);

      $html="<form method='POST' action='/BE/HelpTip/save/{$this->record->ID}' id='dataform'>
        $field
        <div style='padding:10px'>
         <a href=\"javascript:jQuery('#dataform').submit();\" class='iconbutton save'><span></span>Speichern</a></a>
         <a href=\"/BE/HelpTip/delete/{$this->record->ID}\" class='iconbutton delete confirm'><span></span>Löschen</a></a>
        </form>
        ";

      return Array('Form'=>$html);

    }

    public function delete()
    {
      $this->loadRecord();
      $this->record->delete();
      return "<script>parent.popupWindow.hide();</script>";

    }

    public function save()
    {
      $this->loadRecord();
      $this->record->update(array_get($_POST,'fdata'));
      $this->record->write();
      return "<script>parent.popupWindow.hide();</script>";

    }



}

?>
