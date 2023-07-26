<?php
// Function to get server performance data
function getServerPerformanceData() {
  // Get CPU usage
  $cpuUsage = shell_exec("top -bn 1 | grep 'Cpu(s)' | awk '{print $2 + $4}'");
  $cpuUsage = floatval($cpuUsage); // Convert to float

  // Get memory usage
  $memoryUsage = shell_exec("free | grep 'Mem:' | awk '{print $3/$2 * 100.0}'");
  $memoryUsage = floatval($memoryUsage); // Convert to float

  // Get network interface name (change 'eth0' to your server's network interface name if needed)
  $interface = 'eth0';

  // Get network traffic (received and transmitted in bytes)
  $networkData = shell_exec("cat /sys/class/net/$interface/statistics/rx_bytes /sys/class/net/$interface/statistics/tx_bytes");
  list($rxBytes, $txBytes) = explode(" ", $networkData);
  $rxBytes = intval($rxBytes); // Convert to integer
  $txBytes = intval($txBytes); // Convert to integer

  // Convert to GB
  $rxGB = $rxBytes / (1024 * 1024 * 1024);
  $txGB = $txBytes / (1024 * 1024 * 1024);

  // Get disk usage percentage
  $diskUsage = shell_exec("df -h | grep '/dev/sda1' | awk '{print $5}'"); // Change '/dev/sda1' to your server's disk partition if needed
  $diskUsage = trim($diskUsage); // Remove leading/trailing whitespaces

  // Get current timestamp
  $timestamp = date('Y-m-d H:i:s');

  // Return data as an associative array
  return array(
    'cpu' => $cpuUsage,
    'memory' => $memoryUsage,
    'rx_gb' => $rxGB,
    'tx_gb' => $txGB,
    'disk_usage' => $diskUsage,
    'timestamp' => $timestamp,
  );
}

// Retrieve and return server performance data as JSON
$data = getServerPerformanceData();
header('Content-Type: application/json');
echo json_encode($data);
?>
