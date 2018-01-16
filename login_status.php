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
/*$file = __DIR__ . "/gallery/" . $_GET['file'] . "/" ."status.txt";
if (!file_exists($file)) {
    //var_dump("folder created");
    // read the contents
    $contents = file_get_contents($file);

    while ($contents == '0') {
        $contents = file_get_contents($file);
    }
}
*/

$file = __DIR__ . "/status.txt";

$contents = file_get_contents($file);

while ($contents == '0') {
    $contents = file_get_contents($file);
    unlink($file);
}



echo  "done"; //your tokenken; //your token