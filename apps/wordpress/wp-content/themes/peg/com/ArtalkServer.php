<?php
use Lazer\Classes\Database as Lazer;

class ArtalkServer {
  use lib\Action;
  use lib\Table;
  use lib\Http;
  use lib\Permission;
  use lib\Util;
  
  private $conf;
  
  public function __construct($conf)
  {
    $this->conf = $conf;
  
    $this->allowOriginControl();
    $this->initTables();
    
    $actionName = $_GET['action'] ?? $_POST['action'] ?? null;
    $methodName = "action{$actionName}";
    if (method_exists($this, $methodName)) {
      $result = $this->{$methodName}();
    } else {
      $result = $this->error('这是哪？我要干什么？现在几点？蛤？什么鬼！？（╯‵□′）╯︵┴─┴');
    }
    
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
  }
}
