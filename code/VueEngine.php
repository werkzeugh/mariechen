<?php


class VueEngine
{
    public $files=array();
    public $isHot=false;

    public static function singleton($class = null)
    {
        static $instance=null;
        if (!$instance) {
            $instance=new VueEngine();
            LaravelHelpers::init();
        }
        return $instance;
    }


    public function public_path($path)
    {
        return BASE_PATH.'/'.trim($path, '/');
    }

    public function vue_helper($url)
    {
        $info=$this->parseUrl($url);
        if (array_get($info, 'error')) {
            return $info['error'];
        }

        if ($this->isHot) {
            $baseurl='https://localhost:8080';
            if (preg_match('#^https?://192\.168\.[0-9]+\.[0-9]+(:[0-9]+)?$#', array_get($_GET, 'hot'))) {
                $baseurl=array_get($_GET, 'hot');
            }
            return $baseurl.(dirname($url)).'/'.basename($url);
        } else {
            $filename=array_get($this->files, $info['fileName'], $info['fileName']);
            return $info['urlDir'].$filename;
        }
    }

    public function parseUrl($url)
    {
        $info=array();
        if (preg_match('#(/(engine|Mwerkzeug)/.*?)([^/]+)$#', $url, $m)) {
            $info['urlDir']=$m[1];
            $info['fileDir']=$this->public_path($m[1]);
            $info['fileName']=$m[3];
        }
        if ($this->checkDir($info['fileDir'])) {
            return $info;
        } else {
            return array('error' => "error_dir_not_found ({$info['fileDir']})");
        }
    }

    public function checkDir($dir)
    {
        static $okDirs=array();
        if (!array_get($okDirs, $dir)) {
            $okDirs[$dir]=is_dir($dir);
            if ((file_exists('/Applications') && file_exists(dirname($dir).'/is_hot'))|| array_get($_GET, 'hot')) {
                $this->isHot=true;
            }
            if ($okDirs[$dir]) {
                foreach (scandir($dir) as $filename) {
                    $parts=explode('.', $filename);
                    if (sizeof($parts)>2 && strlen($parts[1])>4) {
                        unset($parts[1]);
                        $this->files[implode('.', $parts)]=$filename;
                    }
                }
            }
        }
        return array_get($okDirs, $dir);
    }
}
