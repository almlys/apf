#!/bin/bash

input=$1
output=$2

#ss - start time

#sws quality 2 bilineal 

#default bitrate 687

mencoder -sws 2 -ovc xvid -xvidencopts me_quality=6:bitrate=687 $input -o $output -oac mp3lame -lameopts preset=standard

