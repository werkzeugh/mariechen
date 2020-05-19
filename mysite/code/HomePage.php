<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Controller;

class HomePage extends Article
{
    public function C4P_Place_StyleClasses()
    {
        $conf['min']=1;
        $conf['allowed_types']=array();
        $conf['allowed_types']['Article_C4P_StyleClass']['label']   = "CSS-Klasse";
        $conf['use_angular']=1;
        $conf['max_width']=900;
        return $conf;
    }
    
    
    public function IsCurrentNav()
    {
        if (get_class(Controller::curr())=='MysiteErrorPageController') {
            return true;
        }

        return $this->isCurrent();
    }

    
    public function C4P_Place_FooterContent()
    {
        $conf['min']=1;
        $conf['max']=1;
        $conf['allowed_types']=array();
        $conf['allowed_types']['HomePage_C4P_FooterContent']['label']   = "Footer";
        $conf['use_angular']=1;
        $conf['max_width']=900;
        return $conf;
    }
    
    
    public function allowedChildren()
    {
        return array('Article','MysiteShopCartPage','ProductCategoryPage','RedirectionPage','HomePage');
    }
    
    
    public function getStyleClasses()
    {
        return $this->C4P->getAll_StyleClasses->map('ID', 'CombinedTitle');
    }
    
    public function getStyleClassNames()
    {
        return $this->C4P->getAll_StyleClasses->map('ID', 'ClassName');
    }
    
    
    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->isChanged('C4Pjson_StyleClasses')) {
            $this->createCustomStyleSheet();
        }
    }
    
    public function createCustomStyleSheet()
    {
        $data='';
        
        unset($this->cache['getCElementsForFieldC4Pjson_StyleClasses']);
        foreach ($this->C4P->getAll_StyleClasses as $styleClass) {
            $data.=$styleClass->Css;
        }
        
        file_put_contents(Director::baseFolder().'/files/custom.css', $data);
    }
    
    
    
    public function getNavItems()
    {
        $navpage=$this->getSubPage('navigation');
        if ($navpage) {
            return $navpage->UnHiddenChildren();
        }
    }


    public function getFooter()
    {
        return $this->C4P->getFirst_FooterContent;
    }
}




class HomePageController extends ArticleController
{
    // public function index(HTTPRequest $request)
    // {
    //     if ($this->URLSegment=='home') {
    //         return Controller::curr()->redirect("/de/");
    //     }

    //     return parent::index($request);
    // }
}




class HomePageBEController extends ArticleBEController
{
    public function getRawTabItems()
    {
        $items=parent::getRawTabItems();
        $items["25"]="Header";
        $items["27_C4P_Place_FooterContent"]="Footer";
        $items["35_C4P_Place_StyleClasses"]="CSS-Klassen";
        $items["40"]="Tags";
        return $items;
    }
    
    
    public function step_25()
    {
        $p              = array();
        $p['fieldname'] = "Config_TopLogoID";
        $p['label']     = "Logo Kopfbereich";
        $p['type']      = "MwFileField";
        
        $this->formFields[$p['fieldname']]=$p;
    }
    
    

    public function step_40()
    {
        $settings['baseurl']=$this->Link();
        $settings['liveApiUrl']='https://'.(array_key_exists('HTTP_X_ORIGINAL_HOST', $_SERVER)?$_SERVER['HTTP_X_ORIGINAL_HOST']:$_SERVER['HTTP_HOST'])."/TagNode";
        
        $settingsAsJson=json_encode($settings);


        return '<style>
        #main_savelink {display:none}
        </style>

        <eb-tag-editor class="vueapp-eb_backend" types="colors,usage" value="#white #sand"></eb-tag-editor>

        <div>&nbsp;</div>
        <eb-tag-editor class="vueapp-eb_backend" types="colors,usage" value="#brown #orange"></eb-tag-editor>
        <div>&nbsp;</div>

        <eb-tag-viewer class="vueapp-eb_backend" types="colors"></eb-tag-viewer>



<script>


window.vueAppConf='.$settingsAsJson.';


</script>

<script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/eb_backend/js/chunk-vendors.js').'"></script>
<script type="text/javascript" src="'.VueCliEngine::singleton()->vue_cli_helper('/engine/eb_backend/js/app.js').'"></script>';
    }
    
    public function step_35_C4P_Place_StyleClasses_plainFieldsWrapper($html)
    {
        return '<script type="text/javascript" charset="utf-8">
        $.validator.addMethod(
            "regexp",
            function(value, element,param ) {
                return this.optional(element) || value.match(param[0]);
            },
            $.validator.format("{1}")
        );
        
        </script>';
    }
}




class HomePage_C4P_FooterContent extends Article_C4P_Item
{
    public function setFormFields()
    {
        
        // $p=array();
        // $p['fieldname']     = "ContactText";
        // $p['label']         = "Text (Kontakt)";
        // $p['type']          = "textarea";
        // $p['addon_classes'] = "tinymce";
        // $p['styles'] = "width:500px;height:200px";
        // $this->formFields['tabs']['spalte1']['items'][$p['fieldname']]=$p;
        
        // $p=array();
        // $p['fieldname']     = "MobileContactText";
        // $p['label']         = "Mobile-Kontaktbox <i>(Startseite oben)</i>";
        // $p['type']          = "textarea";
        // $p['addon_classes'] = "tinymce";
        // $p['styles'] = "width:500px;height:200px";
        // $this->formFields['tabs']['spalte1']['items'][$p['fieldname']]=$p;
    }
    
    
    
    
    public function getConfig()
    {
        
        // $conf['_children_col1nav']['allowed_types']['HomePage_C4P_FooterTitle']['label'] = "Headline";
        $conf['_children_col1nav']['allowed_types']['Article_C4P_Text']['label'] = "Text";
        // $conf['_children_col1nav']['allowed_types']['Article_C4P_Image']['label'] = "Bild";
        $conf['_children_col2nav']=$conf['_children_col1nav'];
        $conf['_children_col3nav']=$conf['_children_col1nav'];
        $conf['_children_bottomnav']=$conf['_children_col1nav'];
        $conf['_children_col4nav']=$conf['_children_col1nav'];
        // $conf['_children_bottomnav']['allowed_types']['HomePage_C4P_FooterLink']['label'] = "Link";
        
        return $conf;
    }
    
