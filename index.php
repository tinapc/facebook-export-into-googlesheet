<?php
session_start();

ini_set('max_execution_time', 600); //300 seconds = 5 minutes
ini_set('memory_limit ', '128M');

require_once 'vendor/autoload.php';
require_once 'Config.php';
require_once 'GoogleSheet.php';

use \Lib\Common;

$clientId = CLIENT_ID;
$clientSecret = CLIENT_SECRET;
$redirectUrl = REDIRECT_URL;

$client = new Google_Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectUrl);
$client->setScopes(array('https://spreadsheets.google.com/feeds'));

use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\ServiceRequestFactory;

$serviceRequest = new DefaultServiceRequest(Common::getGoogleTokenFromKeyFile());
ServiceRequestFactory::setInstance($serviceRequest);

$spreadsheetFeed = GoogleSheet::getAllSpreadSheetFeed();


/*
 * Facebook Area
 */
$fbApp = new Facebook\FacebookApp(FACEBOOK_API_KEY, FACEBOOK_API_SECRET);
$fb = new Facebook\Facebook([
		'app_id' => FACEBOOK_API_KEY,
		'app_secret' => FACEBOOK_API_SECRET,
		'default_graph_version' => 'v2.5',
]);

if (isset($_SESSION['facebook_access_token']) && $_SESSION['facebook_access_token']) {
	$fb_user_id = \Lib\FacebookApi::getMe()['id'];

	//\Lib\FacebookApi::getUserGroup($fb_user_id);

} else {
	$fb_user_id = '';

	$helper = $fb->getRedirectLoginHelper();
	$permissions = ['email', 'user_posts']; // optional
	$callback = FACEBOOK_CALLBACK;
	$loginUrl = $helper->getLoginUrl($callback, $permissions);
}


//handle fetch facebook feed and exporting into Google Sheet
if (isset($_POST['submit']) && $_POST['submit'] == 'Export')
{
	$post_spreadSheetFeed = $_POST['spreadsheet'];
	$post_workSheet = $_POST['worksheet'];
	$_post_fbID = $_POST['facebook_user_id'];

	$facebookListFeeds = \Lib\FacebookApi::getListFeeds($_post_fbID, 50); // 10 is limit value. We can change to the value we need

	//Insert data into Google Sheet
	GoogleSheet::addListRow($post_spreadSheetFeed, $post_workSheet, $facebookListFeeds);
}

?>

<!DOCTYPE html>
<html lang="">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Title Page</title>

	<!-- Bootstrap CSS -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>
<body>
<h1 class="text-center">Facebook Feed Into GoogleSheet</h1>
<?php if (!isset($_SESSION['facebook_access_token'])) : ?>
<p class="text-center">
	<a href="<?=$loginUrl?>">Log in with Facebook!</a>
</p>
<?php endif ?>
<div class="container">
	<div class="row">
		<div class="col-sm-6 col-sm-offset-3">
			<form action="" method="post">
				<div class="form-group">
					<label for="">Facebook User ID</label>
					<input type="text" name="facebook_user_id" id="facebook_user_id" class="form-control" value="<?=$fb_user_id?>">
				</div>

				<div class="form-group">
					<label for="">SpreadSheet</label>
					<select name="spreadsheet" id="spreadsheet" class="form-control">
						<option value="">Select SpreadSheet</option>
						<?php foreach ($spreadsheetFeed as $key => $value) : ?>
							<option value="<?=$spreadsheetFeed[$key]->getTitle()?>"><?=$spreadsheetFeed[$key]->getTitle()?></option>
						<?php endforeach ?>
					</select>
				</div>
				<div class="form-group">
					<label for="">WorkSheet</label>
					<select name="worksheet" id="worksheet" class="form-control">
						<option value="">Select WorkSheet</option>

					</select>
				</div>

				<input type="submit" value="Export" name="submit" class="btn btn-primary"/>
			</form>
		</div>
	</div>
</div>


<!-- jQuery -->
<script src="//code.jquery.com/jquery.js"></script>
<!-- Bootstrap JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

<script type="text/javascript" src="js/main.js"></script>

</body>
</html>
