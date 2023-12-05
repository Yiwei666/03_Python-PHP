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
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="shortcut icon" href="https://mctea.one/00_logo/google.png">
    <title>谷歌搜索</title>
    <style>
        body {
            background-color: #303030;
            color: #258fb8; /* Default color set to 蓝绿 */
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        form {
            background-color: #303030;
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
            color: #CCCCCC;
        }

        input[type="text"] {
            border: 1px solid #dcdcdc;
            font-size: 18px;
            height: 40px;
            padding: 0 10px;
            width: 100%;
            background-color: #303030;
            color: #CCCCCC;
        }

        input[type="submit"] {
            background-color: #303030; /* Set to the background color of the form */
            border: 1px solid #dcdcdc;
            color: #CCCCCC; /* Set to the desired font color */
            cursor: pointer;
            font-size: 18px;
            height: 40px;
            margin-top: 10px;
            padding: 0 20px;
        }

        input[type="submit"]:hover {
            background-color: #205c80;
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
        if(isset($_GET['query_keinsciSobereva'])) {
            $query_keinsciSobereva = $_GET['query_keinsciSobereva'];
            $keinsciSobereva_Url = 'https://www.google.com/search?q=site%3Akeinsci.com+OR+site%3Asobereva.com+' . $query_keinsciSobereva;
            echo "<script>window.location.replace('$keinsciSobereva_Url');</script>";
            exit;
        }
        if(isset($_GET['query_weixinSogouzhihu'])) {
            $query_weixinSogouzhihu = $_GET['query_weixinSogouzhihu'];
            $weixinSogouzhihu_Url = 'https://www.google.com/search?q=site%3Aweixin.sogou.com+OR+site%3Azhihu.com+' . $query_weixinSogouzhihu;
            echo "<script>window.location.replace('$weixinSogouzhihu_Url');</script>";
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
        if(isset($_GET['query_DouyinYoutubeTiktokBilibili'])) {
            $query_DouyinYoutubeTiktokBilibili = $_GET['query_DouyinYoutubeTiktokBilibili'];
            $DouyinYoutubeTiktokBilibili_Url = 'https://www.google.com/search?q=site%3Adouyin.com+OR+site%3Ayoutube.com+OR+site%3Atiktok.com+OR+site%3Abilibili.com+' . $query_DouyinYoutubeTiktokBilibili;
            echo "<script>window.location.replace('$DouyinYoutubeTiktokBilibili_Url');</script>";
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
        if(isset($_GET['query_今日头条'])) {
            $query_今日头条 = $_GET['query_今日头条'];
            $今日头条_Url = 'https://www.google.com/search?q=site%3Atoutiao.com+' . $query_今日头条;
            echo "<script>window.location.replace('$今日头条_Url');</script>";
            exit;
        }
        if(isset($_GET['query_ACS'])) {
            $query_ACS = $_GET['query_ACS'];
            $ACS_Url = 'https://www.google.com/search?q=site%3Aacs.org+' . $query_ACS;
            echo "<script>window.location.replace('$ACS_Url');</script>";
            exit;
        }
        if(isset($_GET['query_RSC'])) {
            $query_RSC = $_GET['query_RSC'];
            $RSC_Url = 'https://www.google.com/search?q=site%3Arsc.org+' . $query_RSC;
            echo "<script>window.location.replace('$RSC_Url');</script>";
            exit;
        }
        if(isset($_GET['query_ScienceDirect'])) {
            $query_ScienceDirect = $_GET['query_ScienceDirect'];
            $ScienceDirect_Url = 'https://www.google.com/search?q=site%3Asciencedirect.com+' . $query_ScienceDirect;
            echo "<script>window.location.replace('$ScienceDirect_Url');</script>";
            exit;
        }
        if(isset($_GET['query_AIPScitation'])) {
            $query_AIPScitation = $_GET['query_AIPScitation'];
            $AIPScitation_Url = 'https://www.google.com/search?q=site%3Aaip.org+OR+site%3Ascitation.org+' . $query_AIPScitation;
            echo "<script>window.location.replace('$AIPScitation_Url');</script>";
            exit;
        }
        if(isset($_GET['query_Springer'])) {
            $query_Springer = $_GET['query_Springer'];
            $Springer_Url = 'https://www.google.com/search?q=site%3Aspringer.com+' . $query_Springer;
            echo "<script>window.location.replace('$Springer_Url');</script>";
            exit;
        }
        if(isset($_GET['query_Wiley'])) {
            $query_Wiley = $_GET['query_Wiley'];
            $Wiley_Url = 'https://www.google.com/search?q=site%3Awiley.com+' . $query_Wiley;
            echo "<script>window.location.replace('$Wiley_Url');</script>";
            exit;
        }
        if(isset($_GET['query_Cell'])) {
            $query_Cell = $_GET['query_Cell'];
            $Cell_Url = 'https://www.google.com/search?q=site%3Acell.com+' . $query_Cell;
            echo "<script>window.location.replace('$Cell_Url');</script>";
            exit;
        }
        if(isset($_GET['query_TaylorFrancis'])) {
            $query_TaylorFrancis = $_GET['query_TaylorFrancis'];
            $TaylorFrancis_Url = 'https://www.google.com/search?q=site%3Atandfonline.com+' . $query_TaylorFrancis;
            echo "<script>window.location.replace('$TaylorFrancis_Url');</script>";
            exit;
        }
    ?>

    <form method="GET" target="_blank">
        <label for="query">搜索:</label>
        <input type="text" name="query" id="query" placeholder="在 Reddit、Quora 和 V2EX 中搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_keinsciSobereva">在 keinsci、Sobereva 中搜索:</label>
        <input type="text" name="query_keinsciSobereva" id="query_keinsciSobereva" placeholder=" keinsci、Sobereva 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_weixinSogouzhihu">在 weixinSogou、zhihu 中搜索:</label>
        <input type="text" name="query_weixinSogouzhihu" id="query_weixinSogouzhihu" placeholder=" weixinSogou、zhihu 搜索">
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
    <form method="GET">
        <label for="query_DouyinYoutubeTiktokBilibili">在 Douyin、Youtube、Tiktok、Bilibili 中搜索:</label>
        <input type="text" name="query_DouyinYoutubeTiktokBilibili" id="query_DouyinYoutubeTiktokBilibili" placeholder=" Douyin、Youtube、Tiktok、Bilibili 搜索">
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
    <form method="GET">
        <label for="query_今日头条">在 今日头条 中搜索:</label>
        <input type="text" name="query_今日头条" id="query_今日头条" placeholder=" 今日头条 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_ACS">在 ACS 中搜索:</label>
        <input type="text" name="query_ACS" id="query_ACS" placeholder=" ACS 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_RSC">在 RSC 中搜索:</label>
        <input type="text" name="query_RSC" id="query_RSC" placeholder=" RSC 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_ScienceDirect">在 ScienceDirect 中搜索:</label>
        <input type="text" name="query_ScienceDirect" id="query_ScienceDirect" placeholder=" ScienceDirect 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_AIPScitation">在 AIP、Scitation 中搜索:</label>
        <input type="text" name="query_AIPScitation" id="query_AIPScitation" placeholder=" AIP、Scitation 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_Springer">在 Springer 中搜索:</label>
        <input type="text" name="query_Springer" id="query_Springer" placeholder=" Springer 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_Wiley">在 Wiley 中搜索:</label>
        <input type="text" name="query_Wiley" id="query_Wiley" placeholder=" Wiley 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_Cell">在 Cell 中搜索:</label>
        <input type="text" name="query_Cell" id="query_Cell" placeholder=" Cell 搜索">
        <input type="submit" value="搜索">
    </form>
    <form method="GET">
        <label for="query_TaylorFrancis">在 TaylorFrancis 中搜索:</label>
        <input type="text" name="query_TaylorFrancis" id="query_TaylorFrancis" placeholder=" TaylorFrancis 搜索">
        <input type="submit" value="搜索">
    </form>
</body>
</html>
