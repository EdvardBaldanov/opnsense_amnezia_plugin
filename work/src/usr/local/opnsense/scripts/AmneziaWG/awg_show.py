#!/usr/local/bin/python3

"""
    Copyright (c) 2024 AmneziaWG Plugin
    All rights reserved.

    Redistribution and use in source and binary forms, with or without
    modification, are permitted provided that the following conditions are met:

    1. Redistributions of source code must retain the above copyright notice,
     this list of conditions and the following disclaimer.

    2. Redistributions in binary form must reproduce the above copyright
     notice, this list of conditions and the following disclaimer in the
     documentation and/or other materials provided with the distribution.

    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
    POSSIBILITY OF SUCH DAMAGE.
"""
import subprocess
import ujson
import json
import sys
import os

# Get interface status
interfaces = {}
try:
    for line in subprocess.run(['/sbin/ifconfig'], capture_output=True, text=True, timeout=10).stdout.split("\n"):
        if not line.startswith('\t') and line.find('<') > -1:
            ifname = line.split(':')[0]
            interfaces[ifname] = 'up' if 'UP' in line.split('<')[1].split('>')[0].split(',') else 'down'
except:
    pass

# Get AmneziaWG status using awg show
result = {'records': []}

try:
    sp = subprocess.run(['/usr/local/bin/awg', 'show', 'all', 'dump'], capture_output=True, text=True, timeout=30)
    
    if sp.returncode == 0 and sp.stdout.strip():
        for line in sp.stdout.split("\n"):
            if not line.strip():
                continue
                
            record = {}
            parts = line.split("\t")
            
            # parse fields as explained in 'man awg'
            if len(parts) >= 1:
                record['if'] = parts[0]
                
            if len(parts) == 5:
                # interface record
                record['type'] = 'interface'
                record['public-key'] = parts[2] if len(parts) > 2 else ''
                record['listen-port'] = parts[3] if len(parts) > 3 else ''
                record['fwmark'] = parts[4] if len(parts) > 4 else ''
                # convenience, copy listen-port to endpoint
                record['endpoint'] = parts[3] if len(parts) > 3 else ''
                record['status'] = interfaces.get(record['if'], 'down')
                record['name'] = record['if']  # Use interface name as instance name
                record['latest-handshake'] = 0
                record['transfer-rx'] = 0
                record['transfer-tx'] = 0
                
            elif len(parts) == 9:
                # peer record
                record['type'] = 'peer'
                record['public-key'] = parts[1] if len(parts) > 1 else ''
                record['endpoint'] = parts[3] if len(parts) > 3 else ''
                record['allowed-ips'] = parts[4] if len(parts) > 4 else ''
                record['latest-handshake'] = int(parts[5]) if len(parts) > 5 and parts[5].isdigit() else 0
                record['transfer-rx'] = int(parts[6]) if len(parts) > 6 and parts[6].isdigit() else 0
                record['transfer-tx'] = int(parts[7]) if len(parts) > 7 and parts[7].isdigit() else 0
                record['persistent-keepalive'] = parts[8] if len(parts) > 8 else ''
                record['name'] = record['if']  # Use interface name
                
            else:
                continue
                
            result['records'].append(record)
    
    result['status'] = 'ok'
    
except subprocess.TimeoutExpired:
    result['status'] = 'failed'
    result['error'] = 'Timeout getting awg status'
except Exception as e:
    result['status'] = 'failed'
    result['error'] = str(e)

print(ujson.dumps(result)) 