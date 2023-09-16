<!DOCTYPE html>
<html>
<head>
    <title>Random Video Player</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #000;
        }

        #videoContainer {
            max-width: 800px;
        }
    </style>
</head>
<body>
    <div id="videoContainer">
        <video id="videoPlayer" width="100%" height="100%" controls autoplay>
            <source id="videoSource" src="" type="video/mp4">
        </video>
    </div>

    <script>
        // Function to set a new random video source and play it
        function playRandomVideo(videoList) {
            // Get a random video from the list
            const randomVideoIndex = Math.floor(Math.random() * videoList.length);
            const randomVideoName = videoList[randomVideoIndex];

            // Build the video URL
            const videoUrl = `https://chaye.one/02_douyVideo/${randomVideoName}`;

            // Set the new video source and play it
            const videoPlayer = document.getElementById('videoPlayer');
            const videoSource = document.getElementById('videoSource');
            videoSource.setAttribute('src', videoUrl);
            videoPlayer.load();

            // After the video has loaded, get its natural width and height
            videoPlayer.addEventListener('loadedmetadata', () => {
                const naturalWidth = videoPlayer.videoWidth;
                const naturalHeight = videoPlayer.videoHeight;

                // Check screen size and set video dimensions accordingly
                const screenWidth = window.innerWidth;
                const screenHeight = window.innerHeight;
                if (screenWidth < screenHeight) {
                    // On mobile (height > width)
                    videoPlayer.style.width = '680px';
                    videoPlayer.style.height = 'auto';
                } else {
                    // On desktop (height < width)
                    const aspectRatio = naturalWidth / naturalHeight;

                    if (aspectRatio > 1) {
                        videoPlayer.style.width = '600px';
                        videoPlayer.style.height = 'auto';
                    } else {
                        videoPlayer.style.width = '360px';
                        videoPlayer.style.height = 'auto';
                    }
                }

                // Play the video
                videoPlayer.play();
            });
        }

        // Initialize the video player
        window.onload = function () {
            // Define the video list directly in JavaScript (serverVideoList)
            var serverVideoList = <?php
                $videoDirectory = '/home/01_html/02_douyVideo';
                $videoList = [];

                // Open the directory
                if ($handle = opendir($videoDirectory)) {
                    while (false !== ($entry = readdir($handle))) {
                        // Check if the entry is a file and ends with ".mp4"
                        if (is_file($videoDirectory . '/' . $entry) && pathinfo($entry, PATHINFO_EXTENSION) == 'mp4') {
                            $videoList[] = $entry;
                        }
                    }
                    closedir($handle);
                }

                // Convert the PHP array to a JavaScript array
                echo json_encode($videoList);
            ?>;

            if (serverVideoList.length > 0) {
                // Add event listener to play the next random video when the current one ends
                const videoPlayer = document.getElementById('videoPlayer');
                videoPlayer.addEventListener('ended', () => playRandomVideo(serverVideoList));

                // Start playing the first random video
                playRandomVideo(serverVideoList);
            } else {
                console.error('No videos found.');
            }
        };
    </script>
</body>
</html>
