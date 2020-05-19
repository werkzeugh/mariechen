<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\View\Requirements;

class TagEngine
{
    public static function singleton($class = null)
    {
        static $instance=null;
        if (!$instance) {
            $instance=new TagEngine();
            // LaravelHelpers::init();
        }
        return $instance;
    }



    public function getCodeForBackendWidgets()
    {
        Requirements::customCSS(TagEngine::singleton()->getTagTypesCss());

        // $settings['baseurl']=$this->Link();
        $settings['liveApiUrl']='https://'.(array_key_exists('HTTP_X_ORIGINAL_HOST', $_SERVER)?$_SERVER['HTTP_X_ORIGINAL_HOST']:$_SERVER['HTTP_HOST'])."/ex/tags";
        $settingsAsJson=json_encode($settings);


        return '
<script>
window.vueAppConf='.$settingsAsJson.';
</script>
<script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/eb_backend/js/chunk-vendors.js').'"></script>
<script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/eb_backend/js/app.js').'"></script>';
    }


    public function handlePageTagsForProductId($id)
    {
        $this->callExApi("/tags/touch_product_page/".intval($id));
    }

    public function handlePageTagsForProductVariantId($id)
    {
        $this->callExApi("/tags/touch_product_variant/".intval($id));
    }

    public function callExApi($path, $post_params=null)
    {
        //make call to ex-engine, using https://github.com/stil/curl-easy

        $url='http://127.0.0.1:4000'.$path;
        $request = new cURL\Request($url);
        if ($post_params) {
            $request->getOptions()
             ->set(CURLOPT_POST, true)
             ->set(CURLOPT_POSTFIELDS, $post_params);
        }
        $request->getOptions()->set(CURLOPT_RETURNTRANSFER, true);
        $response=$request->send();
        return json_decode($response->getContent(), true);
    }

    public function getTagIdsForPage($id)
    {
        $db=DBMS::getMdb();
        $ids=$db->getCol("select tag_id from page_tag join SiteTree_Live on (SiteTree_Live.ID=tag_id) where page_id=".intval($id));
        if (!$ids) {
            $ids=[];
        }

        return $ids;
    }

    public function getTagIdsForFile($id)
    {
        $db=DBMS::getMdb();
        $ids=$db->getCol("select tag_id from file_tag  join SiteTree_Live on (SiteTree_Live.ID=tag_id) where file_id=".intval($id));
        if (!$ids) {
            $ids=[];
        }
        return $ids;
    }
    
    public function getTagIdsForChildren($id)
    {
        $db=DBMS::getMdb();
        $ids=$db->getCol("select distinct(tag_id) from page_tag where page_id in (select ID from SiteTree_Live where ParentID=".intval($id).")");
        if (!$ids) {
            $ids=[];
        }
        return $ids;
    }


    public function getTagIdStringForPage($id)
    {
        return implode(" ", $this->getTagIdsForPage($id));
    }


    public function getTagIdStringForFile($id)
    {
        return implode(" ", $this->getTagIdsForFile($id));
    }

    public function getTagIdStringForChildren($id)
    {
        return implode(" ", $this->getTagIdsForChildren($id));
    }

    public function updateTags($taggable_ids, $add_tags, $remove_tags)
    {
        $this->callExApi("/tags/update_tags", [
              'taggable_ids'=>implode(",", $taggable_ids),
              'add_tags'=>$add_tags,
              'remove_tags'=>$remove_tags
         ]);
        //  die("\n\n<pre>mwuits-debug 2019-10-04_20:10 ".print_r($taggable_ids, true));
    }

    public function getTagForSlug($slug)
    {
        if (!isset($this->cache[__FUNCTION__.$slug])) {
            $ret=DataObject::get('TagNode')->filter('URLSegment', $slug);
            if ($ret && $ret->count()==1) {
                $tag=$ret->First();
            } else {
                $tag=null;
            }
            $this->cache[__FUNCTION__.$slug]=$tag;
        }
        return $this->cache[__FUNCTION__.$slug];
    }

    public function getTagForId($id)
    {
        if (!isset($this->cache[__FUNCTION__.$id])) {
            $tag=DataObject::get_by_id('TagNode', $id);
            $this->cache[__FUNCTION__.$id]=$tag;
        }
        return $this->cache[__FUNCTION__.$id];
    }

    public function getTagIdForSlug($slug)
    {
        $tag=$this->getTagForSlug($slug);
        if ($tag) {
            return $tag->ID;
        }
        return 0;
    }

    public function getTagRoot()
    {
        static $tagRoot;
        if (!$tagRoot) {
            $tagRoot=PageManager::getPage("/tags");
        }
        return  $tagRoot;
    }

    public function getTagTypeRoot($type)
    {
        return PageManager::getPage("/tags/$type");
    }

    public function getTagRootId()
    {
        static $id;
        if (!$id) {
            $tagRoot=$this->getTagRoot();
            $id=$tagRoot->ID;
        }
        return  $id;
    }

    public function getAllTagTypes()
    {
        static $tag_types;

        if (!$tag_types) {
            $tagRootId=$this->getTagRootId();
           

            $db=DBMS::getMdb();
            $tag_types=$db->getCol("select URLSegment from SiteTree_Live where ParentID=$tagRootId and Hidden=0 and ClassName='TagNode' order by Sort asc");
            if (!$tag_types) {
                $tag_types=[];
            }
        }
        return $tag_types;
    }
    public function getAllTagTypesString()
    {
        return implode(",", $this->getAllTagTypes());
    }

    public function getTagTypesCss()
    {
        $rules=[];
        foreach ($this->getTagRoot()->children() as $child) {
            $color=$child->Color;
            if ($color) {
                $rules[]=".ti-{$child->URLSegment} { background-color:$color !important; }";
            }
        }
       
        return  implode("\n", $rules);
    }
}
