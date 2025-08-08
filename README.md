# AmneziaWG Plugin for OPNsense

## Overview

AmneziaWG is a VPN plugin for OPNsense that provides secure tunneling capabilities with obfuscation features. This plugin has been redesigned to support multiple instances, similar to the WireGuard plugin architecture.

## Features

### Multi-Instance Support
- Create multiple AmneziaWG connections to different servers
- Each instance has its own configuration and interface
- Automatic instance numbering (0, 1, 2, ...) with corresponding interface names (awg0, awg1, awg2, ...)
- One peer per instance (as per AmneziaWG requirements)

### Web Interface
- Grid-based interface similar to WireGuard
- Inline editing capabilities
- Built-in key pair generation
- Real-time status monitoring
- Import functionality for AmneziaWG configuration files

### Configuration Management
- Automatic configuration file generation
- Service management (start, stop, restart, reconfigure)
- Status monitoring and statistics
- Automatic configuration file cleanup on instance deletion

## Architecture

### Models
- **Instance.xml** - Instance configuration model
- **General.xml** - General settings model
- **InstanceField.php** - Field class for automatic interface naming

### Controllers
- **InstanceController.php** - CRUD operations for instances
- **GeneralController.php** - General settings management
- **ServiceController.php** - Service control operations
- **ImportController.php** - Configuration import functionality

### Views
- **general.volt** - Main interface with instance grid and general settings
- **diagnostics.volt** - Status monitoring interface
- **import.volt** - Configuration import interface
- **dialogEditInstance.xml** - Instance editing form

### Scripts
- **amneziawg-service-control.php** - Main service control script
- **gen_keypair.py** - Key pair generation using `awg` command
- **awg_show.py** - Status monitoring
- **migrate-to-multi-instance.php** - Migration from old architecture

## Configuration Structure

```
OPNsense.amneziawg
├── general.general.enabled
└── instance.instances
    ├── [uuid1]
    │   ├── enabled
    │   ├── name
    │   ├── instance (auto-numbered)
    │   ├── description
    │   ├── private_key
    │   ├── listen_port
    │   ├── address
    │   ├── dns
    │   ├── table
    │   ├── postup
    │   ├── preup
    │   ├── mtu
    │   ├── jc, jmin, jmax, s1, s2, h1, h2, h3, h4
    │   ├── userland
    │   ├── peer_public_key
    │   ├── peer_preshared_key
    │   ├── peer_allowed_ips
    │   ├── peer_endpoint
    │   ├── peer_persistent_keepalive
    │   └── peer_routes
    └── [uuid2]
        └── ...
```

## Installation

1. Install the plugin via OPNsense package manager
2. Access the interface at `/ui/amneziawg/`
3. Enable AmneziaWG in general settings
4. Create and configure instances

## Usage

### Creating Instances
1. Navigate to VPN → AmneziaWG → Instances
2. Click "Add" to create a new instance
3. Configure the instance settings:
   - **Name**: Instance name (e.g., "awg0")
   - **Description**: Optional description
   - **Private Key**: Your private key (use "Generate" button)
   - **Listen Port**: Local listening port
   - **Address**: Interface address (e.g., "10.8.1.4/32")
   - **DNS**: DNS servers for the tunnel
   - **Peer Public Key**: Server's public key
   - **Peer Endpoint**: Server endpoint (IP:port)
   - **Peer Allowed IPs**: Allowed IP ranges
   - **AmneziaWG Parameters**: Jc, Jmin, Jmax, S1, S2, H1, H2, H3, H4, UserLand

### Importing Configurations
1. Navigate to VPN → AmneziaWG → Import
2. Paste AmneziaWG configuration or select a .conf file
3. Click "Import"
4. Review and edit the parsed configuration
5. Save the instance

### Monitoring Status
1. Navigate to VPN → AmneziaWG → Status
2. View real-time status of all instances
3. Monitor transfer statistics and connection status

## Service Management

The plugin automatically loads the `if_amn` kernel module when needed.

### Manual Service Control
```bash
# Start all enabled instances
configctl amneziawg start

# Stop all instances
configctl amneziawg stop

# Restart all instances
configctl amneziawg restart

# Reconfigure specific instance
configctl amneziawg reconfigure <uuid>

# Show status
configctl amneziawg status

# Generate key pair
configctl amneziawg gen_keypair
```

### Configuration Files
- Instance configurations: `/usr/local/etc/amnezia/awgX.conf`
- Service script: `/usr/local/opnsense/scripts/AmneziaWG/amneziawg-service-control.php`

## Migration from Old Architecture

### Automatic Migration
```bash
# Backup current configuration
cp /conf/config.xml /conf/config.xml.backup

# Run migration script
/usr/local/opnsense/scripts/AmneziaWG/migrate-to-multi-instance.php migrate
```

### Manual Migration
1. Access the web interface
2. Create new instances and copy settings from old configuration
3. Enable the instances and AmneziaWG service

## Troubleshooting

### Common Issues
1. **Instance not starting**: Check if AmneziaWG is enabled globally
2. **Configuration file not found**: Verify instance is properly configured
3. **Import not working**: Check configuration format and required fields

### Logs
- Service logs: `/var/log/system.log` (filter by "AmneziaWG")
- Configuration validation: Check web interface for field validation errors

## Development

### Building the Plugin
```bash
cd plugins/security/amneziawg
make package
```

### File Structure
```
plugins/security/amneziawg/
├── src/
│   ├── opnsense/
│   │   ├── mvc/app/
│   │   │   ├── controllers/OPNsense/AmneziaWG/
│   │   │   ├── models/OPNsense/AmneziaWG/
│   │   │   └── views/OPNsense/AmneziaWG/
│   │   ├── scripts/AmneziaWG/
│   │   ├── service/conf/actions.d/
│   │   └── etc/inc/plugins.inc.d/
│   └── ...
├── Makefile
├── README.md
└── pkg-descr
```

## License

This plugin is licensed under the same terms as OPNsense. 