<?php

namespace OPNsense\AmneziaWG;

use OPNsense\Base\IndexController as BaseIndexController;

class IndexController extends BaseIndexController
{
    public function indexAction()
    {
        // Redirect to instances page
        $this->response->redirect('/ui/amneziawg/general#instances');
    }
}
