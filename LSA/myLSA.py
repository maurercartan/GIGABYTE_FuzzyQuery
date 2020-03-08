__author__ = 'user'

from gensim import corpora, models, similarities
import jieba
import os
import configparser
import pickle
import operator
import pymysql
import pymysql.cursors
from tkinter import messagebox

class myNewClass(models.LsiModel):
    def __init__(self,corpus,texts,courses_name,dictionary,numTopic):
        super().__init__(models.TfidfModel(corpus)[corpus],id2word=dictionary, num_topics=numTopic)
        self.corpus = corpus
        self.texts = texts
        self.courses_name = courses_name


def getCorpus(dbHost,dbUser,dbPassword,dbDatabase,dbTable):
    # 匯入檔案內容
    #dirname = os.getcwd()+"\\data"
    stopword = os.getcwd()+"\\LSA\\stop_words.txt"

    courses_name = []
    texts_stemmed = []

    connStr = pymysql.connect(host=dbHost,
                          user=dbUser,
                          password=dbPassword,
                          db=dbDatabase,
                          charset='utf8mb4',
                          cursorclass=pymysql.cursors.DictCursor)
    conn = connStr.cursor()
    sql = 'select name,text,dir_path from '+dbTable+' where isNew=\'1\';'
    conn.execute(sql)
    for row in conn:
        courses_name.append(row['dir_path']+'/'+row['name'])
        # 讀取內容
        document = row['text'].lower()

        # 結巴分詞(含stopword與標點符號的過濾)
        myJ = jieba.cut(document, cut_all=True)
        # texts_filtered.append([word.lower() for word in myJ])
        new_doc = []
        for word in myJ:
            if not word.__contains__('\n') and not word in open(stopword,'r',encoding='UTF-8').read():
                new_doc.append(word)

        # 詞根提取
        # st = LancasterStemmer()
        # texts_stemmed.append([st.stem(word) for word in new_doc])
        texts_stemmed.append([word for word in new_doc])
    conn.close()

    conn_2 = connStr.cursor()
    sql_2 = "UPDATE info SET isNew = '0' WHERE isNew = '1'"
    conn_2.execute(sql_2)
    connStr.commit()
    conn_2.close()

    connStr.close()

    # 去除低頻詞彙(詞頻=1時,則移除)
    all_stems = sum(texts_stemmed, [])
    stems_once = set(stem for stem in set(all_stems) if all_stems.count(stem) == 1)
    texts = [[stem for stem in text if stem not in stems_once] for text in texts_stemmed]

    return [courses_name,texts]

def Training(dbHost,dbUser,dbPassword,dbDatabase,dbTable,numTopic=100):
    # Step1:預處理
    [courses_name,texts] = getCorpus(dbHost,dbUser,dbPassword,dbDatabase,dbTable)

    # 訓練LSA模型(topic=100)
    lsi_path = os.getcwd()+'\\LSA\\lsi.pkl'
    courses_name_path = os.getcwd()+'\\LSA\\courses_name.pkl'

    #myNewClass(corpus,corpus_tfidf,courses_name,dictionary,numTopic)
    if(os.path.isfile(lsi_path)):
        f = open(lsi_path, 'rb')
        lsi = pickle.load(f)
        new_texts = lsi.texts+texts
        new_courses_name = lsi.courses_name+courses_name

        # 建立詞袋
        dictionary = corpora.Dictionary(new_texts)

        # 建立文檔矩陣
        corpus = [dictionary.doc2bow(text) for text in new_texts]

        # 計算TF-IDF
        #tfidf = models.TfidfModel(corpus)
        #corpus_tfidf = tfidf[corpus]

        new_lsi = myNewClass(corpus,new_texts,new_courses_name,dictionary,numTopic)
        with open(lsi_path, 'wb') as f:
            pickle.dump(new_lsi, f, pickle.HIGHEST_PROTOCOL)
    else:
        # 建立詞袋
        #logging.basicConfig(format='%(asctime)s : %(levelname)s : %(message)s', level=logging.INFO)
        dictionary = corpora.Dictionary(texts)

        # 建立文檔矩陣
        corpus = [dictionary.doc2bow(text) for text in texts]

        # 計算TF-IDF
        #tfidf = models.TfidfModel(corpus)
        #corpus_tfidf = tfidf[corpus]
        lsi = myNewClass(corpus,texts,courses_name,dictionary,numTopic)
        with open(lsi_path, 'wb') as f:
            pickle.dump(lsi, f, pickle.HIGHEST_PROTOCOL)
    #lsi = myNewClass(corpus,corpus_tfidf,courses_name,dictionary,numTopic)

