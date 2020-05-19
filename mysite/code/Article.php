<?php

use SilverStripe\View\Requirements;
use SilverStripe\Control\HTTPRequest;

use Tarsana\Functional as F;
use Tarsana\Functional\Stream;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\SSViewer;
use SilverStripe\Control\Controller;


use SilverStripe\View\ArrayData;

class Article extends Page
{
    private static $db = array(
        'C4Pjson_MainContent'      => 'Text',
        'C4Pjson_StyleClasses'      => 'Text',
        'C4Pjson_FooterContent'      => 'Text',
        'PageDate'            => 'Date',
        'ShortText'           => 'Varchar(255)',
        'CustomShortText'           => 'Varchar(255)',
        'Title_de'           => 'Varchar(255)',
        'MetaDescription_de'           => 'Varchar(255)',
    );
    
    
    
    private static $has_one=array(
        'MainPicture'=>'MwFile'
    );
    
    
    
    
    
    
    public function getPageSections()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            $al=new ArrayList();
            
            foreach ($this->getArticleElements() as $el) {
                if ($el->LinkTargetSlug && $el->LinkTargetTitle) {
                    $al->push(new ArrayData(['Slug'=>$el->LinkTargetSlug,'Title'=>$el->LinkTargetTitle ]));
                }
            }
            
