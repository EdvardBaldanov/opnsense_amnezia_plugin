<?php

namespace OPNsense\AmneziaWG\Api;

use OPNsense\Base\ApiControllerBase;
use OPNsense\Core\Backend;

class ServiceController extends ApiControllerBase
{
    public function startAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg start');
            return ['status' => 'ok', 'message' => $response];
        }
        return ['status' => 'failed'];
    }

    public function stopAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg stop');
            return ['status' => 'ok', 'message' => $response];
        }
        return ['status' => 'failed'];
    }

    public function restartAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg restart');
            return ['status' => 'ok', 'message' => $response];
        }
        return ['status' => 'failed'];
    }

    public function statusAction()
    {
        if ($this->request->isGet()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg status');
            return ['status' => 'ok', 'message' => $response];
        }
        return ['status' => 'failed'];
    }

    public function reconfigureAction()
    {
        if ($this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg reconfigure');
            return ['status' => 'ok', 'message' => $response];
        }
        return ['status' => 'failed'];
    }

    public function showAction()
    {
        if ($this->request->isGet() || $this->request->isPost()) {
            $backend = new Backend();
            $response = $backend->configdRun('amneziawg show');
            
            $data = json_decode($response, true);
            if ($data === null) {
                return ['status' => 'failed', 'message' => 'Invalid JSON response'];
            }
            
            // Return records array for bootgrid
            if (isset($data['records']) && is_array($data['records'])) {
                // Add missing fields that bootgrid expects
                foreach ($data['records'] as &$record) {
                    // Add latest-handshake-age field
                    if (isset($record['latest-handshake']) && $record['latest-handshake'] > 0) {
                        $record['latest-handshake-age'] = time() - $record['latest-handshake'];
                        $record['latest-handshake-epoch'] = $record['latest-handshake'];
                    } else {
                        $record['latest-handshake-age'] = null;
                        $record['latest-handshake-epoch'] = null;
                    }
                    
                    // Add peer-status field for peers
                    if ($record['type'] === 'peer') {
                        if (isset($record['latest-handshake']) && $record['latest-handshake'] > 0) {
                            $record['peer-status'] = 'online';
                        } else {
                            $record['peer-status'] = 'offline';
                        }
                    }
                    
                    // Add status field for interfaces
                    if ($record['type'] === 'interface') {
                        if (isset($record['status']) && $record['status'] === 'up') {
                            $record['peer-status'] = 'online';
                        } else {
                            $record['peer-status'] = 'offline';
                        }
                    }
                }
                
                // Return in bootgrid format (same as WireGuard)
                return [
                    'total' => count($data['records']),
                    'rowCount' => count($data['records']),
                    'current' => 1,
                    'rows' => $data['records']
                ];
            } else {
                return [
                    'total' => 0,
                    'rowCount' => 0,
                    'current' => 1,
                    'rows' => []
                ];
            }
        }
        return ['status' => 'failed'];
    }
}
