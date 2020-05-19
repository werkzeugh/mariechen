<?php

use SilverStripe\View\Requirements;

class Article_C4P_Item extends C4P_Container
{
    public $showClassesTab=1;
    
    
    public function getCssStyles()
    {
        $strparts=[];
        
        if ($this->MaxWidth) {
            $strparts['max-width']=$this->MaxWidth;
        }
        
        if ($this->FontSize) {
            $strparts['font-size']=$this->FontSize;
        }
        
        if ($this->TextColor) {
            $strparts['color']=$this->TextColor;
        }
        
        if ($this->BgColor) {
            $strparts['background-color']=$this->BgColor;
        }
        if ($this->PaddingTop) {
            $strparts['padding-top']=$this->PaddingTop;
        }
        if ($this->PaddingBottom) {
            $strparts['padding-bottom']=$this->PaddingBottom;
        }
        
        return $strparts;
    }
    

    public function getLanguages()
    {
        return ['en'=>'english','de'=>'deutsch',];
    }


    public function afterSetFormFields()
    {
        $this->addSettingsTabToContainer();
    }
    
    public function CssClassesAsList()
    {
        // $classesById= $this->Toprecord->Level(1)->getStyleClassNames();
        
        $ids=explode(' ', $this->CustomCssClasses);
        // if(@$_GET['d'] || 1 ) { $x=$ids; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits:{$this->CustomCssClasses} <pre>$x</pre>"; }
        $ret=[];
        foreach ($ids as $classId) {
            if ($classId) {
                $ret[]="custom-".$classId;
            }
        }
        if ($this->Layout) {
            $ret[]=$this->Layout;
        }

        if ($this->OnlyForLanguage) {
            $ret[]=$this->OnlyForLanguage."-only";
        }
        return $ret;
    }
    
    
    public function getCssClassString()
    {
        $str='';
        foreach ($this->CssClassesAsList() as $className) {
            $str.="$className ";
        }
        
        return $str;
    }
    
    
    public function getCssClassesPreview()
    {
        $str=implode(", ", $this->CssClassesAsList());
        
        if ($str) {
            $str="<div class=\"c4p-tag\">$str</div>";
        }
        
        return $str;
    }
    
    
    public function getDefaultValue($name)
    {
        switch ($name) {
            case 'PaddingTop':
            case 'PaddingBottom':
                return '12px';
            break;
        }
    }

    public function getLayouts()
    {
        return [];
    }


    
    public function addSettingsTabToContainer()
    {
        if ($this->showClassesTab) {
            // $p=array();
            // $p['fieldname']  = "CssClasses";
            // $p['label']      = "Css-Klassen";
            // $p['type']      = "checkboxes";
            // $p['options']   = $this->Toprecord->Level(1)->getStyleClasses();
            // $this->formFields['tabs']['Classes']['items'][$p['fieldname']]=$p;
            
            
            $p=array();
            $p['fieldname']  = "PaddingTop";
            $p['label']      = "Padding-Top";
            $p['width']      = "60";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;

            $p=array();
            $p['fieldname']  = "OnlyForLanguage";
            $p['label']      = "show only for language:";
            $p['options']  = $this->getLanguages();
            $p['width']='40';
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;


            
            $p=array();
            $p['fieldname']  = "PaddingBottom";
            $p['label']      = "Padding-Bottom";
            $p['width']      = "60";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
            
            $p=array();
            $p['fieldname']  = "CustomCssClasses";
            $p['label']      = "CustomCss-Klassen <i>durch leerzeichen getrennt</i>";
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;

            if ($this->getLayouts()) {
            }
            $p=array();
            $p['fieldname']  = "Layout";
            $p['label']  = "Stil";
            $p['options']  = $this->getLayouts();
               
            $p['label']      = "stil";
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;

            $p=array();
            $p['fieldname']  = "LinkTargetSlug";
            $p['label']      = "Sprungziel-Name a-z,0-9,-_";
            $p['width']      = "60";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['Sprungmarke']['items'][$p['fieldname']]=$p;

            $p=array();
            $p['fieldname']  = "LinkTargetTitle";
            $p['label']      = "Sprungziel-Titel";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['Sprungmarke']['items'][$p['fieldname']]=$p;

            $p=array();
            $p['fieldname']  = "LinkTargetSlug_de";
            $p['label']      = "Sprungziel-Name a-z,0-9,-_ (DE)";
            $p['width']      = "60";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['Sprungmarke']['items'][$p['fieldname']]=$p;

        
            $p=array();
            $p['fieldname']  = "LinkTargetTitle_de";
            $p['label']      = "Sprungziel-Titel (DE)";
            $p['default']    = $this->getDefaultValue($p['fieldname']);
            $this->formFields['tabs']['Sprungmarke']['items'][$p['fieldname']]=$p;
        }
    }
    
