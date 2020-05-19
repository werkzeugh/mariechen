<?php

use SilverStripe\Control\Controller;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\CMS\Model\SiteTree;

class TagNodeApiController extends FrontendPageController
{
    public function handleRequest(SilverStripe\Control\HTTPRequest  $request)
    {
        //fix handleRequests since silverstripe4

        $params = $request->latestParams();
        $actionUrl = implode("/", $params);
        $request->setUrl($actionUrl);

        return parent::handleRequest($request);
    }

   

    private static $allowed_actions = [
        'all',
        'tags',
        'image'
    ];

  

    public function image($request)
    {
        
        // die("\n\n<pre>mwuits-debug 2019-11-11_10:44a ".print_r(0, true));
        $file_id=$request->param('ID');
        $file=MwFile::getById($request->param('ID'));
        
        $img = $file->CroppedImage(600, 755);

        $url=$img->Link();
        Controller::curr()->redirect($url);
    }

    public function all()
    {
        die("\n\n<pre>mwuits-debug 2019-09-06_09:43 ".print_r(0, true));
    }

    public function tags()
    {
        $types=explode(",", $_GET['types']);
        $ret=[];
        foreach ($types as $type) {
            if ($type) {
                $ret[$type]=$this->getTagsForType($type);
            }
        }

        header('content-type: application/json');
    
        echo json_encode($ret);
        exit();
    }

    public function getTagsForType($type)
    {
        $base_cat=TagNode::get_by_url('/tags/'.$type);
        if ($base_cat) {
            return $base_cat->getChildrenRecursive($base_cat->ID, 0, $params);
        } else {
            return [];
        }
    }
}
