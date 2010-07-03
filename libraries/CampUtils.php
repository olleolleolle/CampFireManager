<?php

/**
 * Utility functions that augment how PHP works.
 */
class CampUtils {
  
  public static function arrayGet(array $array, $key, $default) {
    if(array_key_exists($key, $array))
        return $array[$key];
    elseif( func_num_args() == 3 )
        return $default;
    else {
        error_log('Array index not found: ['.$key.']');
    }
    
  }
}