    public function getCssInfo()
    {
        return $this->getCssStylePreview()." ".$this->getCssClassesPreview();
    }
    
    public function getCssStyleString()
    {
        $styles=$this->CssStyles;
        $strparts=array();
        
        foreach ($styles as $key => $value) {
            $strparts[]="$key:$value";
        }
        
        if ($this->CustomCss) {
            $strparts[]=$this->CustomCss;
        }
        
        $s=trim(implode(';', $strparts));
        
        
        return $s;
    }
    
    public function getCssStylePreview()
    {
        $ret='';
        $str=$this->getCssStyleString();
        if ($str) {
            $ret.="<div class=\"c4p-tag\">$str</div>";
        }
        if ($this->LinkTargetSlug) {
            $ret.=" <div class=\"c4p-tag\" style=\"background:orange\">{$this->LinkTargetTitle} ➜ #{$this->LinkTargetSlug}</div>";
        }
        return $ret;
    }
    
    


    public function getLinkOpen($tag_addon = "", $LinkField = 'ItemLinkID')
    {
        $closingChar=">";
        if ($tag_addon=='__keepOpen') {
            $closingChar="";
            $keepOpen=1;
        }

        if ($this->$LinkField) {
            $obj=MwLink::getObjectForMwLink($this->$LinkField);
            if ($obj) {
                $url=$obj->Link();
                $target=$obj->TargetAttribute;
            }
            return "<a href=\"$url\" $target $tag_addon".$closingChar;
        } else {
            return "<span $tag_addon ".$closingChar;
        }
    }
    
    
    
    public function getLinkPreviewString()
    {
        if ($this->ItemLinkID) {
            $url=MwLink::getURLForMwLink($this->ItemLinkID);
            
            $limit=70;
            if (strlen($url)>$limit) {
                $url="... ".substr($url, -($limit-5));
            }
            
            $target=MwLink::getTargetAttributeForMwLink($this->ItemLinkID);
            if ($target) {
                $target=" ($target)";
            }
            return "$url $target";
        } else {
            return "n/a";
        }
    }
    
    public function getLinkClose()
    {
        if ($this->ItemLinkID) {
            return "</a>";
        } else {
            return "";
        }
    }
    
    public function myPictureCopyright()
    {
        if ($this->Picture) {
            // if($_GET[d] || 1 ) { $x=$this->Picture; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
            $cp=$this->Picture->Copyright;
        }
        if (!$cp) {
            $cp=$this->PictureCopyright;
        }
        
        if ($cp && !strstr($cp, '&copy;') && trim($cp)) {
            $cp="&copy; ".$cp;
        }
        
        return $cp;
    }
    
    public function nl2br($fieldname)
    {
        $content=trim($this->$fieldname);
        return nl2br($content);
    }
    


    public function richtext($fieldname)
    {
        $html=$this->$fieldname;
        // $html=$this->listify($html);
        $html=$this->makeFontawesomeIcons($html);
        return MwLink::resolveLinks($html);
    }

    public function iconify($fieldname)
    {
        return $this->makeFontawesomeIcons($this->$fieldname);
    }

    public function makeFontawesomeIcons($html)
    {
        $html=preg_replace('/\[([^\]]*fa-[^\]]*)\]/mis', '<i class="$1"></i>', $html);
        return $html;
    }

    
    
    public function getTpl($style = "")
    {
        $this->Style=$style;
        
        return $this->renderWith("Includes/C4P_".$this->getCTypeShort());
    }

    public function getLinkedObject()
    {
        if ($this->ItemLinkID) {
            $obj=MwLink::getObjectForMwLink($this->ItemLinkID);
            return $obj;
        } else {
            return "";
        }
    }

    public function __get($name)
    {
        if ($GLOBALS['CurrentLanguage']=='de' && preg_match('/Text|Title|Slug/', $name)) {
            $val=parent::__get($name."_de");
            if ($val) {
                return $val;
            }
        }
        return parent::__get($name);
    }
}


class Article_C4P_2Cols extends Article_C4P_Item
{
    public function getCol1Span()
    {
        return $this->Col1Width?$this->Col1Width:9;
    }
    
