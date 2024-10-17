<?php
    session_start();

    const SERVER = 'mysql310.phy.lolipop.lan';
    const DBNAME = 'LAA1517469-taskura';
    const USER ='LAA1517469';
    const PASS ='1234';
    $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
    $pdo=new PDO($connect, USER, PASS);

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "接続エラー: " . $e->getMessage();
        exit;
    }

    $sql= "SELECT URL, passName, passtxt FROM PassList WHERE user_id = ? ";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $list = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="js">
    <head>
        <meta charset="UTF-8" />
        <title>パスワード</title>
        <link rel="stylesheet" href="css/pstyle.css">
        <link rel="stylesheet" href="css/modal.css">
        <link rel="stylesheet" href="../header/css/header.css">
    </head>
    <body>
        <header>
            <?php require '../header/header.php' ?>
        </header>
        <main>
        <div class="container">
                <div class="left_side">
                    <div class="search_window">
                        <form action="" method="post" class="search_form">
                            <div class="search_bar">
                                <input type="text" autocomplete="off" aria-autocomplete="list" aria-controls="react-autowhatever-1" class="search_word_input" .
                                placeholder="パスワードを検索" name="search_word_input" value="" spellcheck="false" data-ms-editor="true">
                            </div>
                            <div class="search_button">
                                <button type="submit" class="search_word_submit_button">
                                    検索
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="search_preset">
                        <!-- ここに検索ワードのプリセットを表示 -->
                        <div class="search_preset_item">Google</div>
                        <div class="search_preset_item">Microsoft</div>
                    </div>
                </div>
                <div class="right_side">
                    <div class="open_add_modal_button_wrapper">
                        <!-- 押したら追加フォームのモーダルが開く -->
                        <button class="open_add_modal" id="open_add_modal">
                            パスワードを追加
                        </button>
                    </div>
                    <div class="table_wrapper">
                        <table class="pass_table"> 
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>ユーザーID</th>
                                    <th>パスワード</th>
                                    <th>安全性</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody class="table_container">
                                <?php foreach($list as $item):?>
                                    <tr>
                                        <td>
                                            <!-- 当該サイトに飛べる？ -->
                                            <?php echo htmlspecialchars($item['URL'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($item['passName'], ENT_QUOTES, 'UTF-8');?>
                                        </td>
                                        <td>
                                            <!-- 基本的にマスクしておく -->
                                            <!-- 表示する際は再認証させる -->
                                            <!-- 表示用ボタンを後で作る -->
                                            <?php echo htmlspecialchars($item['passtxt'], ENT_QUOTES, 'UTF-8');?>
                                            <!-- 表示用ボタンを後で作る -->
                                            <!-- 再認証しないと機能しない -->
                                            <div class="show_passtxt_button">
                                                表示用ボタンスペース
                                            </div>
                                            <!-- コピー用button -->
                                            <!-- 再認証しないと機能しない -->
                                            <div class="copy_button">
                                                コピー用ボタンスペース
                                            </div>
                                        </td>
                                        <td>
                                            <!-- パスワード強度表示バー -->
                                            <div class="safe_param_bar">
                                                <div class="bar" :style="{ width: safe_param_bar_width + '%' }"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <!-- 編集用ボタン -->
                                            <button class="open_edit_modal">
                                                編集
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- 追加用モーダル -->
        <div id="add_modal" class="add_modal">
                <div class="modal_content">
                    <span class="add_modal_close">&times;</span>
                    <form action="" method="post" class="add_form">
                        <div class="add_form_group">
                            <label for="url">URL:</label>
                            <input type="text" id="url" name="url" required>
                        </div>
                        <div class="add_form_group">
                            <label for="user_id">ユーザーID:</label>
                            <input type="text" id="user_id" name="user_id" required>
                        </div>
                        <div class="add_form_group">
                            <label for="password">パスワード:</label>
                            <input type="password" id="password" name="password" minlength="4" required>
                        </div>
                        <button type="submit" class="add_submit_button">追加</button>
                    </form>
                </div>
            </div>

            <!-- 編集用モーダル -->
            <div id="edit_modal" class="edit_modal">
                <div class="modal_content">
                    <span class="edit_modal_close">&times;</span>
                    <form action="" method="post" class="add_form">
                        <div class="edit_form_group">
                            <label for="url">URL:</label>
                            <input type="text" id="url" name="url" required>
                        </div>
                        <div class="edit_form_group">
                            <label for="user_id">ユーザーID:</label>
                            <input type="text" id="user_id" name="user_id" required>
                        </div>
                        <div class="edit_form_group">
                            <label for="password">パスワード:</label>
                            <input type="password" id="password" name="password" minlength="4" required>
                        </div>
                        <button type="submit" class="edit_submit_button">追加</button>
                    </form>
                </div>
            </div>

            <!-- 再認証用モーダル -->
            <div id="re_auth_modal" class="re_auth_modal">
                <div class="modal_content">
                    <span class="re_auth_modal_close">&times;</span>
                    <form action="" method="post" class="add_form">
                        <div class="re_auth_form_group">
                            <label for="password">パスワード:</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="show_submit_button">認証</button>
                    </form>
                </div>
            </div>

        </main>
        <footer></footer>

        <!-- スクリプト導入 -->
        <script src="js/modal.js"></script>
        <script src="js/safe_param.js" async></script>
    </body>