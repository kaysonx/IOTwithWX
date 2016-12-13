#!/usr/bin/env python  
# -*- coding: utf-8 -*-  
import http.client
import urllib.parse
import json

import RPi.GPIO as GPIO
import time


server = "utfire.com.cn"
lastTemperature = 0
lastHumidity = 0

#----------------------------------以下为上传数据到服务器端代码-----------------------------------
def sendhttp(val):
    data = urllib.parse.urlencode({'token':"wxData",'data': val})   
    headers = {"Content-type": "application/x-www-form-urlencoded",
               "Accept": "text/plain"}
    
    conn = http.client.HTTPConnection(server)
    conn.request('POST', '/WXapp/upload/uploadTemp.php', data, headers)
    httpres = conn.getresponse()
    print (httpres.status)
    print (httpres.reason)
    print (httpres.read())
    conn.close()
           
#----------------------------------以下为获取温度、湿度代码---------------------------------------
def getData():
	while 1:
		channel = 23 #定义GPIO4 BCM方式
		data = []
		j = 0

		GPIO.setmode(GPIO.BCM)
		time.sleep(1)
		GPIO.setup(channel, GPIO.OUT)
		GPIO.output(channel, GPIO.LOW)
		time.sleep(0.05)
		GPIO.output(channel, GPIO.HIGH)
		i = 1
		i = 1
		GPIO.setup(channel, GPIO.IN)

		while GPIO.input(channel) == GPIO.LOW:
		  continue
		while GPIO.input(channel) == GPIO.HIGH:
		  continue

		while j < 40:
		  k = 0
		  while GPIO.input(channel) == GPIO.LOW:
		    continue
		  while GPIO.input(channel) == GPIO.HIGH:
		    k += 1
		    if k > 100:
		      break
		  if k < 8:
		    data.append(0)
		  else:
		    data.append(1)

		  j += 1

		print ("sensor is working.")
		print (data)

		humidity_bit = data[0:8]
		humidity_point_bit = data[8:16]
		temperature_bit = data[16:24]
		temperature_point_bit = data[24:32]
		check_bit = data[32:40]

		humidity = 0
		humidity_point = 0
		temperature = 0
		temperature_point = 0
		check = 0

		global lastTemperature
		global lastHumidity

		for i in range(8):
		  humidity += humidity_bit[i] * 2 ** (7 - i)
		  humidity_point += humidity_point_bit[i] * 2 ** (7 - i)
		  temperature += temperature_bit[i] * 2 ** (7 - i)
		  temperature_point += temperature_point_bit[i] * 2 ** (7 - i)
		  check += check_bit[i] * 2 ** (7 - i)

		tmp = humidity + humidity_point + temperature + temperature_point

		if check == tmp:
		  print ("temperature :", temperature, "*C, humidity :", humidity, "%")
		  if lastTemperature != temperature and lastHumidity != humidity:
		    lastTemperature = temperature
		    lastHumidity = humidity
		    dictData = {"temperature":temperature,"humidity":humidity,"place":"室内"}    
		    jsonData = json.JSONEncoder().encode(dictData)
		    sendhttp(jsonData) 
		  break
		else:
		  time.sleep(0.01)
	  #print "wrong"
	  #print "temperature :", temperature, "*C, humidity :", humidity, "% check
	  #:", check, ", tmp :", tmp
		GPIO.cleanup()
if __name__ == '__main__': 
	while 1:
		getData()
