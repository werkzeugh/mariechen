<?php

use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\Controller;
use SilverStripe\View\ArrayData;



class MwFormTab
{

  var $prevTabItem = NULL;
  var $nextTabItem= NULL;
  var $currentTabItem= NULL;
  var $currentTabID;
  
  public function TabItems()
   {

     $lastTabItem=NULL;
     $currentTabItem=NULL;

     $navitems=new ArrayList();
     foreach($this->getRawTabItems() as $url=>$name)
     {

       if($this->calledViaWithin)
         $link="";
       else
         $link=Controller::curr()->URLSegment."/".Controller::curr()->urlParams['Action']."/".Controller::curr()->urlParams['ID']."/".$url;

       $tabItem=new ArrayData(
       Array(
         "Link"=>$link,
         "Title"=>$name,
         "URLSegment"=>$url,
         "Current"=>($url==$this->CurrentTab()),
         )
         );
       if($tabItem->Current)
       {
         $this->currentTabItem=$tabItem;
         $this->prevTabItem=$lastTabItem;
       }

       if($lastTabItem->Current)
       {
         $this->nextTabItem=$tabItem;
       }

       $navitems->push($tabItem);
       $lastTabItem=$tabItem;
     }
     return $navitems;

   }


   public function CurrentTab()
   {
     if($this->currentTabID)
       $id=$this->currentTabID; //only used for "within functionality"
     else
       $id=Controller::curr()->urlParams['OtherID'];

     if(!$id)
     {
       $id=array_shift(array_keys(($this->getRawTabItems())));
     }

     return $id;
   }

  
}


?>