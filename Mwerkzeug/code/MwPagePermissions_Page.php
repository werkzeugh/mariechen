<?php 
use SilverStripe\View\ViewableData;


class MwPagePermissions_Page extends ViewableData {


  public function getBackendPageHeader($page)
  {
    

    $groupMap=$page->getPagePermissionGroupMap();
    $c4p=new C4P_PagePermissionRule($this);
    $rightMap=$c4p->getRights();

    foreach ($groupMap as $right=>$groups) {
     
      $groupRightsHtml=Array();
      foreach ($groups as $group=>$data) {
        $color=$data['Type']=='allow'?"#33dd55":"#992222";
        $groupRightsHtml[]="<span title='source-page:{$data['Src']}' style='color:$color'>{$group}</span>";
      }

       $mapHtml.="<div><b>{$rightMap[$right]}</b>:".implode(', ', $groupRightsHtml)."</div>";

    }


    return "<h1>Page-Permissions</h1>

      <div>&nbsp;</div>
      effective Rights for this page:
      <div>&nbsp;</div>
      $mapHtml


    <div>&nbsp;</div>
    <div>&nbsp;</div>
    ";
  }


}


