<?php
namespace lib;
use Lazer\Classes\Database as Lazer;

trait Table
{
  private function initTables()
  {
    // comments
    try{
      \Lazer\Classes\Helpers\Validate::table('comments')->exists();
    } catch(\Lazer\Classes\LazerException $e){
      Lazer::create('comments', [
        'id' => 'integer',
        'content' => 'string',
        'nick' => 'string',
        'email' => 'string',
        'link' => 'string',
        'ua' => 'string',
        'page_key' => 'string',
        'rid' => 'integer',
        'ip' => 'string',
        'date' => 'string',
      ]);
    }
  }
  
  private static function getCommentsTable()
  {
    return Lazer::table('comments');
  }
}
