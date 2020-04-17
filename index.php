<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);

    require_once('./view/scarlet/index.php');
    $ver = 1;

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
            $sql -> execute();
            $sql_data = $sql -> fetchAll();

            $data = '<table>';

            $i = 0;
            while($sql_data[$i]) {
                $data = $data."
                    <tr>
                        <td>".$sql_data[$i][0]."</td>
                        <td>".$sql_data[$i][1]."</td>
                        <td>".$sql_data[$i][2]."</td>
                        <td>".$sql_data[$i][3]."</td>
                    </tr>
                ";

                $i += 1;
            }

            $data = $data.'</table>';

            echo load_render('메인', $data);
        } else if($_GET['v'] === 'user') {
            $data = "
                <ul>
                    <li><a href=\"?v=register\">회원가입</a></li>
                    <li><a href=\"?v=login\">로그인</a></li>
                    <li><a href=\"?v=logout\">로그아웃</a></li>
                </ul>
            ";

            echo load_render('사용자 페이지', $data);
        } else {
            http_response_code(404);
            echo redirect('?v=main');    
        }
    } else {
        http_response_code(404);
        echo redirect('?v=main');
    }
?>