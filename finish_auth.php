<?php

// This script derived from the libraries at http://openidenabled.com/php-openid/ and 
// data from http://php.net/manual/en/reserved.variables.server.php

require_once "Auth/common.php";
session_start();
$consumer = getConsumer();

// Complete the authentication process using the server's response.
$return_to = getReturnTo();
$response = $consumer->complete($return_to);

if($_SERVER['https'] == 1) {
  $scheme="https";
  if($_SERVER['SERVER_PORT']!=443) {$port=":" . $_SERVER['SERVER_PORT'];}
} elseif ($_SERVER['https'] == 'on') {
  $scheme="https";
  if($_SERVER['SERVER_PORT']!=443) {$port=":" . $_SERVER['SERVER_PORT'];}
} elseif ($_SERVER['SERVER_PORT']==443) {
  $scheme="https";
} else {
  $scheme="http";
  if($_SERVER['SERVER_PORT']!=80) {$port=":" . $_SERVER['SERVER_PORT'];}
}

$baseurl="$scheme://{$_SERVER['SERVER_NAME']}$port" . dirname($_SERVER['SCRIPT_NAME']);

// Check the response status.
if ($response->status == Auth_OpenID_CANCEL) {
  $reason_text=trim(htmlentities($response->message));
  header("Location: $baseurl?state=cancel&reason=$reason_text");
} else if ($response->status == Auth_OpenID_FAILURE) {
  $reason_text=trim(htmlentities($response->message));
  header("Location: $baseurl?state=fail&reason=$reason_text");
} else if ($response->status == Auth_OpenID_SUCCESS) {
  $openid = $response->getDisplayIdentifier();
  $_SESSION['openid']=htmlentities($openid); // Actual OpenID URL

  $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
  $sreg = $sreg_resp->contents();

  if (@$sreg['email']) {$_SESSION['email']=htmlentities($sreg['email']);}
  if (@$sreg['nickname']) {$_SESSION['nick']=htmlentities($sreg['nickname']);}
  if (@$sreg['fullname']) {$_SESSION['full']=htmlentities($sreg['fullname']);}
  header("Location: $baseurl");
}

?>