    public function getCol2Span()
    {
        return 12-$this->getCol1Span();
    }
    
    public function getCol1Percentage($minus = 0)
    {
        return ($this->getCol1Span()/12)*100-$minus;
    }
    
    public function getCol2Percentage($minus = 0)
    {
        return ($this->getCol2Span()/12)*100-$minus;
    }
    
    public function getPercentage($pos)
    {
        return $pos;
    }
    
    
    public function getDefaultValue($name)
    {
        switch ($name) {
            case 'PaddingTop':
            case 'PaddingBottom':
                return '0px';
            break;
        }
    }

    public function getLayouts()
    {
        return [
             "shifted"=>"shifted",
            "shifted_overlap"=>"shifted_overlap"
        ];
    }

    
    
    public function setFormFields()
    {
        $p              = array(); // ------- new field --------
        $p['fieldname'] = "Col1Width";
        $p['label']     = "Aufteilung der Spalten";
        $p['type']      = "radio";
        $p['options']   = $this->getColumnIcons();
        
        if (!$this->Col1Width) {
            $p['default_value']=6;
        }
        
        $this->formFields['tabs']['spaltenaufteilung']['items']['left'][$p['fieldname']]=$p;

        if (strstr($this->Layout, "shifted")) {
            $p=array();
            $p['fieldname']  = "ShiftDown";
            $p['label']      = "Shift-Down";
            $p['width']      = "60";
            // $p['preset']    = "50%";
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;

            $p=array();
            $p['fieldname']  = "ShiftDownCol";
            $p['label']      = "Shift-Down in welcher Spalte";
            $p['options']    =
            ['col1'=>"linke Spalte",'col2'=>'rechte Spalte',];
            $p['default']     ='col2';
            // $p['preset']    = "50%";
            $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
        }
    }
    
    public function cleanupPercentage($percentage)
    {
        if ($percentage>95) {
            return 100;
        }
    }
    
    public function getColumnIcons()
    {
        $options=array();
        $percentages=array(
            2=>15, 3=>25, 4=>30, 5=>40, 6=>50, 7=>60, 8=>70, 9=>75, 10=>85, 12=>100
        );
        
        for ($i=3; $i <=9; $i++) {
            $percentage1=$percentages[$i];
            $percentage2=100-$percentage1;
            
            $html="<span style='width:".($i*3)."px;border:1px solid #999;display:inline-block;height:25px;margin-right:2px' />";
            $html.="<span style='width:".((12-$i)*3)."px;border:1px solid #999;display:inline-block;height:25px;' />&nbsp;<div
            style='font-size:11px;text-align:center'>$percentage1 / $percentage2</div>";
            
            $options[$i]=$html;
        }
        return $options;
    }
    
    public function swapColumns($args)
    {
        $temp=$this->record['_children_col2'];
        $this->record['_children_col2']=$this->record['_children_col1'];
        $this->record['_children_col1']=$temp;
        if ($this->Col1Width) {
            $this->record['Col1Width']=12-$this->Col1Width;
        }
        $this->write();
        return array('status'=>'ok');
    }
    
    public function PreviewTpl()
    {
        return '
        $getCssInfo.RAW
        
        <style>
        #c4p-itembox_{$ID}
        </style>
        <table class="c4p-table">
        <tr>
        <td style="max-width:{$getCol1Percentage(10)}%;width:{$getCol1Percentage(10)}%" >
        <button ng-show="i.permissions.editChildren" class="btn c4p-hide-on-children c4p-hide-on-alias btn-xs btn-primary" type="button" ng-click="childrenEditItem(i,{groupname:\'col1\'})" title="Inhalte in linker Spalte bearbeiten"><i class="fa fa-pencil"></i> linke spalte</button>
        <button  ng-show="i.permissions.edit" class="btn c4p-hide-on-children c4p-hide-on-alias btn-sm pull-right" type="button" ng-click="callActionOnItem(i,\'swapColumns\',{\'dummy\':1})" title="spalten vertauschen" ><i class="fa fa-arrows-h"></i></button>
        <div class="c4p-preview-children">
        <% loop getChildren("col1") %>
        <div class="c4p-itemline">
        $getBEPreviewHTML.RAW
        </div>
        <% end_loop %>
        </div>
        
        </td>
        <td style="max-width:{$getCol2Percentage(10)}%;width:{$getCol2Percentage(10)}%" >
        <button ng-show="i.permissions.editChildren" class="btn c4p-hide-on-children c4p-hide-on-alias btn-xs btn-primary" type="button" ng-click="childrenEditItem(i,{groupname:\'col2\'})" title="Inhalte in rechter Spalte bearbeiten"><i class="fa fa-pencil"></i> rechte spalte</button>
        <div class="c4p-preview-children">
        <% loop getChildren("col2") %>
        <div class="c4p-itemline">
        $getBEPreviewHTML.RAW
        </div>
        <% end_loop %>
        </div>
        
        </td>
        </tr>
        </table>
        
        ';
    }

