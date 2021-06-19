<?php
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
?>
<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="utf-8">
    <!--Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0"
    crossorigin="anonymous">
    <title></title>
  </head>
  <body>
    <div class="container">
      <h2 class="text-center">EBOOK</h2>
      <table class="table table-bordered text-center">
        <thead>
          <th>ISBN</th>
          <th>제목</th>
          <th>출판사</th>
          <th>출판년도</th>
          <th>CNO</th>
          <th>연장횟수</th>
          <th>대출일</th>
          <th>반납기한</th>
        </thead>
        <tbody>
          <?php
            $stmt = $conn -> prepare("SELECT * FROM EBOOK");
            $stmt -> execute();
            while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
          ?>
              <tr>
                <td><?=$row['ISBN']?></td>
                <td><?=$row['TITLE']?></td>
                <td><?=$row['PUBLISHER']?></td>
                <td><?=$row['YEAR']?></td>
                <td><?=$row['CNO']?></td>
                <td><?=$row['EXTTIMES']?></td>
                <td><?=$row['DATERENTED']?></td>
                <td><?=$row['DATEDUE']?></td>
              </tr>
          <?php
            }
          ?>
        </tbody>
      </table>
  </body>
</html>
