#!/bin/sh

# Build script for AmneziaWG plugin dependencies
# This script installs amnezia-tools and amnezia-kmod from ports

set -e

echo "Installing AmneziaWG dependencies..."

# Check prerequisites
echo "Checking prerequisites..."

# Check if opnsense-code is available
if ! command -v opnsense-code >/dev/null 2>&1; then
    echo "Error: opnsense-code not found"
    echo "Please run: opnsense-code tools ports src"
    exit 1
fi

# Check if ports tree is available
if [ ! -d "/usr/ports" ]; then
    echo "Warning: FreeBSD ports tree not found"
    echo "Please install it with: portsnap fetch extract"
    echo "Or ensure it's available in your build environment"
fi

# Install amnezia-tools from ports
echo "Installing amnezia-tools from ports..."
if [ -d "/usr/ports/net/amnezia-tools" ]; then
    cd /usr/ports/net/amnezia-tools
    make install
    echo "amnezia-tools installed successfully"
else
    echo "Warning: /usr/ports/net/amnezia-tools not found"
    echo "Trying to install via pkg..."
    pkg install amnezia-tools || echo "amnezia-tools not available in package repository"
fi

# Install amnezia-kmod from ports
echo "Installing amnezia-kmod from ports..."
if [ -d "/usr/ports/net/amnezia-kmod" ]; then
    cd /usr/ports/net/amnezia-kmod
    make install
    echo "amnezia-kmod installed successfully"
else
    echo "Warning: /usr/ports/net/amnezia-kmod not found"
    echo "Trying to install via pkg..."
    pkg install amnezia-kmod || echo "amnezia-kmod not available in package repository"
fi

echo "Dependencies installation completed!"
echo "You can now build the plugin with: make package" 