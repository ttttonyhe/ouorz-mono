<?php
namespace lib;

trait Action
{
  public function actionAdminCheck()
  {
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($nick)) return $this->error('昵称 不能为空');
    if (empty($email)) return $this->error('邮箱 不能为空');
    if (empty($password)) return $this->error('密码 不能为空');
    
    if (!$this->isAdmin($nick, $email)) {
      return $this->error('无需管理员权限');
    }
    if ($this->checkAdminPassword($nick, $email, $password)) {
      return $this->success('密码正确');
    } else {
      return $this->error('密码错误');
    }
  }
  
  public function actionCommentAdd()
  {
    $content = trim($_POST['content'] ?? '');
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $link = trim($_POST['link'] ?? '');
    $rid = intval(trim($_POST['rid'] ?? 0));
    $pageKey = trim($_POST['page_key'] ?? '');
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $password = trim($_POST['password'] ?? '');
  
    if (empty($pageKey)) return $this->error('pageKey 不能为空');
    if (empty($nick)) return $this->error('昵称不能为空');
    if (empty($email)) return $this->error('邮箱不能为空');
    if (empty($content)) return $this->error('内容不能为空');
  
    if ($this->isAdmin($nick, $email) && !$this->checkAdminPassword($nick, $email, $password)) {
      return $this->error('需要管理员身份', ['need_password' => true]);
    }
    if (!empty($link) && !$this->urlValidator($link)) {
      return $this->error('链接不是 URL');
    }
    
    $commentData = [
      'content' => $content,
      'nick' => $nick,
      'email' => $email,
      'link' => $link,
      'page_key' => $pageKey,
      'rid' => $rid,
      'ua' => $ua,
      'date' => date("Y-m-d H:i:s"),
      'ip' => $this->getUserIP()
    ];
    $comment = self::getCommentsTable();
    $comment->set($commentData);
    $comment->save();
    
    $commentData['id'] = $comment->lastId();
    return $this->success('评论成功', ['comment' => $this->beautifyCommentData($commentData)]);
  }
  
  public function actionCommentGet()
  {
    $pageKey = trim($_POST['page_key'] ?? '');
    if (empty($pageKey)) {
      return $this->error('page_key 不能为空');
    }
    
    $commentsRaw = self::getCommentsTable()
      ->where('page_key', '=', $pageKey)
      ->orderBy('date', 'DESC')
      ->findAll()
      ->asArray();
    
    $comments = [];
    foreach ($commentsRaw as $item) {
      $comments[] = $this->beautifyCommentData($item);
    }
    
    return $this->success('获取成功', ['comments' => $comments]);
  }
  
  private function beautifyCommentData($rawComment)
  {
    $comment = [];
    $showField = ['id', 'content', 'nick', 'link', 'page_key', 'rid', 'ua', 'date'];
    foreach ($rawComment as $key => $value) {
      if (in_array($key, $showField)) {
        $comment[$key] = $value;
      }
    }
    
    $comment['email_encrypted'] = md5(strtolower(trim($rawComment['email'])));
    $comment['badge'] = null;
    if ($this->isAdmin($rawComment['nick'] ?? null, $rawComment['email'] ?? null)) {
      $comment['badge'] = '托尼本托';
    }
    return $comment;
  }
  
  public function actionCommentReplyGet()
  {
    $nick = trim($_POST['nick'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if (empty($nick)) return $this->error('昵称 不能为空');
    if (empty($email)) return $this->error('邮箱 不能为空');
    
    $replyRaw = self::getCommentsTable();
    
    if (!$this->isAdmin($nick, $email)) {
      $myComments = self::getCommentsTable()
        ->where('nick', '=', $nick)
        ->andWhere('email', '=', $email)
        ->orderBy('date', 'DESC')
        ->findAll()
        ->asArray();
  
      $idList = [];
      foreach ($myComments as $item) {
        $idList[] = $item['id'];
      }
      
      $replyRaw = $replyRaw->where('rid', 'IN', $idList);
    }
    
    $replyRaw = $replyRaw
      ->orderBy('date', 'DESC')
      ->findAll()
      ->asArray();
  
    $reply = [];
    foreach ($replyRaw as $item) {
      $reply[] = $this->beautifyCommentData($item);
    }
    
    return $this->success('获取成功', ['reply_comments' => $reply]);
  }
}
