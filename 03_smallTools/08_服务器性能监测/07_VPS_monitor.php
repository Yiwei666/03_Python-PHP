<!DOCTYPE html>
<html>
<head>
  <title>Server Performance Monitor</title>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.5.0/chart.min.js"></script>
  <style>
    /* CSS style to control the size of the chart container */
    #chartContainer {
      width: 300px;
      height: 600px;
    }
  </style>
</head>
<body>
  <!-- Container with ID "chartContainer" to control chart size -->
  <div id="chartContainer">
    <canvas id="performanceChart" width="100%" height="100%"></canvas>
  </div>

  <script>
    // Initialize the chart and bind it to 'window.performanceChart' variable
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: [], // Empty labels array for dynamic updates
        datasets: [
          {
            label: 'CPU Usage (%)',
            data: [],
            borderColor: 'red',
            fill: false,
          },
          {
            label: 'Memory Usage (%)',
            data: [],
            borderColor: 'blue',
            fill: false,
          },
        ],
      },
    });

    // Bind the chart object to a global variable for later access
    window.performanceChart = performanceChart;

    // Function to update chart with new data
    function updateChart(data) {
      const chart = window.performanceChart;

      // Update the chart data with new CPU and Memory usage values
      chart.data.labels.push(data.timestamp);
      chart.data.datasets[0].data.push(data.cpu);
      chart.data.datasets[1].data.push(data.memory);

      // Limit the number of data points shown on the chart (e.g., show last 10 points)
      const maxDataPoints = 100;
      if (chart.data.labels.length > maxDataPoints) {
        chart.data.labels.shift();
        chart.data.datasets[0].data.shift();
        chart.data.datasets[1].data.shift();
      }

      // Update the chart
      chart.update();
    }

    // Function to fetch server performance data
    function fetchServerData() {
      // Replace '07_server_data.php' with the actual URL path
      fetch('07_server_data.php')
        .then(response => response.json())
        .then(data => updateChart(data))
        .catch(error => console.error('Error fetching data:', error));
    }

    // Update the chart every 5 seconds
    setInterval(fetchServerData, 1000);

    // Initial data fetch on page load
    fetchServerData();
  </script>
</body>
</html>
