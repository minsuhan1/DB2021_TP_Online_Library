<?php
  session_start();
  if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>alert('세션이 끊겼습니다.');";
    echo "window.location.replace('v_login.php');</script>";
    exit;
  }else{
    echo "Name: {$_SESSION['user_name']}";
  }
?>
<!DOCTYPE html>
<html lang="ko" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <input type="button" name="logout_bt" value="로그아웃"
      onclick="location.href='v_login.php'"><br>
    <?php include_once 'pdo_test.php'?>
  </body>
</html>
