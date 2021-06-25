<?php
// DB 연동
$tns = "
  (DESCRIPTION=
    (ADDRESS_LIST= (ADDRESS=(PROTOCOL=TCP)(HOST=127.0.0.1)(PORT=1521)))
    (CONNECT_DATA= (SERVICE_NAME=XE))
  )
";
$dsn = "oci:dbname=".$tns.";charset=utf8";
$username = 'D201902767';
$password = 'alstn7794';
try {
  $conn = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
  echo "에러 내용: ".$e->getMessage();
}

// 입력받은 id, pw 받기
$user_id = $_POST['user_id'];
$user_pw = $_POST['user_pw'];

// 빈 입력란이 있을 경우 로그인 화면으로 돌아감
if ( $user_id == "" || $user_pw == "" ) {
  header("Content-Type: text/html; charset=UTF-8");
  echo "<script>alert('회원번호와 패스워드를 입력해주세요');";
  echo "window.location.replace('v_login.php');</script>";
  exit;
}
 else {  // 제대로 입력된 경우
  # DB의 ID, PASSWORD 대조
  $stmt = $conn -> prepare("SELECT CNO, NAME, PASSWD FROM CUSTOMER WHERE CNO = $user_id");
  $stmt -> execute();
  $row = $stmt -> fetch(PDO::FETCH_ASSOC);

  // 없는 회원번호이거나 패스워드가 틀린 경우
  if ( !isset($row['CNO']) || $row['PASSWD'] != $user_pw ) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<script>alert('회원번호 또는 패스워드가 잘못되었습니다.');";
    echo "window.location.replace('v_login.php');</script>";
  }
  // if success
  elseif ( $row['PASSWD'] == $user_pw ) {
    header("Content-Type: text/html; charset=UTF-8");
    // 환영 메시지
    echo "<script>alert('{$row['NAME']}님 환영합니다.');";

    session_start();  // user_id와 user_name을 세션정보로 저장
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $row['NAME'];

    if ($user_id != 0) {  // 일반 유저는 회원 메인페이지로
      echo "location.href='v_user_main.php?id=ebook'</script>";
    } else {  // 관리자는 관리자 메인페이지로
      echo "location.href='v_admin_main.php?id=cur_rent_list'</script>";
    }
  }
}
?>
