<?php

/*
 * Copyright (C) 2024 AmneziaWG Plugin
 * All rights reserved.
 */

namespace OPNsense\AmneziaWG;

use OPNsense\Base\IndexController;

class ImportController extends IndexController
{
    public function indexAction()
    {
        $this->view->formDialogEditInstance = $this->getForm("dialogEditInstance");
        $this->view->pick('OPNsense/AmneziaWG/import');
    }
} 