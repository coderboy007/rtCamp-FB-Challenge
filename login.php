<?php
if (!session_id()) {
    session_start();
}
if (isset($_SESSION['fb_user']['fb_access_token']) && !empty($_SESSION['fb_user']['fb_access_token'])) {
    header("Location: index.php");
}
//Include & Initialize FBMethods function file.
include_once("FBMethods.php");
$FBMethods = new FBMethods();
$getFBLoginURL = $FBMethods->getLoginURL(); //Get FB Login url.
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <title>rtCamp FB Challenge</title>
    <link rel="icon" href="./assets/images/pics/rtcamp.png" sizes="16x16" type="image/png">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="keywords" content="rt, rtcamp, facebook challenge">
	<meta name="description" content="rtCamp facebook Challenge done by Amit Dudhat.">
	<!-- css -->
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">
	<link rel="stylesheet" href="assets/css/font-awesome.min.css">
	<link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>
    <div class="log-body">
        <div class="log-wrapper">
            <div class="fb-signin">
                <h2 class="ad-title">rtCamp FB Challenge</h2>
                <img src="./assets/images/pics/rtcamp.png" class="rt-logo" />
                <a href="<?php echo $getFBLoginURL ?>" class="btn btn-lg btn-primary btn-block">Log in with Facebook</a> 
            </div>
        </div>
    </div>
<!-- js -->	
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>