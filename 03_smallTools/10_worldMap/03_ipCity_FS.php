<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map with Marked Places</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        #map {
            height: 100vh;
            width: 100%;
            position: absolute;
            top: 0;
            left: 0;
        }

        .info {
            position: absolute;
            bottom: 10px;              /*bottom*/
            left: 10px;             /*right*/
            z-index: 1000;
            background: rgba(255, 255, 255, 0.3); /* 0.3 is the alpha (transparency) value, 0 is 100 % transparency */
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <?php
    // Get IP address from the request
    $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];

    // Get latitude and longitude from ipapi.co
    $lat = file_get_contents("https://ipapi.co/{$ip}/latitude/");
    $lon = file_get_contents("https://ipapi.co/{$ip}/longitude/");

    // Get location information based on IP address from ipapi.co
    $country = file_get_contents("https://ipapi.co/{$ip}/country_name/");
    $city = file_get_contents("https://ipapi.co/{$ip}/city/");

    // Get current time in Beijing time
    $beijingOffset = 8; // UTC+8
    $date = new DateTime('now', new DateTimeZone('UTC'));
    $date->modify('+' . $beijingOffset . ' hours');
    ?>

    <div id="map"></div>

    <div class="info">
        <p>Your IP address is: <?php echo $ip; ?></p>
        <p>Your location is: <?php echo $country; ?>, <?php echo $city; ?></p>
        <p>The current time is: <?php echo $date->format('Y/m/d H:i:s'); ?></p>
        <p>City information: { lat: <?php echo $lat; ?>, lon: <?php echo $lon; ?>, name: '<?php echo $city; ?>, <?php echo $country; ?>' }</p>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var cityInformation = {
            lat: <?php echo $lat; ?>,
            lon: <?php echo $lon; ?>,
            name: '<?php echo $city; ?>, <?php echo $country; ?>'
        };

        var map = L.map('map').setView([cityInformation.lat, cityInformation.lon], 10);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var locations = [cityInformation];

        locations.forEach(function(location) {
            var marker = L.marker([location.lat, location.lon]).addTo(map);
            marker.bindPopup(location.name);
        });

        // Adjust map height to 100% of the screen height
        document.body.style.height = '100vh';
    </script>
</body>

</html>
