<?php
namespace lib;

trait Http
{
  private function success($msg = null, $data = null)
  {
    return [
      'success' => true,
      'msg' => $msg,
      'data' => $data
    ];
  }
  
  private function error($msg = null, $data = null)
  {
    return [
      'success' => false,
      'msg' => $msg,
      'data' => $data
    ];
  }
}