def Search(mySearch,eps):
    f = open(os.getcwd()+'\\LSA\\lsi.pkl', 'rb')
    lsi = pickle.load(f)
    courses_name = lsi.courses_name
    index = similarities.MatrixSimilarity(lsi[lsi.corpus])
    dictionary = lsi.id2word
    # print(lsi.show_topics())
    # print(lsi.show_topic(10))
    # print(lsi.print_topic(10))
    myTopic = lsi.show_topics()

    # 查詢向量化
    ml_course = jieba.cut(mySearch, cut_all=True)
    ml_bow = dictionary.doc2bow(ml_course)
    ml_lsi = lsi[ml_bow]

    maxCount = ml_lsi[0][0]
    maxItem = ml_lsi[0][1]
    for item in ml_lsi:
        if maxItem<item[1]:
            maxCount = item[0]
            maxItem=item[1]

    # print("(index,機率)=",(maxCount,maxItem))
    # print("(index,可能topic)=",myTopic[maxCount])
    topicDict = {}
    for item in myTopic[maxCount][1].split('+'):
        myList = item.strip(' ').split('*')
        topicDict[myList[1].replace('"','')]=float(myList[0])

    # print(topicDict)
    topicList = sorted(topicDict.keys(),key=lambda item: -topicDict[item])
    # print(topicList)

    # 與(文檔ml_course)相似的(前10個文檔)
    sims = index[ml_lsi]
    sort_sims = sorted(enumerate(sims), key=lambda item: -item[1])
    myDict = {}
    nameList = []
    for item in sort_sims:
        if item[1]>=eps:
            myDict[courses_name[item[0]]] = item[1]
            myStr = courses_name[item[0]]
            nameList.append(myStr[myStr.rfind('/')+1:len(myStr)])
    myDict = sorted(myDict.items(), key=operator.itemgetter(1),reverse=True)
    return [myDict,topicList,nameList]

# BOM = Byte-Order-Mark = 位元組順序記號
# stripBOM的目的是:
# 將(帶BOM的Utf-8)改為(無BOM的utf-8)
# (帶BOM的Utf-8) = 內文開頭帶有 '\ufeff'
def stripBOM(fileName):
    try:
        with open(fileName, encoding='utf-8', mode='r') as f:
            reading = []
            for line in f:
                line=line.replace('\ufeff',"")
                reading.append(line)
                for rest in f:
                    reading.append(rest)
        with open(fileName, encoding='utf-8', mode='w') as f:
            for line in reading:
                f.write(line)
    except:
        #print("Could not open file:" + fileName + "\nMaybe it is not saved as UTF8")
        #exit()
        return

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
def dbSearch(dbHost,dbUser,dbPassword,dbDatabase,dbTable,myName):
    connStr = pymysql.connect(host=dbHost,
                          user=dbUser,
                          password=dbPassword,
                          db=dbDatabase,
                          charset='utf8mb4',
                          cursorclass=pymysql.cursors.DictCursor)
    conn = connStr.cursor()
    mySql = 'select name,text from '+dbTable+' where name="'+myName+'";'
    conn.execute(mySql)
    myText = ''
    for row in conn:
        myText = row['text']
    conn.close()
    connStr.close()
    return myText

if __name__ == "__main__":
    config_path = os.getcwd()+'\\config_search.cfg'
    config_path_2 = os.getcwd()+'\\config.cfg'

    read_key = os.getcwd()+'\\read_key.txt'
    read_value = os.getcwd()+'\\read_value.txt'
    read_topic = os.getcwd()+'\\read_topic.txt'

    stripBOM(config_path)

    config = configparser.ConfigParser()

    config.read(config_path,encoding='utf-8')
    myTrain = config.get('LSA', 'train').strip('"')
    mySearchText = config.get('LSA', 'searchText').strip('"')
    mySimilar = float(config.get('LSA', 'similar').strip('"'))

    config.read(config_path_2)
    dbHost = config.get('ARGUMENT', 'HOST').strip('"')
    dbUser = config.get('ARGUMENT', 'USER').strip('"')
    dbPassword = config.get('ARGUMENT', 'PASSWORD').strip('"')
    dbDatabase = config.get('ARGUMENT', 'DATABASE_NAME').strip('"')
    dbTable = config.get('ARGUMENT', 'DATABASE_TABLE').strip('"')
    numTopic = config.get('ARGUMENT', 'NUM_TOPIC').strip('"')

    myResult_1 = ""
    myResult_2 = ""
    myResult_3 = ""

    if myTrain=='1':
        Training(dbHost,dbUser,dbPassword,dbDatabase,dbTable,numTopic)
    else:
        [myDict,topicList,nameList] = Search(mySearchText,mySimilar)
        k = open('read_text.txt','w',encoding='utf-8')
        for item in range(len(myDict)):
            myResult_1 += myDict[item][0]+","
            myResult_2 += str(myDict[item][1])+","

            myName = myDict[item][0]
            myName = myName[myName.rfind('/')+1:len(myName)]
            myText = dbSearch(dbHost,dbUser,dbPassword,dbDatabase,dbTable,myName)
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
            mySubText = ''
            for item_2 in topicList:
                mySubText = subText(myText,item_2,stringLength=100)
                if mySubText!="":
                    break
            if mySubText!="":
                k.write(mySubText.replace('\n','').replace('\r','')+"\n")
            else:
                newText = myText.replace('\n','').replace('\r','')
                if len(newText)<100:
                    k.write(newText+"\n")
                else:
                    k.write(newText[0:100]+"\n")
        k.close()

        for item in topicList:
            myResult_3 += item+","

        # print(myResult_1)
        # print(myResult_2)
        # print(myResult_3)
        f = open(read_key,'w',encoding='utf-8')
        f.write(myResult_1.strip(','))
        f.close()
        g = open(read_value,'w',encoding='utf-8')
        g.write(myResult_2.strip(','))
        g.close()
        h = open(read_topic,'w',encoding='utf-8')
        h.write(myResult_3.strip(','))
        h.close()