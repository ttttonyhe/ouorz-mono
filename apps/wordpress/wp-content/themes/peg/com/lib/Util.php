<?php
namespace lib;

trait Util
{
  private function getUserIP()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    return $ip;
  }
  
  private function urlValidator($value, $httpType = 'https|http')
  {
    if (is_string($value) && strlen($value) < 2000) {
      if (preg_match('/^(' . $httpType . '):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(?::\d{1,5})?(?:$|[?\/#])/i', $value)) {
        return true;
      }
    }
    
    return false;
  }
}
