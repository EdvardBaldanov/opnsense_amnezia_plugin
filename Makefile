PLUGIN_NAME=     amneziawg
PLUGIN_VERSION=        1.0
PLUGIN_COMMENT=        AmneziaWG VPN Plugin
PLUGIN_DEPENDS=        amnezia-kmod amnezia-tools
PLUGIN_MAINTAINER= antspopov@gmail.com

# Prerequisites for building dependencies:
# opnsense-code tools ports src
#
# Dependencies installation (from ports):
# amnezia-tools: cd /usr/ports/net/amnezia-tools && make install
# amnezia-kmod: cd /usr/ports/net/amnezia-kmod && make install
#   or: pkg install amnezia-tools amnezia-kmod (if available in package repository)

.include "../../Mk/plugins.mk"