    public function ShiftDownStyle()
    {
        if (strstr($this->ShiftDown, "-")) {
            $str="padding-top:0;top:".$this->ShiftDown;
        } else {
            $str="padding-top:".$this->ShiftDown;
        }
        return $str;
    }
    
    public function getTpl($style = '')
    {
        return '
        <section class="ce ce-2cols align-$Alignment $getCssClassString"  id="$LinkTargetSlug" style="$getCssStyleString">
            <div class="ce-outer">
                <div class="ce-inner">
                    <div class="col1 span-$getCol1Span <% if  ShiftDownCol=="col1"  %>shifted-col<% end_if %>" >
                        <div class="col-content">
                            <div class="col-inner" <% if  ShiftDownCol=="col1"  %>style="{$ShiftDownStyle}"<% end_if %>>
                                <% loop getColumnElements("col1") %>
                                $getHTML
                                <% end_loop %>
                            </div>
                        </div>
                    </div>
                    <div class="col2 span-$getCol2Span <% if  ShiftDownCol=="col2"  %>shifted-col<% end_if %>" >
                        <div class="col-content">
                            <div class="col-inner" <% if  ShiftDownCol=="col2"  %>style="{$ShiftDownStyle}"<% end_if %>>
                                <% loop getColumnElements("col2") %>
                                $getHTML
                                <% end_loop %>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        ';
    }
    
    public function getConfig()
    {
        $conf['_children_col1']['allowed_types']=$this->Toprecord->getDefaultC4PElements();
        
        $conf['_children_col2'] = $conf['_children_col1'];
        $conf['childgroups'] =array('col1','col2');
        
        return $conf;
    }
    
    public function getColumnElements($groupname)
    {
        return $this->getChildren($groupname);
    }
}


