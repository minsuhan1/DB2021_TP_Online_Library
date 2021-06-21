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
  // 대출중인 도서인지 확인. 대출중인 도서에 한해서만 연장 가능
  $stmt0 = $conn -> prepare("SELECT CNO FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
  $stmt0 -> execute();
  $row0 = $stmt0 -> fetch(PDO::FETCH_ASSOC);
  if ($row0['CNO'] != $_SESSION['user_id']){
    echo "<script>alert('대출중인 도서가 아닙니다.');";
    echo "window.location.replace('v_user_main.php?id=ebook');</script>";
    exit;
  }

  // 예약중인 도서인지 확인. 예약 중인 도서는 연장 불가
  $stmt1 = $conn -> prepare("SELECT CNO FROM RESERVE WHERE ISBN = {$_GET['isbn']}");
  $stmt1 -> execute();
  $row1 = $stmt1 -> fetch(PDO::FETCH_ASSOC);
  if (isset($row1['CNO'])){
    echo "<script>alert('다른 회원이 예약 중인 도서는 대출 연장이 불가합니다.');";
    echo "window.location.replace('v_user_main.php?id=rent_list');</script>";
    exit;
  }

  // 이미 2회 연장한 경우 연장 불가
  $stmt2 = $conn -> prepare("SELECT EXTTIMES FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
  $stmt2 -> execute();
  $row2 = $stmt2 -> fetch(PDO::FETCH_ASSOC);
  if ($row2['EXTTIMES'] >= 2){
    echo "<script>alert('이미 가능한 최대 연장 횟수(2)만큼 연장하셨습니다.');";
    echo "window.location.replace('v_user_main.php?id=rent_list');</script>";
    exit;
  }

  // 위의 조건들을 모두 통과한 경우 연장 처리
  $stmt = $conn -> prepare("UPDATE EBOOK
    SET CNO = {$_SESSION['user_id']}, EXTTIMES = EXTTIMES+1, DATEDUE = DATEDUE+10
    WHERE ISBN = {$_GET['isbn']}");
  $stmt -> execute();
  echo "<script>alert('연장되었습니다.');";
  echo "window.location.replace('v_user_main.php?id=rent_list');</script>";

?>
