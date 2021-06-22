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

  // 관리자가 회원의 예약을 취소하는 경우
  if ($_SESSION['user_id'] == 0) {
    // 예약내역에서 삭제
    $stmt = $conn -> prepare("DELETE FROM RESERVE
      WHERE ISBN = {$_GET['isbn']} AND CNO = {$_GET['cno']}");
    $stmt -> execute();

    echo "<script>alert('예약이 취소되었습니다.');";
    echo "window.location.replace('v_admin_main.php?id=cur_reserve_list');</script>";

    // TODO: 대기순번 1번인 회원에게 메일 발송

    exit;
  } else {  // 일반 회원이 예약취소하는 경우
    // 예약내역에서 삭제
    $stmt = $conn -> prepare("DELETE FROM RESERVE
      WHERE ISBN = {$_GET['isbn']} AND CNO = {$_SESSION['user_id']}");
    $stmt -> execute();

    echo "<script>alert('예약이 취소되었습니다.');";
    echo "window.location.replace('v_user_main.php?id=reserve_list');</script>";

    // TODO: 대기순번 1번인 회원에게 메일 발송
  }
?>
