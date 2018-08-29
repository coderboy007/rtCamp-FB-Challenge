[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/coderboy007/rtCamp-FB-Challenge/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/coderboy007/rtCamp-FB-Challenge/?branch=master) [![Code Intelligence Status](https://scrutinizer-ci.com/g/coderboy007/rtCamp-FB-Challenge/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence) [![Build Status](https://scrutinizer-ci.com/g/coderboy007/rtCamp-FB-Challenge/badges/build.png?b=master)](https://scrutinizer-ci.com/g/coderboy007/rtCamp-FB-Challenge/build-status/master)
# Facebook-Photos Challenge

Application Demo Link - https://photoprimes.com

Platform - PHP

API Used - [Facebook Graph API SDK for PHP](https://github.com/facebook/php-graph-sdk), [Google APIs Client Library for PHP](https://github.com/google/google-api-php-client), [PHPUnit – The PHP Testing Framework](https://phpunit.de/)

Scripting Language : jQuery, AJAX

Styling -  Bootstrap CSS

## Part - 1 :

First the user visit's the application and there he/she has to log in to their Facebook account using their Facebook account credentials.The application will ask the user to authorize the application while they log into the system, in order to give the application access to the user's e-mail and photos .Once logged in, the user will be redirected to the "home_url" page where he/she can see their albums listed along with their respective album covers.

## Part - 2 :

Once on the home_url page, the user can view his/her albums. The albums are displayed with a name alongside the photo count for that particular album.

When you click on the album thumbnail , photos of that particular album will be displayed to the user in a full-screen carousel in an effective photo-gallery format.

A "Download Album" button is available for each album below the album name to download each album separately. When the user clicks on that button,an AJAX jQuery request is fired which will collect all the photos of that album, create a Zip file on server with all the photos and then the browser prompts the user with a download dialog asking the user as to where that file should be saved.

A checkbox is displayed before each "Download Album" button in case the user wants to download selected albums. A "Download Selected Album" button is displayed on bottom of all the albums listed. When clicked , it will work in a similar way as above when the user downloads a single album. Here ,more than one album will be mounted in a single Zip file on the server.

A "Download All Album" button is displayed bottom of the all listed albums. When clicked , an AJAX jQuery request is fired which will collect all the photos of all the albums. It will then create a Zip file with a folder for each album which will contain the photos of the respective album. The folder name will be the album name itself. Once the Zip file is generated, the browser will prompt the user with a download dialog asking the user as to where that file should be saved. The master Zip file will be of the format "Facebook User ID".

During the time the Zip and download process is going on, the user will be shown a nice pre-loader while the user awaits the file.

## Part - 3 :

The user can also move his/her albums to their respective Google Drive as well.

## How-To-Use :

**Using the Facebook Graph API SDK for PHP :**

The Facebook SDK for PHP provides developers with a modern, native library for accessing the Graph API and taking advantage of Facebook Login. Usually this means you're developing with PHP for a Facebook Canvas app, building your own website, or adding server-side functionality to an app.

More information and examples:  [https://developers.facebook.com/docs/reference/php](https://developers.facebook.com/docs/reference/php)

**Step - 1 : Logging into a Developer Account**

You need to login with your facebook account at  [https://developers.facebook.com/](https://developers.facebook.com/). Once done you can create a new app over there.Follow the steps and the app is created as per your need.

**Step - 2 : Configuring your App**

Once the app is created you will be provided with a appId and appSecret which are very important and you shouldn't disclose them to anybody but you or a trusted developer.Afterwards, you need to set the App Domain for your app, a Site URL and a valid OAuth redirection url. They all must be the same and should be in the whitelisted URL list which are secure and allowed by Facebook.

This app-id and app-Secret are used to authenticate and authorize your app with Facebook when a user tries to access and user your app's services.

**Step - 3 : Integrating and Working with your app**

Once you have downloaded the PHP SDK to your working project directory, you need to import the autoload.php file in you code. Create an instance of the file, replace app-id and app-secret and provide the redirection URL and a callback page which will be used for redirection once the user logs in.

    **Set Facebook app-id & app-secret in FBMethods.php**
    
    // Declared Constant Variables.  
    // Replace {app-id} with your app id  
    const app_id = 'XXXXXXXXXX';  
    // Replace {app-secret} with your app secret  
    const app_secret = 'XXXXXXXXXXXXXXXXXXXXXXXX';

You can always search the web and read the documentation of the API in aiding to implement the functionalites you want as per your need.

**Using the Google Drive REST API SDK for PHP :**

The Google Drive API for PHP client gives you a secure way to interact with google drive, letting you create,delete folders and uploading files to folders.More information and examples:  [https://developers.google.com/drive/api/v3/quickstart/php](https://developers.google.com/drive/api/v3/quickstart/php)

**Step - 1 : Logging into a Developer Account**

You need to login with your Google account at  [https://console.developers.google.com/](https://console.developers.google.com/). Once done you can create a new app over there.Search the web on how to do it, Follow the steps and the app is created as per your need.

**Step - 2 : Configuring your App**

Once the app is created you will be provided with a client_id and client_secret which are very important and you shouldn't disclose them to anybody but you or a trusted developer.Afterwards, you need to set the Origin URl for your app and a valid redirection url. Once done, save the changes and download the "client_secret.json" file from the Credentials tab.

This app-id and app-Secret are used to authenticate and authorize your app with facebook when a user tries to access and user your app's services.

**Step - 3 : Integrating and Working with your app**

Once you have downloaded the Google Drive Rest API SDK to your working project directory, you need to import the autoload.php file in you code. Create an instance of the file, give the path of the client_secret.json file which you downloaded and you are done. It's a good practise to put the file in the API library folder itself for easy access and use.

    **Set client_secret.json in FBMethods.php**
    
    $this->client = new Google_Client();  
    $this->client->setAuthConfig('client_secret.json');  
    if(empty($_SESSION['google_user']['gd_access_token'])) {  
      $this->client->setRedirectUri($this->globalfunctions->home_url() . "/FBmethods/googleLogin");  
    }  
    $this->client->addScope(Google_Service_Drive::DRIVE);
