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
    <!-- 로그인 입력내용 전달폼(POST 방식) -->
    <form action="loginChk.php" method="post">
      <center>
        <h1>LOGIN</h1>
        <p>
          <!-- 회원번호 입력란 -->
          <input type="text" name="user_id" placeholder="회원번호" maxlength="5" style="width: 150;">
        </p>
        <p>
          <!-- 패스워드 입력란 -->
          <input type="password" name="user_pw" placeholder="패스워드" maxlength="10" style="width: 150;">
        </p>
        <p>
          <!-- 로그인 버튼 -->
          <input type="submit" value="로그인">
        </p>
      </center>
    </form>
  </body>
</html>
