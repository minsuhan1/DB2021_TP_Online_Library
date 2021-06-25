<?php
  // 세션 체크
  session_start();
  include_once('sessionChk.php')
?>
<!DOCTYPE html>
<html lang="ko" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title></title>
    <link rel="stylesheet" href="style_main.css">
    <!--Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-wEmeIV1mKuiNpC+IOBjI7aAzPcEZeedi5yW5f2yOq55WWLwNGmvvx4Um1vskeMj0"
    crossorigin="anonymous">
  </head>
  <body>
    <div class="wrap" id="wrap">
      <header class="header" style="padding:5px">
        <?php echo "Name: {$_SESSION['user_name']}"; ?>
        <input type="button" name="logout_bt" value="로그아웃"
          onclick="location.href='v_login.php'"><br>
        <p>
          <a href="v_user_main.php?id=ebook" name="go_main_bt">메인으로(LOGO)</a>
        </p>
      </header>
      <div class="content">
        <div class="aside"> <!--사이드바, 회원 기능 메뉴 -->
          <ul style="list-style:none;">
            <div style="padding:20px;"></div>
            <li><a class="aside_list" href="v_user_main.php?id=ebook">도서검색<br><br></a></li>
            <li><a class="aside_list" href="v_user_main.php?id=rent_list">대출목록조회<br><br></a></li>
            <li><a class="aside_list" href="v_user_main.php?id=reserve_list">예약목록조회</a></li>
          </ul>
        </div>  <!--사용자 요청 페이지 화면(URL 파라미터 id값으로 구분)-->
        <div class="main" style="width:100%;height:810px;overflow-y:auto;overflow-x:hidden; padding:20px">
          <?php
          if (!isset($_GET['id'])) {  // id is not set
            echo "<script>window.location.replace('v_user_main.php?id=ebook');</script>";
          } else if($_GET['id'] != 'ebook'){  // id = rent_list or reserve_list
            include_once('v_user_'.$_GET['id'].'.php');
          } else {  // 도서검색 페이지 (id = ebook)
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
          ?>
          <div class="container">
            <h2 class="text-center"><a href="v_user_main.php" style="text-decoration: none">도서목록</a></h2>
            <!--검색필터-->
            <form action="v_user_main.php?id=ebook" method="post">
              <div class="" style="text-align: center; padding: 10px">
                <input type="text" name="title" placeholder="제목" maxlength="100" style="width: 500px;">
                <input type="text" name="author" placeholder="저자" maxlength="20" style="width: 150;">
                <input type="text" name="publisher" placeholder="출판사" maxlength="20" style="width: 150;">
                <input type="text" name="year" placeholder="출판년도" maxlength="4" style="width: 100px;">
                <input type="submit" name="user_id" value="검색" maxlength="5" style="width: 150;">
                <input type="button" value="초기화" onclick="location.href='v_user_main.php'">
              </div>
            </form>
            <!--검색 필터 바탕으로 쿼리구성-->
            <?php
              $filter = "";
              if(!empty($_POST['title'])) {
                $filter .= "AND E.TITLE LIKE '%".$_POST['title']."%'";
              }
              if(!empty($_POST['author'])) {
                $filter .= "AND A.AUTHOR LIKE '%".$_POST['author']."%'";
              }
              if(!empty($_POST['publisher'])) {
                $filter .= "AND E.PUBLISHER LIKE '%".$_POST['publisher']."%'";
              }
              if(!empty($_POST['year'])) {
                $filter .= "AND EXTRACT(YEAR FROM E.YEAR) LIKE '%".$_POST['year']."%'";
              }
            ?>

            <table class="table table-bordered text-center">
              <thead>
                <th>ISBN</th>
                <th>제목</th>
                <th>저자</th>
                <th>출판사</th>
                <th>출판년도</th>
                <th>대출가능여부</th>
                <th>대출/예약</th>
              </thead>
              <tbody>
                <?php
                  // DB에 저장된 도서들의 정보를 출력하는 쿼리, 도서 정보와 대출가능여부를 알아낸다.
                  $stmt = $conn -> prepare("SELECT E.ISBN ISBN, E.TITLE TITLE, lISTAGG(A.AUTHOR, ',') WITHIN GROUP(ORDER BY A.AUTHOR) AS AUTHORS, E.PUBLISHER PUBLISHER,
      EXTRACT(YEAR FROM E.YEAR) YEAR, CASE WHEN E.CNO IS NOT NULL THEN '대출중' ELSE '대출가능' END AS POS
      FROM EBOOK E, AUTHORS A
      WHERE E.ISBN = A.ISBN {$filter} group by E.ISBN, E.TITLE, E.YEAR, CASE WHEN E.CNO IS NOT NULL THEN '대출중' ELSE '대출가능' END, E.CNO,
      '대출중', '대출가능', E.PUBLISHER");
                  $stmt -> execute();
                  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                      <td><?=$row['ISBN']?></td>
                      <td><?=$row['TITLE']?></td>
                      <td><?=$row['AUTHORS']?></td>
                      <td><?=$row['PUBLISHER']?></td>
                      <td><?=$row['YEAR']?></td>
                      <?php
                        // 현재 도서가 예약중인 도서인지 확인하는 쿼리
                        $stmt1 = $conn -> prepare("SELECT CNO FROM RESERVE WHERE ISBN = {$row['ISBN']}");
                        $stmt1 -> execute();
                        $row1 = $stmt1 -> fetch(PDO::FETCH_ASSOC);

                        $isRes = 0;  // 예약중인 도서이면 isRes > 0
                        if(isset($row1['CNO'])){
                          $isRes = $row1['CNO'];
                        }
                        if ($row['POS'] == '대출가능' && $isRes == 0) {  // 대출가능한 경우 대출버튼 활성화
                          echo "<td>대출가능</td>"; // 대출가능 표시
                          echo "<td><a href=\"user_process_rent.php?isbn={$row['ISBN']}\">대출</a></td>";
                        } else {  // 대출중이거나 예약자가 있는경우 예약버튼 활성화
                          echo "<td>대출/예약중</td>"; // 대출-예약중 표시
                          echo "<td><a href=\"user_process_reserve.php?isbn={$row['ISBN']}\">예약</a></td>";
                        }
                      ?>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          <?php
          }
          ?>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
