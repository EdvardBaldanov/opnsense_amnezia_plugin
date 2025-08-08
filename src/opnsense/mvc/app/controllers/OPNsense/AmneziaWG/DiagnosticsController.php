<?php

/*
 * Copyright (C) 2024 AmneziaWG Plugin
 * All rights reserved.
 */

namespace OPNsense\AmneziaWG;

class DiagnosticsController extends \OPNsense\Base\IndexController
{
    protected function templateJSIncludes()
    {
        $result = parent::templateJSIncludes();
        $result[] = '/ui/js/moment-with-locales.min.js';
        return $result;
    }

    public function indexAction()
    {
        $this->view->pick('OPNsense/AmneziaWG/diagnostics');
    }
} 