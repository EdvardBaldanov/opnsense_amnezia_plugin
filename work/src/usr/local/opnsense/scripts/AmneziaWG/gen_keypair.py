#!/usr/local/bin/python3
"""
AmneziaWG Key Pair Generator
Copyright (C) 2024 AmneziaWG Plugin
All rights reserved.
"""

import subprocess
import sys
import json

def generate_keypair():
    """Generate AmneziaWG keypair using awg genkey and awg pubkey"""
    try:
        # Generate private key using awg genkey
        result = subprocess.run(
            ['/usr/local/bin/awg', 'genkey'],
            capture_output=True,
            text=True,
            timeout=30
        )
        
        if result.returncode != 0:
            print(json.dumps({'status': 'failed', 'error': result.stderr}))
            return False
        
        # Get the private key from output
        private_key = result.stdout.strip()
        if not private_key:
            print(json.dumps({'status': 'failed', 'error': 'Failed to generate private key'}))
            return False
        
        # Generate public key from private key using awg pubkey
        result = subprocess.run(
            ['/usr/local/bin/awg', 'pubkey'],
            input=private_key,
            capture_output=True,
            text=True,
            timeout=30
        )
        
        if result.returncode != 0:
            print(json.dumps({'status': 'failed', 'error': result.stderr}))
            return False
        
        # Get the public key from output
        public_key = result.stdout.strip()
        if not public_key:
            print(json.dumps({'status': 'failed', 'error': 'Failed to generate public key'}))
            return False
        
        # Return the keypair
        print(json.dumps({
            'status': 'ok',
            'privkey': private_key,
            'pubkey': public_key
        }))
        return True
            
    except subprocess.TimeoutExpired:
        print(json.dumps({'status': 'failed', 'error': 'Timeout generating keypair'}))
        return False
    except FileNotFoundError:
        print(json.dumps({'status': 'failed', 'error': 'awg command not found'}))
        return False
    except Exception as e:
        print(json.dumps({'status': 'failed', 'error': str(e)}))
        return False

if __name__ == '__main__':
    success = generate_keypair()
    sys.exit(0 if success else 1) 