class Article_C4P_Image extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array();
        $p['fieldname'] = "PictureID";
        $p['label']     = "Foto";
        $p['type']      = "MwFileField";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname'] = "PictureCopyright";
        $p['label']     = "Copyright <i>wenn noch nicht im Bild hinterlegt</i>";
        $p['type']      = "text";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname'] = "PictureDescription";
        $p['label']     = "Bild-Beschreibung";
        $p['type']      = "textarea";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname']     = "ItemLinkID";
        $p['label']         = "Link";
        $p['type']          = "hidden";
        $p['addon_classes'] = "MwLinkField";
        $this->formFields['tabs']['content']['items']['right'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname'] = "MaxWidth";
        $p['label']     = "max. Breite";
        $p['placeholder'] = "z.B: 50px";
        $p['width']      = "100";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
        

        $p=array();
        $p['fieldname'] = "PictureRatio";
        $p['label']     = "Picture Aspect-Ratio";
        $p['options']      = [
            '1_1'=>'square',
            ''=>'original',
        ];
        $this->formFields['tabs']['content']['items']['right'][$p['fieldname']]=$p;

              
        $p=array();
        $p['fieldname'] = "OverlayText";
        $p['label']     = "Text-Overlay";
        $p['type']      = "textarea";
        $this->formFields['tabs']['TextOverlay']['items'][$p['fieldname']]=$p;

        $p=array();
        $p['fieldname'] = "OverlayText_de";
        $p['label']     = "Text-Overlay (DE)";
        $p['type']      = "textarea";
        $this->formFields['tabs']['TextOverlay']['items'][$p['fieldname']]=$p;


        $p=array();
        $p['fieldname'] = "OverlayTextSize";
        $p['label']     = "Schriftgrösse";
        $p['options']      = [
            'txt-s'=>'small',
            'txt-m'=>'medium',
            'txt-l'=>'large',

        ];
        $p['default']= "txt-m";
        $this->formFields['tabs']['TextOverlay']['items'][$p['fieldname']]=$p;


        $p=array();
        $p['fieldname'] = "OverlayTextColor";
        $p['label']     = "Farbe";
        $p['options']      = [
            'black'=>'schwarz',
            'white'=>'weiss',
        ];
        $p['default']= "black";
        $this->formFields['tabs']['TextOverlay']['items'][$p['fieldname']]=$p;

        
        // $p=array(); // ------- new field --------
        // $p['fieldname']     = "Alignment";
        // $p['label']         = "Ausrichtung";
        // $p['type']         = "radio";
        // $p['options']         = array(
            //     ''=>'linksbündig',
            //     'center'=>'zentriert',
            //     // 'rechts'=>'rechts'
            // );
            
            // $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
    }
        
        
    public function PreviewTpl()
    {
        return '
            
            $getCssInfo.RAW
            
            <div style="$getCssStyleString">
             
                    <img src="$myPictureUrl" alt="{$PictureDescription}"  style="max-width:200px">
            </div>
            <b>$nl2br("OverlayText").RAW</b>
            
            <div>&nbsp;</div>
            <% if  PictureCopyright %> $myPictureCopyright.RAW <% end_if %>
            ';
    }
        
    public function myPictureUrl()
    {
        $image = $this->Picture;
        
        if ($image) {
            // return $image->Link();
            $image=$image->Image();
            $w=$image->getWidth();
            $h=$image->getHeight();
            if (!$w) {
                $w=1000;
            }
            if ($w>1200) {
                $w=1200;
            }
            if (!$h) {
                $h=1000;
            }
            if ($h>1200) {
                $h=1200;
            }
            if ($this->PictureRatio=='1_1') {
                $x=min($w, $h);
                $h=$x;
                $w=$x;
                $thumbnail = $image->CroppedImage($w, $h);
            }
            if ($thumbnail) {
                return $thumbnail->Link();
            }
            return $image->Link();
        }
    }
        
        
    public function getTpl($style = "")
    {
        return '
            <section class="ce ce-image  align-$Alignment $getCssClassString" style="$getCssStyleString">
                <div class="ce-outer">
                    <div class="ce-inner">
                        <div class="imgwrap">
                        $LinkOpen.RAW
                            <% if  OverlayText %>
                            <div class="overlay color-{$OverlayTextColor} {$OverlayTextSize}">
                            <div class="txt">
                                $nl2br("OverlayText").RAW
                            </div></div>
                            <% end_if %>
                            
                                <img src="$myPictureUrl" title="{$PictureDescription}" />
                            $LinkClose.RAW
                            <% if  myPictureCopyright %> <div class="copyright">$myPictureCopyright.RAW</div> <% end_if %>
                        </div>
                    </div>
                </div>
            </section>
            ';
    }
}
    
class Article_C4P_Text extends Article_C4P_Item
{
    public function setFormFields()
    {
            
        // $p=array(); // ------- new field --------
        // $p['fieldname']     = "Title";
        // $p['label']         = "Titel <i>optional</i>";
        // $this->formFields['tabs']['content']['items'][$p['fieldname']]=$p;
            
        $p=array(); // ------- new field --------
        $p['fieldname']     = "Text";
        $p['label']         = "Text";
        $p['type']          = "textarea";
        $p['addon_classes'] = "tinymce";
        $p['jsdata']        = array('allowed_styles'=>array('h2','h3','h4','text-klein'),'body_class'=>'typography','content_css'=>'/mysite/css/backend.css');
        $p['styles']        = "height:400px;width:700px;";
        $this->formFields['tabs']['content']['items'][$p['fieldname']]=$p;

        $p=array(); // ------- new field --------
        $p['fieldname']     = "Text_de";
        $p['label']         = "Text (DE)";
        $p['type']          = "textarea";
        $p['addon_classes'] = "tinymce";
        $p['jsdata']        = array('allowed_styles'=>array('h2','h3','h4','text-klein'),'body_class'=>'typography','content_css'=>'/mysite/css/backend.css');
        $p['styles']        = "height:400px;width:700px;";
        $this->formFields['tabs']['content']['items'][$p['fieldname']]=$p;

            
            
        // $p=array(); // ------- new field --------
        // $p['fieldname']     = "Alignment";
        // $p['label']         = "Ausrichtung";
        // $p['type']         = "radio";
        // $p['options']         = array(
            //     ''=>'linksbündig',
            //     'center'=>'zentriert',
            //     // 'rechts'=>'rechts'
            // );
                
            // $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
                
                
            // $p=array(); // ------- new field --------
            // $p['fieldname']     = "FontSize";
            // $p['label']         = "Schriftgrösse";
            // $p['placeholder']   = "z.b: 30px";
            // $p['width']         = "60";
            // $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
                
            // $p=array(); // ------- new field --------
            // $p['fieldname']     = "FontClass";
            // $p['label']         = "Schriftart";
            // $p['options']       = [
                //         ''=>'inherit',
                //         'font-serif'=>'serif',
                //         'font-sans-serif'=>'sans-serif',
                //         'font-fixed'=>'fixed',
                // ];
                // $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
                    
                    
                // $p=array(); // ------- new field --------
                // $p['fieldname']     = "CustomCss";
                // $p['label']         = "CustomCss";
                // $p['placeholder']   = "z.b: max-width:400px";
                // $this->formFields['tabs']['css']['items'][$p['fieldname']]=$p;
    }
                
