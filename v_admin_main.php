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
    <!-- 현재 접속자 이름 표시 및 로그아웃버튼, 메인페이지 이동 버튼 -->
    <div class="wrap" id="wrap">
      <header class="header" style="padding:5px">
        <?php echo "Name: {$_SESSION['user_name']}"; ?>
        <input type="button" name="logout_bt" value="로그아웃"
          onclick="location.href='v_login.php'"><br>
        <p>
          <a href="v_admin_main.php?id=cur_rent_list" name="go_main_bt">메인으로</a>
        </p>
      </header>
      <div class="content">
        <div class="aside"> <!--사이드바. 관리자 메뉴 선택 가능 -->
          <ul style="list-style:none;">
            <div style="padding:20px;"></div>
            <li><a class="aside_list" href="v_admin_main.php?id=cur_rent_list">회원대출관리<br><br></a></li>
            <li><a class="aside_list" href="v_admin_main.php?id=prev_rent_list">대출/반납기록조회<br><br></a></li>
            <li><a class="aside_list" href="v_admin_main.php?id=cur_reserve_list">회원예약관리<br><br></a></li>
            <li><a class="aside_list" href="v_admin_main.php?id=stat">대출통계조회<br><br></a></li>
          </ul>
        </div>
        <div class="main" style="width:100%;height:810px;overflow-y:auto;overflow-x:hidden; padding:20px">
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

          // 관리자 페이지 화면(URL 파라미터값 id에 따라 각 기능 페이지 출력)
          if (!isset($_GET['id'])) {  // id not set
            echo "<script>window.location.replace('v_admin_main.php?id=cur_rent_list');</script>";
          } else if($_GET['id'] == 'cur_rent_list'){  // 회원대출관리
            ?>
            <div class="container">
              <h2 class="text-center"><a href="v_admin_main.php?id=cur_rent_list" style="text-decoration: none">회원대출관리</a></h2>
              <!--검색필터-->
              <form action="v_admin_main.php?id=cur_rent_list" method="post">
                <div class="" style="text-align: center; padding: 10px">
                  <input type="text" name="filt_user_id" placeholder="회원ID" maxlength="10" style="width: 70px;">
                    <input type="text" name="user_name" placeholder="회원명" maxlength="10" style="width: 100px;">
                  <input type="text" name="isbn_" placeholder="ISBN" maxlength="10" style="width: 70px;">
                  <input type="text" name="title" placeholder="제목" maxlength="100" style="width: 400px;">
                  <input type="text" name="author" placeholder="저자" maxlength="20" style="width: 150;">
                  <input type="text" name="publisher" placeholder="출판사" maxlength="20" style="width: 150;">
                  <input type="text" name="year" placeholder="출판년도" maxlength="4" style="width: 100px;">
                  <input type="submit" name="search" value="검색" maxlength="5" style="width: 150;">
                  <input type="button" value="초기화" onclick="location.href='v_admin_main.php'">
                </div>
              </form>
              <!--검색 필터 바탕으로 쿼리구성-->
              <?php
                $filter = "";
                if(!empty($_POST['filt_user_id'])) {
                  $filter .= "AND E.CNO = {$_POST['filt_user_id']}";
                }
                if(!empty($_POST['user_name'])) {
                  $filter .= "AND C.NAME LIKE '%".$_POST['user_name']."%'";
                }
                if(!empty($_POST['isbn_'])) {
                  $filter .= "AND E.ISBN = {$_POST['isbn_']}";
                }
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

              <!-- 목록 테이블 -->
              <table class="table table-bordered text-center">
                <thead>
                  <th>ISBN</th>
                  <th>제목</th>
                  <th>저자</th>
                  <th>출판사</th>
                  <th>출판년도</th>
                  <th>대출자 ID</th>
                  <th>대출자명</th>
                  <th>대출일</th>
                  <th>반납기한</th>
                  <th>연장횟수</th>
                  <th>반납</th>
                  <th>연장</th>
                </thead>
                <tbody>
                  <?php
                  // 현재 대출중인 도서 목록 정보 출력 쿼리
                    $stmt = $conn -> prepare("SELECT E.ISBN ISBN, E.TITLE TITLE, E.CNO CNO, lISTAGG(A.AUTHOR, ',') WITHIN GROUP(ORDER BY A.AUTHOR) AS AUTHORS, E.PUBLISHER PUBLISHER,
        EXTRACT(YEAR FROM E.YEAR) YEAR, E.EXTTIMES EXTTIMES, E.DATERENTED DATERENTED, E.DATEDUE DATEDUE, C.NAME NAME
        FROM EBOOK E, AUTHORS A, CUSTOMER C
        WHERE E.ISBN = A.ISBN AND E.CNO = C.CNO {$filter} AND DATERENTED IS NOT NULL
        group by E.ISBN, E.TITLE, E.CNO, E.YEAR, E.PUBLISHER, E.EXTTIMES, E.DATERENTED, E.DATEDUE, C.NAME
        ORDER BY E.DATEDUE DESC, E.DATERENTED DESC");

                    $stmt -> execute();
                    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                  ?>
                      <tr>
                        <td><?=$row['ISBN']?></td>
                        <td><?=$row['TITLE']?></td>
                        <td><?=$row['AUTHORS']?></td>
                        <td><?=$row['PUBLISHER']?></td>
                        <td><?=$row['YEAR']?></td>
                        <td><?=$row['CNO']?></td>
                        <td><?=$row['NAME']?></td>
                        <td><?=$row['DATERENTED']?></td>
                        <td><?=$row['DATEDUE']?></td>
                        <td><?=$row['EXTTIMES']?></td>
                        <!-- 반납, 연장 버튼 -->
                        <?php echo "<td><a href=\"user_process_return.php?isbn={$row['ISBN']}\">반납</a></td>"; ?>
                        <?php echo "<td><a href=\"user_process_extend.php?isbn={$row['ISBN']}\">연장</a></td>"; ?>
                      </tr>
                  <?php
                    }
                  ?>
                </tbody>
              </table>
            </div>  <!-- 회원대출목록조회 end -->
            <?php

          } else if($_GET['id'] == 'prev_rent_list') { // 대출반납기록조회
            ?>
            <div class="container">
              <h2 class="text-center"><a href="v_admin_main.php?id=prev_rent_list" style="text-decoration: none">대출/반납기록</a></h2>
              <!--검색필터-->
              <form action="v_admin_main.php?id=prev_rent_list" method="post">
                <div class="" style="text-align: center; padding: 10px">
                  <input type="text" name="filt_user_id" placeholder="회원ID" maxlength="10" style="width: 70px;">
                  <input type="text" name="user_name" placeholder="회원명" maxlength="10" style="width: 100px;">
                  <input type="text" name="isbn_" placeholder="ISBN" maxlength="10" style="width: 70px;">
                  <input type="text" name="title" placeholder="제목" maxlength="100" style="width: 400px;">
                  <input type="text" name="author" placeholder="저자" maxlength="20" style="width: 150;">
                  <input type="text" name="publisher" placeholder="출판사" maxlength="20" style="width: 150;">
                  <input type="text" name="year" placeholder="출판년도" maxlength="4" style="width: 100px;">
                  <input type="submit" name="search" value="검색" maxlength="5" style="width: 150;">
                  <input type="button" value="초기화" onclick="location.href='v_admin_main.php?id=prev_rent_list'">
                </div>
              </form>
              <!--검색 필터 바탕으로 쿼리구성-->
              <?php
                $filter = "";
                if(!empty($_POST['filt_user_id'])) {
                  $filter .= "AND P.CNO = {$_POST['filt_user_id']}";
                }
                if(!empty($_POST['user_name'])) {
                  $filter .= "AND C.NAME LIKE '%".$_POST['user_name']."%'";
                }
                if(!empty($_POST['isbn_'])) {
                  $filter .= "AND P.ISBN = {$_POST['isbn_']}";
                }
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
                  <th>대출자 ID</th>
                  <th>대출자명</th>
                  <th>대출일</th>
                  <th>반납일</th>
                </thead>
                <tbody>
                  <?php
                  // 대출반납기록 출력 쿼리
                    $stmt = $conn -> prepare("SELECT P.ISBN ISBN, E.TITLE TITLE, P.CNO CNO, lISTAGG(A.AUTHOR, ',') WITHIN GROUP(ORDER BY A.AUTHOR) AS AUTHORS, E.PUBLISHER PUBLISHER,
            EXTRACT(YEAR FROM E.YEAR) YEAR, P.DATERENTED DATERENTED, E.DATEDUE DATEDUE, P.DATERETURNED DATERETURNED, C.NAME NAME
            FROM EBOOK E, AUTHORS A, PREVIOUSRENTAL P, CUSTOMER C
            WHERE P.ISBN = E.ISBN AND E.ISBN = A.ISBN AND P.CNO = C.CNO {$filter} AND P.DATERENTED IS NOT NULL
            group by P.ISBN, E.TITLE, P.CNO, E.YEAR, E.PUBLISHER, P.DATERENTED, E.DATEDUE, P.DATERETURNED, C.NAME
            ORDER BY P.DATERENTED DESC, P.DATERETURNED DESC");

                    $stmt -> execute();
                    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                  ?>
                      <tr>
                        <td><?=$row['ISBN']?></td>
                        <td><?=$row['TITLE']?></td>
                        <td><?=$row['AUTHORS']?></td>
                        <td><?=$row['PUBLISHER']?></td>
                        <td><?=$row['YEAR']?></td>
                        <td><?=$row['CNO']?></td>
                        <td><?=$row['NAME']?></td>
                        <td><?=$row['DATERENTED']?></td>
                        <td><?=$row['DATERETURNED']?></td>
                      </tr>
                  <?php
                    }
                  ?>
                </tbody>
              </table>
            </div>  <!-- 대출반납기록조회 end -->
            <?php
          } else if($_GET['id'] == 'cur_reserve_list') {  // 회원예약관리
            ?>
            <div class="container">
              <h2 class="text-center"><a href="v_admin_main.php?id=cur_reserve_list" style="text-decoration: none">회원예약관리</a></h2>
              <!--검색필터-->
              <form action="v_admin_main.php?id=cur_reserve_list" method="post">
                <div class="" style="text-align: center; padding: 10px">
                  <input type="text" name="filt_user_id" placeholder="회원ID" maxlength="10" style="width: 70px;">
                  <input type="text" name="user_name" placeholder="회원명" maxlength="10" style="width: 100px;">
                  <input type="text" name="isbn_" placeholder="ISBN" maxlength="10" style="width: 70px;">
                  <input type="text" name="title" placeholder="제목" maxlength="100" style="width: 400px;">
                  <input type="text" name="author" placeholder="저자" maxlength="20" style="width: 150;">
                  <input type="text" name="publisher" placeholder="출판사" maxlength="20" style="width: 150;">
                  <input type="text" name="year" placeholder="출판년도" maxlength="4" style="width: 100px;">
                  <input type="submit" name="search" value="검색" maxlength="5" style="width: 150;">
                  <input type="button" value="초기화" onclick="location.href='v_admin_main.php?id=cur_reserve_list'">
                </div>
              </form>
              <!--검색 필터 바탕으로 쿼리구성-->
              <?php
                $filter = "";
                if(!empty($_POST['filt_user_id'])) {
                  $filter .= "AND R.CNO = {$_POST['filt_user_id']}";
                }
                if(!empty($_POST['user_name'])) {
                  $filter .= "AND C.NAME LIKE '%".$_POST['user_name']."%'";
                }
                if(!empty($_POST['isbn_'])) {
                  $filter .= "AND R.ISBN = {$_POST['isbn_']}";
                }
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
                  <th>예약일</th>
                  <th>예약자 ID</th>
                  <th>예약자명</th>
                  <th>대기순번</th>
                  <th>예약취소</th>
                </thead>
                <tbody>
                  <?php
                  // 예약중인 도서 목록 출력 쿼리
                    $stmt = $conn -> prepare("SELECT R.ISBN ISBN, R.CNO CNO, E.TITLE TITLE, LISTAGG(A.AUTHOR, ',') WITHIN GROUP(ORDER BY A.AUTHOR) AS AUTHORS,
            E.PUBLISHER PUBLISHER, EXTRACT(YEAR FROM E.YEAR) YEAR, R.DATETIME DATETIME, C.NAME NAME
            FROM RESERVE R, EBOOK E, AUTHORS A, CUSTOMER C
            WHERE R.ISBN = E.ISBN AND R.ISBN = A.ISBN AND R.CNO = C.CNO {$filter}
            group by E.TITLE, R.CNO, E.PUBLISHER, EXTRACT(YEAR FROM E.YEAR), E.YEAR, R.ISBN, R.DATETIME, C.NAME
            ORDER BY R.ISBN, R.DATETIME ASC");
                    $stmt -> execute();
                    while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                  ?>
                      <tr>
                        <td><?=$row['ISBN']?></td>
                        <td><?=$row['TITLE']?></td>
                        <td><?=$row['AUTHORS']?></td>
                        <td><?=$row['PUBLISHER']?></td>
                        <td><?=$row['YEAR']?></td>
                        <td><?=$row['DATETIME']?></td>
                        <td><?=$row['CNO']?></td>
                        <td><?=$row['NAME']?></td>
                        <!-- 현재 사용자의 현재 도서에 대한 대기순번을 가져오는 쿼리 -->
                        <?php
                          $stmt2 = $conn -> prepare("SELECT RANK
            FROM (SELECT CNO, RANK() OVER (ORDER BY DATETIME) RANK FROM RESERVE WHERE ISBN = {$row['ISBN']})
            WHERE CNO = {$row['CNO']}");
                          $stmt2 -> execute();
                          $row2 = $stmt2 -> fetch(PDO::FETCH_ASSOC);
                        ?>
                        <td><?=$row2['RANK']?></td>
                        <!--예약취소버튼 -->
                        <?php echo "<td><a href=\"user_process_reserve_cancel.php?isbn={$row['ISBN']}&cno={$row['CNO']}\">취소</a></td>";
                        ?>
                      </tr>
                  <?php
                    }
                  ?>
                </tbody>
              </table>
            </div>
          <?php
        } else if ($_GET['id'] == 'stat') {  // 대출통계조회
          ?>
          <!-- 대출통계조회 1 (다독자) -->
          <span class="container">
            <h2 class="text-center"><a href="v_admin_main.php?id=stat" style="text-decoration: none">회원별 대출건수 (다독자순)</a></h2>

            <table class="table table-bordered text-center">
              <thead>
                <th>회원ID</th>
                <th>이름</th>
                <th>대출건수</th>
              </thead>
              <tbody>
                <?php
                  // 각 회원별 대출건수를 대출건수 순으로 내림차순 출력하는 쿼리
                  $stmt = $conn -> prepare("SELECT P.CNO CNO, C.NAME NAME, COUNT(*) 대출건수
                  FROM PREVIOUSRENTAL P, CUSTOMER C
                  WHERE P.CNO = C.CNO
                  GROUP BY P.CNO, C.NAME
                  ORDER BY 대출건수 DESC");
                  $stmt -> execute();
                  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                      <td><?=$row['CNO']?></td>
                      <td><?=$row['NAME']?></td>
                      <td><?=$row['대출건수']?></td>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          </span>  <!-- 대출통계조회 1 (다독자) end -->

          <span class="container"> <!-- 대출통계조회 2 (인기도서)-->
            <h2 class="text-center"><a href="v_admin_main.php?id=stat" style="text-decoration: none">도서별 대출건수 (인기도서순)</a></h2>

            <table class="table table-bordered text-center">
              <thead>
                <th>ISBN</th>
                <th>제목</th>
                <th>대출건수</th>
              </thead>
              <tbody>
                <?php
                  // 각 도서별 대출건수를 인기도서순으로 출력하는 쿼리
                  $stmt = $conn -> prepare("SELECT P.ISBN ISBN, E.TITLE TITLE, COUNT(*) 대출건수
                  FROM PREVIOUSRENTAL P, EBOOK E
                  WHERE P.ISBN = E.ISBN
                  GROUP BY P.ISBN, E.TITLE
                  ORDER BY 대출건수 DESC");
                  $stmt -> execute();
                  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                      <td><?=$row['ISBN']?></td>
                      <td><?=$row['TITLE']?></td>
                      <td><?=$row['대출건수']?></td>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          </span>  <!-- 대출통계조회 2 end -->

          <span class="container"> <!-- 대출통계조회 3 (종합대출기록)-->
            <h2 class="text-center"><a href="v_admin_main.php?id=stat" style="text-decoration: none">종합대출기록</a></h2>

            <table class="table table-bordered text-center">
              <thead>
                <th>제목</th>
                <th>대출자</th>
                <th>총 대출건수</th>
              </thead>
              <tbody>
                <?php
                  // CUBE 함수를 이용하여 종합 대출기록을 출력하는 쿼리
                  $stmt = $conn -> prepare("SELECT
                  CASE GROUPING(E.TITLE)
                   WHEN 1         THEN 'All E-Books'
                   ELSE E.TITLE   END AS 제목,
                  CASE GROUPING(C.NAME)
                   WHEN 1         THEN 'All Customers'
                   ELSE C.NAME    END AS 대출자,
                  COUNT(*) TOTAL
                  FROM PREVIOUSRENTAL P, CUSTOMER C, EBOOK E
                  WHERE P.CNO = C.CNO AND P.ISBN = E.ISBN
                  GROUP BY CUBE(E.TITLE, C.NAME)");
                  $stmt -> execute();
                  while ($row = $stmt -> fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                      <td><?=$row['제목']?></td>
                      <td><?=$row['대출자']?></td>
                      <td><?=$row['TOTAL']?></td>
                    </tr>
                <?php
                  }
                ?>
              </tbody>
            </table>
          </span>  <!-- 대출통계조회 3 end -->
          <?php
        }
          ?>
        </div>
      </div>
    </div>
  </body>
</html>
