<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);

    require_once('./view/scarlet/index.php');
    $ver = 2;

    $conn = new PDO('sqlite:data.db');
    session_start();

    function file_fix($url) {
        return preg_replace('/\/index.php$/' , '', $_SERVER['PHP_SELF']).$url;
    }

    function start_init($conn) {
        $sql = $conn -> prepare('create table if not exists data(user text, link text, site text, problem text uploder text)');
        $sql -> execute([]);

        $sql = $conn -> prepare('create table if not exists user(name text, password text, acl text)');
        $sql -> execute([]);
    }

    function start_update($conn, $ver) {

    }

    function redirect($data) {
        return "<script>window.location.href = '".$data."';</script>";
    }

    start_init($conn);
    start_update($conn, $ver);

    if($_GET['v']) {
        if($_GET['v'] === 'main') {
            $sql = $conn -> prepare('select user, site, problem, link from data');
            $sql -> execute([]);
            $sql_data = $sql -> fetchAll();

            $data = "
                <table style=\"width: 100%; text-align: center;\">
                    <tr style=\"background: #eee;\">
                        <td style=\"width: 25%;\">기록자</td>
                        <td style=\"width: 25%;\">사이트</td>
                        <td style=\"width: 25%;\">문제 번호</td>
                        <td style=\"width: 25%;\">링크</td>
                    </tr>
            ";

            $i = 0;
            while($sql_data[$i]) {
                $data = $data."
                    <tr>
                        <td>".htmlspecialchars($sql_data[$i][0])."</td>
                        <td>".htmlspecialchars($sql_data[$i][1])."</td>
                        <td>".htmlspecialchars($sql_data[$i][2])."</td>
                        <td><a href=\"".htmlspecialchars($sql_data[$i][3])."\">링크</a></td>
                    </tr>
                ";

                $i += 1;
            }

            $data = $data.'</table>';

            if($_SESSION["id"]) {
                $menu = [["추가", "?v=add"]];
            } else {
                $menu = [];
            }

            echo load_render('메인', $data, $menu);
        } else if($_GET['v'] === 'user') {
            if($_SESSION["id"]) {
                $state = $_SESSION["id"];
            } else {
                $state = "비로그인";
            }

            $data = "
                <ul>
                    <li>로그인 상태 : ".$state."</li>
                    <br>
                    <li><a href=\"?v=register\">회원가입</a></li>
                    <li><a href=\"?v=login\">로그인</a></li>
                    <li><a href=\"?v=logout\">로그아웃</a></li>
                </ul>
            ";

            echo load_render('사용자 페이지', $data);
        } else if($_GET['v'] === 'add') {
            if($_SESSION["id"]) {
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if($_POST["site"] !== '' && $_POST["q_num"] !== '') {
                        if(!preg_match("/^http(s)?:\/\//i", $_POST["link"])) {
                            $link = "";
                        } else {
                            $link = $_POST["link"];
                        }

                        $sql = $conn -> prepare('insert into data (user, link, site, problem) values (?, ?, ?, ?)');
                        $sql -> execute([$_SESSION["id"], $link, $_POST["site"], $_POST["q_num"]]);

                        echo redirect('?v=main');
                    } else {
                        echo load_render('오류', '필수 항목을 입력하세요.');
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            사이트 (필수)
                            <br>
                            <input name=\"site\">
                            <br>
                            <br>
                            문제 번호 (필수)
                            <br>
                            <input name=\"q_num\">
                            <br>
                            <br>
                            링크
                            <br>
                            <input name=\"link\">
                            <br>
                            <br>
                            <button type=\"submit\">추가</buttom>
                        </form>
                    ";
    
                    echo load_render('기록 추가', $data, [["돌아가기", "?v=main"]]);
                }
            } else {
                echo redirect('?v=login');
            }
        } else if($_GET['v'] === 'register') {
            if($_SERVER['REQUEST_METHOD'] === 'POST') {
                if($_POST["id"] !== '' && $_POST["password"] !== '' && $_POST["repeat"] !== '') {
                    if(!preg_match("/^[a-zA-Z0-9ㄱ-힣]+$/", $_POST["id"])) {
                        echo load_render('오류', '아이디에는 알파벳, 숫자, 한글만 허용됩니다.');
                    } else {
                        if($_POST["password"] !== $_POST["repeat"]) {
                            echo load_render('오류', '비밀번호와 비밀번호 재확인이 일치하지 않습니다.');
                        } else {
                            $sql = $conn -> prepare('select name from user where name = ?');
                            $sql -> execute([$_POST["id"]]);
                            $sql_data = $sql -> fetchAll();
                            if($sql_data) {
                                echo load_render('오류', '동일한 아이디의 사용자가 있습니다.');
                            } else {
                                $sql = $conn -> prepare('select name from user limit 1');
                                $sql -> execute([]);
                                $sql_data = $sql -> fetchAll();
                                if($sql_data) {
                                    $acl = 'owner';
                                } else {
                                    $acl = 'normal';
                                }

                                $sql = $conn -> prepare('insert into user (name, password, acl) values (?, ?, ?)');
                                $sql -> execute([$_POST["id"], hash("sha256", $_POST["password"]), $acl]);

                                echo redirect('?v=login');
                            }
                        }
                    }
                } else {
                    echo load_render('오류', '모든 항목을 입력하세요.');
                }
            } else {
                $data = "
                    <form method=\"post\">
                        아이디
                        <br>
                        <input name=\"id\">
                        <br>
                        <br>
                        비밀번호
                        <br>
                        <input type=\"password\" name=\"password\">
                        <br>
                        <br>
                        비밀번호 재확인
                        <br>
                        <input type=\"password\" name=\"repeat\">
                        <br>
                        <br>
                        <button type=\"submit\">가입</buttom>
                    </form>
                ";

                echo load_render('회원가입', $data, [["돌아가기", "?v=user"]]);
            }    
        } else if($_GET["v"] === 'login') {
            if($_SESSION["id"]) {
                echo load_render('오류', '이미 로그인 되어 있습니다.');
            } else {
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if($_POST["id"] !== '' && $_POST["password"] !== '') {
                        $sql = $conn -> prepare('select password from user where name = ?');
                        $sql -> execute([$_POST["id"]]);
                        $sql_data = $sql -> fetchAll();
                        if($sql_data) {
                            if(hash("sha256", $_POST["password"]) === $sql_data[0]["password"]) {
                                $_SESSION["id"] = $_POST["id"];

                                echo redirect('?v=user');
                            } else {
                                echo load_render('오류', '비밀번호가 다릅니다.');    
                            }
                        } else {
                            echo load_render('오류', '계정이 없습니다.');    
                        }
                    } else {
                        echo load_render('오류', '모든 항목을 입력하세요.');
                    }
                } else {
                    $data = "
                        <form method=\"post\">
                            아이디
                            <br>
                            <input name=\"id\">
                            <br>
                            <br>
                            비밀번호
                            <br>
                            <input type=\"password\" name=\"password\">
                            <br>
                            <br>
                            <button type=\"submit\">로그인</buttom>
                        </form>
                    ";

                    echo load_render('로그인', $data, [["돌아가기", "?v=user"]]);
                }
            }
        } else if($_GET["v"] === 'logout') {
            if($_SESSION["id"]) {
                $_SESSION["id"] = NULL;

                echo redirect('?v=user');
            } else {
                echo redirect('?v=login');
            }
        } else {
            http_response_code(404);
            echo redirect('?v=main');    
        }
    } else {
        http_response_code(404);
        echo redirect('?v=main');
    }
?>