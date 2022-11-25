<?php
namespace lib;

trait Permission
{
  private function allowOriginControl()
  {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $allowOrigin = isset($this->conf['allow_origin']) ? $this->conf['allow_origin'] : [];
    if (in_array($origin, $allowOrigin)){
      header('Access-Control-Allow-Origin:' . $origin);
    }
  }
  
  private function getAdminUsers() {
    return $this->conf['admin_users'] ?? [];
  }
  
  private function findAdminUser($nick, $email)
  {
    $nick = trim($nick);
    $email = trim($email);
    
    $adminUsers = $this->getAdminUsers();
    if (empty($adminUsers)) {
      return null;
    }
    
    $user = [];
    foreach ($adminUsers as $i => $item) {
      if (strtolower($item['nick']) === strtolower($nick) || strtolower($item['email']) === strtolower($email)) {
        $user = $item;
        break;
      }
    }
    
    return $user;
  }
  
  private function isAdmin($nick, $email)
  {
    if (empty($this->getAdminUsers()))
      return false;
    
    if (empty($this->findAdminUser($nick, $email)))
      return false;
    
    return true;
  }
  
  private function checkAdminPassword($nick, $email, $password)
  {
    $password = trim($password);
    $user = $this->findAdminUser($nick, $email);
    if (!empty($user) && $password === trim($user['password'])) {
      return true;
    } else {
      return false;
    }
  }
}
