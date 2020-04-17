<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL & ~E_NOTICE);

    function load_render($title, $data) {
        $skin_data = "
            <!DOCTYPE html>
            <html>
                <head>    
                    <meta charset=\"utf-8\">
                    <title>".$title."</title>
                    <link rel=\"stylesheet\" href=\"".file_fix("/view/scarlet/css/main.css?ver=1")."\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                </head>
                <body>
                    <header>
                        <span class=\"give_margin\"></span>
                        <a href=\"?v=main\">메인</a> | <a href=\"?v=user\">사용자</a>
                    </header>
                    <section>
                        <div id=\"title\"><h1>".$title."</h1></div>
                        <div id=\"data\">
                            ".$data."
                        </div>
                    </section>
                    <footer>

                    </footer>
                </body>
            </html>
        ";

        return $skin_data;
    }
?>