    public function getCol1NavItems()
    {
        return $this->getChildren('col1nav');
    }
    public function getCol2NavItems()
    {
        return $this->getChildren('col2nav');
    }

    public function getCol3NavItems()
    {
        return $this->getChildren('col3nav');
    }

    public function getBottomNavItems()
    {
        return $this->getChildren('bottomnav');
    }
    

    public function getCol4NavItems()
    {
        return $this->getChildren('col4nav');
    }
            
            
            
            
    public function PreviewTpl($style = '')
    {
        $cols='';
        for ($i=1; $i <=4; $i++) {
            $cols.='
                    <td>
                    <button class="btn btn-xs btn-info" type="button" ng-click="childrenEditItem(i,{groupname:\'col'.$i.'nav\'})"><i class="fa fa-pencil"></i> Spalte-'.$i.'</button>
                    <% loop getChildren("col'.$i.'nav") %>
                    
                    <div class="c4p-itemline">
                    $getBEPreviewHTML.RAW
                    </div>
                    <% end_loop %>
                    </td>
                    ';
        }
                
        //         <tr><td colspan="4" class="typography">$richtext(ContactText).RAW</td></tr>
        // <tr><td colspan="4" class="typography"><center>$richtext(MobileContactText).RAW</center></td></tr>
                
        return '
                <table class="c4p-table">
                <tr>
                '.$cols.'
                </tr>
                </table>

                <button class="btn btn-xs btn-info" type="button" ng-click="childrenEditItem(i,{groupname:\'bottomnav\'})"><i class="fa fa-pencil"></i> Fussleiste</button>
                    <% loop getChildren("bottomnav") %>
                     <div class="c4p-itemline">
                    $getBEPreviewHTML.RAW
                    </div>
                    <% end_loop %>
                ';
    }
}
        
                
class HomePage_C4P_FooterLink extends Article_C4P_Item
{
    public function setFormFields()
    {
                
        // $jsParams=Array();
        // $jsParams['texts']['ChooseButtonText']="Link-Ziel wählen";
        // $jsParams['texts']['ImportButtonText']="Teaser befüllen";
        // $jsParams['importToTeaserEnabled']=false;
                
                
        $p=array();
        $p['fieldname']     = "ItemLinkID";
        $p['label']         = "Link-Ziel";
        $p['type']          = "hidden";
        $p['addon_classes'] = "MwLinkField";
        $p['jsconf']        = json_encode($jsParams);
        $this->formFields['right'][$p['fieldname']]=$p;
                
        $p=array();
        $p['fieldname']     = "Text";
        $p['label']         = "Text";
        $p['type']          = "text";
        $this->formFields['right'][$p['fieldname']]=$p;
    
        $p=array();
        $p['fieldname']     = "Title";
        $p['label']         = "Titel (mouse-over)";
        $p['type']          = "text";
        $this->formFields['right'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname']     = "IconClass";
        $p['label']         = "Icon-Name (optional) <i>siehe ➜ <a href=\"http://www.fontawesome.com/icons\" target=\"_new\">liste der icon namen</a></i> z.B. 'fas fa-camera'";
        $p['type']          = "text";
        $this->formFields['right'][$p['fieldname']]=$p;
    }
            
    public function MenuText()
    {
        $t=trim($this->Text);
                
      
        
       
        return htmlspecialchars($t);
    }
            
            
    public function Link()
    {
        return $this->getLink();
    }
            
    public function getPortal()
    {
        return Controller::curr()->currentPortal;
    }
            
    public function NewsletterLink()
    {
        $url=$this->getLinkUrl();
        $portal=$this->getPortal();
        if ($portal) {
            $url=$portal->fixInterPortalUrls($url);
        }
        return $url;
    }
            
    public function getTitleStr()
    {
        $str='';
        if ($this->IconClass) {
            $str.="<i class='{$this->IconClass}' title=\"{$this->MenuText()}\"></i> ";
        }
        $str.=$this->MenuText();
                
        $str=trim($str);
        if ($this->Title) {
            $str="<span title='{$this->Title}'>$str</span>";
        }
        return $str;
    }
            
            
    public function PreviewTpl($style = '')
    {
        return '
                <% if  IconClass %>
                <div class="c4-item-line">Icon:$IconClass Title-Text: $Title</div>
                <div>&nbsp;</div>
                <% end_if %>
                $TitleStr.RAW $LinkPreviewString.RAW
                
                ';
    }
            
    public function getTpl($style = '')
    {
        return '$getLinkOpen.RAW$TitleStr.RAW$getLinkClose.RAW';
    }
}
        
        
class HomePage_C4P_FooterTitle extends HomePage_C4P_FooterLink
{
    public function PreviewTpl($style = '')
    {
        return '
                <b><% if  IconClass %>
                <div class="c4-item-line">Icon:$IconClass Title-Text: $Title</div>
                <div>&nbsp;</div>
                <% end_if %>
                $TitleStr.RAW $LinkPreviewString.RAW
                </b>
                ';
    }
            
            
    public function getTpl($style = '')
    {
        return '<h5>$getLinkOpen$TitleStr$getLinkClose</h5>';
    }
}
