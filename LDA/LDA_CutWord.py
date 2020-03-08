__author__ = 'user'

from gensim import corpora, models
import jieba
import os
import configparser
import pickle
import pymysql
import pymysql.cursors

def getCorpus(dbHost,dbUser,dbPassword,dbDatabase,dbTable):
    # 匯入檔案內容
    config = configparser.ConfigParser()
    myINI = os.getcwd()+'\\LDA\\dict.ini'
    config.read(myINI,encoding='utf-8')
    stopword = os.getcwd()+"\\LDA\\stop_words.txt"
    lowFreq = os.getcwd()+"\\LDA\\lowFrequency.txt"
    userDefine = os.getcwd()+"\\LDA\\user_define.txt"

    courses_name = []
    texts_stemmed = []
    stopwordList = open(stopword,'r',encoding='utf-8').readlines()[0].split(',')
    lowFreqList = open(lowFreq,'r',encoding='utf-8').readlines()[0].split(',')
    userDefineList = open(userDefine,'r',encoding='utf-8').readlines()[0].split(',')
    symbol = ['+','-','*','/','\\','=','_','(',')','&','^','.','%','$','#','@','!','`','~','＋','－','＊','／','＼','＝','＿','（','）','＆','︿','．','，','％','＄','＃','＠','！','‵','～','\'','"','’',',',' ','　','’','＂','\r\n']

    # 加入使用者自定義詞彙
    for item in userDefineList:
        jieba.add_word(item,999999)
    connStr = pymysql.connect(host=dbHost,
                          user=dbUser,
                          password=dbPassword,
                          db=dbDatabase,
                          charset='utf8mb4',
                          cursorclass=pymysql.cursors.DictCursor)
    conn = connStr.cursor()
    sql = 'select id,name,text,dir_path from '+dbTable+' where isNew=\'1\';'
    conn.execute(sql)
    for row in conn:
        courses_name.append(row['dir_path']+'/'+row['name'])
        # 讀取內容
        document = row['text'].lower()

        # 結巴分詞(含stopword與標點符號的過濾)
        myJ = jieba.cut(document)
        new_doc = {}    # document的(詞彙list)
        print("id = ",row['id'])
        if row['name']!="":
            if "id_"+str(row['id']) not in config.sections():
                config.add_section("id_"+str(row['id']))
            else:
                for key in config.options("id_"+str(row['id'])):
                    config.set("id_"+str(row['id']),key,'0')
            for word in myJ:
                word = noSymbo(word,symbol)
                if not word in stopwordList \
                   and not word.isnumeric()\
                   and len(word)>1:
                    if word in new_doc.keys():
                        new_doc[word] += 1
                    else:
                        new_doc[word] = 1
            for word in new_doc.keys():
                config.set("id_"+str(row['id']),word,str(new_doc[word]))
        config.write(open(myINI, 'w',encoding='utf-8'))
        texts_stemmed.append(new_doc)
    conn.close()

    conn_2 = connStr.cursor()
    sql_2 = "UPDATE info SET isNew = '0' WHERE isNew = '1'"
    conn_2.execute(sql_2)
    connStr.commit()
    conn_2.close()

    connStr.close()

    # 合併字典
    from collections import Counter
    all_stems = {}
    for item in texts_stemmed:
        all_stems = dict(Counter(all_stems)+Counter(item))

    # 取出(低頻詞彙,詞頻=1)
    for item in all_stems.keys():
        if all_stems[item]<2 and item not in lowFreqList:
            lowFreqList.append(item)
            with open(lowFreq,'a',encoding='utf-8') as f:
                f.write(item+",")

def noSymbo(word,symbol):
    for item in symbol:
        while(str(word).__contains__(item)): word = word.replace(item,"")
    return word

def main():
    config_path = os.getcwd()+'\\config.cfg'
    config = configparser.ConfigParser()
    config.read(config_path)
    dbHost = config.get('ARGUMENT', 'HOST').strip('"')
    dbUser = config.get('ARGUMENT', 'USER').strip('"')
    dbPassword = config.get('ARGUMENT', 'PASSWORD').strip('"')
    dbDatabase = config.get('ARGUMENT', 'DATABASE_NAME').strip('"')
    dbTable = config.get('ARGUMENT', 'DATABASE_TABLE').strip('"')
    numTopic = config.get('ARGUMENT','NUM_TOPIC').strip('"')

    getCorpus(dbHost,dbUser,dbPassword,dbDatabase,dbTable)


if __name__ == "__main__":
    main()