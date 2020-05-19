<?php

//we don't use Page, we use FrontendPage instead, this class is just for compatibility
// copy this page to your own project and remove the underscore in the filename


class Page extends FrontendPage
{
    
    function PreviewLink($action = null)
    {
        return $this->Link($action)."?preview=1";
    }
}

class PageController extends FrontendPageController
{
  
  
    public function vue_cli($filename)
    {
                                
        return VueCliEngine::singleton()->vue_cli_helper($filename);
    }
}



class PageBEController extends FrontendPageBEController
{
  
  
  
  
}
