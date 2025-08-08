<?php

namespace OPNsense\AmneziaWG;

use OPNsense\Base\IndexController as BaseIndexController;

class GeneralController extends BaseIndexController
{
    public function indexAction()
    {
        $this->view->title = "AmneziaWG";
        $this->view->formDialogEditInstance = $this->getForm("dialogEditInstance");
        $this->view->generalForm = $this->getForm("general");
        $this->view->formGridInstance = $this->getFormGrid("dialogEditInstance");
        $this->view->pick('OPNsense/AmneziaWG/general');
    }
} 