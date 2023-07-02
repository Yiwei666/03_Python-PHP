# -*- coding: utf-8 -*-
"""
Created on Sun Jan  1 16:05:01 2023

@author: 31598
"""
import math
import sys
import json
import random
import pyttsx3     # text-to-speech librarty

import datetime
from numpy import *
import os
import numpy as np
import matplotlib.pyplot as plt
from matplotlib.pyplot import MultipleLocator
from mpl_toolkits.mplot3d.axes3d import Axes3D
import pandas as pd
import tkinter as tk
from tkinter import filedialog
from collections import Counter

import nltk       # 自然语言处理
from nltk.corpus import reuters  # 造句

# import nltk
from nltk.corpus import wordnet
from nltk.util import ngrams
# import nltk
nltk.data.path.append('D:/software/04_Python_library/01_NLTK/nltk_data')

import warnings
warnings.filterwarnings("ignore", category=UserWarning, module='pyttsx3')



class wordsLearning():
    def __init__(self,filenameDB):  # 注意是__是双下划线
        self.filenameDB = filenameDB # 势函数数据库文件名
        
    # 01函数，将数据从json文件读取到内存中
    def loadData(self,filename):                                # 将数据从json文件读取到内存中
        '''
        self.allData = allData        # 设置全局变量，json数据库中的所有数据
        '''
        self.filename = filename
        print('|----------------------------------------调用函数：loadData')
        if self.filename[-4:] == 'json':
            print("输入的文件名称为json后缀")
        else:
            print("注意：输入的文件名称为非json后缀，请仔细检查！")
            sys.exit('404')
        with open(self.filename,'r') as f:              # r 以只读方式打开文件。
            allData = json.load(f)
        # print('读取的json文件: ',self.filename,'从json文件中读取到内存的数据: ',allData,'\n')
        # print('读取的json文件: ',self.filename,'从json文件中读取到内存的字典键',sorted(list(allData.keys())),'\n')   # 注意是字典形式
        self.allData = allData        # 设置全局变量，json数据库中的所有数据
        return allData                             # 返回被加载的数据，注意返回的数据格式，可能是字典或者列表
    
    
    # 02函数，将数据写入到json文件中，数据可以是字典
    def dumpData(self,dumpFile,writenData):                      # 将数据从内存写入到json文件中，会覆盖原有文件内容,传入的数据是需要写入的数据
        self.dumpFile = dumpFile
        self.writenData = writenData
        print('|----------------------------------------调用函数：dumpData')
        if self.dumpFile[-4:] == 'json':
            print("输入的文件名称为json后缀")
        else:
            print("注意：输入的文件名称为非json后缀，请仔细检查！")
            sys.exit('404')
        jsonData = self.writenData
        # filename = '38_[扩展-1化合物原子组成数据库]单质或化合物或多相混合体系密度计算.json'
        with open(self.dumpFile,'w') as f:              # w 打开一个文件只用于写入。如果该文件已存在则覆盖。如果该文件不存在，创建新文件。。
            # json.dump(jsonData,f,ensure_ascii=False)
            json.dump(jsonData,f)
        # print('内存写入到json文件中的数据: ',jsonData)    # 文件的内容起始就是jsonData的内容，一摸一样


    # 03函数，查看数据库内容
    def seeDatabase(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。需要用到其输出的self.allData全局变量
        noLen = 4  # 将打印单词序号的长度设置为4，可更改
        print('|----------------------------------------调用函数：seeDatabase')
        print("数据库中的所有一级键为：",list(self.allData.keys()))
        print("请从上述一级键中进行选择，选择序号即可，如 01, 注意数字不能有重复")
        keyIndexInput = input()
        for i in list(self.allData.keys()):
            iList = i.split('_')
            if keyIndexInput in iList:
                keyInput = i
        print("选择的一级键为：",keyInput)
        if keyInput not in list(self.allData.keys()):
            print("输入的键不在数据库中，请仔细检查！")
            sys.exit('404')
        else:
            print('数据库中关于该键的内容：',self.allData[keyInput])

        wordList = list(self.allData[keyInput].keys())
        wordLengthList = [len(i) for i in wordList]  #  所有单词长度的列表
        print('所有单词长度的列表',wordLengthList)   #  所有单词长度的列表
        maxLength = max(wordLengthList)  # 所有单词最大的长度
        def blank(n):    # 返回一个长度为n的空字符串
            balnkStr = ''
            for i in range(n):
                balnkStr = balnkStr+' '
            return balnkStr
        
        allWords = ''   # 返回所有单词和意思组成的长字符串
        for k,v in self.allData[keyInput].items():
            IndexStr = str(self.allData[keyInput][k]['Index'])
            # WordStr = k
            MeaningStr = self.allData[keyInput][k]['Meaning']
            perWordStr = blank(noLen-len(IndexStr))+IndexStr+'  '+k+blank(maxLength-len(k)) +' : '+MeaningStr
            # print(self.allData['dict_01'][k]['Index'],' ',k,":",self.allData['dict_01'][k]['Meaning'])
            print(perWordStr)
            allWords = allWords+k+':'+self.allData[keyInput][k]['Meaning']+';'
        
        '''
        print("\n所有的单词和意思：",allWords) 
        for k,v in self.allData[keyInput].items():
            print(k,blank(maxLength-len(k)),self.allData[keyInput][k]['TotalCount'])
            # print()
            # print(v)
        '''
        print("单词总数为：",len(self.allData[keyInput].keys()))

            
    # 04函数，添加单词相关信息       
    def addData(self):
        print('|----------------------------------------调用函数：addData')
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('数据库中关于单词信息的内容：',self.allData['dict_01'])
        print('''
              请依次输入需要添加的单词及相应含义，单词和含义用:隔开，
              同一单词多个含义之间用逗号,隔开（非:;都可），
              每一组单词和用英文;隔开，
              如 happy:高兴的,快乐的;ball:球，分割标点符号采用中英文皆可
              可使用换行符
              ''')
        wordInputStr = input()
        w1 = wordInputStr.replace('：', ':')
        w2 = w1.replace('，', ',')
        w3 = w2.replace('；', ';')
        w4 = w3.replace('\n', '')  # 取代掉换行符
        wordGroup = w4.split(';')
        wordMeaningList = []        # 初始化列表，想要将输入的字典转化为列表形式
        for couple in wordGroup:
            coupleList = couple.split(':')
            if len(coupleList)%2 != 0:
                print('输入的语法错误，请重新输入！')
                sys.exit('404')   
            else:
                wordMeaningList = wordMeaningList + coupleList
        print("输入的单词及含义列表为：",wordMeaningList)
        for i in range(0,len(wordMeaningList),2):
            if wordMeaningList[i] in list(self.allData['dict_01'].keys()):
                print('警告：数据库中已经存在该单词:',wordMeaningList[i],'; 若程序继续会覆盖该单词已有的信息!\n') 
                sys.exit('404')
            print('--------添加的新的单词信息',wordMeaningList[i],wordMeaningList[i+1])
            self.allData['dict_01'][wordMeaningList[i]] = {}
            self.allData['dict_01'][wordMeaningList[i]]['Index'] = len(list(self.allData['dict_01'].keys()))
            self.allData['dict_01'][wordMeaningList[i]]['Meaning'] = wordMeaningList[i+1]
            self.allData['dict_01'][wordMeaningList[i]]['Time'] = str(datetime.datetime.now().strftime('%Y-%m-%d  %H:%M:%S'))
            self.allData['dict_01'][wordMeaningList[i]]['Length'] = len(wordMeaningList[i])
            self.allData['dict_01'][wordMeaningList[i]]['TotalCount'] = [0,0]    # 总共复习的次数,错误次数,正确次数
            
        print('修改后的数据库单词内容：',self.allData['dict_01'])
        allWords = ''
        for k,v in self.allData['dict_01'].items():
            print(self.allData['dict_01'][k]['Index'],' ',k,":",self.allData['dict_01'][k]['Meaning'])
            allWords = allWords+k+':'+self.allData['dict_01'][k]['Meaning']+';'
        print("所有的单词和意思：",allWords)        
        print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        saveChose = input()
        if saveChose == '':
            self.dumpData(self.filenameDB,self.allData)   # 存为json文件
            print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('修改后的数据未保存！')
        # Paleolithic:旧石器时代的;Neolithic:新时期时代的;exhume:掘出
        
    # 05函数，看单词选意思，四选一
    def wordChooseMeaning(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('|----------------------------------------调用函数：wordChooseMeaning')
        print("数据库中的所有一级键为：",list(self.allData.keys()))
        print("请从上述一级键中进行选择，选择序号即可，如 01")
        keyIndexInput = input()
        for i in list(self.allData.keys()):
            iList = i.split('_')
            if keyIndexInput in iList:
                keyInput = i
        print("选择的一级键为：",keyInput)
        if keyInput not in list(self.allData.keys()):
            print("输入的键不在数据库中，请仔细检查！")
            sys.exit('404')
        else:
            print('数据库中关于该键的内容：',self.allData[keyInput])
            
        allWordList = list(self.allData[keyInput].keys())  # 列出选中字典的所有单词
        wrongWordList = [] # 初始化一个列表，用来储存答错的单词
        message = ""
        yesCount = 0         # 计数，答对的数量
        noCount  = 0         # 计数，答错的数量
        while message != 'q':
            rdm1 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm2 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm3 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm4 = random.randint(0,len(list(self.allData[keyInput].keys())))
            Word1 = allWordList[rdm1] # 单词
            Word2 = allWordList[rdm2]
            Word3 = allWordList[rdm3]
            Word4 = allWordList[rdm4]
            Mean1 = self.allData[keyInput][Word1]['Meaning']
            Mean2 = self.allData[keyInput][Word2]['Meaning']
            Mean3 = self.allData[keyInput][Word3]['Meaning']
            Mean4 = self.allData[keyInput][Word4]['Meaning']
            print("请选择如下单词的意思,输入序号即可，如 1，输入q结束程序\n")
            WordList = [Word1]+[Word2]+[Word3]+[Word4]
            wordChose = random.choice(WordList)        # 选择的单词
            wordChoseMean = self.allData[keyInput][wordChose]['Meaning']  # 选择的单词对应的含义
            print(wordChose,'\n')  # 列出需要判断的单词
            print("1:",Mean1," \n2:",Mean2," \n3:",Mean3," \n4:",Mean4)  # 列出选项中的意思
            message = input()
            if message == 'q':
                print("程序运行正常结束！")
            elif message in ['1','2','3','4']:
                # MeanNo = 'Mean'+message
                if eval('Word'+str(message)) != wordChose:
                    noCount = noCount + 1
                    wrongWordList.append(wordChose)
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print('选择错误，正确答案为：----',wordChoseMean,'----\n')
                    self.allData[keyInput][wordChose]['TotalCount'][1] = self.allData[keyInput][wordChose]['TotalCount'][1] + 1
                    
                    keyWordsDict = 'dict_09_KEYWORDS'
                    keyWord = wordChose
                    keyWordMeaning = wordChoseMean
                    if keyWord  not in list(self.allData[keyWordsDict].keys()):
                        self.allData[keyWordsDict][keyWord] = {}
                        if len(list(self.allData[keyWordsDict].keys())) >= 1:
                            self.allData[keyWordsDict][keyWord]['Index'] = len(list(self.allData[keyWordsDict].keys()))
                        else:
                            self.allData[keyWordsDict][keyWord]['Index'] = 1
                        self.allData[keyWordsDict][keyWord]['Meaning'] = keyWordMeaning
                        self.allData[keyWordsDict][keyWord]['Time'] = str(datetime.datetime.now().strftime('%Y-%m-%d  %H:%M:%S'))
                        self.allData[keyWordsDict][keyWord]['Length'] = len(keyWord)
                        self.allData[keyWordsDict][keyWord]['TotalCount'] = [0,0]    # 总共复习的次数,错误次数,正确次数
                        print("该单词已被收录到 dict_09_KEYWORDS。")
                else:
                    yesCount = yesCount + 1
                    print("GGGG-OOOO-OOOO-DDDD!")
                    print("GGGG-OOOO-OOOO-DDDD!")
                    print("恭喜答案正确！\n")
                    self.allData[keyInput][wordChose]['TotalCount'][0] = self.allData[keyInput][wordChose]['TotalCount'][0] + 1
            else:
                print('选项超出1-4范围！')
                print("!!!!!!!!!!!!!!!!!!!")
                print("!!!!!!!!!!!!!!!!!!!")
                print("!!!!!!!!!!!!!!!!!!!")
                
            engine = pyttsx3.init()
            voices = engine.getProperty('voices')
            # Set the default voice to the first voice in the list
            engine.setProperty('voice', voices[1].id)
            # Set the rate, volume and other properties of the speech engine
            engine.setProperty('rate', 130)
            engine.setProperty('volume', 1)
            engine.say(wordChose)
            engine.runAndWait()
            
        print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        saveChose = input()
        if saveChose == '':
            self.dumpData(self.filenameDB,self.allData)   # 存为json文件
            print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('修改后的数据未保存！\n')
        print("共答题：",yesCount+noCount,' 其中正确:',yesCount,' 错误:',noCount,' 正确率:',yesCount/(yesCount+noCount)*100,"%")
        if len(wrongWordList) != 0: # 将错误的单词和意思都打印出来
            print('以下是本轮答题出错的单词')
            for i in wrongWordList:
                print(i,':',self.allData[keyInput][i]['Meaning'])
        
        # Paleolithic:旧石器时代的;Neolithic:新时期时代的;exhume:掘出
        
                    
    # 06函数，对每个单词字典添加子选项信息，使用前请自定义该方法
    def subInfoAdd(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('|----------------------------------------调用函数：subInfoAdd')
        # print('请输入你想要添加的键和值，用英文逗号,隔开')
        # dictInputList = input().split(',')
        print('''请选择操作类型：
              1: 针对二级键的值进行修改，修改前请自定义函数，仅适用于dict_01字典
              2: 新增一级键，对应值为空字典，每次仅能操作一个
              3: 删除一级键，每次仅能操作一个
              4: 选定一级键添加二级键，使用前请自定义
              ''')
        choseInput = input()
        if choseInput == '1':
            for k,v in self.allData['dict_01'].items():
                self.allData['dict_01'][k]['TotalCount'] = [0,0]     # 分别对应正确的和错误的
            print('修改后的数据库单词内容：',self.allData['dict_01'])
        elif choseInput == '2':
            print("数据库中的所有一级键为：",list(self.allData.keys()))
            print("请输入待添加的一级键的名称")
            keyInput = input()
            if keyInput not in list(self.allData.keys()):
                self.allData[keyInput] = {}  # 添加新的一级键
                print('修改后的数据库一级键列表：',list(self.allData.keys()))
            else:
                print("输入的键已在数据库中，请仔细检查！")
                sys.exit('404')
        elif choseInput == '3': # 删除一级键
            print("数据库中的所有一级键为：",list(self.allData.keys()))
            print("请输入待删除的一级键的名称")
            keyInput = input()  
            if keyInput in list(self.allData.keys()):
                del self.allData[keyInput]  # 删除一级键
                print("输入的一级键已被删除。")
                print('修改后的数据库一级键列表：',list(self.allData.keys()))
            else:
                print("输入的键不在数据库中，请仔细检查！")
                sys.exit('404')
        elif choseInput == '4': # 选定一级键添加二级键，使用前请自定义
            print("数据库中的所有一级键为：",list(self.allData.keys()))
            print("请输入待添加二级键的一级键的名称")
            keyInput = input()

            if keyInput in list(self.allData.keys()):
                print("数据库中的对应该一级键的所有二级键为：",list(self.allData[keyInput].keys()))
                print("请输入待添加二级键的的名称，对应值为列表")
                keyTwoInput = input()
                if keyTwoInput not in list(self.allData[keyInput].keys()):
                    self.allData[keyInput][keyTwoInput] = []  # 添加新的一级键
                    print('修改后的数据库二级键列表：',list(self.allData[keyInput].keys()))
                else:
                    print("输入的二级键已在数据库中，请仔细检查！")
                    sys.exit('404') 
            else:
                print("输入的一级键不在数据库中，请仔细检查！")
                sys.exit('404')     
        else:
            print('您选择的操作超出范围，请仔细检查后重新选择！')
            sys.exit('404')

        print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        saveChose = input()
        if saveChose == '':
            self.dumpData(self.filenameDB,self.allData)   # 存为json文件
            print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('修改后的数据未保存！')

    # 07函数，给选定一级键的所有单词添加意思，相比04函数针对一个特定的一级键，这个函数可以适用多个一级键。
    def addDataMore(self):
        print('|----------------------------------------调用函数：addDataMore')
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print("数据库中的所有一级键为：",list(self.allData.keys()))
        print("请从上述一级键中进行选择，选择序号即可，如 01")
        keyIndexInput = input()
        for i in list(self.allData.keys()):
            iList = i.split('_')
            if keyIndexInput in iList:
                keyInput = i
        print("选择的一级键为：",keyInput)
        if keyInput not in list(self.allData.keys()):
            print("输入的键不在数据库中，请仔细检查！")
            sys.exit('404')
        else:
            print('数据库中关于该键的内容：',self.allData[keyInput])
            
        # print('请依次输入需要添加的单词及相应含义，单词和含义用:隔开，同一单词多个含义之间用逗号,隔开（非:;都可），每一组单词和用英文;隔开，如 happy:高兴的,快乐的;ball:球，分割标点符号采用中英文皆可')
        
        
        print('''
              请选择单词的写入方式：
              
              方式 1，输入 1
              foremost 最好的,最重要的
              stack 堆,垛,堆积
              circus 马戏,环形广场
              disguise 伪装,掩饰 
              stagger 蹒跚,踉跄
              ...
              
              方式 2，输入 2
              foremost:最好的,最重要的;stack:堆,垛,堆积;circus:马戏,环形广场;disguise:伪装,掩饰;stagger:蹒跚,踉跄
              请依次输入需要添加的单词及相应含义，单词和含义用中英文冒号隔开，
              同一单词多个含义之间用中英文逗号隔开，每一组单词用中英文分号隔开，
              可使用换行符
              
              方式3，输入 3
                ornament	*	n. 装饰；[建][服装] 装饰物；教堂用品vt. 装饰，修饰
                apprehension	*	n. 理解；恐惧；逮捕；忧惧
                indicative	*	adj. 象征的；指示的；表示…的n. 陈述语气；陈述语气的动词形式
                hinder	*	vi. 成为阻碍vt. 阻碍；打扰adj. 后面的n. (Hinder)人名；(芬)欣德
                municipal	*	adj. 市政的，市的；地方自治的              
              ''')
        choseInput = input()
              
        if choseInput == "1":
            print("请按照方式 1 输入单词和意思，注意分隔符")
            wordInputStr = input()
            w1 = wordInputStr.replace(' ', ':')  # 取代中文冒号
            w2 = w1.replace('，', ',')            # 取代中文逗号
            w3 = w2.replace('\n', ';')             # 取代掉换行符
            wordGroup = w3.split(';')            
        elif choseInput == "2":
            print("请按照方式 2 输入单词和意思，注意分隔符")
            wordInputStr = input()
            w1 = wordInputStr.replace('：', ':')  # 取代中文冒号
            w2 = w1.replace('，', ',')            # 取代中文逗号
            w3 = w2.replace('；', ';')            # 取代中文分号
            w4 = w3.replace('\n', '')             # 取代掉换行符
            w5 = w4.replace(' ', '')              # 取代中文空格
            w6 = w5.replace(' ', '')              # 取代英文空格
            wordGroup = w6.split(';')
        elif choseInput == "3":
            print("请按照方式 3 输入单词和意思，注意分隔符")
            wordInputStr = input()
            w1 = wordInputStr.replace('\t', '')  # 取代中文冒号
            w2 = w1.replace('；', ',')
            w3 = w2.replace(';', ',')
            w4 = w3.replace('，', ',')
            w5 = w4.replace(':', '：')
            w6 = w5.replace('_', ':')            # 取代中文逗号
            w7 = w6.replace('"', '')             # 取代掉换行符
            w8 = w7.replace('\n', ';')             # 取代掉换行符
            w9 = w8.replace('*', '')
            w10 = w9.replace('（', '(')
            w11 = w10.replace('）', ')')
            wordGroup = w11.split(';')
        
        else:
            print("方式超出范围！")
            sys.exit("404")

        wordMeaningList = []        # 初始化列表，想要将输入的字典转化为列表形式
        for couple in wordGroup:
            coupleList = couple.split(':')
            if len(coupleList)%2 != 0:
                print('输入的语法错误，请重新输入！，问题出在：',couple)
                sys.exit('404')   
            else:
                wordMeaningList = wordMeaningList + [coupleList[0].replace(' ', '')] + [coupleList[1].replace(' ','')]
        print("输入的单词及含义列表为：",wordMeaningList)
        repeatWordList = [] # 重复的单词列表，本程序会跳过
        for i in range(0,len(wordMeaningList),2):
            if wordMeaningList[i] in list(self.allData[keyInput].keys()):
                print('警告：数据库中已经存在该单词:',wordMeaningList[i],'; 若程序继续会跳过该单词或者中断!\n') 
                repeatWordList.append(wordMeaningList[i])   # 重复单词列表添加重复单词
                continue
                sys.exit('404')
            print('--------添加的新的单词信息',wordMeaningList[i],wordMeaningList[i+1])
            self.allData[keyInput][wordMeaningList[i]] = {}
            self.allData[keyInput][wordMeaningList[i]]['Index'] = len(list(self.allData[keyInput].keys()))
            self.allData[keyInput][wordMeaningList[i]]['Meaning'] = wordMeaningList[i+1]
            self.allData[keyInput][wordMeaningList[i]]['Time'] = str(datetime.datetime.now().strftime('%Y-%m-%d  %H:%M:%S'))
            self.allData[keyInput][wordMeaningList[i]]['Length'] = len(wordMeaningList[i])
            self.allData[keyInput][wordMeaningList[i]]['TotalCount'] = [0,0]    # 总共复习的次数,错误次数,正确次数
            
        print('修改后的数据库单词内容：',self.allData[keyInput])
        allWords = ''
        for k,v in self.allData[keyInput].items():
            print(self.allData[keyInput][k]['Index'],' ',k,":",self.allData[keyInput][k]['Meaning'])
            allWords = allWords+k+':'+self.allData[keyInput][k]['Meaning']+';'
        print("所有的单词和意思：",allWords)   
        print("\n本次添加，重复的单词列表为：",repeatWordList,"\n这些单词已被跳过。")
        print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        saveChose = input()
        if saveChose == '':
            self.dumpData(self.filenameDB,self.allData)   # 存为json文件
            print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('修改后的数据未保存！')
        
        # Paleolithic:旧石器时代的;Neolithic:新时期时代的;exhume:掘出

    # 08函数，根据意思选单词，与05函数是对应的
    def MeaningChooseWord(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('|----------------------------------------调用函数：MeaningChooseWord')
        print("数据库中的所有一级键为：",list(self.allData.keys()))
        print("请从上述一级键中进行选择，选择序号即可，如 02 03 04 05 10，可同时选择多个序号，用空格隔开")
        keyIndexInputList = input().split(' ')             # 输入如 01 02 03
        keyIndexInputList = list(set(keyIndexInputList))   # 删除列表中重复的元素
        firstKeyList = []                                  # 创建一个空列表用于储存对应于输入的一级字典
        for l,j in enumerate(keyIndexInputList):
            for i in list(self.allData.keys()):
                iList = i.split('_')
                if j in iList:
                    firstKeyList.append(i)
            if len(firstKeyList) != (l+1):
                print(f"对应于序号{j}的一级键不存在于数据库中，请仔细检查！")
        print("请确认输入序号对应的一级键是否为",firstKeyList)
        merged_dict = {}  # 将输入的所有字典进行合并
        for k in firstKeyList:
            if k not in list(self.allData.keys()):
                print(f"输入的{k}键不在数据库中，请仔细检查！")
                sys.exit('404')
            else:
                # print('数据库中关于该键的内容：',self.allData[keyInput])
                print(f"一级键{k}的单词数量为{len(list(self.allData[k].keys()))}")
                merged_dict = {**merged_dict, **self.allData[k]}
        self.allData['tempoary_dict'] = merged_dict  # 将合并后的字典临时添加到数据库中
        print(f"合并字典 tempoary_dict 的单词数量为{len(list(merged_dict .keys()))}")
        keyInput = "tempoary_dict"   # 将选中的字典初始化为合并字典
        '''
        keyIndexInput = input()
        for i in list(self.allData.keys()):
            iList = i.split('_')
            if keyIndexInput in iList:
                keyInput = i
        print("选择的一级键为：",keyInput)
        if keyInput not in list(self.allData.keys()):
            print("输入的键不在数据库中，请仔细检查！")
            sys.exit('404')
        else:
            print('数据库中关于该键的内容：',self.allData[keyInput])
        '''    
        allWordList = list(self.allData[keyInput].keys())
        wrongWordList = [] # 初始化一个列表，用来储存答错的单词
        wrongWordMeaningList = []  # 初始化一个列表，用来储存答错单词的意思
        message = ""         # 初始化
        yesCount = 0         # 计数，答对的数量
        noCount  = 0         # 计数，答错的数量
                
        while message != 'q':
            rdm1 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm2 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm3 = random.randint(0,len(list(self.allData[keyInput].keys())))
            rdm4 = random.randint(0,len(list(self.allData[keyInput].keys())))
            Word1 = allWordList[rdm1] # 单词
            Word2 = allWordList[rdm2]
            Word3 = allWordList[rdm3]
            Word4 = allWordList[rdm4]
            Mean1 = self.allData[keyInput][Word1]['Meaning']
            Mean2 = self.allData[keyInput][Word2]['Meaning']
            Mean3 = self.allData[keyInput][Word3]['Meaning']
            Mean4 = self.allData[keyInput][Word4]['Meaning']
            print("请选择如下意思对应的单词,输入序号即可，如 1，输入q结束程序\n")
            WordList = [Word1]+[Word2]+[Word3]+[Word4]
            MeanList = [Mean1]+[Mean2]+[Mean3]+[Mean4]
            # wordChose = random.choice(WordList)     # 选择的单词
            meanChose = random.choice(MeanList)       # 选择意思
            meanIndex = MeanList.index(meanChose)     # 找到对应意思的索引
            meanChoseWord = WordList[meanIndex]       # 找到对应该索引的单词
            
            def wordToSentence(wordInput):  # 获取选中单词的例句
                word = wordInput  # 输入的单词
                sentences = reuters.sents()
                # Find all sentences that contain the word
                example_sentences = [sentence for sentence in sentences if word in sentence]
                # If there is at least one sentence that contains the word
                if example_sentences:
                    # Randomly select one sentence
                    one_sentence = example_sentences[0]
                    print(" ".join(one_sentence))
                else:
                    print(f"No sentence containing the word '{word}' was found.")
            #wordChoseMean = self.allData[keyInput][wordChose]['Meaning']  # 选择的单词对应的含义
            def nearMeanWord(wordInput):   # 近义词
                # Download the NLTK data if you haven't done it before
                # nltk.download()
                # Get the synonyms of the word "happy" from the WordNet corpus
                synonyms = wordnet.synsets(wordInput)
                # Print the name and definition of each synonym
                synonymsDict ={}
                for i,syn in enumerate(synonyms):
                    for lemma in syn.lemmas():
                        print(f"{i+1} {lemma.name()}: {syn.definition()}")
                # Get the antonyms of the word "happy"
                antonyms = []
                for syn in synonyms:
                    for lemma in syn.lemmas():
                        if lemma.antonyms():
                            antonyms.append(lemma.antonyms()[0].name())
                print(f"Antonyms of 'happy': {antonyms}")

            print(meanChose,'\n')  # 列出需要判断的意思
            print("1:",Word1," 2:",Word2," 3:",Word3," 4:",Word4)  # 列出选项中的意思
            message = input()
            if message == 'q':
                print("程序运行正常结束！")
            elif message in ['1','2','3','4']:
                # MeanNo = 'Mean'+message
                if eval('Word'+str(message)) != meanChoseWord:
                    wrongWordList.append(meanChoseWord)
                    wrongWordMeaningList.append(self.allData[keyInput][meanChoseWord]['Meaning'])
                    noCount = noCount + 1
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print("!!!!!!!!!!!!!!!!!!!")
                    print('选择错误，正确答案为：----',meanChoseWord,'----\n')
                    self.allData[keyInput][meanChoseWord]['TotalCount'][1] = self.allData[keyInput][meanChoseWord]['TotalCount'][1] + 1
                    # 下面是将选择错误的单词记录到错题本字典'dict_08_KEYWORDS'中
                    keyWordsDict = 'dict_09_KEYWORDS'
                    keyWord = meanChoseWord
                    keyWordMeaning = meanChose
                    if keyWord  not in list(self.allData[keyWordsDict].keys()):
                        self.allData[keyWordsDict][keyWord] = {}
                        if len(list(self.allData[keyWordsDict].keys())) >= 1:
                            self.allData[keyWordsDict][keyWord]['Index'] = len(list(self.allData[keyWordsDict].keys()))
                        else:
                            self.allData[keyWordsDict][keyWord]['Index'] = 1
                        self.allData[keyWordsDict][keyWord]['Meaning'] = keyWordMeaning
                        self.allData[keyWordsDict][keyWord]['Time'] = str(datetime.datetime.now().strftime('%Y-%m-%d  %H:%M:%S'))
                        self.allData[keyWordsDict][keyWord]['Length'] = len(keyWord)
                        self.allData[keyWordsDict][keyWord]['TotalCount'] = [0,0]    # 总共复习的次数,错误次数,正确次数
                        print("该单词已被收录到 dict_09_KEYWORDS。")
                    nearMeanWord(meanChoseWord)                 # 调用函数，获取近义词
                    wordToSentence(meanChoseWord)             # 调用上述函数，输出例句
                    print('\n')
                else:      # 对选对的单词进行计数和输出
                    yesCount = yesCount + 1
                    print("GGGG-OOOO-OOOO-DDDD!")
                    print("GGGG-OOOO-OOOO-DDDD!")
                    print("恭喜答案正确！\n")
                    self.allData[keyInput][meanChoseWord]['TotalCount'][0] = self.allData[keyInput][meanChoseWord]['TotalCount'][0] + 1                   

            else:
                print('选项超出1-5范围！')
                print("!!!!!!!!!!!!!!!!!!!")
                print("!!!!!!!!!!!!!!!!!!!")
                print("!!!!!!!!!!!!!!!!!!!")
            engine = pyttsx3.init()
            voices = engine.getProperty('voices')
            # Set the default voice to the first voice in the list
            engine.setProperty('voice', voices[1].id)
            # Set the rate, volume and other properties of the speech engine
            engine.setProperty('rate', 130)
            engine.setProperty('volume', 1)
            engine.say(meanChoseWord)
            engine.runAndWait()
        del self.allData['tempoary_dict']  # 任务运行结束后，要对生成的字典进行释放
        print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        saveChose = input()
        if saveChose == '':
            self.dumpData(self.filenameDB,self.allData)   # 存为json文件
            print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('修改后的数据未保存！\n')
        for i,j in enumerate(list(self.allData.keys())):  # 统计各一级键中单词数量
            print(f"{i+1}:一级键{j}中的单词数量为{len(list(self.allData[j].keys()))}")
        print("\n共答题：",yesCount+noCount,' 其中正确:',yesCount,' 错误:',noCount,' 正确率:',yesCount/(yesCount+noCount)*100,"%")
        if len(wrongWordList) != 0: # 将错误的单词和意思都打印出来
            print('以下是本轮答题出错的单词\n')
            for i,j in zip(wrongWordList,wrongWordMeaningList):
                print(f"单词{i}的意思为{j}")
                # print(i,':',self.allData[keyInput][i]['Meaning'])

    # 09函数，统计打卡的天数
    def countDays(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('|----------------------------------------调用函数：countDays')
        keyInput = 'other_01_days'
        keyTwoInput = 'total_days'
        date = str(datetime.datetime.now().strftime('%Y-%m-%d'))

        if date not in self.allData[keyInput][keyTwoInput]:
            self.allData[keyInput][keyTwoInput].append(date)  # 添加日期
            print(date,"已签到成功！")
        else:
            print(date,"今日已签到！")
        totalDays = len(self.allData[keyInput][keyTwoInput])
        print("累计签到天数：",totalDays)
        # print('是否需要将修改后的数据库字典进行保存？Enter键为保存，F或f为不保存。')
        # saveChose = input()
        saveChose = ''
        if saveChose == '':
            with open(self.filenameDB,'w') as f:              # w 打开一个文件只用于写入。如果该文件已存在则覆盖。如果该文件不存在，创建新文件。。
                # json.dump(self.allData,f,ensure_ascii=False)
                json.dump(self.allData,f)
            # print('内存写入到json文件中的数据: ',self.allData) 
            # print('默认保存的文件为原文件，文件名：', self.filenameDB,'存储方式为覆盖保存')
        else:
            print('\n')
            

    # 10函数，随机某个数据库中的单词和意思
    def randomprint(self):
        self.loadData(self.filenameDB)       # 要访问同个类中的其它方法定义的实例变量，必须先调用该方法。
        print('|----------------------------------------调用函数：randomprint')
        """
        数据库中的所有一级键为： ['dict_01', 'dict_03_GRE', 'dict_02_TOEFL', 'dict_04_IELTS', 'dict_05_CET6', 
                      'dict_08_TEM8', 'dict_06_GMAT', 'dict_07_SAT', 'other_01_days', 'dict_09_KEYWORDS', 'dict_10_GRE7500']
        """
        fistKeyList =  ['dict_03_GRE', 'dict_02_TOEFL', 'dict_04_IELTS', 'dict_05_CET6', 'dict_07_SAT', 'dict_10_GRE7500']
        keyInput = random.choice(fistKeyList)  # 随机选中数据库
        print(keyInput,"\n")
        wordList = list(self.allData[keyInput].keys())
        wordLengthList = [len(i) for i in wordList]  #  所有单词长度的列表
        # print('所有单词长度的列表',wordLengthList)   #  所有单词长度的列表
        maxLength = max(wordLengthList)  # 所有单词最大的长度
        def blank(n):    # 返回一个长度为n的空字符串
            balnkStr = ''
            for i in range(n):
                balnkStr = balnkStr+' '
            return balnkStr
        
        allWords = ''   # 返回所有单词和意思组成的长字符串
        # for k,v in self.allData[keyInput].items():
        k = random.choice(wordList) # 随机选中一个单词
        IndexStr = str(self.allData[keyInput][k]['Index'])
        # WordStr = k
        MeaningStr = self.allData[keyInput][k]['Meaning']
        noLen = 4  # 将打印单词序号的长度设置为4，可更改
        perWordStr = blank(noLen-len(IndexStr))+IndexStr+'  '+k+blank(maxLength-len(k)) +' : '+MeaningStr
        # print(self.allData['dict_01'][k]['Index'],' ',k,":",self.allData['dict_01'][k]['Meaning'])
        print(perWordStr)
        allWords = allWords+k+':'+self.allData[keyInput][k]['Meaning']+';'
        # print(allWords)
    
        
# F01函数，选择输入文件，F代表基础函数
def inputFunction():     # 打印出当前目录下的所有文件名
    print("默认打开当前目录，是否调用GUI获取文件路径?Yes=1,No=2, 直接输入Enter键默认为2")     # 提示命令行输入
    fileOpenStyle = input()
    if fileOpenStyle == '':
        fileOpenStyle = 2
    # fileOpen = int(input())              # 注意字符串输入变量的数据类型转换
    if fileOpenStyle == "1":
        root = tk.Tk()
        root.withdraw()
        f_path = filedialog.askopenfilename()
        txtName = f_path
        print('\n获取的文件地址：', f_path)
    else:
        for root, dirs, files in os.walk("."):
            for filename in files:
                print(filename)  
        print("请输出需要处理的文件名,包含xyz、ener等格式，如 50_SiV(0-500).xyz、SiV-1.ener")     # 提示命令行输入
        txtName = input()              # 注意字符串输入变量的数据类型转换
    return txtName
        


if __name__ == '__main__':
    print('''
  本脚本的功能如下:
      01: 创建json数据库文件，初始内容为 {"dict_01": {}, "dict_02": {}}}
      02: 查看单词数据库内容，满足所有json格式数据库的查看
      03: 添加单词相关信息,仅适用于dict_01, 建议使用06功能
      04: 根据单词选意思，四选一
      05: 新增、删除一级键，修改二级值，使用前请自定义该方法
      06: 给指定一级键的所有单词添加信息
      07: 根据意思选单词，四选一
      08: 随机打印出某个数据库的某个单词和意思
      
      -1: 测试
           
          ''')
    print("请选择功能，输入Enter默认为-1测试")     # 提示选择功能
    # defChoose = input()
    defChoose = 0
    
    if defChoose == '' :                       # 将Enter快捷键默认值设为-1
        defChoose = "-1"
        
    if defChoose == "-1":
        print("提示：您选择的功能正在开发，请重新选择！")
        
    elif defChoose == "01":  # 创建json格式文件
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        print('请为你想要创建的json文件命名，.json格式，Enter采用默认文件名: 09N_单词数据库.json')
        print('数据库初始内容为 {"dict_01": {}, "dict_02": {}}')
        jsonName = input()       # 输入势参数文件名，Enter代表默认的文件名，也可以自定义文件名
        if jsonName == '' :
            jsonName = filenameDB           
        writenData = {}
        writenData["dict_01"] = {}
        writenData["dict_02"] = {}
        infile.dumpData(jsonName,writenData)   # 默认创建一个为空的json文件

    elif defChoose == "02":  # 查看json格式数据库内容
        print('请选择数据库，可以是所有json格式的数据库。')
        # filenameDB = inputFunction()
        filenameDB = '09N_单词数据库.json' 
        infile = wordsLearning(filenameDB)  # 实例化
        infile.seeDatabase()        
        
    elif defChoose == "03":  # 添加单词相关信息
        # filenameDB = inputFunction()
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        infile.addData()  # 实例化
        
    elif defChoose == "04":  # 根据单词选意思
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        infile.wordChooseMeaning()  # 实例化        
        
    elif defChoose == "05":  # 每个单词字典添加一些新的子键
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        infile.subInfoAdd()  # 实例化
        
    elif defChoose == "06": 
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        infile.addDataMore()  # 实例化    

    elif defChoose == "07":  # 根据单词选意思
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        infile = wordsLearning(filenameDB)  # 实例化
        infile.MeaningChooseWord()  # 实例化  

    elif defChoose == "08":  # 随机打印出某个数据库的单词和意思
        filenameDB = '09N_单词数据库.json' # 默认的数据库文件
        
        infile = wordsLearning(filenameDB)  # 实例化
        infile.randomprint()  # 实例化  


    else:
        print("提示：您选择的功能正在开发，请重新选择！")
    
    
    filenameDB = '/home/01_html/15_pythonword/09N_单词数据库.json' # 默认的数据库文件
    infile = wordsLearning(filenameDB)  # 实例化
    infile.countDays()  # 实例化
    
    ##########
    # filenameDB = '09N_单词数据库.json' # 默认的数据库文件
    
    # infile = wordsLearning(filenameDB)  # 实例化
    infile.randomprint()  # 实例化 
    