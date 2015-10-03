<?php

if(!function_exists('userErrorHandler'))
{
	error_reporting(0);
	ini_set('display_errors', false);
	function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
	{
		echo("<p><font color='#ffffff'>Error: " . $errmsg . "</font></p>");
	}
	$old_error_handler = set_error_handler("userErrorHandler");
}

?>
<html>
<head>
<title>Klarity Menu</title>
<META http-equiv="Content-Type" content="text/html;charset=utf-8" >
<LINK href="klarity.css" type="text/css" rel="stylesheet" >
</head>
<body bgcolor="#400040">

<script type="text/javascript"><!--
google_ad_client = "pub-2131800239178007";
/* 468x15, created 4/26/08 */
google_ad_slot = "5631702945";
google_ad_width = 468;
google_ad_height = 15;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>

<?php

$query = 'http://rss.news.yahoo.com/rss/tech';
if(isset($_GET['q']))
{
	$query = trim($_GET['q']);
}
if(isset($_POST['q']))
{
	$query = trim($_POST['q']);
}

require("../klarity.php");

if((strcmp($query, '') != 0) && (strpos($query, 'http://') == 0))
{
	try
	{

		$klarity = new Klarity_Class();
		$klarity->showFeedHeadLinks = false;
		$klarity->showFeedHeadDescription = false;
		$klarity->linkTarget = "klarity_content";

		if(PHP_VERSION <= "5.0.4") $klarity->oldFix = true;

		$klarity->showFile($query);

	} catch (Exception $e)
	{
		echo("<p align='center'><font color='#ffffff'>Failed!</font></p>");
	}
}

?>

</body>
</html>