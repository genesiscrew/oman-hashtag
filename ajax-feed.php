<?php

include('config.php');
require_once('lib/twitteroauth.php');
header('Access-Control-Allow-Origin: *');
//error_reporting(0); // Disable all errors.

//error_reporting(E_ERROR |
ini_set('error_reporting', E_STRICT);

//error_reporting(0);

if ($_GET["hashtag"]) {
    $hashtag = $_GET["hashtag"];
}



$filename = "null";
$county = "null";
$numbre = "null";
$instaitems = [];



if(isset($_GET['function'])) {
    if($_GET['function'] == 'insta') {
        updateInstagram($_GET['hashtag']);
    }
    elseif($_GET['function'] == 'twitter') {
        updateTwitter($twitter, $_GET['hashtag']);
    }elseif($_GET['function'] == 'date') {
        // do date stuff
    } elseif($_GET['function'] == 'addDB') {
        // do date stuff
        if(file_get_contents("php://input")){
            //$json = json_decode(file_get_contents("php://input"),TRUE);
            //add to DB
            header("Content-Type: application/json");
            $postdata = file_get_contents("php://input");
            parse_str(file_get_contents('php://input', false , null, -1 ,
                $_SERVER['CONTENT_LENGTH'] ), $postdata);

            addtoTweetDB($db,$twitter,$postdata,$_GET['hashtag']);
            //$db,$twitter, $tweet, $hashtag
        }

    } elseif($_GET['function'] == 'deleteFromDB') {
        // do date stuff
        deletefromDB($db,$_GET['source_id']);
    }
}



function addtoDB($db, $insta, $hashtag,$instagram){
    $db_con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);

    // get the last id
    $query = mysqli_query($db_con, "SELECT * FROM media WHERE hashtag='$hashtag' AND source='instagram' ORDER BY source_id DESC LIMIT 1");
    $result = mysqli_fetch_array($query);

    $get_media_url = 'https://api.instagram.com/v1/tags/'.$hashtag.'/media/recent?access_token='.$instagram['access_token'];
    if (isset($result['source_id'])){
        $get_media_url .= '&max_tag_id='.$result['source_id'];
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $get_media_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $media = json_decode($response, true);

        $time_now = time();
        $source_id = $insta['id'];
        $created_at = date('r', $insta['created_time']);
        $user_id = $insta['user']['id'];
        $post_link = $insta['link'];
        $screen_name = mysqli_real_escape_string($db_con,  $insta['user']['username']);
        $this_name =  mysqli_real_escape_string($db_con, stripEmojis($insta['user']['full_name']));
        $text =  mysqli_real_escape_string($db_con, stripEmojis($insta['caption']['text']));
        $likes = $insta['likes']['count'];

        if ($insta['type'] == 'video'){
            $type= 'video';
            $media_url=$insta['images']['standard_resolution']['url'];
            $media_url_https=$insta['videos']['standard_resolution']['url'];
        } else {
            $type= 'photo';
            $media_url=$insta['images']['standard_resolution']['url'];
            $media_url_https= '';
        }

        if (mysqli_query($db_con,
            "insert into media (time_now, source_id, created_at, user_id, name, screen_name, text, likes, media_url, media_url_https, source, type, hashtag, post_url) ".
            "values('$time_now', '$source_id', '$created_at','$user_id','$this_name','$screen_name', '$text', '$likes', '$media_url', '$media_url_https', 'instagram', '$type', '$hashtag', '$post_link')")){}


    mysqli_close($db_con);


}



function stripEmojis($text){
    $clean_text = "";

    // Match Emoticons
    $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    $clean_text = preg_replace($regexEmoticons, '', $text);

    // Match Miscellaneous Symbols and Pictographs
    $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    $clean_text = preg_replace($regexSymbols, '', $clean_text);

    // Match Transport And Map Symbols
    $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    $clean_text = preg_replace($regexTransport, '', $clean_text);

    return $clean_text;
}

