<?php

namespace OPNsense\AmneziaWG\Api;

use OPNsense\Base\ApiMutableModelControllerBase;
use OPNsense\Core\Config;
use OPNsense\Core\Backend;

class GeneralController extends ApiMutableModelControllerBase
{
    protected static $internalModelName = 'general';
    protected static $internalModelClass = '\OPNsense\AmneziaWG\General';

    public function getAction()
    {
        // define list of configurable settings
        $result = [];
        if ($this->request->isGet()) {
            $result[static::$internalModelName] = $this->getModelNodes();
        }
        return $result;
    }

    public function setAction()
    {
        $result = ['result' => 'failed'];
        if ($this->request->isPost()) {
            $originalPost = $this->request->getPost();
            
            Config::getInstance()->lock();
            $mdl = $this->getModel();
            
            // Get old enabled state
            $oldEnabled = (string)$mdl->enabled;
            
            // Set the data directly in the model fields
            if (isset($originalPost['enabled'])) {
                $mdl->enabled = $originalPost['enabled'];
            }
            
            $result = $this->validate();
            if (empty($result['result'])) {
                $hookErrorMessage = $this->setActionHook();
                if (!empty($hookErrorMessage)) {
                    $result['error'] = $hookErrorMessage;
                } else {
                    $result = $this->save(false, true);
                    
                    // If save was successful, handle service state
                    if ($result['result'] === 'saved') {
                        $newEnabled = (string)$mdl->enabled;
                        
                        // If enabled state changed, reconfigure service
                        if ($oldEnabled !== $newEnabled) {
                            if ($newEnabled === '1') {
                                // Service was enabled, start it
                                $backend = new Backend();
                                $backend->configdRun('amneziawg start');
                            } else {
                                // Service was disabled, stop it
                                $backend = new Backend();
                                $backend->configdRun('amneziawg stop');
                            }
                        }
                    }
                }
            }
            Config::getInstance()->unlock();
        }
        return $result;
    }
} 