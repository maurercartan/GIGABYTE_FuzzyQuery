import os
import configparser
import pickle
import operator
import pymysql
import pymysql.cursors
import time

def dbSearch_id2data(connStr,dbTable,myID):
    # conn = connStr.cursor()
    mySql = 'select text from '+dbTable+' where id="'+str(myID)+'";'
    conn.execute(mySql)
    myText = ''
    for row in conn:
        myText = row['text']
    # conn.close()
    return myText
	
def subText(text,search,stringLength=20):
    searchPosition = text.find(search)
    if searchPosition>=0:
        if stringLength<len(text):
            start = searchPosition-stringLength/2
            end = searchPosition+stringLength/2+stringLength%2
            if start<0:
                end = end -start
                start = 0
            if end >len(text):
                start = start - (end-len(text))
                end = len(text)
            if start<0:
                start = 0
                end = len(text)
            start = int(start)
            end = int(end)
            return text[start:end]
        else:
            return text
    return ''
	
connStr = pymysql.connect(host='localhost',
                              user='barry',
                              password='barry',
                              db='fileinfo',
                              charset='utf8mb4',
                              cursorclass=pymysql.cursors.DictCursor)
conn = connStr.cursor()

	
myText = dbSearch_id2data(conn,"info","1440")
# 移除多餘符號
while myText.__contains__('\n'):
	myText = myText.replace('\n','')
while myText.__contains__('\r'):
	myText = myText.replace('\r','')
while myText.__contains__('	'):
	myText = myText.replace('	',' ')
while myText.__contains__('　'):
	myText = myText.replace('　',' ')
while myText.__contains__('  '):
	myText = myText.replace('  ',' ')
while myText.__contains__('..'):
	myText = myText.replace('..','.')
while myText.__contains__('_'):
	myText = myText.replace('_','')
#myText=myText.strip('"')
while myText.__contains__('"'):
	myText = myText.replace('"','')
mySubText = ""
mySubText = subText(myText,"",stringLength=100)
print(mySubText)