<?php
/**
 * Created by PhpStorm.
 * User: hamidabubakr
 * Date: 14/12/17
 * Time: 7:04 PM
 */


error_reporting(0);


// we create the directory where we will be adding all the files necessary for creation of the video
// the reason why it is added here is because once we grab the username from instagram, we need to save
// it somewhere, and most obviously the best place would be the file where everything is located

/*if (!file_exists(__DIR__ . "/gallery/" . $_GET['file'])) {
    //var_dump("creating folder now");
    mkdir(__DIR__ . "/gallery/" . $_GET['file'], 0777, true);
}*/

$fp = fopen( __DIR__ . "/status.txt", "w" );

file_put_contents(__DIR__ . "/status.txt", "0");

var_dump("we are here");


//var_dump($_GET['file']);

//we are redirected here from instagram, and with the redirection is the acc


$fields = array(
    'client_id'     => '37ead112d6de4ea58d1b9125e75ede5f',
    'client_secret' => '8e6a8d1d20e94d62bc0608a4d506755b',
    'grant_type'    => 'authorization_code',
    'redirect_uri'  => 'http://localhost/twitter/hashtag-pull/login_forInsta.php',
    'code'          => $_GET['code'],
    'scope' => 'public_content'
);

$url = 'https://api.instagram.com/oauth/access_token';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 20);
curl_setopt($ch,CURLOPT_POST,true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
$result = curl_exec($ch);
curl_close($ch);
$result2 = json_decode($result, true);

var_dump("total result");
var_dump($result2);
$i=0;
foreach($result2 as $output) {
    var_dump("result item");
    var_dump($output);
$i = $output;
break;

}
//header($i);

//var_dump("we have a problem");
var_dump($i);

$fp = fopen( __DIR__ . "/status.txt", "w" );

fwrite( $fp, $i  );

file_put_contents(__DIR__ . "/status.txt", "1");

if (file_exists(__DIR__ . "/access.txt")) {
    var_dump("deleted exisint one");
    unlink(__DIR__ . "/access.txt");
}

$fp = fopen( __DIR__ . "/access.txt", "w" );

file_put_contents(__DIR__ . "/access.txt", $i);

//echo  "<div> You have logged in succesfully to your instagram account. If this window does not automatically close in 5 seconds, please close it and return to the bmw site.</div>"; //your tokenken; //your token