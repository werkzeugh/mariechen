<?php

//

class FolderPage extends Page
{
    public function onBeforeWrite()
    {
        $this->ShowInMenus=false;
        parent::onBeforeWrite();
    }

    public function getIconForPageTree()
    {
        return 'fa fa-folder';
    }

     
    public function allowedChildren()
    {
        return array('Article','TagNode');
    }
}




class FolderPageController extends PageController
{
}

class FolderPageBEController extends PageBEController
{
}
