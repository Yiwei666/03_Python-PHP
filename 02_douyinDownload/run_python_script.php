<?php
    $pythonPath = '/home/00_software/01_Anaconda/bin/python';
    $scriptPath = '/home/01_html/05_douyinDownload/01_douyinDown.py';

    exec($pythonPath . ' ' . $scriptPath);

    echo 'Python script executed!';
?>
