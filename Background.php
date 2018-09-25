<?php
//Add the facebook dependency.
require_once( 'lib/fb/vendor/autoload.php' );
//Add the Google dependency.
require_once( 'lib/google/vendor/autoload.php' );
//Include common function file.
include_once('GlobalFunctions.php');
//Include gearman library
require_once('Lib_gearman.php');
class Background{
    // Declared Constant Variables.
    // Replace {app-id} with your app id
    const app_id = '2211756065735532';
    // Replace {app-secret} with your app secret
    const app_secret = '940d1000c3b9a74eb6f06e70c0042660';
    public $main_arr = array();

    public function worker() {
        $gearman = new Lib_gearman();
        $worker = $gearman->gearman_worker();
        $gearman->add_worker_function('albumMoveToGD', 'Background::albumMoveToGD');
        $gearman->add_worker_function('getAllAlbumData', 'Background::getAllAlbumData');
        $gearman->add_worker_function('getAlbumsArray', 'Background::getAlbumsArray');

        while ($gearman->work()) {
            if (!$worker->returnCode()) {
                echo "\n----------- " . date('y-m-d H:i:s') . " worker job done successfully---------\n";
            }
            if ($worker->returnCode() != GEARMAN_SUCCESS) {

                echo "return_code: " . $gearman->current('worker')->returnCode() . "\n";
                break;
            }
        }
    }

    public static function albumMoveToGD($job = null) {
	    //echo "\n \n ".date('y-m-d H:i:s')."---------------------backgrorund test start---------------------";
        $data = unserialize($job->workload());
        //print_r($data);
        $globalfunctions = new GlobalFunctions();
        $UserName = $globalfunctions->StrRegularExp($data['sess']['fb_user']['FBFullName']);
        $ParentFolder = "facebook_".$UserName."_albums";
        //Make Parent folder in google drive
        $client = new Google_Client();
        $client_json_path = realpath(dirname(__FILE__)).'/client_secret.json';
        $client->setAuthConfig($client_json_path);
        if(empty($data['sess']['google_user']['gd_access_token'])) {
            $client->setRedirectUri($globalfunctions->home_url() . "/FBmethods/googleLogin");
        }
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessToken($data['sess']['google_user']['gd_access_token']);
        $drive = new Google_Service_Drive($client);
        $fileMetadata = new Google_Service_Drive_DriveFile(array(
            'name' => $ParentFolder,
            'mimeType' => 'application/vnd.google-apps.folder'));
        $ParentFolderID = $drive->files->create($fileMetadata, array('fields' => 'id'));

        foreach ($data['albsid'] as $key => $value){
            $albumsDatas = explode(',',$value);
            $albumId = $albumsDatas[0];
            $albumName = $albumsDatas[1];
            $fileMetadata = new Google_Service_Drive_DriveFile(array(
                'name' => $albumName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => array($ParentFolderID->id)
            ));
            $ChildFolderID = $drive->files->create($fileMetadata, array('fields' => 'id'));
            $graphEdge = self::getAllAlbumData($albumId,$data['sess']);
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
        }
	    //echo "\n \n ".date('y-m-d H:i:s')."---------------------backgrorund test Done--------------------- \n \n";
    }

    public static function getAllAlbumData($albumId,$data){
        global $main_arr;
        $accessToken = $data['fb_user']['fb_access_token'];
        $fb = new Facebook\Facebook([
            'app_id' => self::app_id,
            'app_secret' => self::app_secret,
            'default_graph_version' => 'v3.1',
        ]);
        $request = $fb->get("$albumId/photos?fields=images,name,album&limit=100", $accessToken);
        $arr_alb = $request->getGraphEdge();
        $i = 0;
        $response = self::getAlbumsArray($arr_alb,$i);
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
    public static function getAlbumsArray($arr_alb,$i){
        global $main_arr;
        $fb = new Facebook\Facebook([
            'app_id' => self::app_id,
            'app_secret' => self::app_secret,
            'default_graph_version' => 'v3.1',
        ]);
        foreach ($arr_alb as $arr_alb_val) {
            $main_arr[$i]['img_url'] = $arr_alb_val['images'][0]['source'];
            $i++;
        }
        $arr_albs = $fb->next($arr_alb);
        if(!empty($arr_albs)) {
            self::getAlbumsArray($arr_albs, $i);
        }
        return $main_arr;
    }

}

$background = new Background();
$background->worker();
