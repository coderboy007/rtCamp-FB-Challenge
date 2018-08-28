<?php
if (!session_id()) {
    session_start();
}
if (!isset($_SESSION['fb_user']['fb_access_token']) && empty($_SESSION['fb_user']['fb_access_token'])) {
    header("Location: login.php");
}
//Include & Initialize FBMethods function file.
include_once("FBMethods.php");
$FBMethods = new FBMethods();
//Initialize common function file.
$globfun = new GlobalFunctions();
$UserProfile = $FBMethods->UserProfile(); //Get FB User Profile
//echo "<pre>";
//print_r($_SESSION);
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
<body data-spy="scroll" data-target=".navbar-collapse">

<!-- preloader -->
<div class="preloader">
    <div class="load-status"></div>
</div>

<!-- header section -->
<header class="in-header">
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<img src="<?php echo $UserProfile['FBProImage']; ?>" class="img-responsive img-circle" alt="<?php echo $UserProfile['FBID']; ?>">
				<h1 class="ad-title"><?php echo $UserProfile['FBFullName']; ?> &nbsp;<a href="<?php echo $globfun->home_url().'/FBmethods/LogOut' ?>" class="log-button" role="button" data-toggle="tooltip" title="Logout"><i class="fa fa-sign-out"></i></a></h1>
			</div>
		</div>
	</div>
</header>

<!-- album photos section -->
<div class="container photo-section">
	<div class="row">
		<div class="col-md-12 col-sm-12">
            <?php
            if (!empty($UserProfile['FBAlbums']) && count($UserProfile['FBAlbums']) > 0) {
                foreach ($UserProfile['FBAlbums'] as $alb_val) {
            ?>
                <div class="col-md-4 col-sm-12 album-div">
                    <span class="count"><?php echo $alb_val['count']; ?></span>
                    <div class="gallery usr-alb" alb-id="<?php echo $alb_val['id']; ?>">
                        <img src="<?php echo $alb_val['picture']['url']; ?>"  width="100%" height="185">
                        <div class="desc"><?php echo $globfun->content_substr($alb_val['name'], 27); ?></div>
                    </div>
                    <div class="col-md-12 col-sm-12 alb-action">
                        <div class="col-md-2"><label><input type="checkbox" name="slct-alb" class="ischecked" value="<?php echo $alb_val['id'].','.$alb_val['name']; ?>"><span data-toggle="tooltip" title="Select Album"></span></label></div>
                        <div class="col-md-5"><button type="button" class="btn btn-success download-alb-btn" dwn_type="1" alb-id="<?php echo $alb_val['id'].','.$alb_val['name']; ?>">Download Album</button></div>
                        <div class="col-md-5"><button type="button" class="btn btn-success move-alb-btn" move_type="1" alb-id="<?php echo $alb_val['id'].','.$alb_val['name']; ?>">Move Album</button></div>
                    </div>
                </div>
            <?php 
                }
            } else {
            ?>
                <div class="col-md-12 col-sm-12 album-div"><div>User Albums Not Found.</div></div>
            <?php 
            } 
            ?>
		</div>
	</div>
    <div class="row">
        <div class="col-md-12 col-sm-12 div-action">
            <div class="col-md-3 col-sm-12"><button type="button" class="btn btn-primary btn-action dwn-slct-alb" dwn_type="2" disabled>Download Selected Album</button></div>
            <div class="col-md-3 col-sm-12"><button type="button" class="btn btn-primary btn-action dwn-all-alb" dwn_type="3">Download All Album</button></div>
            <div class="col-md-3 col-sm-12"><button type="button" class="btn btn-primary btn-action move-slct-alb" move_type="2" disabled>Move Selected Album To Google Drive</button></div>
            <div class="col-md-3 col-sm-12"><button type="button" class="btn btn-primary btn-action move-all-alb" move_type="3">Move All Album To Google Drive</button></div>
        </div>
    </div>
</div>


<!-- album photos slider -->
<div class="overlay">
    <a href="javascript:void(0)" class="closebtn">×</a>
    <div class="overlay-content">
        <div class ="alb-images-slider">
            <div class="alb-preloader">
                <div class="alb-status"></div>
            </div>
        </div>
    </div>
</div>

<!-- zip processbar -->
<div class="overlay-process">
    <a href="javascript:void(0)" class="overlay-closebtn">×</a>
    <div class="overlay-content-process">
        <div class="col-md-3 col-sm-12 zip-process-bar">
            <img src="assets/images/pics/zipprocess.gif"><span></span>
        </div>
    </div>
</div>

<!-- footer section -->
<footer class="in-footer">
    <div class="pull-right">
        <button class="btn scroll-top"><i class="fa fa-angle-up fa-2"></i></button>
    </div>
	<div class="container">
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<p>rtCamp FB Challenge</p>
			</div>
		</div>
	</div>
</footer>

<!-- js -->	
<script src="assets/js/jquery.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/custom.js"></script>

</body>
</html>