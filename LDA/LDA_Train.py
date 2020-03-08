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
        # print("id = ",row['id'])
        if row['name']!="":
            if "id_"+str(row['id']) not in config.sections(): config.add_section("id_"+str(row['id']))
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
        if all_stems[item]==1:
            with open(lowFreq,'a',encoding='utf-8') as f:
                f.write(item+",")

def Training(numTopic=100):
    import time
    start = time.time()
    print("***預處理***")
    lowFreq = os.getcwd()+"\\LDA\\lowFrequency.txt"

    stems_once = []
    f = open(lowFreq,'r',encoding='utf-8')
    for item in f.readlines()[0].split(','):
        try:
            # print(item)
            stems_once.append(item)
        except:
            pass
    f.close()

    # 去除低頻詞彙(詞頻=1時,則移除)
    config = configparser.ConfigParser()
    myINI = os.getcwd()+'\\LDA\\dict.ini'
    config.read(myINI,encoding='utf-8')

    texts = []
    courses_id = []
    for section in config.sections():
        myID = section.replace("id_","")
        print(myID)
        courses_id.append(myID)
        myList = []
        for key in config.options(section):
            if key not in stems_once:
                for i in range(int(config.get(section,key))):
                    myList.append(key)
        texts.append(myList)
    print("***花費秒數 = ",round(start-time.time(),5),"***")

    print("***訓練LDA模型***")
    start = time.time()
    # 訓練LDA模型(topic=100)
    lda_path = os.getcwd()+'\\LDA\\lda.pkl'
    lda_name = os.getcwd()+'\\LDA\\name.pkl'
    lda_corpus = os.getcwd()+'\\LDA\\corpus.pkl'

    print("***建立詞袋***")
    # 建立詞袋
    dictionary = corpora.Dictionary(texts)
    print("***花費秒數 = ",round(time.time()-start,5),"***")

    print("***建立文檔矩陣***")
    # 建立文檔矩陣
    corpus = [dictionary.doc2bow(text) for text in texts]
    print("***花費秒數 = ",round(time.time()-start,5),"***")

    print("***計算TF-IDF***")
    # 計算TF-IDF
    tfidf = models.TfidfModel(corpus)
    corpus_tfidf = tfidf[corpus]
    lda = models.LdaModel(corpus_tfidf,
                          id2word=dictionary,
                          num_topics=numTopic,
                          gamma_threshold=0.0001,
                          iterations=1000,
                          passes=1)
    print("***花費秒數 = ",round(time.time()-start,5),"***")

    with open(lda_path, 'wb') as f:
        pickle.dump(lda, f, pickle.HIGHEST_PROTOCOL)
    with open(lda_name, 'wb') as f:
        pickle.dump(courses_id, f, pickle.HIGHEST_PROTOCOL)
    with open(lda_corpus, 'wb') as f:
        pickle.dump(corpus, f, pickle.HIGHEST_PROTOCOL)
    print("***訓練完成***")

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
    Training(numTopic)


if __name__ == "__main__":
    main()