            $this->cache[__FUNCTION__]= $al;
        }
        return $this->cache[__FUNCTION__];
    }
    
    public function getCurrentSite()
    {
        if (!$this->cache[__FUNCTION__]) {
            $ret=$this->Level(1);
            
            $this->cache[__FUNCTION__]=$ret;
        }
        
        return $this->cache[__FUNCTION__];
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
    
    // define c4p_places ---------- BEGIN
    
    public function C4P_Place_MainContent()
    {
        $conf['allowed_types']=$this->getDefaultC4PElements();
        $conf['use_angular']=1;
        $conf['max_width']=900;
        return $conf;
    }
    
    
    
    public function getDefaultC4PElements()
    {
        $conf=[];
        $conf['Article_C4P_PageTitle']['label']   = "Seiten-Titel";
        $conf['Article_C4P_Text']['label']        = "Text";
        $conf['Article_C4P_Image']['label']       = "Bild";
        $conf['Article_C4P_LinkList']['label']     = "Link-Liste";
        // $conf['Article_C4P_Divider']['label']     = "Trenner";
        $conf['Article_C4P_PlainHtml']['label']   = "HTML";
        $conf['Article_C4P_2Cols']['label']       = "2-Spalter";
        // $conf['Article_C4P_Section']['label']     = "Element-Gruppe";
        $conf['Article_C4P_TeaserList']['label']     = "Teaser-Liste";
        $conf['Article_C4P_Widget']['label']     = "Widget";
        $conf['Article_C4P_Button']['label']     = "Button";
        // $conf['Article_C4P_FullpagePanel']['label']     = "Fullpage-Panel";
        $conf['Article_C4P_FullPageSlider']['label']    = "Startseiten-Slider";
        $conf['Article_C4P_ImgSlider']['label']    = "Image-Slider";
        
        
        return $conf;
    }
    
    // define c4p_places---------- END
    
    
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        //handle SearchableContent:
        
        if ($this->isChanged('C4Pjson_MainContent')
        || $this->isChanged('Title')
        ) {
            $this->updateSearchableContent();
            $this->findAndSetMainPictureAndShortText();
        }
    }
    
    public function updateSearchableContent()
    {
        $txt="";
        foreach ($this->C4P->getAll_MainContent as $c) {
            $txt.=$this->getSearchableContentForCelement($c);
        }
        return MwSearchableContent::set4Object($txt, $this->SelfOrAlias);
    }
    
    public function getSearchableContentForCelement($c)
    {
        if (!is_object($c)) {
            return "";
        }
        
        $txt='';
        if ($c->hasMethod('getSearchableContent')) {
            $txt.=$c->getSearchableContent();
        } else {
            if ($c->Title) {
                $txt.=$c->Title."\n";
            }
            if ($c->Text) {
                $txt.=str_replace('&nbsp;', ' ', $c->Text)."\n";
            }
            if ($c->hasMethod('getChildren')) {
                foreach ($c->getChildren('all') as $subContentElement) {
                    $txt.=$this->getSearchableContentForCelement($subContentElement);
                }
            }
        }
        
        if ($this->CustomAbstract) {
            $txt.=$this->CustomAbstract."\n";
        }
        
        
        return $txt;
    }
    
    
    public function getArticleElements()
    {
        return $this->C4P->getElementsForPlace("MainContent");
    }
    
    
    
    public function getAllPictureIDs()
    {
        $bag=[];
        $this->findPicturesAndTextInContentElements($this->C4P->getElementsForPlace("MainContent"), $bag);
        
        
        if ($bag['pictures']) {
            return $bag['pictures'];
        }
        return null;
    }
    
    public $loop1Count=0;
    
    public function getOgShortText()
    {
        return strip_tags($this->myShortText());
    }
    
    public function myShortText()
    {
        if ($this->CustomShortText) {
            return nl2br($this->CustomShortText);
        }
        
        if ($this->loop1Count++ > 50) {
            throw new Exception("encountered endless loop", 1);
        }
        if ($this->ShortText=='n/a') {
            return "";
        }
        if ($this->ShortText) {
            return $this->ShortText;
        }
        $this->findAndSetMainPictureAndShortText();
        $this->write();
        return $this->myShortText();
    }
    
    
    public function myMainPicture()
    {
        if ($this->loop2Count++ > 50) {
            throw new Exception("encountered an endless loop", 1);
        }
        if ($this->MainPictureID>0 && $this->MainPicture()) {
            return $this->MainPicture();
        }
        
        if ($this->MainPictureID==-1) {
            return null;
        }
        $this->findAndSetMainPictureAndShortText();
        $this->write();
        return $this->myMainPicture();
    }
    
    
    public function findAndSetMainPictureAndShortText()
    {
        $bag=array();
        
        $this->findPicturesAndTextInContentElements($this->C4P->getElementsForPlace("MainContent"), $bag);
        
        $this->MainPictureID=-1;
        
        if ($bag['pictures']) {
            $firstimageId=array_shift(array_keys($bag['pictures']));
            
            if ($firstimageId>1) {
                $this->MainPictureID=$firstimageId;
            }
        }
        $this->ShortText="n/a";
        if ($bag['texts']) {
            $firstShortText=array_shift($bag['texts']);
            
            if (strlen($firstShortText)>2) {
                $this->ShortText=$firstShortText;
            }
        }
    }
    
    
    public function findPicturesAndTextInContentElements($elements, &$bag, $p = array())
    {
        $textLimit=140;
        if ($p['textLimit']) {
            $textLimit=$p['textLimit'];
        }
        
        foreach ($elements as $el) {
            if ($el->AliasTo) {
                continue;
            }
            
            if ($el->PictureID) {
                if (!$bag['pictures'][$el->PictureID]) {
                    $bag['pictures'][$el->PictureID]=$el->myPictureCopyright(array('raw'=>1));
                }
            }
            
            
            if ($text=trim(strip_tags($el->Text))) {
                if ($p['keepOriginalTexts']) {
                    $bag['texts'][$el->ID]=$el->Text;
                } else {
                    $bag['texts'][$el->ID]=MwUtils::ShortenText($text, $textLimit);
                }
            }
            
            
            if ($el->hasMethod('getChildren')) {
                $children=$el->getChildren('all');
                if ($children) {
                    $this->findPicturesAndTextInContentElements($children, $bag, $p);
                }
            }
        }
    }
    
    
    public function ShopCategories(Type $var = null)
    {
        return $this->Level(2)->UnHiddenChildren("Article");
    }
    
    public function SubTypes()
    {
        $al=new ArrayList();
        
        $link=$this->Link();
        $currentType=Controller::curr()->Type;
        
        $taglist=[];
        
        $usageroot=TagEngine::singleton()->getTagTypeRoot('usage');
        if ($usageroot) {
            foreach ($usageroot->UnHiddenChildren() as $tag) {
                $taglist[]=$tag->URLSegment;
                $al->push(new ArrayData(array( 'MenuTitle' => $tag->getField('Title'),
                'Link'=>$link."type/".$tag->URLSegment,
                'Tag'=>$tag->URLSegment,
                'Count'=>0,
                'CssClass'=>($tag->URLSegment==$currentType)?'active':'not-active', )));
            }
        }
        
        $tagCounts=BagManager::singleton()->getTagCounts($this, $taglist);
        
        
        foreach ($al as $el) {
            if ($cnt=array_get($tagCounts, $el->Tag, 0)) {
                $el->Count=$cnt;
            }
        }
        
        return $al;
    }

    
    
    public function getIconForPageTree()
    {
        if ($this->Config_Redirect) {
            return 'fa fa-arrow-circle-right';
        }
        return null;
    }
}


class ArticleController extends PageController
{
    private static $allowed_actions= [
        'type'
    ];
    
    
    public function init()
    {
        parent::init();
        SSViewer::setRewriteHashLinksDefault(false);
        $lang=$this->CurrentLanguage();
    }
    
    public function index(HTTPRequest $request)
    {
        if ($this->Config_Redirect) {
            return $this->doRedirect($this->Config_Redirect);
        }
        $ret=parent::index($request);
        if ($this->Level(2)->URLSegment=='shop') {
            $this->summitSetTemplateFile("Layout", "Article_BagList");
            $this->isBagList=true;
            $c=[];
            // $c['SkipMainContentDiv']=1;
            return $c;
        }
        
        return $ret;
    }
    
    public $AddonTitle="";
    public function type(HTTPRequest $request)
    {
        $type=$request->param('ID');
        $this->Type=$type;
        
        $tag=TagEngine::singleton()->getTagForSlug($type);
        if ($tag) {
            $this->AddonTitle=" / ".$tag->getField('Title');
        }
        return $this->index($request);
    }
    
