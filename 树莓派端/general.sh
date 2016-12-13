#!/bin/bash

#----light
python3 ./light/switchOperation.py & 

#-----webcam
sh ./cam/stream.sh && ./cam/natapp &

#----temp
python3 ./temp/tempV1.py &