class DomFinder {
    function __construct($page) {
        $html = @file_get_contents($page);
        $doc = new DOMDocument();
        $this->xpath = null;
        if ($html) {
            $doc->preserveWhiteSpace = true;
            $doc->resolveExternals = true;
            @$doc->loadHTML($html);
            $this->xpath = new DOMXPath($doc);
            $this->xpath->registerNamespace("html", "http://www.w3.org/1999/xhtml");
        }
    }

    function find($criteria = NULL, $getAttr = FALSE) {
        if ($criteria && $this->xpath) {
            $entries = $this->xpath->query($criteria);
            $results = array();
            foreach ($entries as $entry) {
                if (!$getAttr) {
                    $results[] = $entry->nodeValue;
                } else {
                    $results[] = $entry->getAttribute($getAttr);
                }
            }
            return $results;
        }
        return NULL;
    }

    function count($criteria = NULL) {
        $items = 0;
        if ($criteria && $this->xpath) {
            $entries = $this->xpath->query($criteria);
            foreach ($entries as $entry) {
                $items++;
            }
        }
        return $items;
    }
}

function shouldUpdate($db, $hashtag){
    $db_con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);
    $now = time();
    $query = mysqli_query($db_con, "SELECT * FROM media WHERE hashtag='$hashtag' AND source='twitter' ORDER BY source_id DESC LIMIT 1");
    $result = mysqli_fetch_array($query);

    mysqli_close($db_con);

    if (($now - intval($result['time_now'])) >= (2.5*60)){
        return $result['source_id'];
    } else {
        return false;
    }
}

function addtoTweetDB($db,$twitter, $tweet, $hashtag) {

    $db_con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);

    //$content = $connection->get('account/verify_credentials');


                    $twitter_id = $tweet['id'];
                    if(!$twitter_id){
                        //$twitter_id = null;

                    }

                    $tweet_id = $tweet['id_str'];

                    if(!$tweet_id){
                        $tweet_id = null;

                    }

                    $created_at = $tweet['created_at'];
                    if(!$created_at){
                        $created_at = null;

                    }

                    $user_id = $tweet['user']['id'];
                    if(!$user_id){
                        $user_id = null;

                    }

                    $twitter_profile_image = $tweet['user']['profile_image_url'];
                    if(!$twitter_profile_image){
                        $twitter_profile_image = null;

                    }

                    $this_name = mysqli_real_escape_string($db_con, stripEmojis($tweet['user']['name']));
                    if(!$this_name){
                        $this_name = null;

                    }

                    $screen_name =  mysqli_real_escape_string($db_con, $tweet['user']['screen_name']);
                    if(!$screen_name){
                        $screen_name = null;

                    }

                    $user_location = mysqli_real_escape_string($db_con, stripEmojis($tweet['user']['location']));
                    if(!$user_location){
                        $user_location = "Unknown";

                    }

                    $text = mysqli_real_escape_string($db_con, stripEmojis($tweet['text']));

                    if(!$text){
                        $text = null;
                    }

                    $link_post = 'https://twitter.com/'.$screen_name.'/status/'.$tweet_id;

                    $time_now = time();
                    if(!$twitter_id ||  !$user_id || !$twitter_profile_image || !$this_name || !$screen_name || !$user_location || !$text || !$link_post) {
                   //var_dump($twitter_id . " " . $user_id . " " . $twitter_profile_image . " " . $this_name . " " . $screen_name . " " . $user_location . " " . $text . " " . $link_post);
                    }
                    $is_vine = false;
                    $is_tweet = false;
                    $type = 'photo';
                    if ($tweet['is_tweet']){
                        $is_tweet = true;
                        $media_url = $tweet['media_url'];
                        $media_url_https = $tweet['media_url_https'];
                        if (!$media_url_https) {
                            $media_url_https = "Unknown";
                        }
                    }

                    if ($is_tweet || $is_vine){
                        if (mysqli_query($db_con,
                            "insert into media (user_image,time_now, source_id, created_at, user_id, name, screen_name, user_location, text, media_url, media_url_https, source, type, hashtag, post_url) ".
                            "values('$twitter_profile_image', '$time_now', '$twitter_id', '$created_at','$user_id','$this_name','$screen_name', '$user_location', '$text', '$media_url', '$media_url_https', 'twitter', '$type', '$hashtag', '$link_post')")){}
                    }







    mysqli_close($db_con);

                    echo "DB succesfully updated";







}

