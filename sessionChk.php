<?php
if(!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
  header("Content-Type: text/html; charset=UTF-8");
  echo "<script>alert('세션이 끊겼습니다.');";
  echo "window.location.replace('v_login.php');</script>";
  exit;
}
?>