    public function hasNavCol()
    {
        if ($this->isBagList) {
            return true;
        }
        if ($this->dataRecord->getPageSections()->count()>0) {
            return true;
        }
        return false;
    }
    
    
    public function doRedirect($type)
    {
        switch ($type) {
            case "first_subpage":
                $firstpage=$this->dataRecord->UnHiddenChildren()->First();
                if ($firstpage) {
                    $target_url=$firstpage->Link();
                    return $this->redirect($target_url);
                }
                die("no subpage found to redirect to");
            break;
            case "custom_link":
                if ($this->Config_RedirectTarget) {
                    $obj=MwLink::getObjectForMwLink($this->Config_RedirectTarget);
        
                    if ($obj) {
                        if ($obj->ID==$this->ID) {
                            echo('redirection-page cannot redirect to itself !');
                            return "#redirect_loop_error";
                        }
                        $target_url=$obj->Link();
                        return $this->redirect($target_url);
                    }
                }
        
            break;
            default:
            die("invalid redirect type $type");
        }
    }
    

   

    public function callC4P()
    {
        return C4P::callC4P($this);
    }
}

class ArticleBEController extends FrontendPageBEController
{
    public function init()
    {
        parent::init();
        Requirements::customCSS(TagEngine::singleton()->getTagTypesCss());
    }
    
    public function getRawTabItems()
    {
        $items=array(
            "10_C4P_Place_MainContent"    =>"Inhalte",
            "23"                        => "SEO",
            "20"                          =>"Settings",
            // "30"                          =>"CSS",

            
        );
        
        
        return $items;
    }
    
    public function step_30()
    {
        $p              = array();
        $p['fieldname'] = "Config_BgColor";
        $p['label']     = "Seiten-Hintergrundfarbe <i>z.B: #FF7A58</i>";
        $p['width']     = "200";
        $this->formFields[$p['fieldname']]=$p;
    }
    
    public function step_20()
    {
        parent::step_20();
        
        $p              = array();
        $p['fieldname'] = "Title_de";
        $p['label']     = "Title (DE)";
        $p['width']     = "200";
        $p['type']     = "text";
        $this->formFields[$p['fieldname']]=$p;
        
        $p              = array();
        $p['fieldname'] = "Config_Redirect";
        $p['label']     = "Redirect to";
        $p['options']     = [
            'first_subpage'=>'first Sub-Page',
            'custom_link'=>'custom Destination',
        ];
        $this->formFields[$p['fieldname']]=$p;
        
        
        if ($this->record->Config_Redirect=='custom_link') {
            $p=array();
            $p['label']="custom Destination";
            $p['fieldname']="Config_RedirectTarget";
            $p['addon_classes']="MwLinkField";
            $p['type']="hidden";
            $this->formFields[$p['fieldname']]=$p;
        }
    }
}



class Article_C4P_Divider extends Article_C4P_Item
{
    public function getTpl($style = '')
    {
        $this->Style=$style;
        return '<div class="c4p-divider {$getClasses}" style="{$getStyles}"><div class="defaultcontainer">'.$PreContent.'<div class="hr"></div>'.$PostContent.'</div></div>';
    }
    
    public function getClasses()
    {
        $classes=array();
        
        if (!$this->autogenerated && !$this->HideRuler) {
            $classes[]='showruler';
        }
        
        if ($this->ctype1 && $this->ctype2) {
            $classes[]=strtolower($this->ctype1.'-vs-'.$this->ctype2);
            $classes[]='after-'.strtolower($this->ctype1);
            $classes[]='before-'.strtolower($this->ctype2);
        }
        
        
        
        return implode(' ', $classes);
    }
    
    public function getStyles()
    {
        $styles=array();
        
        if ($this->myMarginTop()!==null) {
            $styles[]="padding-top:".$this->myMarginTop()."px";
        }
        if ($this->myMarginBottom()!==null) {
            $styles[]="padding-bottom:".$this->myMarginBottom()."px";
        }
        return implode(';', $styles);
    }
    
    
    
    public function myMarginTop()
    {
        if (is_numeric($this->MarginTop)) {
            return $this->MarginTop;
        } else {
            return null;
        }
    }
    
    public function myMarginBottom()
    {
        if (is_numeric($this->MarginBottom)) {
            return $this->MarginBottom;
        } else {
            return null;
        }
    }
    
    
    
    
    public function PreviewTpl($style = '')
    {
        return "----------- DIVIDER ----------";
    }
    
    
    public function setFormFields()
    {
        $p=array();
        $p['fieldname']  = "HideRuler";
        $p['label']      = "Hide Ruler";
        $p['type']       = "checkbox";
        $this->formFields['tabs']['divider']['items']['right'][$p['fieldname']]=$p;
        
        
        $p=array();
        $p['fieldname']  = "MarginTop";
        $p['label']      = "Margin-Top";
        $p['tag_addon']  = "placeholder='10'";
        
        $p['styles']="width:110;";
        $this->formFields['tabs']['divider']['items']['right'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname']  = "MarginBottom";
        $p['label']      = "Margin-Bottom";
        $p['tag_addon']  = "placeholder='10'";
        
        $p['styles']="width:120px;";
        $this->formFields['tabs']['divider']['items']['right'][$p['fieldname']]=$p;
    }
}
