<?php

// Get IP address from the request
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

// Get location information based on IP address
$locationResponse = file_get_contents("https://ipinfo.io/{$ip}/json");
$locationData = json_decode($locationResponse);

// Get latitude and longitude separately
list($lat, $lon) = explode(',', $locationData->loc);

// Get current time in Beijing time
$beijingOffset = 8; // UTC+8
$date = new DateTime('now', new DateTimeZone('UTC'));
$date->modify('+' . $beijingOffset . ' hours');

// Get deadline times in Beijing time
$deadlines = [
    ['hour' => 11, 'minute' => 30],
    ['hour' => 17, 'minute' => 30],
    ['hour' => 22, 'minute' => 0],
];

$beijingDeadlines = array_map(function ($deadline) use ($date, $beijingOffset) {
    $deadlineTime = clone $date;
    $deadlineTime->setTime($deadline['hour'] - $beijingOffset, $deadline['minute']);
    return $deadlineTime;
}, $deadlines);

// Calculate remaining time for each deadline
$remainingTimes = array_map(function ($deadline, $index) use ($date, $deadlines) {
    $deadlineStr = $deadlines[$index]['hour'] . ":" . $deadlines[$index]['minute'];
    
    // Calculate remaining time in seconds
    $remaining = $deadline->getTimestamp() - $date->getTimestamp();
    if ($remaining < 0) {
        return "<p>Deadline {$deadlineStr} has passed</p>";
    }

    // Convert remaining time to hours, minutes, and seconds
    $hours = floor($remaining / 3600);
    $remaining -= $hours * 3600;
    $minutes = floor($remaining / 60);
    $remaining -= $minutes * 60;
    $seconds = $remaining;

    return "<p>Deadline {$deadlineStr} - {$hours}h {$minutes}m {$seconds}s left</p>";
}, $beijingDeadlines, array_keys($beijingDeadlines));

// Calculate remaining time until May 23rd, 2025, in total hours and total seconds
$may23rd2025 = new DateTime('2025-05-23T00:00:00.000Z');
$remainingSeconds = $may23rd2025->getTimestamp() - $date->getTimestamp();
$remainingHours = floor($remainingSeconds / 3600);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP, HTML, and JavaScript Example</title>
</head>
<body>

    <p>Hello world</p>
    <p>Your IP address is: <?php echo $ip; ?></p>
    <p>Your location is: <?php echo $locationData->country; ?>, <?php echo $locationData->city; ?></p>
    <p>The current time is: <?php echo $date->format('Y-m-d H:i:s'); ?></p>

    <?php echo implode('', $remainingTimes); ?>

    <p>Days until May 23rd, 2025: <?php echo floor($remainingSeconds / 86400); ?></p>
    <p>Remaining time until May 23rd, 2025: <?php echo $remainingHours; ?> hours (<?php echo $remainingSeconds; ?> seconds)</p>

    <p>City information: { lat: <?php echo $lat; ?>, lon: <?php echo $lon; ?>, name: '<?php echo $locationData->city . ', ' . $locationData->country; ?>' }</p>

    <script>
        // You can include JavaScript code here
        // For example:
        console.log('This is JavaScript code in the PHP file.');
    </script>

</body>
</html>
