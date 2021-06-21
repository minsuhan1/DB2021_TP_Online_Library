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

  // 이미 대출중인 도서인지 확인
  $stmt0 = $conn -> prepare("SELECT CNO FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
  $stmt0 -> execute();
  $row0 = $stmt0 -> fetch(PDO::FETCH_ASSOC);
  if ($row0['CNO'] == $_SESSION['user_id']){
    echo "<script>alert('이미 대출중인 도서입니다.');";
    echo "window.location.replace('v_user_main.php?id=ebook');</script>";
    exit;
  }

  // 이미 예약중인 도서인지 확인
  $stmt1 = $conn -> prepare("SELECT CNO FROM RESERVE WHERE ISBN = {$_GET['isbn']} AND CNO = {$_SESSION['user_id']}");
  $stmt1 -> execute();
  $row1 = $stmt1 -> fetch(PDO::FETCH_ASSOC);
  if (isset($row1['CNO'])){
    echo "<script>alert('이미 예약목록에 있습니다.');";
    echo "window.location.replace('v_user_main.php?id=ebook');</script>";
    exit;
  }

  // 이미 3권 예약한 경우
  $stmt2 = $conn -> prepare("SELECT COUNT(CNO) RES_COUNT FROM RESERVE
  WHERE CNO = {$_SESSION['user_id']}
  GROUP BY CNO");
  $stmt2 -> execute();
  $row2 = $stmt2 -> fetch(PDO::FETCH_ASSOC);
  if (isset($row2['RES_COUNT']) && $row2['RES_COUNT'] >= 3){
    echo "<script>alert('이미 가능한 예약 횟수(3)만큼 예약하셨습니다. 예약 취소 후 진행해주세요.');";
    echo "window.location.replace('v_user_main.php?id=reserve_list');</script>";
    exit;
  }

  // 위의 조건들을 모두 통과한 경우 예약 처리
  $stmt = $conn -> prepare("INSERT INTO RESERVE
    VALUES ({$_GET['isbn']}, {$_SESSION['user_id']}, SYSDATE)");
  $stmt -> execute();
  echo "<script>alert('예약되었습니다.');";
  echo "window.location.replace('v_user_main.php?id=reserve_list');</script>";
?>
