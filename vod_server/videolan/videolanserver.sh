#!/bin/bash

# Configuración

# Ruta ABSOLUTA a donde estan guardados los vídeos
ABS_VIDEO_PATH="/mnt/win_dat/storage/apf/videos";
# Binario de VideoLAN a usar ("/usr/bin/vlc")
#VLCBIN="/usr/bin/vlc";
VLCBIN="vlc"
PARAMS="-vvv --color -I telnet --vlm-conf vlanconfig.cfg --rtsp-host 0.0.0.0:5000 --telnet-host localhost --telnet-password admin"

./genserverconfig.py $ABS_VIDEO_PATH

cd $ABS_VIDEO_PATH
echo "Starting VideoLAN service..."
$VLCBIN $PARAMS
