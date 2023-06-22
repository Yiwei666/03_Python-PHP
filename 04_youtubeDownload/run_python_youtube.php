<?php
    $pythonPath = '/home/00_software/01_Anaconda/bin/python';
    $scriptPath = '/home/01_html/06_youtubeDownload/downPHP_youtube.py';

    exec($pythonPath . ' ' . $scriptPath);

    echo 'Python script executed!';
?>
