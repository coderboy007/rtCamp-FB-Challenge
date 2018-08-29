<?php
if (!session_id()) {
    session_start();
}
//Include & Initialize FBMethods function file.
include_once("FBMethods.php");
$FBmethods = new FBmethods();
//Initialize common function file.
$globfun = new GlobalFunctions();
if (!empty($_POST['method'])) {
    if ($_POST['method'] == "getAlbumImages") {
        $albumId = $_POST['albumId'];
        if (!empty($albumId)) {
            $graphEdge = $FBmethods->getAlbumData($albumId);
            $slider_html = "<div class='slideshow-container'>";
            foreach ($graphEdge as $graphNode) {
                $img_caption = (@$graphNode['name']) ? ($globfun->content_substr($graphNode['name'], 100)) : ' ';
                $slider_html .= "<div class='mySlides fade'>
                    <img src='".$graphNode['images'][0]['source']."'>
                    <div class='text'>".$img_caption."</div>
                </div>";
            }
            $slider_html .= "<a class='prev' onclick='plusSlides(-1)'>❮</a>
                            <a class='next' onclick='plusSlides(1)'>❯</a>
                        </div>";
            echo $slider_html;
        }
    }elseif ($_POST['method'] == "generateAlbumZip") {
        $albumsId = json_decode($_POST['albumsId']);
        if (!empty($albumsId)) {
            $FBID = $_SESSION['fb_user']['FBID'];
            $album_data_location = "./assets/album/".$FBID."/";
            $globfun->removeDir($album_data_location);
            foreach ($albumsId as $key => $value) {
                $albumsDatas = explode(',', $value);
                $albumId = $albumsDatas[0];
                $albumName = $albumsDatas[1];
                $create_dir = $album_data_location.$albumName."/";
                if (is_dir($create_dir)) {
                    $create_dir = $album_data_location.$albumName.time()."/";
                }
                @mkdir($create_dir, 0777, true) || exit("Can't Create folder");
                $graphEdge = $FBmethods->getAllAlbumData($albumId);
                $i = 0;
                foreach ($graphEdge as $graphNode) {
                    $photoUrl = $graphNode['img_url'];
                    $complete_Save_location = $create_dir.'img'.$i.'.jpg';
                    file_put_contents($complete_Save_location, file_get_contents($photoUrl));
                    $i++;
                }
            }
            $destination = "./assets/album/zip/".$FBID.".zip";
            if (file_exists($destination)) {
                /** @scrutinizer ignore-unhandled */ @unlink($destination);
            }
            $globfun->zippingProcess($album_data_location, $destination);
            echo "<div class='dwn-zip'><a href='$destination' class='btn btn-primary rm-dwn-zip'>Download Album ZIP</a></div>";
        }
    }elseif ($_POST['method'] == "deleteZipAndDir") {
        $FBID = $_SESSION['fb_user']['FBID'];
        if (!empty($FBID)) {
            /*$destination = "./assets/album/zip/" . $FBID . ".zip";
            if (file_exists($destination)) {
                @unlink($destination);
            }*/
            $album_data_location = "./assets/album/".$FBID."/";
            $globfun->removeDir($album_data_location);
        }
    }elseif ($_POST['method'] == "isGoogleLogin") {
        if (!empty($_SESSION['google_user']['gd_access_token'])) {
            echo /** @scrutinizer ignore-type */ true;
        }
        echo /** @scrutinizer ignore-type */ false;
    }elseif ($_POST['method'] == "GoogleLoginURL") {
        $googleLoginURL = $globfun->home_url()."/FBmethods/googleLogin";
        echo "<div class='dwn-zip'><a href='$googleLoginURL' class='btn btn-primary rm-dwn-zip'>Log in with Google</a></div>";
    }elseif ($_POST['method'] == "AlbumMoveToDrive") {
        $albumsId = json_decode($_POST['albumsId']);
        if (!empty($albumsId)) {
            $FBmethods->albumMoveToDrive($albumsId);
        }
    }
    else {
        echo "No Method found here.";
    }
}
?>