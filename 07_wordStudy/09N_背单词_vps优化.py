# -*- coding: utf-8 -*-
"""
Created on Sun Jul  2 20:25:12 2023

@author: sun78
"""

import sys
import json
import random
import datetime


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
    

if __name__ == '__main__':

    filenameDB = '/home/01_html/15_pythonword/09N_单词数据库.json' # 默认的数据库文件
    infile = wordsLearning(filenameDB)  # 实例化
    infile.countDays()  # 实例化
    
    ##########
    # filenameDB = '09N_单词数据库.json' # 默认的数据库文件
    
    # infile = wordsLearning(filenameDB)  # 实例化
    infile.randomprint()  # 实例化 
    
