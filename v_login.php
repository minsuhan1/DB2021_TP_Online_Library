<?php
  session_start();
  session_destroy();
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8">
    <title>로그인</title>
  </head>
  <body>
    <form action="loginChk.php" method="post">
      <center>
        <p>
          <input type="text" name="user_id" placeholder="회원번호" maxlength="5" style="width: 150;">
        </p>
        <p>
          <input type="password" name="user_pw" placeholder="패스워드" maxlength="10" style="width: 150;">
        </p>
        <p>
          <input type="submit" value="로그인">
        </p>
      </center>
    </form>
  </body>
</html>
