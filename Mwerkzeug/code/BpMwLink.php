<?php

use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;

class BpMwLinkController extends BackendPageController
{
    var $cache;

    private static $allowed_actions = [
        'jsonGetInfo',
        'ajaxChooser',
        'ajaxChooserEmail',
        'ajaxChooserExternalUrl',
        'ajaxChooserSearchFiles',
        'chooser',
        'import',
    ];

    public function UrlEncodedMwLink()
    {
        return urlencode(array_get($_REQUEST, 'MwLink'));
    }

    public function RecentPages()
    {

      //TODO: limit to siteroots, limit to own edits

        $limit=20;



        if (!$this->cache[__FUNCTION__]) {
            $res=DataObject::get(SiteTree::class, "", "LastEdited desc", '', $limit);

            $dos=new ArrayList();

            foreach ($res as $item) {
                $item->ReadableUrl=MwUtils::ShortenTextInTheMiddle($item->Link(), 80);
                $dos->push($item);
            }

            $this->cache[__FUNCTION__]=$dos;
        }
        return $this->cache[__FUNCTION__];
    }


    public function jsonGetInfo()
    {

        $link=array_get($_GET, 'MwLink');
        $this->record=MwLink::getObjectForMwLink($link);

        $ret=array();

      //$ret['requested_params']=$_POST;

        if ($this->record) {
            $ret['record']=$this->record->toMap();
            $ret['Title']=$this->record->MenuTitle;

            if (!$ret['Title']) {
                $ret['Title']=$this->record->Title;
            }

            $ret['Title']=MwUtils::ShortenTextInTheMiddle($ret['Title'], 50);

            if (get_class($this->record)=='MwLink') {
                $ret['ReadableUrl']= $this->record->ReadableUrl();
            } else {
                $ret['ReadableUrl']=MwUtils::ShortenTextInTheMiddle($this->record->Link(), 40);
            }
        }
        return json_encode($ret);
        exit();
    }


    public function RecentFiles()
    {

      //TODO: limit to siteroots, limit to own edits

        $limit=20;



        if (!$this->cache[__FUNCTION__]) {
            $res=DataObject::get("MwFile", "Deleted=0 and IsFolder=0", "LastEdited desc", '', $limit);

            $dos=new ArrayList();

            if ($res) {
                foreach ($res as $item) {
                    $item->ReadableUrl=MwUtils::ShortenTextInTheMiddle($item->Link(), 80);
                    $dos->push($item);
                }
            }

            $this->cache[__FUNCTION__]=$dos;
        }
        return $this->cache[__FUNCTION__];
    }




    public function ajaxChooserExternalUrl()
    {
        Requirements::clear();

        $obj=MwLink::getObjectForMwLink(array_get($_REQUEST, 'MwLink'));
        $c=array();
        $c['CurrentValue']=$obj->data['url'];
        return $this->customise($c)->renderWith('Layout/BpMwLink_ajaxChooserExternalUrl');
    }


    public function ajaxChooserEmail()
    {
        Requirements::clear();

        $obj=MwLink::getObjectForMwLink(array_get($_REQUEST, 'MwLink'));

        $c=array();
        $c['CurrentValue']=$obj->data['email'];

        return $this->customise($c)->renderWith('Layout/BpMwLink_ajaxChooserEmail');
    }

    public function ajaxChooserSearchFiles()
    {
                Requirements::clear();

        return $this->renderWith('Layout/BpMwLink_ajaxChooserSearchFiles');
    }

    public function chooser()
    {

        Requirements::javascript('Mwerkzeug/thirdparty/jqueryui/development-bundle/ui/jquery.ui.tabs.js');

        $this->summitSetTemplateFile('main', 'BackendPage_iframe');

        Requirements::customCSS("
        body  { background:#F0F0EE ;margin:15px}
        ");

        $c=array();

        if (MwPage::conf('HideRecentItemsInLinkChooser')) {
            $c['HideRecentItems']=true;
        }

        return $c;
    }

    public function import()
    {
        $mwlink=array_get($_GET, 'MwLink');
        $o=MwLink::getObjectForMwLink($mwlink);
        $c['TargetObj']=$o;

        $this->summitSetTemplateFile('main', 'BackendPage_iframe');

        if ($o->hasMethod('provideDataForTeaser')) {
            $res=$o->provideDataForTeaser();
            if (!is_object($res)) {
                $res=MwUtils::convertArray2ArrayList($res);
            }
            $c['ImportData']=$res;
        } else {
            $c['ImportData']=$this->provideDataForTeaserFromObject($o);
        }

        foreach ($c['ImportData'] as &$item) {
            $item->Value=html_entity_decode($item->Value, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        }
        

        Requirements::customCSS("
          body  { background:#F0F0EE ;margin:15px}
      ");
        return $c;
    }

    public function provideDataForTeaserFromObject(&$o)
    {

        $dos=new ArrayList();

        if ($o->Title) {
            $dos->push(new ArrayData(array( 'Key' => "Title", 'Label' => 'Title', 'Value' => $o->Title, 'Checked' => 1)));
        }

        if ($o->ShortText) {
            $txt=trim(strip_tags($o->ShortText));
        }

        if (!$txt && $o->IntroText) {
            $txt=MwUtils::ShortenText(trim(strip_tags($o->IntroText), 500));
        }
        if (!$txt && $o->Text) {
            $txt=MwUtils::ShortenText(trim(strip_tags($o->Text), 500));
        }
        if (!$txt && $o->Content) {
            $txt=MwUtils::ShortenText(trim(strip_tags($o->Content), 500));
        }

        if (trim($txt)) {
            $dos->push(new ArrayData(array( 'Key' => "Text", 'Label' => 'Text', 'Value' => $txt, 'Checked' => 1 )));
        }


        if ($pictureids=$o->AllPictureIDs) {
            foreach ($pictureids as $pictureid => $copyright) {
                if ($pictureid) {
                    $img_n++;
                    $dos->push(new ArrayData(array( 'Key' => "PictureID", 'Label' => 'Picture', 'Value' => $pictureid, 'Image' => MwFile::getByID($pictureid), 'Checked' => ($img_n==1)?1:0 )));
                }
            }
        } else {
            $pictureid=$o->MainPictureID;
            if ($pictureid) {
                $dos->push(new ArrayData(array( 'Key' => "PictureID", 'Label' => 'Picture', 'Value' => $pictureid, 'Image' => MwFile::getByID($pictureid), 'Checked' => 1)));
            }
        }
        return $dos;
    }
}
