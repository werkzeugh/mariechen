<?php

use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Config\Config;

class MaintainanceController extends BackendPageController
{
    private static $allowed_actions = [
        'oldcolors',
        'replaceimages',
        'fixvariants',
        'preloadimages'
    ];

    public function fixvariants()
    {
        foreach (DataObject::get('ProductVariant') as $pv) {
            if (strstr($pv->Title, '-')) {
                $newTitle=preg_replace('/^.*-([^-]+)$/', '\\1', $pv->Title);
                if ($newTitle) {
                    echo "\n<li>".$pv->Title." ➜ ".$newTitle;
                    $pv->Title=$newTitle;
                    $pv->URLSegment=strtolower($newTitle);
                    $pv->write();
                }
            }
        }
    }


    public function preloadimages()
    {
        foreach (DataObject::get('ProductPage') as $p) {
            $n++;
            echo "\n$n {$p->Title}";
            $p->preloadImages();
        }
    }

    




    public function replaceimages()
    {


        //get all products

        $products=DataObject::get('ProductPage');
        foreach ($products as $p) {
            echo "\n<li>{$p->Title}";
            echo "<ul>";
            $this->importImagesForProduct($p);
            echo "</ul>";
            flush();
        }
    }

    public function importImagesForProduct($p)
    {
        $files=$p->getImages();
        foreach ($files as $f) {
            echo "\n<li>".$f->Filename;
            $this->findAndMoveNewFile($f);
            flush();
        }
    }


    public function findAndMoveNewFile($f)
    {
        $name=basename($f->Filename);
        $old_file=$f->getAbsoluteFilename();
        $new_file="/www/evablut/files/.protected/import/".$name;
        if (file_exists($new_file)) {
            echo " ✔ $old_file ".filesize($new_file)."&gt;".filesize($old_file);
            if (filesize($new_file) > filesize($old_file)) {
                $this->replaceFile($f, $new_file);
            }
        }
    }
    
    public function replaceFile(MwFile $old_file, string $new_file)
    {
        if (copy($new_file, $old_file->AbsoluteFilename)) {
            echo $new_file."➜". $old_file->AbsoluteFilename;
            // $old_file->touch();
            echo " ri: ";
            $old_file->removeCachedImages();
            // die("\n\n<pre>mwuits-debug 2019-11-17_22:16 ".print_r(0, true));
        }
    }
  
      

    public function oldcolors(SilverStripe\Control\HTTPRequest $request)
    {
        // $p=DataObject::get_one('ProductPage', 8);

        $p=new ProductPage();
        die("\n\n<pre>mwuits-debug 2019-09-11_23:16a ".print_r($p, true));

        die("\n\n<pre>mwuits-debug 2019-09-11_23:15 ".print_r(0, true));

        $db=DBMS:: setConfForType('legacy', "localhost", "root", "root", "evablut_legacy");

        $db=DBMS::getMdb('legacy');

        $res=$db->getAssoc("select id,name,options from tl_iso_attribute");

        foreach ($res as &$row) {
            $row['options']=unserialize($row['options']);
        }
        $attrs=[];
        foreach ($res as &$row) {
            if ($row['options']) {
                foreach ($row['options'] as $opt) {
                    $attrs[]=$row['name']." / ".$opt['label'];
                }
            }
        }

        die("\n\n<pre>mwuits-debug 2019-09-05_21:07 ".print_r($attrs, true));
    }
}
