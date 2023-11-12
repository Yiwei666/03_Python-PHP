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
    </style>
</head>
<body>
    <div id="map"></div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var locations = [
            { lat: 37.7749, lon: -122.4194, name: 'Santa Clara, US' },
            { lat: 40.8584, lon: -74.1638, name: 'Clifton, US' },
            { lat: 1.3521, lon: 103.8198, name: 'Singapore' },
            { lat: 40.7934, lon: -74.0247, name: 'North Bergen, US' },
            { lat: 39.0438, lon: -77.4874, name: 'Ashburn, US' },
            { lat: 37.7594, lon: -77.1068, name: 'Tappahannock, US' },
            { lat: 33.4484, lon: -112.0740, name: 'Phoenix, US' },
            { lat: 35.6895, lon: 139.6917, name: 'Tokyo, Japan' },
            { lat: 52.3676, lon: 4.9041, name: 'Amsterdam, Netherlands' },
            { lat: 39.9906, lon: 116.2887, name: 'Haidian, CN' }
        ];

        var map = L.map('map').setView([20, 0], 2);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        locations.forEach(function (location) {
            var marker = L.marker([location.lat, location.lon]).addTo(map);
            marker.bindPopup(location.name);
        });
    </script>
</body>
</html>
