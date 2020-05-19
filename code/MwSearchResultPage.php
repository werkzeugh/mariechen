<?php

use SilverStripe\Core\Convert;
use SilverStripe\View\ViewableData;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\ORM\DataObject;

//use PageController;


class MwSearchResultPage extends Page
{




}

class MwSearchResult extends ViewableData
{
    var $page;
    var $kw;
    var $text;
    public function __construct($page, $kw, $text)
    {
        $this->page=$page;
        $this->kw=$kw;
        $this->text=$text;
    }


    public function getPage()
    {
        return $this->page;
    }

    public function getTitle()
    {
        return $this->page->Title;
    }

    public function getID()
    {
        return $this->page->ID;
    }

    public function Link($action = null)
    {
        return $this->page->Link();
    }

    public function Excerpt()
    {
        $res= $this->makeExcerpt($this->text, $this->kw, $radius = 100, $ending = "...");

        return $this->highlight($res, Convert::raw2xml($this->kw));
    }

    public function highlight($text, $kw)
    {
        $kw=explode(' ', str_replace(array('','\\','+','*','?','[','^',']','$','(',')','{','}','=','!','<','>','|',':','#','-','_'), '', $kw));
        for ($i=0; $i<sizeOf($kw); $i++) {
            $text=preg_replace("/($kw[$i])(?![^<]*>)/i", "<span class=\"highlight\">\${1}</span>", $text);
        }
        return $text;
    }

    function makeExcerpt($text, $phrase, $radius = 100, $ending = "...")
    {


         $phraseLen = mb_strlen($phrase);
        if ($radius < $phraseLen) {
             $radius = $phraseLen;
        }

         $phrases = explode(' ', $phrase);

        foreach ($phrases as $phrase) {
                $pos = mb_strpos(mb_strtolower($text), mb_strtolower($phrase));
            if ($pos > -1) {
                break;
            }
        }

         $startPos = 0;
        if ($pos > $radius) {
            $startPos = $pos - $radius;
        }

         $textLen = mb_strlen($text);
         $endPos = $pos + $phraseLen + $radius;
        if ($endPos >= $textLen) {
            $endPos = $textLen;
        }

         $excerpt = mb_substr($text, $startPos, $endPos - $startPos);
        if ($startPos != 0) {
            $excerpt = MwUtils::mb_substr_replace($excerpt, $ending, 0, $phraseLen);
        }

        if ($endPos != $textLen) {
            $excerpt = MwUtils::mb_substr_replace($excerpt, $ending, -$phraseLen, null);
        }

         return $excerpt;
    }
}

class MwSearchResultPageController extends PageController
{

    var $currentParams;

    public function getCurrentKeyword()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $kw=array_get($_REQUEST, 'q');

            if (!$kw && $this->currentParams && $this->currentParams['query']['keyword']) {
                $kw=$this->currentParams['query']['keyword'];
            }

            $kw=trim($kw);
            $this->cache[__FUNCTION__]=$kw;
        }
        return $this->cache[__FUNCTION__];
    }

    public function getQuerySQL()
    {
        $kw=$this->getCurrentKeyword();
        $kw=Convert::raw2sql($kw);
        $sql="
        select ID,Text,Hidden
		from
			MwSearchableContent
			join SiteTree_Live using(ID)
		where
			Hidden=0 and
			MATCH (Text) AGAINST ('$kw*' IN BOOLEAN MODE)
        order by
            SiteTree_Live.created desc
        limit 200
                ";

        if (array_get($_GET, 'showsql')) {
            if (array_get($_GET, 'd') || 1) {
                $x=$sql;
                $x=htmlspecialchars(print_r($x, 1));
                echo "\n<li>ArrayList: <pre>$x</pre>";
            }
        }

        return $sql;
    }

    public function MatchedIDs()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $db=DBMS::getMdb();
            $sql=$this->getQuerySQL();
            $res=$db->getAssoc($sql);
            $dos=new ArrayList();

            foreach ($res as $row) {
                unset($row['hidden']);
                $dos->push(new ArrayData($row));
            }

            $this->cache[__FUNCTION__]=$dos;
        }

        return $this->cache[__FUNCTION__];
    }


    public function getPortalLink()
    {
        if ($this->CurrentPortal) {
            $portallink=$this->CurrentPortal->Link();
        }
        if (!$portallink) {
            $portallink='/';
        }


        return $portallink;
    }


    public function FoundPages()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $matchingIDs3=new ArrayList();


            $portallink=$this->getPortalLink();

            $matchingIDs2=$this->MatchedIDs();

            if ($matchingIDs2) {
                foreach ($matchingIDs2 as $rec) {
                    if ($rec->mwlink) {
                        $page=MwLink::getObjectForMwLink($rec->mwlink);
                    } else {
                        $page=SiteTree::get_by_id(SiteTree::class, $rec->id);
                    }

                    if ($page->Title  && !$page->Hidden &&  strstr($page->RawLink(), $portallink)) {
                        $matchingIDs3->push(new MwSearchResult($page, $this->getCurrentKeyword(), $rec->text));
                    }
                }
            }

            $dos=$matchingIDs3->limit($this->getPageSize(), array_get($_GET, 'start'));

    //$dos->setPageLimits(array_get($_GET,'start'), $this->getPagesize(), $matchingIDs3->count());

            $this->cache[__FUNCTION__]=$dos;
        }

        return $this->cache[__FUNCTION__];
    }


    public function index(SilverStripe\Control\HTTPRequest $request)
    {

        return array();
    }


    public function reIndexAllPages()
    {

        set_time_limit(3600);
        echo "<h1>reIndexAllPages</h1>";

        $pages=DataObject::get(SiteTree::class)->filter('Hidden', 0);
        if (array_get($_GET, 'id')) {
            $pages=$pages->filter('ID', array_get($_GET, 'id'));
            $debug=1;
        }

        $n=0;
        $total=$pages->count();

        foreach ($pages->column('ID') as $pageId) {
            $n++;
            echo "<li> $n/$total ";

            if (array_get($_GET, 'skipuntil') && !$stopSkipping) {
                if ($pageId==array_get($_GET, 'skipuntil')) {
                    $stopSkipping=1;
                }
                echo $skip;
                continue;
            }

            $p=PageManager::getPage($pageId);
            echo "<a href='?id={$p->ID}'>reindex</a> {$p->ID} {$p->Title}";
            if ($p->hasMethod('updateSearchableContent')) {
                $ret=$p->updateSearchableContent();
                if ($debug) {
                    if (array_get($_GET, 'd') || 1) {
                        $x=$ret->toMap();
                        $x=htmlspecialchars(print_r($x, 1));
                        echo "\n<li>mwuits: <pre>$x</pre>";
                    }
                }
                //$p->write();
                flush();
            }
        }

        echo "FIN";
    }
}


class SearchResultPageBEController extends BpMysitePageController
{


}