function deletefromDB($db,$tweet_id) {

    $db_con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);

    if (mysqli_query($db_con,
        "DELETE FROM media WHERE source_id='$tweet_id'")){}


    mysqli_close($db_con);

    echo "DB succesfully updated";




}

function updateTwitter($twitter, $hashtag){



    $connection = new TwitterOAuth($twitter['consumer_key'], $twitter['consumer_secret'], $twitter['access_token'], $twitter['access_token_secret']);
    //$content = $connection->get('account/verify_credentials');

    if (!preg_match('/[^A-Za-z0-9]/', $hashtag)) // '/[^a-z\d]/i' should also work.
    {
        // string contains only english letters & digits
        $content['twitter'] = $connection->get(
            "search/tweets", array(
                'q' => '#'.$hashtag.' filter:images',
                //'since_id' => $twitter['last_id'],
                'include_entities' => true,
                'lang' => 'en',
                'count' => 100,
                'rpp' => 100,
            )
        );
    }

    else {

        $content['twitter'] = $connection->get(
            "search/tweets", array(
                'q' => '#'.$hashtag. 'filter:images',
                //'since_id' => $twitter['last_id'],
                'include_entities' => true,
                'lang' => 'ar',
                'count' => 100,
                'rpp' => 100,
            )
        );

    }

    $twitter_output = [];

    foreach($content as $name => $media){
        if (isset($media->statuses)){
            foreach($media->statuses as $tweet){
                if (/*$tweet->id > $twitter['last_id'] && */(!empty($tweet->entities->urls) || isset($tweet->entities->media))){

                    $tweety = (object) array();
                    $twitter_id = $tweet->id;
                    $tweety->id = $twitter_id;
                    if(!$tweet->id){
                        $tweety->id = null;
                    }

                    $tweet_id = $tweet->id_str;
                    $tweety->id_str = $tweet->id_str;

                    if(!$tweet->id_str){
                        $tweety->id_str = null;

                    }

                    $created_at = $tweet->created_at;
                    $tweety->created_at = $created_at;
                    if(!$created_at){
                        $tweety->created_at = null;

                    }

                    $user_id = $tweet->user->id;
                    $tweety->user->id = $user_id;
                    if(!$user_id){
                        $tweety->user->id = null;

                    }

                    $twitter_profile_image = $tweet->user->profile_image_url;
                    $tweety->user->profile_image_url = $tweet->user->profile_image_url;

                    if(!$twitter_profile_image){
                        $tweety->user->profile_image_url = null;

                    }
                    $this_name = $tweet->user->name;
                    $tweety->user->name = $this_name;
                    if(!$tweet->user->name){
                        $tweety->user->name = null;

                    }

                    $screen_name =  $tweet->user->screen_name;
                    $tweety->user->screen_name =  $screen_name ;
                    if(!$screen_name){
                        $tweety->user->screen_name = null;

                    }
                    $user_location = $tweet->user->location;
                    $tweety->user->location =$user_location;
                    if(!$tweet->user->location){
                        $tweety->user->location = "Unknown";

                    }

                    $text = $tweet->text;
                    $tweety->text = $text;
                    if(!$text){
                        $tweety->text  = null;

                    }

                    $link_post = 'https://twitter.com/'.$screen_name.'/status/'.$tweet_id;


                    $time_now = time();
                    if(!$twitter_id ||  !$user_id || !$twitter_profile_image || !$this_name || !$screen_name || !$user_location || !$text || !$link_post) {
                       // var_dump("missing data");
                    }
                    $is_vine = false;
                    $tweety->is_tweet = false;
                    $type = 'photo';
                    if (isset($tweet->entities->media)){
                        $tweety->is_tweet = true;
                        $media_url = $tweet->entities->media[0]->media_url;
                        $media_url_https = $tweet->entities->media[0]->media_url_https;
                        $tweety->media_url = $media_url;
                        $tweety->media_url_https = $media_url_https;
                        if (!$media_url_https) {
                            $tweety->media_url_https = "Unknown";
                        }
                    }

                    array_push($twitter_output,$tweety);



                }
            }
            echo json_encode($twitter_output);
        }
    }



}

