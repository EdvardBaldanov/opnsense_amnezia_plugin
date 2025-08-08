<?php

/*
 * Copyright (C) 2024 AmneziaWG Plugin
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 * OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

function amneziawg_enabled()
{
    return (string)(new \OPNsense\AmneziaWG\General())->enabled == '1';
}

function amneziawg_services()
{
    $services = [];

    if (!amneziawg_enabled()) {
        return $services;
    }

    $services[] = [
        'description' => 'AmneziaWG VPN',
        'configd' => [
            'start' => ["amneziawg start"],
            'restart' => ["amneziawg restart"],
            'stop' => ["amneziawg stop"],
        ],
        'name' => 'amneziawg',
        'nocheck' => true,
        'id' => 'amneziawg',
    ];

    return $services;
}

function amneziawg_syslog()
{
    return [
        'amneziawg' => ['facility' => ['amneziawg']]
    ];
}

function amneziawg_interfaces()
{
    $interfaces = [];

    if (!amneziawg_enabled()) {
        return $interfaces;
    }

    $interfaces['amneziawg'] = [
        'descr' => gettext('AmneziaWG (Group)'),
        'if' => 'amneziawg',
        'virtual' => true,
        'enable' => true,
        'type' => 'group',
        'networks' => [],
    ];

    return $interfaces;
}

function amneziawg_xmlrpc_sync()
{
    $result = [];

    $result['id'] = 'amneziawg';
    $result['section'] = 'OPNsense.amneziawg';
    $result['description'] = gettext('AmneziaWG');
    $result['services'] = ['amneziawg'];

    return [$result];
}

function amneziawg_devices()
{
    $names = [];
    
    if (amneziawg_enabled()) {
        $instances = new \OPNsense\AmneziaWG\Instance();
        
        foreach ($instances->instances->instance->iterateItems() as $uuid => $instance) {
            if ((string)$instance->enabled == '1') {
                $instance_number = (string)$instance->instance;
                $interface = "awg{$instance_number}";
                $name = (string)$instance->name ?: "Instance {$instance_number}";
        
        $names[$interface] = [
                    'descr' => sprintf('%s (AmneziaWG)', $name),
            'ifdescr' => 'AmneziaWG',
            'name' => $interface
        ];
            }
        }
    }
    
    return [[
        'function' => 'amneziawg_prepare',
        'configurable' => false,
        'pattern' => '^awg',
        'type' => 'amneziawg',
        'volatile' => true,
        'names' => $names,
    ]];
}

function amneziawg_prepare($device)
{
    if (!amneziawg_enabled()) {
        return null;
    }

    $instances = new \OPNsense\AmneziaWG\Instance();
    
    foreach ($instances->instances->instance->iterateItems() as $uuid => $instance) {
        if ((string)$instance->enabled == '1') {
            $instance_number = (string)$instance->instance;
            $interface = "awg{$instance_number}";
            
            if ($device == $interface) {
                return $device;
            }
        }
    }

    return null;
}

function amneziawg_configure()
{
    return [
        'newwanip' => ['amneziawg_sync'],
        'vpn' => ['amneziawg_configure_do'],
    ];
}

function amneziawg_configure_do($verbose = false)
{
    if (!amneziawg_enabled()) {
        return;
    }

    service_log('Configuring AmneziaWG VPN...', $verbose);

    configd_run('amneziawg reconfigure');

    service_log("done.\n", $verbose);
}

function amneziawg_sync($verbose = false)
{
    if (!amneziawg_enabled()) {
        return;
    }

    service_log('Synchronizing AmneziaWG VPN...', $verbose);

    // Sync configuration if needed
    configd_run('amneziawg reconfigure');

    service_log("done.\n", $verbose);
}
