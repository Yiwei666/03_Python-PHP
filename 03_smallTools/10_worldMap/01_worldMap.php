<!DOCTYPE html>
<html>
<head>
    <title>World Map with Marked Places</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 100vh;
            width: 100%;
        }

        #location-list {
            position: absolute;
            bottom: 10px;              /*bottom*/
            left: 10px;             /*right*/
            z-index: 1000;
            background: rgba(255, 255, 255, 0.6); /* 0.1 is the alpha (transparency) value, 0 is 100 % transparency */
            padding: 10px;
            border-radius: 5px;
            font-size: 12px; /* Adjust the font size as needed */
            line-height: 1.2; /* Adjust the line height as needed */
        }


        .custom-tooltip {
            background: rgba(255, 255, 255, 0.9);; /* Transparent background */
            border: none; /* No border */
            padding: 3px; /* Adjust the padding as needed */
            border-radius: 3px; /* Adjust the border-radius as needed */
        }

    </style>
</head>
<body>
    <div id="map"></div>
    <div id="location-list"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var locations = [
            { lat: 40.7421, lon: -74.0138, name: 'North Bergen, US, do3-1' },
            { lat: 37.7749, lon: -122.4194, name: 'Santa Clara, US, do3-2' },
            { lat: 1.3036, lon: 103.8554, name: 'Singapore, do1-1' },
            { lat: 40.7930, lon: -74.0247, name: 'North Bergen, US, do1-2' },
            { lat: 39.0438, lon: -77.4874, name: 'Ashburn, US, aws1-2' },
            { lat: 45.8499, lon: -119.6322, name: 'Boardman, US, aws1-3' },
            { lat: 37.7594, lon: -77.1068, name: 'Tappahannock, US, az1-1' },
            { lat: 33.4484, lon: -112.0740, name: 'Phoenix, US, az1-2' },
            { lat: 35.6895, lon: 139.6917, name: 'Tokyo, Japan, az5-1' },
            { lat: 52.3676, lon: 4.9041, name: 'Amsterdam, Netherlands, az6-1' },
            { lat: 34.0544, lon: -118.2441, name: 'Los Angeles, US, cc1-1' },
            { lat: 39.9906, lon: 116.2887, name: 'Haidian, CN' }
        ];

        var map = L.map('map').setView([20, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var locationList = document.getElementById('location-list');

        locations.forEach(function (location, index) {
            var marker = L.marker([location.lat, location.lon]).addTo(map);
            marker.bindPopup(location.name);

            // Display the location numbers on the markers with custom tooltip styles
            marker.bindTooltip((index + 1).toString(), { permanent: true, className: 'custom-tooltip' });

            // Display the location names with numbers in the bottom left corner
            locationList.innerHTML += '<p>' + (index + 1) + '. ' + location.name + '</p>';
        });
    </script>
</body>
</html>
