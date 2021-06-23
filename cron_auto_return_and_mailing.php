<?php
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  use PHPMailer\PHPMailer\SMTP;

  require "C:/Users/MinsuHan/Documents/PHPMailer/src/PHPMailer.php";
  require "C:/Users/MinsuHan/Documents/PHPMailer/src/SMTP.php";
  require "C:/Users/MinsuHan/Documents/PHPMailer/src/Exception.php";
  // 반납 기일이 도래한 도서를 자정이 되면 자동적으로 반납 처리되도록 함.
  // 예약자가 있는 도서를 반납한 경우 1순위 예약자에게 메일 발송
  // 자동화 스케줄링 프로그램 cron을 사용하여 매일 자정마다 본 php 파일을 실행

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

  // 대출중인 도서 중 DATEDUE가 지난 경우 반납 처리
  // 대출 중인 도서 중 반납기일이 지난 도서를 반납 처리하고 1순위 예약자에게 메일 발송
  $stmt = $conn -> prepare("SELECT E.CNO CNO, E.ISBN ISBN, E.TITLE TITLE
  FROM EBOOK E
  WHERE E.CNO IS NOT NULL AND E.DATEDUE < SYSDATE");  // 조건: 대출상태이고 반납기일 < 현재시각
  $stmt -> execute();
  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
    // EBOOK 테이블 업데이트
    $stmt1 = $conn -> prepare("UPDATE EBOOK
      SET CNO = '', EXTTIMES = '', DATERENTED = '', DATEDUE = ''
      WHERE ISBN = {$row['ISBN']}");
    $stmt1 -> execute();
    echo "1";
    // PREVIOUSRENTAL 테이블 업데이트
    // (isbn, 대출일, 반납일, cno)
    $stmt2 = $conn -> prepare("UPDATE PREVIOUSRENTAL
    SET DATERETURNED = SYSDATE
    WHERE ISBN = {$row['ISBN']} AND CNO = {$row['CNO']}");
    $stmt2 -> execute();
    echo "2";

    // LOG 테이블에 기록
    // (isbn, title, cno, datereturned)
    echo "INSERT INTO AUTO_RETURN_LOG
    VALUES ({$row['ISBN']}, '{$row['TITLE']}', {$row['CNO']}, SYSDATE)";

    $stmt3 = $conn -> prepare("INSERT INTO AUTO_RETURN_LOG
    VALUES ({$row['ISBN']}, '{$row['TITLE']}', {$row['CNO']}, SYSDATE)");
    $stmt3 -> execute();
    echo"3";
    // TODO: 예약된 도서인 경우 1순위 예약자에게 메일 발송
    $stmt4 = $conn -> prepare("SELECT *
    FROM (SELECT * FROM RESERVE WHERE ISBN = {$row['ISBN']} ORDER BY DATETIME ASC)
    WHERE ROWNUM = 1
    ");
    $stmt4 -> execute();
    $row4 = $stmt4 -> fetch(PDO::FETCH_ASSOC);
    echo "4";

    if(isset($row4['CNO'])) {
      // 예약자 메일 가져오기
      $stmt5 = $conn -> prepare("SELECT EMAIL FROM CUSTOMER WHERE CNO = {$row4['CNO']}");
      $stmt5 -> execute();
      $row5 = $stmt5 -> fetch(PDO::FETCH_ASSOC);
      echo "{$row5['EMAIL']}";
      echo "5";
      // 메일 발송
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
          $mail->SMTPDebug = 1;

          //Recipients
          $mail->setFrom('proghan@naver.com', 'Mailer');
          $mail->addAddress('trms7794han@gmail.com');

          // // Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

          // Content
          $mail->isHTML(true);                                  // Set email format to HTML
          $mail->Subject = '예약하신 도서를 대출하실 수 있습니다.';
          $mail->Body    = "예약하신 도서 \"{$row['TITLE']}\"가 반납되어 이제 대출하실 수 있습니다.";

          $mail->send();
          echo "7";
          continue;
      } catch (Exception $e) {
          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
          continue;
      }
    }
  }
?>
