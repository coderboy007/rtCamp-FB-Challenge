<?php
if(!session_id()) {
    session_start();
}
//Add the facebook dependency.
require_once( 'lib/fb/vendor/autoload.php' );
//Add the Google dependency.
require_once( 'lib/google/vendor/autoload.php' );
//Include common function file.
include_once('GlobalFunctions.php');
//Include gearman library
require_once('Lib_gearman.php');
//Create Class FBmethods.
class FBmethods{

    // Declared Constant Variables.
    // Replace {app-id} with your app id
    const app_id = '2211756065735532';
    // Replace {app-secret} with your app secret
    const app_secret = '940d1000c3b9a74eb6f06e70c0042660';
    public $main_arr = array();
    public function __construct(){
        //Dependency Injection to inject common function file
        $this->globalfunctions = new GlobalFunctions();
        $this->globalfunctions->ini_Settings();
        //Dependency Injection to inject Google_Client
        $this->client = new Google_Client();
        $this->client->setAuthConfig('client_secret.json');
        if(empty($_SESSION['google_user']['gd_access_token'])) {
            $this->client->setRedirectUri($this->globalfunctions->home_url() . "/FBmethods/googleLogin");
        }
        $this->client->addScope(Google_Service_Drive::DRIVE);
    }

    /**
     * used to set fbhelper
     * @return $data
     */
    public function FBHelper(){
        $data = array();
        $fb = new Facebook\Facebook([
            'app_id' => self::app_id,
            'app_secret' => self::app_secret,
            'default_graph_version' => 'v3.1',
        ]);
        $data['fb'] = $fb;
        $data['helper'] = $fb->getRedirectLoginHelper();
        return $data;
    }

    /**
     * for call the method which is requested.
     */
    public function processFunc(){
        $REQUEST_URI = $_SERVER['REQUEST_URI'];
        $REQUEST_URI_array = explode('?',$REQUEST_URI); //explode with ? mark
        $REQUEST_URI_array = explode('/',$REQUEST_URI_array[0]); // explode with forward slash & remove get parameter
        $methodname = end($REQUEST_URI_array); //get URI last segment
        $methodname = strtolower($methodname); //get 1st array element as a method name.
        if ((int) method_exists($this, $methodname) > 0){ // check method is exist or not
            $this->$methodname();
        }
    }

    /**
     * get fb login url
     * @return loginUrl
     */
    public function getLoginURL(){
        $FBHelperData = $this->FBHelper();
        $helper = $FBHelperData['helper'];
        $permissions = ['email','public_profile','user_photos']; // Optional permissions
        $actual_link = $this->globalfunctions->home_url()."/FBmethods/getLoginToken";
        return  $helper->getLoginUrl($actual_link, $permissions);
    }

    /**
     * get access token from $helper
     */
    public function getLoginToken(){
        if(!empty($_GET['error_code'])){
            header("Location: ../index.php");
        }
        $FBHelperData = $this->FBHelper();
        $helper = $FBHelperData['helper'];
        $accessToken = $helper->getAccessToken();
        if (! isset($accessToken)) {
            exit;
        }
        // Logged in
        // The OAuth 2.0 client handler helps us manage access tokens
        $fb = $FBHelperData['fb'];
        $oAuth2Client = $fb->getOAuth2Client();
        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(self::app_id);
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();
        if (! $accessToken->isLongLived()) {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
        }
        $_SESSION['fb_user']['fb_access_token'] = (string) $accessToken;
        if ( isset( $accessToken ) ) {
            header("Location: ../index.php");
        }else{
            /** @scrutinizer ignore-call */
            $loginUrl = $helper->getLoginUrl();
            header("Location: ".$loginUrl);
        }
    }

    /**
     * logout user from application and flush session.
     */
    public function LogOut(){
        unset($_SESSION['fb_user']);
        header("Location: ../login.php");
    }

    /**
     * get userprofile as album,profile image,user ID, user name.
     * @return $ResponseData
     */
    public function UserProfile(){
        $ResponseData = array(); //Initialize the response array.
        $FBHelperData = $this->FBHelper();
        $fb = $FBHelperData['fb'];
        $accessToken = $_SESSION['fb_user']['fb_access_token'];
        $_fb = new Facebook\FacebookApp(self::app_id, self::app_secret);
        $request = new Facebook\FacebookRequest( $_fb,$accessToken, 'GET', '/me?fields=id,name,picture.type(large),albums{count,description,name,picture.type(album)}' );
        $response = $fb->getClient()->sendRequest($request);
        $graphObject = $response->getGraphNode();
        if(!empty($graphObject)){
            $fbId = /** @scrutinizer ignore-deprecated */ $graphObject->getProperty('id'); // To Get Facebook user ID
            $fbFullName = /** @scrutinizer ignore-deprecated */ $graphObject->getProperty('name'); // To Get Facebook user full name
            $fbProImage = /** @scrutinizer ignore-deprecated */ $graphObject->getProperty('picture'); // To Get Facebook user profile picture
            $fbalbums = /** @scrutinizer ignore-deprecated */ $graphObject->getProperty('albums'); // To Get Facebook user Albums
            /* ---- fbId Stored Into Session -----*/
            $_SESSION['fb_user']['FBID'] = $fbId;
            $_SESSION['fb_user']['FBFullName'] = $fbFullName;

            $ResponseData['FBID'] = $fbId;
            $ResponseData['FBFullName'] = $fbFullName;
            $ResponseData['FBAlbums'] = $fbalbums;
            $ResponseData['FBProImage'] = $fbProImage['url'];
        }
        return $ResponseData;
    }