    public function getLayouts()
    {
        return ["zitat"=>"zitat" ];
    }
                
                
    public function PreviewTpl()
    {
        return '
                    $getCssInfo.RAW
                    <% if FontClass  %>
                    <div class="c4p-tag">$FontClass</div>
                    <% end_if %>
                    <div>&nbsp;</div>
                    <div style="$getCssStyleString">
                    <% if  Alignment=="center" %> <div style="text-align:center"> <% end_if %>
                    <div class="typography">$richtext(Text).RAW</div>
                    <% if  Alignment=="center" %> </div> <% end_if %>
                    </div>
                    ';
    }
    public function getTpl($style = "")
    {
        return '
                    <section class="ce ce-text align-$Alignment $FontClass $getCssClassString" style="$getCssStyleString"  id="$LinkTargetSlug">
                    <div class="ce-outer">
                    <div class="ce-inner">
                    <div class="typography">$richtext(Text).RAW</div>
                    </div>
                    </div>
                    </section>
                    ';
    }
}
            
            
            
            
            


            
class Article_C4P_Link extends Article_C4P_Item
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
                ➜ $TitleStr.RAW $LinkPreviewString.RAW
                
                ';
    }
            
    public function getTpl($style = '')
    {
        return '$getLinkOpen.RAW<i class="icon fal fa-long-arrow-right"></i><span class="txt">$TitleStr.RAW</span>$getLinkClose.RAW';
    }
}
            
class Article_C4P_FullPageSlider extends Article_C4P_Item
{
    public function init()
    {
        Requirements::javascript("mysite/thirdparty/tinyfade/dist/tinyfase_legacy.js");
        Requirements::CSS("mysite/thirdparty/tinyfade/tinyfade.css");
        parent::init();
    }
                
    public function getConfig()
    {
        $conf['_children']['allowed_types']=array();
        $conf['_children']['allowed_types']['Article_C4P_Slide']['label'] = "Slide";
        return $conf;
    }
                
    public function PreviewTpl($style = "")
    {
        $moreChilds=$this->getChildren()->count();
        return '
                    $getCssInfo.RAW
                    <% loop Children %>
                    
                    <div class="c4p-itembox" style="width:150px;">
                    $getBEPreviewHTML.RAW
                    </div>
                    <% end_loop %>
                    ';
    }
}
 

class Article_C4P_ImgSlider extends Article_C4P_Item
{
    public function init()
    {
        Requirements::javascript("node_modules/swiper/js/swiper.min.js");
        Requirements::CSS("node_modules/swiper/css/swiper.min.css");
        parent::init();
    }
                
    public function getConfig()
    {
        $conf['_children']['allowed_types']=array();
        $conf['_children']['allowed_types']['Article_C4P_Slide']['label'] = "Slide";
        return $conf;
    }
                
    public function PreviewTpl($style = "")
    {
        $moreChilds=$this->getChildren()->count();
        return '
                    $getCssInfo.RAW
                    <% loop Children %>
                    
                    <div class="c4p-itembox" style="width:150px;">
                    $getBEPreviewHTML.RAW
                    </div>
                    <% end_loop %>
                    ';
    }
}
            
            
class Article_C4P_Slide extends Article_C4P_Item
{
    public $showClassesTab=0;
    public static $myCasting = array(
        // 'Picture'=>'MwFile',
        'OverlayPicture'=>'MwFile',
      );
  
