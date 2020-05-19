<?php

use SilverStripe\Control\Director;
use ScssPhp\ScssPhp\Compiler;

class Article_C4P_StyleClass extends Article_C4P_Item
{
    public $showClassesTab=false;

    public static function getByClassName($key, $c4p_item)
    {
        static $styles;
        if (!$styles) {
            $stylesTemp=$c4p_item->Toprecord->C4P->getAll_StyleClasses->toArray();
            foreach ($stylesTemp as $value) {
                $styles[$value->ClassName]=$value;
            }
        }

        $ret=$styles[$key];
//      if($_GET[d] || 1 ) { $x=$ret; $x=htmlspecialchars(print_r($x,1));echo "\n<li>mwuits: <pre>$x</pre>"; }
        if ($ret) {
            return $ret;
        }

        return null;
    }


    public function getCombinedTitle()
    {
        return $this->ClassName.' : '.$this->Title;
    }

    public function compile($src)
    {
        $scss = new Compiler();

        $scss->addImportPath(function ($path) {
            $styleClass=$this->getByClassName($path, $this);
            if ($styleClass) {
                $tempFileName = tempnam(sys_get_temp_dir(), $path);
                file_put_contents($tempFileName, $styleClass->Scss);
                // echo "\n<li>$tempFileName";
                return $tempFileName;
            }
            return null;
        });

        $src=".custom-{$this->ClassName} { $src }";
        return $scss->compile($src);
    }

    public function onBeforeWrite(&$record)
    {
        try {
            $css=$this->compile($record['Scss']);
            $record['Css']=$css;
        } catch (Exception $e) {
            $msg=json_encode($e->getMessage());
            $record['Error']=$msg;

            $script=<<<JAVASCRIPT
      <script type="text/javascript">
      var scope = parent.angular.element('#c4p').scope();
      scope.\$apply(function() {
        scope.showErrorForItemById('{$this->ID}','$msg');
        });        
    </script>
JAVASCRIPT;

            die($script);
        }
    }

    public function setFormFields()
    {
        $p=array();
        $p['fieldname']     = "ClassName";
        $p['label']         = "Klassen-Name";
        $p['placeholder']    = "wird als Referenz-Name benutzt";

        $p['validation']    = "required:true,regexp:[/^[a-z0-9_]*\$/,'In Feld \"Klassen-Name\" sind nur folgende Zeichen erlaubt: a-z, 0-9, _ ']";

        $this->formFields[$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']     = "Title";
        $p['label']         = "Beschreibung";
        $this->formFields[$p['fieldname']]=$p;

        $p=array();
        $p['fieldname']     = "Scss";
        $p['label']         = "CSS <i>SASS-Syntax</i>";
        $p['type']          = "textarea";
        $p['styles']          = "height:200px";
        $this->formFields[$p['fieldname']]=$p;
    }

    public function PreviewTpl($style = "")
    {
        return '
                <div><b>$ClassName</b></div>
                <table class="c4p-table" width="100%">
                <tr>
                  <td width="50%">$Title</td>
                  <td width="50%"><code>$nl2br("Scss").RAW</code></td>
                </tr>
                </table>

  
            ';
    }

    public function getTpl($value = '')
    {
        return '
           $CSS.RAW
        ';
    }
}
