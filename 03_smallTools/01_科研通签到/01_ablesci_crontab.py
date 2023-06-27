import datetime
import time

target_time = datetime.datetime.now().replace(hour=7, minute=0, second=0) + datetime.timedelta(minutes=60)
print("target_time:",target_time)
end_time = datetime.datetime.now().replace(hour=8, minute=0, second=0)

while datetime.datetime.now() < end_time:
    current_time = datetime.datetime.now()

    if current_time >= target_time:
        import subprocess
        command = '/home/00_software/01_Anaconda/bin/python /home/01_html/01_ablesci_spyder.py'
        subprocess.call(command, shell=True)
        break

    time.sleep(60)

