
```
0 21 * * * rm  /home/experiment/musicUrl.txt
0 21 * * * rm  /home/experiment/finalmusic.txt
0 21 * * * curl -o /home/experiment/latest.html  https://www.kekenet.com/song/tingge/
2 21 * * * /home/anaconda/anaconda3_installation/bin/python  /home/experiment/01keke.py
4 21 * * * /home/anaconda/anaconda3_installation/bin/python  /home/experiment/musicdown.py

```