    public function setFormFields()
    {
        $p=array();
        $p['fieldname'] = "PictureID";
        $p['label']     = "Picture";
        $p['type']      = "MwFileField";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;
                    
                    
        // $p=array();
        // $p['fieldname']     = "Text";
        // $p['label']         = "Text";
        // $p['type']          = "textarea";
        // // $p['styles']          = "height:100px;width:700px";
        // $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;


        $p=array();
        $p['fieldname'] = "ImageID";
        $p['label']     = "Overlay-Picture";
        $p['type']      = "MwFileField";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;
    }
                
    public function PreviewTpl($style = "")
    {
        return '
                    <div style="max-width:200px">$Picture.SetWidth(400)</div>
                    <% if  ImageID %>
                        <img style="max-width:200px" src="$Image.Link">
                    <% end_if %>
                    <div class="typography">$nl2br(Text).RAW</div>
                    <br clear=all>
                    
                    ';
    }
                
    public function getTpl($value = '')
    {
        return '
                    <li class="c4p-icon">
                    
                    <div class="non-hover">$Picture</div>
                    
                    <div class="text">$nl2br("Text").RAW</div>
                    </li>
                    
                    ';
    }
}
            
            
class Article_C4P_LinkList extends Article_C4P_Item
{
    public function getConfig()
    {
        $conf['_children']['allowed_types']=array();
        $conf['_children']['allowed_types']['Article_C4P_Link']['label'] = "Link";
        return $conf;
    }
                
    public function PreviewTpl($style = "")
    {
        $moreChilds=$this->getChildren()->count();
        return '
                    $getCssInfo.RAW
                    <% loop Children %>
                        <div class="c4p-itemline">
                        $getBEPreviewHTML.RAW
                        </div>
                    <% end_loop %>
                    ';
    }
                
    public function getTpl($style = "")
    {
        return '
                    <section class="ce ce-linklist">
                        <div class="ce-outer">
                            <div class="ce-inner">
                                <ul>
                                    <% loop Children %>
                                        <li>
                                            $getHtml.RAW
                                        </li>
                                    <% end_loop %>
                                </ul>
                            </div>
                        </div>
                    </section>
                    ';
    }
}
            
            
class Article_C4P_PageTitle extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array(); // ------- new field --------
        $p['fieldname']    = "Title";
        $p['type']         = "text";
        $p['label']        = "Seiten-Titel <i>wenn das Feld leer bleibt, wird der Titel der Seite verwendet</i>";
        $p['placeholder']  =  $this->Toprecord->SelfOrAlias->Title;
        $this->formFields['tabs']['content']['items'][$p['fieldname']]=$p;
    }
                
    public function MyTitle()
    {
        if (trim($this->Title)) {
            return $this->Title;
        } else {
            return $this->Toprecord->SelfOrAlias->Title;
        }
    }
                
    public function PreviewTpl()
    {
        return '$getCssInfo.RAW
                    <% if FontClass  %>
                    <div class="c4p-tag">$FontClass</div>
                    <% end_if %>
                    <div class="c4p-h1">$MyTitle</div>
                    ';
    }
                
    public function DateString()
    {
        $datum=new Datum($this->Toprecord->SelfOrAlias->PageDate);
        return $datum->GermanDate('j. F Y');
    }
                
    public function ShowCount()
    {
        static $c=1;
        return $c++;
    }
                
                
                
    public function getTpl($style = "")
    {
        return '
                    <section class="ce ce-page-title $FontClass $getCssClassString" style="$getCssStyleString">
                    <div class="ce-outer">
                    <div class="ce-inner ">
                    <h1>$MyTitle</h1>
                    <% if $Toprecord.ClassName=="NewsPage" %>
                    <% if $ShowCount==1  %>
                    <div class="pagedate">$DateString</div>
                    <% end_if %>
                    <% end_if %>
                    </div>
                    </div>
                    </section>
                    ';
    }
}

class Article_C4P_FullpagePanel extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array();
        $p['fieldname'] = "PictureID";
        $p['label']     = "Bild <i>(wird vollflächig im Hintergrund dargestellt)</i>";
        $p['type']      = "MwFileField";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;


        $p=array();
        $p['fieldname']     = "Title";
        $p['label']         = "Titel";
        $p['type']          = "text";
        $this->formFields['tabs']['content']['items']['left'][$p['fieldname']]=$p;
    }

    public function PreviewTpl()
    {
        return '
            
            $getCssInfo.RAW
            
            <div style="$getCssStyleString">
           
            <div style="width:300px;height:150px;padding: 10% 0;background: center url(\'{$Picture.Link()}\');background-size:cover;
            display:flex">
            <div class="c4p-h2" style="text-align:center;width:100%">
                $Title
            </div>
            </div>

            </div>
            
           ';
    }
}
            
