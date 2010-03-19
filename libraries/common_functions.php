<?php
/*******************************************************
 * CampFireManager
 * General purpose functions that don't require any 
 * Database Activity
 * Version 0.5 2010-03-19 JonTheNiceGuy
 *******************************************************
 * Version History
 * 0.5 2010-03-19 Migrated from personal SVN store
 * at http://spriggs.org.uk/jon_code/CampFireManager
 * where all prior versions are stored to Google Code at
 * http://code.google.com/p/campfiremanager/
 ******************************************************/

$baseurl=calculateBaseURL();
function calculateBaseURL() {
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
  return("$scheme://{$_SERVER['SERVER_NAME']}$port" . dirname($_SERVER['SCRIPT_NAME']) . "/");
}

function genRandStr($minLen, $maxLen, $alphaLower = 1, $alphaUpper = 1, $num = 1, $batch = 1) {
  // This code from php.net's comments at http://www.php.net/manual/en/function.rand.php#94788
  // Tweaked on 2010-02-05 by Jon Spriggs to remove similar looking characters from the returned strings
  /*
  $minLen is the minimum length of the string. Required.
  $maxLen is the maximum length of the string. Required.
  $alphaLower toggles the use of lowercase letters (a-z). Default is 1 (lowecase letters may be used).
  $alphaUpper toggles the use of uppercase letters (A-Z). Default is 1 (uppercase letters may be used).
  $num toggles the use of numbers (0-9). Default is 1 (numbers may be used).
  $batch specifies the number of strings to create. Default is 1 (returns one string). When $batch is not 1 the function returns an array of multiple strings.

  */
  $alphaLowerArray = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
  $alphaUpperArray = array('A', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
  $numArray = array(2, 3, 4, 6, 7, 9);

  if (isset($minLen) && isset($maxLen)) {
    if ($minLen == $maxLen) {
      $strLen = $minLen;
    } else {
      $strLen = rand($minLen, $maxLen);
    }
    $merged = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);
    
    if ($alphaLower == 1 && $alphaUpper == 1 && $num == 1) {
      $finalArray = array_merge($alphaLowerArray, $alphaUpperArray, $numArray);
    } elseif ($alphaLower == 1 && $alphaUpper == 1 && $num == 0) {
      $finalArray = array_merge($alphaLowerArray, $alphaUpperArray);
    } elseif ($alphaLower == 1 && $alphaUpper == 0 && $num == 1) {
      $finalArray = array_merge($alphaLowerArray, $numArray);
    } elseif ($alphaLower == 0 && $alphaUpper == 1 && $num == 1) {
      $finalArray = array_merge($alphaUpperArray, $numArray);
    } elseif ($alphaLower == 1 && $alphaUpper == 0 && $num == 0) {
      $finalArray = $alphaLowerArray;
    } elseif ($alphaLower == 0 && $alphaUpper == 1 && $num == 0) {
      $finalArray = $alphaUpperArray;                       
    } elseif ($alphaLower == 0 && $alphaUpper == 0 && $num == 1) {
      $finalArray = $numArray;
    } else {
      return FALSE;
    }
    
    $count = count($finalArray);
    
    if ($batch == 1) {
      $str = '';
      $i = 1;
      while ($i <= $strLen) {
        $rand = rand(0, $count);
        $newChar = $finalArray[$rand];
        $str .= $newChar;
        $i++;
      }
      $result = $str;
    } else {
      $j = 1;
      $result = array();
      while ($j <= $batch) {
        $str = '';
        $i = 1;
        while ($i <= $strLen) {
          $rand = rand(0, $count);
          $newChar = $finalArray[$rand];
          $str .= $newChar;
          $i++;
        }
        $result[] = $str;
        $j++;
      }
    }
    return $result;
  }
}

