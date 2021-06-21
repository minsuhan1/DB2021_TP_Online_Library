<?php
  session_start();
  include_once('sessionChk.php');

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

  // 본인이 대출중인 도서인지 확인
  $stmt0 = $conn -> prepare("SELECT CNO, DATERENTED FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
  $stmt0 -> execute();
  $row0 = $stmt0 -> fetch(PDO::FETCH_ASSOC);
  if ($row0['CNO'] != $_SESSION['user_id']){
    echo "<script>alert('잘못된 접근입니다.');";
    echo "window.location.replace('v_login.php');</script>";
    exit;
  }
  // 본인이 대출한 도서가 맞는 경우 반납처리
  if ($row0['CNO'] == $_SESSION['user_id']) {
    // EBOOK 테이블 업데이트
    $stmt1 = $conn -> prepare("UPDATE EBOOK
      SET CNO = '', EXTTIMES = '', DATERENTED = '', DATEDUE = ''
      WHERE ISBN = {$_GET['isbn']}");
    $stmt1 -> execute();

    // PREVIOUSRENTAL 테이블 업데이트
    // (isbn, 대출일, 반납일, cno)
    $stmt2 = $conn -> prepare("UPDATE PREVIOUSRENTAL
    SET DATERETURNED = SYSDATE
    WHERE ISBN = {$_GET['isbn']} AND CNO = {$row0['CNO']}");
    $stmt2 -> execute();

    echo "<script>alert('반납되었습니다.');";
    echo "window.location.replace('v_user_main.php?id=rent_list');</script>";
  }

  // TODO: 대기순번이 1인 예약자에게 메일 전송
?>
