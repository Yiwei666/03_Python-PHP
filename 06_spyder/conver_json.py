# -*- coding: utf-8 -*-
"""
Created on Wed Jul 20 16:21:41 2022

@author: sun78
"""

import json

filename = '1.json'

with open(filename) as f_obj:
    numbers=json.load(f_obj)
    print(len(numbers))
    for i in range(len(numbers)):
        e = numbers[i]
        print('['+' '.join(e['title'])+']'+'('+ e['visit_url']+ ')'+'\n')
        with open('1.txt', 'a') as new_file:
            # new_file.write(str(e['title'])+'('+ e['visit_url']+ ')'+'\n')
            new_file.write('['+' '.join(e['title'])+']'+'('+ e['visit_url']+ ')      '+'\n')
        # print(i)
        
    
