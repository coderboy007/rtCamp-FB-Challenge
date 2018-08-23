<?php
if(!session_id()) {
    session_start();
}
//Add the facebook dependency.
require_once( 'lib/fb/vendor/autoload.php' );
//Add the Google dependency.
require_once( 'lib/google/vendor/autoload.php' );
//Include common function file.
include_once("GlobalFunctions.php");
//Create Class FBmethods.
class FBmethods{

    // Declared Constant Variables.
    // Replace {app-id} with your app id
    const app_id = 'xxxxxxxx';
    // Replace {app-secret} with your app secret
    const app_secret = 'xxxxxxxxxxxxxxxxx';

    public function __construct(){
        //set ini settings value
        ini_set('max_execution_time', 9999999);
        ini_set('memory_limit','9999M');
        ini_set('upload_max_filesize', '500M');
        ini_set('max_input_time', '-1');
        ini_set('max_execution_time', '-1');
        //Dependency Injection to inject common function file
        $this->globalfunctions = new GlobalFunctions();
        //Dependency Injection to inject Google_Client
        $this->client = new Google_Client();
        $this->client->setAuthConfig('client_secret.json');
        if(empty($_SESSION['google_user']['gd_access_token'])) {
            $this->client->setRedirectUri($this->globalfunctions->home_url() . "/FBmethods/googleLogin");
        }
        $this->client->addScope(Google_Service_Drive::DRIVE);
    }

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

    public function getLoginURL(){
        $FBHelperData = $this->FBHelper();
        $helper = $FBHelperData['helper'];
        $permissions = ['email','public_profile','user_photos']; // Optional permissions
        $actual_link = $this->globalfunctions->home_url()."/FBmethods/getLoginToken";
        return $loginUrl = $helper->getLoginUrl($actual_link, $permissions);
    }

    public function getLoginToken(){
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
            $loginUrl = $helper->getLoginUrl();
            header("Location: ".$loginUrl);
        }
    }

    public function LogOut(){
        unset($_SESSION['fb_user']);
        header("Location: ../login.php");
    }

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
            $fbId = $graphObject->getProperty('id'); // To Get Facebook user ID
            $fbFullName = $graphObject->getProperty('name'); // To Get Facebook user full name
            $fbProImage = $graphObject->getProperty('picture'); // To Get Facebook user profile picture
            $fbalbums = $graphObject->getProperty('albums'); // To Get Facebook user Albums
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

    public function getAlbumData($albumId){
        $accessToken = $_SESSION['fb_user']['fb_access_token'];
        $FBHelperData = $this->FBHelper();
        $fb = $FBHelperData['fb'];
        $request = $fb->get("$albumId/photos?fields=images,name,album&limit=500", $accessToken);
        return $request->getGraphEdge();
    }

    public function googleLogin(){
        if (! isset($_GET['code'])) {
            $auth_url = $this->client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        } else {
            $this->client->authenticate($_GET['code']);
            $token_array = $this->client->getAccessToken();
            $_SESSION['google_user']['gd_access_token'] = $token_array['access_token'];
            $redirect_uri = $this->globalfunctions->home_url()."/index.php";
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    public function albumMoveToDrive($albumsId){
        $UserName = $this->globalfunctions->StrRegularExp($_SESSION['fb_user']['FBFullName']);
        $ParentFolder = "facebook_".$UserName."_albums";
        /*Make Parent folder in google drive */
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
            $graphEdge = $this->getAlbumData($albumId);
            $i = 0;
            foreach ($graphEdge as $graphNode) {
                $photoUrl=$graphNode['images'][0]['source'];
                $fileMetadata = new Google_Service_Drive_DriveFile(array(
                    'name' => 'img'.$i.'jpg',
                    'parents' => array($ChildFolderID->id)
                ));
                $file = $drive->files->create($fileMetadata, array(
                    'data' => file_get_contents($photoUrl),
                    'mimeType' => 'image/jpeg',
                    'uploadType' => 'multipart',
                    'fields' => 'id'));
                $i++;
            }
        }
    }

    private function MyTest(){
        //print_r($_POST);
        //echo "Hello World";
        //echo $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }
}
// Initiate Library
$FBmethods = new FBmethods;
$FBmethods->processFunc();
?>