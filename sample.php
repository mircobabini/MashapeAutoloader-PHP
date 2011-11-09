<?
require_once "MashapeAutoloader.php";

// you can find these on mashape.com
$publicKey = "your-public-key";
$privateKey = "your-private-key";


// set local store dir
MashapeAutoloader::store ("mashape-apis/");

// first way
$json = MashapeAutoloader::Unshortener ($publicKey, $privateKey)->unshort ("http://wp.me/p1e4Gf-6r");

// second way: compatibility version
MashapeAutoloader::auth ($publicKey, $privateKey);
$json2 = MashapeAutoloader::exec ("Unshortener", "unshort", "http://wp.me/p1e4Gf-6r");

