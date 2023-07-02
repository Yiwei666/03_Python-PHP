<?php
session_start();

// If the user is not logged in, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
  header('Location: login.php');
  exit;
}

// If the user clicked the logout link, log them out and redirect to the login page
if (isset($_GET['logout'])) {
  session_destroy(); // destroy all session data
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>谷歌搜索</title>
	<style>
		body {
			background-color: #f2f2f2;
			font-family: Arial, sans-serif;
			font-size: 14px;
			line-height: 1.5;
			margin: 0;
			padding: 0;
		}
		
		form {
			background-color: #fff;
			border: 1px solid #dcdcdc;
			box-shadow: 0 1px 1px rgba(0,0,0,0.1);
			margin: 50px auto;
			max-width: 700px;
			padding: 20px;
		}
		
		label {
			display: block;
			font-size: 18px;
			margin-bottom: 10px;
		}
		
		input[type="text"] {
			border: 1px solid #dcdcdc;
			font-size: 18px;
			height: 40px;
			padding: 0 10px;
			width: 100%;
		}
		
		input[type="submit"] {
			background-color: #f2f2f2;
			border: 1px solid #dcdcdc;
			color: #333;
			cursor: pointer;
			font-size: 18px;
			height: 40px;
			margin-top: 10px;
			padding: 0 20px;
		}
		
		input[type="submit"]:hover {
			background-color: #e6e6e6;
		}
	</style>
</head>
<body>
	<?php
		if(isset($_GET['query'])) {
			$query = $_GET['query'];
			$searchUrl = 'https://www.google.com/search?q=site%3Areddit.com+OR+site%3Aquora.com+OR+site%3Av2ex.com+' . $query;
			echo "<script>window.location.replace('$searchUrl');</script>";
			exit;
		}
		if(isset($_GET['v2ex_query'])) {
			$v2ex_query = $_GET['v2ex_query'];
			$v2exSearchUrl = 'https://www.google.com/search?q=site%3Av2ex.com+' . $v2ex_query;
			echo "<script>window.location.replace('$v2exSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['reddit_query'])) {
			$reddit_query = $_GET['reddit_query'];
			$redditSearchUrl = 'https://www.google.com/search?q=site%3Areddit.com+' . $reddit_query;
			echo "<script>window.location.replace('$redditSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['quora_query'])) {
			$quora_query = $_GET['quora_query'];
			$quoraSearchUrl = 'https://www.google.com/search?q=site%3Aquora.com+' . $quora_query;
			echo "<script>window.location.replace('$quoraSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['twitter_query'])) {
			$twitter_query = $_GET['twitter_query'];
			$twitterSearchUrl = 'https://www.google.com/search?q=site%3Atwitter.com+' . $twitter_query;
			echo "<script>window.location.replace('$twitterSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['wikipedia_query'])) {
			$wikipedia_query = $_GET['wikipedia_query'];
			$wikipediaSearchUrl = 'https://www.google.com/search?q=site%3Aen.wikipedia.org+OR+site%3Azh.wikipedia.org+' . $wikipedia_query;
			echo "<script>window.location.replace('$wikipediaSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['github_query'])) {
			$github_query = $_GET['github_query'];
			$githubSearchUrl = 'https://www.google.com/search?q=site%3Agithub.com+' . $github_query;
			echo "<script>window.location.replace('$githubSearchUrl');</script>";
			exit;
		}
		if(isset($_GET['query_ACSRSCsciencedirect'])) {
		    $query_ACSRSCsciencedirect = $_GET['query_ACSRSCsciencedirect'];
		    $ACSRSCsciencedirect_Url = 'https://www.google.com/search?q=site%3Aacs.org+OR+site%3Arsc.org+OR+site%3Asciencedirect.com+' . $query_ACSRSCsciencedirect;
		    echo "<script>window.location.replace('$ACSRSCsciencedirect_Url');</script>";
		    exit;
		}
		if(isset($_GET['query_NatureScience'])) {
		    $query_NatureScience = $_GET['query_NatureScience'];
		    $NatureScience_Url = 'https://www.google.com/search?q=site%3Anature.com+OR+site%3Ascience.org+' . $query_NatureScience;
		    echo "<script>window.location.replace('$NatureScience_Url');</script>";
		    exit;
		}
		if(isset($_GET['query_ScitationSpringerWiley'])) {
		    $query_ScitationSpringerWiley = $_GET['query_ScitationSpringerWiley'];
		    $ScitationSpringerWiley_Url = 'https://www.google.com/search?q=site%3Ascitation.org+OR+site%3Aspringer.com+OR+site%3Awiley.com+' . $query_ScitationSpringerWiley;
		    echo "<script>window.location.replace('$ScitationSpringerWiley_Url');</script>";
		    exit;
		}
		if(isset($_GET['cp2k_query'])) {
			$cp2k_query = $_GET['cp2k_query'];
			$cp2kSearchUrl = 'https://www.google.com/search?q=site%3Acp2k.org+' . $cp2k_query;
			echo "<script>window.location.replace('$cp2kSearchUrl');</script>";
			exit;
		}
	?>
	<form method="GET" target="_blank">
		<label for="query">搜索:</label>
		<input type="text" name="query" id="query" placeholder="在 Reddit、Quora 和 V2EX 中搜索">
		<input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
		<label for="v2ex_query">在 V2EX 中搜索:</label>
		<input type="text" name="v2ex_query" id="v2ex_query" placeholder="搜索 V2EX">
		<input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
		<label for="reddit_query">在 Reddit 中搜索:</label>
		<input type="text" name="reddit_query" id="reddit_query" placeholder="搜索 Reddit">
		<input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
		<label for="quora_query">在 Quora 中搜索:</label>
		<input type="text" name="quora_query" id="quora_query" placeholder="搜索 Quora">
		<input type="submit" value="搜索">
	</form>	
	<form method="GET" target="_blank">
		<label for="twitter_query">在 twitter 中搜索:</label>
		<input type="text" name="twitter_query" id="twitter_query" placeholder="搜索 twitter">
		<input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
		<label for="wikipedia_query">在 wikipedia 中搜索:</label>
		<input type="text" name="wikipedia_query" id="wikipedia_query" placeholder="搜索 wikipedia">
		<input type="submit" value="搜索">
	</form>	
	<form method="GET" target="_blank">
		<label for="github_query">在 github 中搜索:</label>
		<input type="text" name="github_query" id="github_query" placeholder="搜索 github">
		<input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
	    <label for="query_ACSRSCsciencedirect">搜索:</label>
	    <input type="text" name="query_ACSRSCsciencedirect" id="query_ACSRSCsciencedirect" placeholder="在 ACS、RSC、sciencedirect 中搜索">
	    <input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
	    <label for="query_NatureScience">搜索:</label>
	    <input type="text" name="query_NatureScience" id="query_NatureScience" placeholder="在 Nature、Science 中搜索">
	    <input type="submit" value="搜索">
	</form>
	<form method="GET">
	    <label for="query_ScitationSpringerWiley">在 Scitation、Springer、Wiley 中搜索:</label>
	    <input type="text" name="query_ScitationSpringerWiley" id="query_ScitationSpringerWiley" placeholder=" Scitation、Springer、Wiley 搜索">
	    <input type="submit" value="搜索">
	</form>
	<form method="GET" target="_blank">
		<label for="cp2k_query">在 cp2k 中搜索:</label>
		<input type="text" name="cp2k_query" id="cp2k_query" placeholder="搜索 cp2k">
		<input type="submit" value="搜索">
	</form>
</body>
</html>
