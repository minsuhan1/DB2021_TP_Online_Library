<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  require "./PHPMailer/src/PHPMailer.php";
  require "./PHPMailer/src/SMTP.php";
  require "./PHPMailer/src/Exception.php";

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

    // 대기번호가 1순위인 예약자에게 메일 발송
    // 예약된 도서인 경우 1순위 예약자에게 대기순위 안내 메일 발송
    $stmt4 = $conn -> prepare("SELECT *
    FROM (SELECT * FROM RESERVE WHERE ISBN = {$_GET['isbn']} ORDER BY DATETIME ASC)
    WHERE ROWNUM = 1");
    $stmt4 -> execute();
    $row4 = $stmt4 -> fetch(PDO::FETCH_ASSOC);

    if(isset($row4['CNO'])) {
      // 예약자 메일 가져오기
      $stmt5 = $conn -> prepare("SELECT EMAIL FROM CUSTOMER WHERE CNO = {$row4['CNO']}");
      $stmt5 -> execute();
      $row5 = $stmt5 -> fetch(PDO::FETCH_ASSOC);

      // 도서제목
      $stmt6 = $conn -> prepare("SELECT TITLE FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
      $stmt6 -> execute();
      $row6 = $stmt6 -> fetch(PDO::FETCH_ASSOC);

      // Instantiation and passing `true` enables exceptions
      $mail = new PHPMailer(true);

      try {
          //Server settings
          $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'smtp.naver.com';                    // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = 'proghan@naver.com';                     // SMTP username
          $mail->Password   = 'flqnd7794han%';                               // SMTP password
          $mail->SMTPSecure = "ssl";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
          $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
          $mail->CharSet="utf-8";

          //Recipients
          $mail->setFrom('proghan@naver.com', 'Mailer');
          $mail->addAddress($row5['EMAIL']);

          // // Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '예약 도서 대기순위 알림';
          $mail->Body    = "예약 도서: \"{$row6['TITLE']}\"<br>대기순위: 1";

          $mail->send();

          echo "<script>alert('예약이 취소되었습니다.');</script>";
          echo "<script>window.location.replace('v_admin_main.php?id=cur_reserve_list');</script>";
      } catch (Exception $e) {
          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    }

    echo "<script>alert('예약이 취소되었습니다.');";
    echo "window.location.replace('v_admin_main.php?id=cur_reserve_list');</script>";
    exit;

  } else {  // 일반 회원이 예약취소하는 경우
    // 예약내역에서 삭제
    $stmt = $conn -> prepare("DELETE FROM RESERVE
      WHERE ISBN = {$_GET['isbn']} AND CNO = {$_SESSION['user_id']}");
    $stmt -> execute();

    // 대기번호가 1순위인 예약자에게 메일 발송
    // 예약된 도서인 경우 1순위 예약자에게 메일 발송
    $stmt4 = $conn -> prepare("SELECT *
    FROM (SELECT * FROM RESERVE WHERE ISBN = {$_GET['isbn']} ORDER BY DATETIME ASC)
    WHERE ROWNUM = 1");
    $stmt4 -> execute();
    $row4 = $stmt4 -> fetch(PDO::FETCH_ASSOC);

    if(isset($row4['CNO'])) {
      // 예약자 메일 가져오기
      $stmt5 = $conn -> prepare("SELECT EMAIL FROM CUSTOMER WHERE CNO = {$row4['CNO']}");
      $stmt5 -> execute();
      $row5 = $stmt5 -> fetch(PDO::FETCH_ASSOC);

      // 도서제목
      $stmt6 = $conn -> prepare("SELECT TITLE FROM EBOOK WHERE ISBN = {$_GET['isbn']}");
      $stmt6 -> execute();
      $row6 = $stmt6 -> fetch(PDO::FETCH_ASSOC);

      // Instantiation and passing `true` enables exceptions
      $mail = new PHPMailer(true);

      try {
          //Server settings
          $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
          $mail->isSMTP();                                            // Send using SMTP
          $mail->Host       = 'smtp.naver.com';                    // Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
          $mail->Username   = 'proghan@naver.com';                     // SMTP username
          $mail->Password   = 'flqnd7794han%';                               // SMTP password
          $mail->SMTPSecure = "ssl";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
          $mail->Port       = 465;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
          $mail->CharSet="utf-8";

          //Recipients
          $mail->setFrom('proghan@naver.com', 'Mailer');
          $mail->addAddress($row5['EMAIL']);

          // // Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '예약 도서 대기순위 알림';
          $mail->Body    = "예약 도서: \"{$row6['TITLE']}\"<br>대기순위: 1";

          $mail->send();

          echo "<script>alert('예약이 취소되었습니다.');</script>";
          echo "<script>window.location.replace('v_user_main.php?id=reserve_list');</script>";
      } catch (Exception $e) {
          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    }

    echo "<script>alert('예약이 취소되었습니다.');";
    echo "window.location.replace('v_user_main.php?id=reserve_list');</script>";

    // TODO: 대기순번 1번인 회원에게 메일 발송
  }
?>