function updateInstagram($hashtag){


    $insta_access_path = __DIR__ . "/access.txt";
    $access_token = file_get_contents($insta_access_path);
    //unlink($insta_access_path);
    $get_media_url = 'https://api.instagram.com/v1/tags/'.$hashtag.'/media/recent?access_token='. $access_token;
   // if (isset($result['source_id'])){
     //   $get_media_url .= '&max_tag_id='.$result['source_id'];
    //}

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $get_media_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $media = json_decode($response, true);

    //var_dump($response);

    $instaoutput = [];

    foreach($media['data'] as $insta){

        $time_now = time();
        $source_id = $insta['id'];
        $created_at = date('r', $insta['created_time']);
        $user_id = $insta['user']['id'];
        $user_image = $insta['user']['profile_picture'];
        $post_link = $insta['link'];
        $screen_name = $insta['user']['username'];
        $this_name =   stripEmojis($insta['user']['full_name']);
        $text =   stripEmojis($insta['caption']['text']);
        $likes = $insta['likes']['count'];

        if ($insta['type'] == 'video'){
            $type= 'video';
            $media_url=$insta['images']['standard_resolution']['url'];
            $media_url_https=$insta['videos']['standard_resolution']['url'];
        } else {
            $type= 'photo';
            $media_url=$insta['images']['standard_resolution']['url'];
            $media_url_https= '';
        }


        array_push($instaoutput,json_encode($insta));
    }

    echo json_encode($instaoutput);

  //  mysqli_close($db_con);
}






function outputFeed($db, $hashtag){
    $html = '<ul class="feed">';
    $db_con = mysqli_connect($db['host'], $db['user'], $db['password'], $db['name']);
    $query = mysqli_query($db_con, "SELECT * FROM media WHERE hashtag='$hashtag' ORDER BY time_now DESC");
    $counter = 0;
    if (mysqli_num_rows($query) > 0) {
        while ($post = mysqli_fetch_assoc($query)) {
            if ($post['type'] == 'photo'){
                $media = '<a href="' . $post['post_url'] . '"><img class="' . $post['source'] . '" src="' . $post['user_image'] . '" alt=""/></a>';
                $media2 = '<a href="' . $post['post_url'] . '">' . $post['text'] . '</a>';
            } else if ($post['media_url_https']!='') {
                $media = '<video width="100%" height="100%" controls poster="'. $post['media_url'] . '">
                    <source src="'. $post['media_url_https'] . '" type="video/mp4">
                    Your browser does not support the video tag.
                    </video>';
            }

            $text = str_replace("'", "\'", $post['text']);

            if ($counter == 0) {

                $numbre = 'zero';
            }
            else {
                $numbre = N2L($counter);
            }

            $filename = date('YmdHis');
            $county = 'a'.strval($counter);
            $numbre = json_encode($post);


            $html .= '<li class="quad"  style=" margin-bottom: 6px;border-top: 1px solid gray; border-bottom: 1px solid gray; border-left: 1px solid gray; border-right: 1px solid gray;" class="'.$post['source'].' col-sm-3" style=""><div style="font-size: 7px;"> '.$media2.'</div><div>'.$media.'</div><button class="btn-success" id="insta-login1" >
					Add to Database
				</button></li>';
            $counter++;
        }
    }
    $html .= '</ul>';
    mysqli_close($db_con);

    return $html;
}


/*
$shouldUpdate = shouldUpdate($db, $hashtag);

if ($shouldUpdate !== false){
    $twitter['last_id'] = $shouldUpdate;
    //

    if (file_exists(__DIR__ . "/access.txt")) {
        updateInstagram($db, $instagram, $hashtag);

    }
    updateTwitter($db, $twitter, $hashtag);
}

echo outputFeed($db, $hashtag);  */


?>