    /**
     * @param $albumId
     * @return albumData
     */
    public function getAlbumData($albumId){
        $accessToken = $_SESSION['fb_user']['fb_access_token'];
        $FBHelperData = $this->FBHelper();
        $fb = $FBHelperData['fb'];
        $request = $fb->get("$albumId/photos?fields=images,name,album&limit=500", $accessToken);
        return $request->getGraphEdge();
    }

    /**
     * get all images of single album using $albumId
     * @param $albumId
     * @return $response
     */
    public function getAllAlbumData($albumId){
        global $main_arr;
        $accessToken = $_SESSION['fb_user']['fb_access_token'];
        $FBHelperData = $this->FBHelper();
        $fb = $FBHelperData['fb'];
        $request = $fb->get("$albumId/photos?fields=images,name,album&limit=100", $accessToken);
        $arr_alb = $request->getGraphEdge();
        $i = 0;
        $response = $this->getAlbumsArray($arr_alb,$i);
        if(!empty($response)){
            $main_arr = array();
            return $response;
        }else{
            return array();
        }
    }

    /**
     * get recursively all images of single album
     * @param $arr_alb
     * @param $i
     * @return $main_arr
     */
    public function getAlbumsArray($arr_alb,$i){
        global $main_arr;
        $FBHelperData = $this->FBHelper();
        $fb = $FBHelperData['fb'];
        foreach ($arr_alb as $arr_alb_val) {
            $main_arr[$i]['img_url'] = $arr_alb_val['images'][0]['source'];
            $i++;
        }
        $arr_albs = $fb->next($arr_alb);
        if(!empty($arr_albs)) {
            $this->getAlbumsArray($arr_albs, $i);
        }
        return $main_arr;
    }

    /**
     * google login
     */
    public function googleLogin(){
        if (! isset($_GET['code'])) {
            $auth_url = $this->client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        } else {
            /** @scrutinizer ignore-deprecated */ $this->client->authenticate($_GET['code']);
            $token_array = $this->client->getAccessToken();
            $_SESSION['google_user']['gd_access_token'] = $token_array['access_token'];
            $redirect_uri = $this->globalfunctions->home_url()."/index.php";
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    /**
     * albums move to google drive.
     * @param $albumsId
     */
    public function albumMoveToDrive($albumsId){
        if (class_exists('GearmanClient')) {
            $gearman = new Lib_gearman();
            $gearman->gearman_client();
            $gearman->do_job_background('albumMoveToGD', serialize(['sess' => $_SESSION, 'albsid' => $albumsId]));
        }
        else {
            echo "Gearman Does Not Support";
        }
        /*$UserName = $this->globalfunctions->StrRegularExp($_SESSION['fb_user']['FBFullName']);
        $ParentFolder = "facebook_".$UserName."_albums";
        //Make Parent folder in google drive
        $this->client->setAccessToken($_SESSION['google_user']['gd_access_token']);
        $drive = new Google_Service_Drive($this->client);
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $ParentFolder,
            'mimeType' => 'application/vnd.google-apps.folder'));
        $ParentFolderID = $drive->files->create($fileMetadata, array('fields' => 'id'));

        foreach ($albumsId as $key => $value){
            $albumsDatas = explode(',',$value);
            $albumId = $albumsDatas[0];
            $albumName = $albumsDatas[1];
            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => $albumName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($ParentFolderID->id)
            ));
            $ChildFolderID = $drive->files->create($fileMetadata, array('fields' => 'id'));
            $graphEdge = $this->getAllAlbumData($albumId);
            $i = 0;
            foreach ($graphEdge as $graphNode) {
                $photoUrl=$graphNode['img_url'];
                $fileMetadata = new Google_Service_Drive_DriveFile(array(
                    'name' => 'img'.$i.'jpg',
                    'parents' => array($ChildFolderID->id)
                ));
                $drive->files->create($fileMetadata, array(
                    'data' => file_get_contents($photoUrl),
                    'mimeType' => 'image/jpeg',
                    'uploadType' => 'multipart',
                    'fields' => 'id'));
                $i++;
            }
       }*/
    }

    /**
     * only testing purpose.
     */
    private function MyTest(){
        //print_r($_POST);
        echo "Hello World";
        //echo $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}
// Initiate Library
$FBmethods = new FBmethods;
$FBmethods->processFunc();
?>