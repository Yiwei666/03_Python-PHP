<?php
// Get the client's IP address
$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

// Get country and city using ipapi.co
$country = file_get_contents("https://ipapi.co/{$ip}/country_name/");
$city = file_get_contents("https://ipapi.co/{$ip}/city/");
$latitude = number_format(file_get_contents("https://ipapi.co/{$ip}/latitude/"), 4);
$longitude = number_format(file_get_contents("https://ipapi.co/{$ip}/longitude/"), 4);

// Get current date and time
$date = new DateTime('now', new DateTimeZone('Asia/Shanghai'));

// Get deadlines in Beijing time
$deadlines = [
    ['hour' => 11, 'minute' => 30],
    ['hour' => 17, 'minute' => 30],
    ['hour' => 22, 'minute' => 0],
];

// Calculate remaining time for each deadline
$remainingTimes = [];
foreach ($deadlines as $index => $deadline) {
    $deadlineStr = $deadline['hour'] . ":" . $deadline['minute'];

    // Calculate remaining time in seconds
    $remaining = strtotime("{$date->format('Y-m-d')} {$deadlineStr}") - $date->getTimestamp();
    if ($remaining < 0) {
        $remainingTimes[] = "<p>Deadline {$deadlineStr} has passed</p>";
    } else {
        // Convert remaining time to hours, minutes, and seconds
        $hours = floor($remaining / 3600);
        $remaining -= $hours * 3600;
        $minutes = floor($remaining / 60);
        $remaining -= $minutes * 60;
        $seconds = $remaining;

        $remainingTimes[] = "<p>Deadline {$deadlineStr} - {$hours}h {$minutes}m {$seconds}s left</p>";
    }
}

// Calculate remaining time until May 23rd, 2025
$may23rd2025 = new DateTime('2025-05-23 00:00:00', new DateTimeZone('UTC'));
$remainingSeconds = $may23rd2025->getTimestamp() - $date->getTimestamp();
$remainingHours = floor($remainingSeconds / 3600);

// City information
$cityInfo = [
    'lat' => $latitude,
    'lon' => $longitude,
    'name' => "{$city}, {$country}",
];

// Generate the response
$message = "<html><head><style>body { font-family: 'Calibri', sans-serif; }</style></head><body><p>Hello world</p><p>Your IP address is: {$ip}</p><p>Your location is: {$country}, {$city}</p><p>The current time is: {$date->format('Y-m-d H:i:s')}</p>";
$message .= implode('', $remainingTimes);
$message .= "<p>Days until May 23rd, 2025: " . floor($remainingSeconds / 86400) . "</p>";
$message .= "<p>Remaining time until May 23rd, 2025: {$remainingHours} hours ({$remainingSeconds} seconds)</p>";
$message .= "<p>City information: { lat: {$latitude}, lon: {$longitude}, name: '{$city}, {$country}' }</p></body></html>";

// Set the response headers
header('Content-Type: text/html');

// Send the response
echo $message;
?>
