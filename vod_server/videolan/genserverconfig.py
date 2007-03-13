#!/usr/bin/python
# -*- coding: utf-8 -*-

import glob
import os
import sys


if len(sys.argv)>1:
    os.chdir(sys.argv[1])

input=["*.avi","*.wmv","*.mpg","*.mov","*.mp4","*.ogg","*.ogm"]

input_files=[]

for i in input:
    input_files=input_files + glob.glob(i)

#print input_files


f=file("vlanconfig.cfg","w")

for i in input_files:
    o=os.getcwd() + "/" + i
    f.write("new %s vod enabled\n" %i)
    f.write("setup %s input \"%s\"\n" %(i,o))

f.close()
