<?php
// Function to get server performance data
function getServerPerformanceData() {
  // Get CPU usage
  $cpuUsage = shell_exec("top -bn 1 | grep 'Cpu(s)' | awk '{print $2 + $4}'");
  $cpuUsage = floatval($cpuUsage); // Convert to float

  // Get memory usage
  $memoryUsage = shell_exec("free | grep 'Mem:' | awk '{print $3/$2 * 100.0}'");
  $memoryUsage = floatval($memoryUsage); // Convert to float

  // Get current timestamp
  $timestamp = date('Y-m-d H:i:s');

  // Return data as an associative array
  return array(
    'cpu' => $cpuUsage,
    'memory' => $memoryUsage,
    'timestamp' => $timestamp,
  );
}

// Retrieve and return server performance data as JSON
$data = getServerPerformanceData();
header('Content-Type: application/json');
echo json_encode($data);
?>