class Article_C4P_Section extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array(); // ------- new field --------
        $p['fieldname']     = "InternalName";
        $p['label']         = "Name <i>wird nur im Backend angezeigt</i>";
        $this->formFields['tabs']['settings']['items'][$p['fieldname']]=$p;
    }
                
                
                
                
    public function PreviewTpl()
    {
        return '
                    <style>
                    #c4p-itembox_{$ID}
                    </style>
                    
                    $getCssInfo.RAW
                    <div>&nbsp;</div>
                    <div class="c4p-h2 c4p-nice2have" style="max-width:100%" >$InternalName</div>
                    
                    <div class="c4p-itembox" style="max-width:100%" >
                    <div>
                    <% loop getChildren %>
                    <div class="c4p-itemline">
                    $getBEPreviewHTML
                    </div>
                    <% end_loop %>
                    </div>
                    </div>
                    
                    
                    ';
    }
                
                
                
    public function getTpl($style = "")
    {
        $html='
                    <div class="c4p-section $getCssClassString" id="section-$ID" style="$getCssStyleString">
                    <% loop getChildren %>
                    $getHtml().RAW
                    <% end_loop %>
                    </div>
                    ';
                    
                    
        return $html;
    }
                
                
    public function getConfig()
    {
        $conf['_children']['allowed_types']=$this->Toprecord->getDefaultC4PElements();
        return $conf;
    }
                
                
                
    public function getColumnElements($groupname)
    {
        return $this->getChildren($groupname) ;
    }
}
            
            
            
            
class Article_C4P_PlainHtml extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array(); // ------- new field --------
        $p['fieldname'] = "Text";
        $p['label']     = "HTML";
        $p['type']      = "textarea";
        $p['styles']    = "height:200px";
        $this->formFields['tabs']['content']['items'][$p['fieldname']]=$p;
    }
                
    public function EscapedText()
    {
        return MwUtils::ShortenText($this->Text, 200);
    }
                
    public function PreviewTpl()
    {
        return '
                    <div>
                        $getCssInfo.RAW
                    </div>
                    
                    <div class="typography">$EscapedText.RAW</div>
                    ';
    }
                
                
    public function getTpl($style = "")
    {
        return '
                    <section class="ce ce-text align-$Alignment $FontClass $getCssClassString" style="$getCssStyleString">
                    <div class="ce-outer">
                    <div class="ce-inner">
                    $Text.RAW
                    </div>
                    </div>
                    </section>
                    ';
    }
}



class Article_C4P_Button extends Article_C4P_Item
{
    public function setFormFields()
    {
        $p=array();
        $p['fieldname'] = "Title";
        $p['label']     = "Button-Text";
        $p['type']      = "text";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']     = "ItemLinkID";
        $p['label']         = "Link";
        $p['type']          = "hidden";
        $p['addon_classes'] = "MwLinkField";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;
        
        $p=array();
        $p['fieldname'] = "ButtonClass";
        $p['label']     = "ButtonClass";
        $p['type']      = "text";
        $p['default']   = "btn-primary";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']     = "IconClass";
        $p['label']         = "Icon-Name (optional) <i>siehe ➜ <a href=\"http://www.fontawesome.com/icons\" target=\"_new\">liste der icon namen</a></i> z.B. 'fas fa-camera'";
        $p['type']          = "text";
        $p['default']       = "far fa-long-arrow-alt-right";
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;
    }

    public function getTitleStr()
    {
        $str=trim($this->Title);
        if ($this->IconClass) {
            $str="<i class='{$this->IconClass}'></i> ".$str;
        }
    
                
        return $str;
    }
        

    public function PreviewTpl()
    {
        return '
            
         <div>
             $getCssInfo.RAW
         </div>
            <button class="btn $ButtonClass" type="button" ng-click="">$TitleStr.RAW</button> 
            $LinkPreviewString

           
     ';
    }


    public function getTpl($style = '')
    {
        return '

    <section class="ce ce-button  $getCssClassString" style="$getCssStyleString">
        <div class="ce-outer">
            <div class="ce-inner">
                <div>
                $getLinkOpen(\'class="btn '.$this->ButtonClass.'"\').RAW$TitleStr.RAW$LinkClose.RAW
                </div>
            </div>
        </div>
    </section>
    ';
    }
}
