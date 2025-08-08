<?php

namespace OPNsense\AmneziaWG\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Backend;
use OPNsense\Core\Config;

class InstanceController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'instance';
    protected static $internalModelClass = '\OPNsense\AmneziaWG\Instance';

    public function keyPairAction()
    {
        return json_decode((new Backend())->configdRun('amneziawg gen_keypair'), true);
    }

    public function searchInstanceAction()
    {
        return $this->searchBase('instances.instance');
    }

    public function getInstanceAction($uuid = null)
    {
        return $this->getBase('instance', 'instances.instance', $uuid);
    }

    public function addAction()
    {
        $result = parent::addAction();
        return $result;
    }

    public function addInstanceAction($uuid = null)
    {
        $result = $this->addBase('instance', 'instances.instance', $uuid);
        
        // If instance was added successfully and AmneziaWG is enabled, reconfigure
        if ($result['result'] === 'saved') {
            $general = new \OPNsense\AmneziaWG\General();
            if ((string)$general->enabled === '1') {
                (new Backend())->configdRun('amneziawg reconfigure');
            }
        }
        
        return $result;
    }

    public function delInstanceAction($uuid)
    {
        // First, get the instance data before deletion
        $instance = $this->getBase('instance', 'instances.instance', $uuid);
        
        // Stop the instance and remove its configuration file
        if ($instance && isset($instance['instance'])) {
            (new Backend())->configdRun('amneziawg remove_instance ' . $uuid);
        }
        
        // Now delete the instance from OPNsense configuration
        $result = $this->delBase('instances.instance', $uuid);
        
        // If AmneziaWG is enabled, also reconfigure
        if ($result['result'] === 'deleted') {
            $general = new \OPNsense\AmneziaWG\General();
            if ((string)$general->enabled === '1') {
                (new Backend())->configdRun('amneziawg reconfigure');
            }
        }
        
        return $result;
    }

    public function setInstanceAction($uuid = null)
    {
        $result = $this->setBase('instance', 'instances.instance', $uuid);
        
        // If instance was updated successfully and AmneziaWG is enabled, reconfigure
        if ($result['result'] === 'saved') {
            $general = new \OPNsense\AmneziaWG\General();
            if ((string)$general->enabled === '1') {
                (new Backend())->configdRun('amneziawg reconfigure ' . $uuid);
            }
        }
        
        return $result;
    }

    public function setAction($uuid = null)
    {
        return $this->setInstanceAction($uuid);
    }

    public function toggleInstanceAction($uuid)
    {
        $result = $this->toggleBase('instances.instance', $uuid);
        
        // If instance was toggled successfully and AmneziaWG is enabled, reconfigure with specific UUID
        if ($result['result'] === 'saved') {
            $general = new \OPNsense\AmneziaWG\General();
            if ((string)$general->enabled === '1' && $uuid) {
                (new Backend())->configdRun('amneziawg reconfigure ' . $uuid);
            }
        }
        
        return $result;
    }
} 