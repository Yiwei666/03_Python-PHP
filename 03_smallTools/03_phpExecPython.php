<?php
header('Content-Type: text/html; charset=utf-8');

$pythonInterpreter = '/home/00_software/01_Anaconda/bin/python';
$pythonScriptPath = '/home/01_html/05_douyinDownload/02_date.py';

$start = microtime(true);

$result = exec($pythonInterpreter . ' ' . $pythonScriptPath);

$end = microtime(true);

$executionTime = $end - $start;

echo "Python script execution result: " . $result . "<br>";
echo "Script execution time: " . $executionTime . " seconds";
?>
