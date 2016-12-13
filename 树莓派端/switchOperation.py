#!/usr/bin/env python  
# -*- coding: utf-8 -*-  
#从服务器端获取灯光状态值，进行操作
import http.client
import urllib.parse
import json

import RPi.GPIO as GPIO
import time

GPIO.setwarnings(False)

#树莓派引脚12
PIN_NO=12
# 使用树莓派板引脚号码
GPIO.setmode(GPIO.BOARD)
#设置GPIO引脚作为输出通道（LED输出）
GPIO.setup(PIN_NO, GPIO.OUT)


server = "utfire.com.cn"
def switch():
    data = urllib.parse.urlencode({'token':"wxData"})   
    headers = {"Content-type": "application/x-www-form-urlencoded",
               "Accept": "text/plain"}
    
    conn = http.client.HTTPConnection(server)
    conn.request('POST', '/WXapp/upload/getSwitch.php', data, headers)
    httpres = conn.getresponse()
    print (httpres.status)
    print (httpres.reason)
  
    c = httpres.read(1)
    c = str(c,'utf-8')
    print (c)
    if c == '<':
      state = httpres.read(1)
      state = str(state,'utf-8')
      print (state)
      if state == '1':
        print ("opening light")
        GPIO.output(PIN_NO,GPIO.HIGH) #点亮LED灯
      else:
        print ("closing light")
        GPIO.output(PIN_NO,GPIO.LOW)  #熄灭LED灯
    conn.close()
           
              
if __name__ == '__main__':  
	try :
		while 1:
			switch() 
			time.sleep(1)
	except KeyboardInterrupt:
		GPIO.cleanup()

   
