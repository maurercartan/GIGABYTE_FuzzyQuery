__author__ = 'Barry.Chen'

from gensim import similarities
import jieba
import os
import configparser
import pickle
import operator
import pymysql
import pymysql.cursors
import time

def load():
    f = open(os.getcwd()+'\\LDA\\lda.pkl', 'rb')
    lda = pickle.load(f)
    g = open(os.getcwd()+'\\LDA\\name.pkl', 'rb')
    courses_id = pickle.load(g)
    h = open(os.getcwd()+'\\LDA\\corpus.pkl', 'rb')
    corpus = pickle.load(h)
    index = similarities.MatrixSimilarity(lda[corpus])
    dictionary = lda.id2word
    return [lda,courses_id,index,dictionary]

def Search(myLoad,mySearch,eps):
    lda = myLoad[0]
    courses_id = myLoad[1]
    index = myLoad[2]
    dictionary = myLoad[3]

    #userDefine = os.getcwd()+"\\LDA\\user_define.txt"
    #userDefineList = open(userDefine,'r',encoding='utf-8').readlines()[0].split(',')
    # 加入使用者自定義詞彙
    #for item in userDefineList:
    #    jieba.add_word(item,999999)

    # 查詢向量化
    ml_course = jieba.cut(mySearch)
    ml_bow = dictionary.doc2bow(ml_course)
    ml_lda = lda[ml_bow]

    topicDict = {}
    for test in ml_lda:
        new_topic = lda.print_topic(test[0],10).split('+')
        for item in new_topic:
            myList = item.strip(' ').split('*')
            topicDict[myList[1].replace('"','')]=float(myList[0])

    topicList = sorted(topicDict.keys(),key=lambda item: -topicDict[item])

    # 與(文檔ml_course)相似的(前10個文檔)
    sims = index[ml_lda]
    sort_sims = sorted(enumerate(sims), key=lambda item: -item[1])
    myDict = {}
    for item in sort_sims:
        if item[1]>=eps:
            myDict[courses_id[item[0]]] = item[1]
    myDict = sorted(myDict.items(), key=operator.itemgetter(1),reverse=True)
    return [myDict,topicList]

# (搜尋文字)+(擷取字串)
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

# 資料庫搜尋
def dbSearch_id2data(connStr,dbTable,myID):
    # conn = connStr.cursor()
    mySql = 'select text from '+dbTable+' where id="'+str(myID)+'";'
    conn.execute(mySql)
    myText = ''
    for row in conn:
        myText = row['text']
    # conn.close()
    return myText

# 資料筆數
def dbSearch_number(conn,dbTable):
    # conn = connStr.cursor()
    mySql = 'select count(*) from '+dbTable+';'
    conn.execute(mySql)

    num = ''
    for row in conn:
        num = row['count(*)']
    # conn.close()
    return num

# 刪除資料庫
def deleteIP(dbHost,dbUser,dbPassword,dbDatabase,dbTable_2,myLocalIP):
    connStr = pymysql.connect(host=dbHost,
                          user=dbUser,
                          password=dbPassword,
                          db=dbDatabase,
                          charset='utf8mb4',
                          cursorclass=pymysql.cursors.DictCursor)
    conn = connStr.cursor()
    mySql = 'delete from '+dbTable_2+' where ip=\"'+myLocalIP+'\";'
    conn.execute(mySql)
    connStr.commit()
    conn.close()
    connStr.close()

def delete_by_ip(conn,dbTable_3,myLocalIP):
    mySql = 'delete from '+dbTable_3+' where ip=\"'+myLocalIP+'\";'
    conn.execute(mySql)

if __name__ == "__main__":
    os.chdir("D:/Barry/Software/xampp/htdocs/KM")
    config_path = os.getcwd()+'\\config.cfg'
    config = configparser.ConfigParser()
    config.read(config_path)
    dbHost = config.get('ARGUMENT', 'HOST').strip('"')
    dbUser = config.get('ARGUMENT', 'USER').strip('"')
    dbPassword = config.get('ARGUMENT', 'PASSWORD').strip('"')
    dbDatabase = config.get('ARGUMENT', 'DATABASE_NAME').strip('"')
    dbTable = config.get('ARGUMENT', 'DATABASE_TABLE').strip('"')		# info
    dbTable_2 = config.get('ARGUMENT', 'DATABASE_TABLE_2').strip('"')	# result
    dbTable_3 = config.get('ARGUMENT','DATABASE_TABLE_3').strip('"')	# search
    numTopic = config.get('ARGUMENT','NUM_TOPIC').strip('"')

    connStr = pymysql.connect(host=dbHost,
                              user=dbUser,
                              password=dbPassword,
                              db=dbDatabase,
                              charset='utf8mb4',
                              cursorclass=pymysql.cursors.DictCursor)
    myLoad = load()
    count = 0
    conn = connStr.cursor()
    while 1:
        print("count = ",count)
        connStr.commit()
        if(dbSearch_number(conn,dbTable_3)!=0):
            print(dbSearch_number(conn,dbTable_3))
            userList = []

            mySql = 'select ip,similar,search_word from '+dbTable_3+';'
            conn.execute(mySql)
            for row in conn:
                print(row['search_word'])
                userList.append([row['ip'],row['similar'],row['search_word']])

            for item in userList:
                myLocalIP = item[0]
                mySimilar = float(item[1])
                mySearchText = item[2]

                deleteIP(dbHost,dbUser,dbPassword,dbDatabase,dbTable_2,myLocalIP)

                [myDict,topicList] = Search(myLoad,mySearchText,mySimilar)

                topicGroup = ""
                for item in topicList:
                    topicGroup += item+","
                topicGroup = topicGroup.strip(',')

                for item in range(len(myDict)):
                    fileID = myDict[item][0]
                    similarValue = myDict[item][1]
                    #myText = dbSearch_id2data(conn,dbTable,item)
                    myText = dbSearch_id2data(conn,dbTable,fileID)
                    mySubText = ""
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
                    myText=myText.strip('"')
                    while myText.__contains__('"'):
                        myText = myText.replace('"','')

                    for item_2 in topicList:
                        mySubText = subText(myText,item_2,stringLength=100)
                        if mySubText!="":
                            break
                    if mySubText!="":
                        mySubText = mySubText.replace('\n','').replace('\r','')
                    else:
                        newText = myText.replace('\n','').replace('\r','')
                        if len(newText)<100:
                            mySubText = newText
                        else:
                            mySubText = newText[0:100]
                    
                    # 移除多餘符號
                    mySubText=mySubText.strip('"')
                    while mySubText.__contains__('"'):
                        mySubText = mySubText.replace('"','')

                    sql = "INSERT INTO result (id,ip,fileid,similar,topic_group,subtext) "+\
                          "VALUES (NULL,\""+myLocalIP+"\",\""+fileID+"\","+str(similarValue)+",\""+topicGroup+"\",\""+mySubText+"\")"
                    try:
                        conn.execute(sql)
                        connStr.commit()
                    except:
                        pass
                    
                delete_by_ip(conn,dbTable_3,myLocalIP)
                connStr.commit()
        count += 1
        time.sleep(1)
    connStr.close()
