<?php

use SilverStripe\Control\Controller;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\Requirements;
use SilverStripe\View\ArrayData;

if (! function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     * @return bool
     */

     
    function starts_with($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
                return true;
            }
        }
        
        return false;
    }
}


if (! function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @param  bool    $secure
     * @return string
     */
    function asset($path, $manifestDirectory, $secure = null)
    {
        $path=$manifestDirectory.$path;
        return str_replace('/www/naturfreunde/engine/', '/engine/', $path);
    }
}

class Article_C4P_Widget extends Article_C4P_Item
{
    public function mix($path = '', $manifestDirectory = '')
    {
        return $this->laravel_mix($path, '/www/naturfreunde/'.$manifestDirectory);
    }
    
    
    public function laravel_mix($path, $manifestDirectory = '')
    {
        static $manifests = array();
        if (!starts_with($path, '/')) {
            $path = "/{$path}";
        }
        if ($manifestDirectory && ! starts_with($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        
        $manifestPath = $manifestDirectory.'/mix-manifest.json';
        
        if (! isset($manifests[$manifestPath])) {
            if (! file_exists($manifestPath)) {
                die('The Mix-Manifest '.$manifestPath.' does not exist.');
            }
            
            $manifests[$manifestPath] = json_decode(file_get_contents($manifestPath), true);
        }
        
        $manifest = $manifests[$manifestPath];
        
        if (! isset($manifest[$path])) {
            die("\n\n<pre>missing $path".print_r($manifests, 1));
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your ".
                'mix-manifest.json file'
            );
        }
        
        return asset($manifest[$path], $manifestDirectory);
    }
    
    
    
    public function setFormFields()
    {
        $p=array();
        $p['fieldname']     = "Type";
        $p['label']     = "Widget-Typ";
        // $p['type']     = "radio";
        // $p['one_line_per_option']=1;
        $p['options']       = $this->getTypes();
        
        $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;


        if ($this->Type=='baglist') {
            $p=array(); // ------- new field --------
            $p['fieldname']="Tags";
            $p['type']="hidden";
            $p['label']="Tags";
            $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='".TagEngine::singleton()->getAllTagTypesString()."' ref_id='input_Tags'></eb-tag-editor>".TagEngine::singleton()->getCodeForBackendWidgets();
            $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;

            $p=array(); // ------- new field --------
            $p['fieldname']="Tags2";
            $p['type']="hidden";
            $p['label']="and Tags";
            $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='".TagEngine::singleton()->getAllTagTypesString()."' ref_id='input_Tags2'></eb-tag-editor>";
            $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;

            $p=array(); // ------- new field --------
            $p['fieldname']="Tags3";
            $p['type']="hidden";
            $p['label']="and Tags";
            $p['after']="<eb-tag-editor class='vueapp-eb_backend' types='".TagEngine::singleton()->getAllTagTypesString()."' ref_id='input_Tags3'></eb-tag-editor>";
            $this->formFields['tabs']['main']['items'][$p['fieldname']]=$p;
        }
    }
    
    
    public function onBeforeWrite()
    {
        if (array_get($_POST, 'fdata.JsonConfig')) {
            if (!MwUtils::jsonIsValid(array_get($_POST, 'fdata.JsonConfig'))) {
                $msg=json_encode("JSON code ungültig, bitte korrigieren !");
                $record['Error']=$msg;
                
                $script="
                <script type='text/javascript'>
                var scope = parent.angular.element('#c4p').scope();
                scope.\$apply(function() {
                    scope.showErrorForItemById('{$this->ID}','$msg');
                });
                </script>";
                
                die($script);
            }
            $this->JsonConfig=MwUtils::tidyJSON($this->JsonConfig);
        }
    }
    
    public function getConfigData()
    {
        if (!isset($this->cache[__FUNCTION__])) {
            if ($this->JsonConfig) {
                $ret=json_decode($this->JsonConfig, 1);
            } else {
                $ret=[];
            }
            $this->cache[__FUNCTION__]=$ret;
        }
        return $this->cache[__FUNCTION__];
    }
    
    
    public function getTypes()
    {
        return array(
            'baglist'  => 'Produkte',
            'newsletter_form'  => 'NewsletterForm',
        );
    }
    
    public function getTypeName()
    {
        $types=$this->getTypes();
        if ($this->Type) {
            $typename=$types[$this->Type];
            if ($typename) {
                return $typename;
            } else {
                return $this->Type;
            }
        }
    }
    
    
    public function PreviewTpl()
    {
        $html= '
        $getCssInfo.RAW <div>&nbsp;</div>
        <i class="fa fa-rocket fa-lg pull-left fa-border"> Widget:</i>
        <div class="c4p-itembox"><div class="c4p-h2">$getTypeName</div></div>
        ';

        if ($this->Type=='baglist') {
            $html.="
            <div> Tags: <eb-tag-viewer class='vueapp-eb_backend' tagids='\$Tags'></eb-tag-viewer></div>".TagEngine::singleton()->getCodeForBackendWidgets().
            "<div> and Tags: <eb-tag-viewer class='vueapp-eb_backend' tagids='\$Tags2'></eb-tag-viewer></div>".
            "<div> and Tags: <eb-tag-viewer class='vueapp-eb_backend' tagids='\$Tags3'></eb-tag-viewer></div>";

            // $html.='tags: $Tags';
        }
    
        
        return $html;
    }
    

    public function getNewsletterFormHtml()
    {
        return $this->renderWith('Includes/NewsletterForm');
    }
    
    public function getHTML($style = "")
    {
        switch ($this->Type) {
            case 'baglist':
                $html=$this->getBagListHtml();
                break;

            case 'newsletter_form':
                $html=$this->getNewsletterFormHtml();
                break;

            default:
                $html="Widget für Typ:  {$this->Type} wird demnächst hier angezeigt, muss noch gebaut werden";
                break;
        }
        
        return  $html;
    }
    
    
    public function getBagListHtml()
    {
        return BagManager::singleton()->getWidgetHtml($this);
    }
}
