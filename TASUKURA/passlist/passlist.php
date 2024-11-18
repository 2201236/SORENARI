<?php
    session_start();

    const SERVER = 'mysql310.phy.lolipop.lan';
    const DBNAME = 'LAA1517469-taskura';
    const USER ='LAA1517469';
    const PASS ='1234';

    $_SESSION['is_logged_in'] = isset($_SESSION['user_id']) ? true : false;

    // ユーザーがログインしていればpasslistを取得
    if ($_SESSION['is_logged_in']) {
        $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
        $pdo=new PDO($connect, USER, PASS);

        try {
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "接続エラー: " . $e->getMessage();
            exit;
        }

        // 検索ワードの取得と安全な処理
        $search_word = '';
        if (isset($_GET['s'])) {
            // 文字エンコーディングを確実にUTF-8に
            $search_word = mb_convert_encoding(trim($_GET['s']), 'UTF-8', 'AUTO');
            
            // HTMLスペシャルキャラクターをエスケープ
            $search_word = htmlspecialchars($search_word, ENT_QUOTES, 'UTF-8');
        }

        if ($search_word) {
            // 検索ワードがある場合に部分一致検索
            $sql = "SELECT pass_id, URL, passName FROM PassList WHERE user_id = ? AND (URL LIKE ? OR passName LIKE ?)";
            $stmt = $pdo->prepare($sql);
        
            // 部分一致検索用のワイルドカードを検索ワードに追加
            $search_term = '%' . $search_word . '%';
            $stmt->execute([$_SESSION['user_id'], $search_term, $search_term]);
        
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // 検索ワードが空の場合、全件を取得
            $sql = "SELECT pass_id, URL, passName FROM PassList WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_SESSION['user_id']]);
            $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $list = [];
    }

?>
<!DOCTYPE html>
<html lang="js">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>パスワード</title>
        <link rel="stylesheet" href="css/pstyle.css">
        <link rel="stylesheet" href="css/modal.css">
    </head>
    <body>
        <header>
            <?php require '../header/header2.php' ?>
        </header>
        <main>
            <div class="container">
                <div class="container_header">
                    <div class="search_window">
                        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="get" class="search_form">
                            <div class="search_bar">
                                <input type="text" autocomplete="off" aria-autocomplete="list" aria-controls="react-autowhatever-1" class="search_word_input" .
                                placeholder="パスワードを検索" name="s" value="" spellcheck="false" data-ms-editor="true">
                            </div>
                            <div class="search_button">
                                <button type="submit" class="search_word_submit_button">
                                    検索
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="open_add_modal_button_wrapper">
                        <!-- 押したら追加フォームのモーダルが開く -->
                        <button class="open_add_modal" id="open_add_modal">
                            パスワードを追加
                        </button>
                    </div>
                </div>
                <div class="table_wrapper">
                    <table class="table_header"> 
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th>ユーザーID</th>
                                <th>パスワード</th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="table_container">
                            <?php foreach($list as $item):?>
                                <tr>
                                    <td>
                                        <input type="hidden" class="pass_id" value="<?php echo $item['pass_id']; ?>" />
                                        <?php echo htmlspecialchars($item['URL'], ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($item['passName'], ENT_QUOTES, 'UTF-8');?>
                                    </td>
                                    <td>
                                        <span class="passtxt">***************</span>
                                    </td>
                                    <td class="buttons_view">
                                        <div class="buttons_wrapper">
                                            <div class="button_wrapper">
                                                <button type="button" class="toggle_passtxt_button">表示</button>
                                            </div>
                                            <div class="button_wrapper">
                                                <button type="button" class="copy_button">コピー</button>
                                            </div>
                                        </div>
                                        <div class="buttons_wrapper">
                                            <div class="button_wrapper">
                                                <button class="open_edit_modal">編集</button>
                                            </div>
                                            <div class="button_wrapper">
                                                <button class="del_button">削除</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 追加用モーダル -->
            <div id="add_modal" class="modal_wrapper">
                <div class="modal_content">
                    <span id="add_modal_close" class="modal_close">&times;</span>
                    <form method="post" class="modal_form" id="add_form">
                        <div class="form_group">
                            <label for="add_url">URL:</label>
                            <input type="text" class="url" id="add_url" name="url">
                        </div>
                        <div class="form_group">
                            <label for="add_passName">ユーザーID:</label>
                            <input type="text" class="passName" id="add_passName" name="passName">
                        </div>
                        <div class="form_group">
                            <label for="add_passtxt">パスワード:</label>
                            <input type="password" class="passtxt" id="add_passtxt" name="passtxt" minlength="4" required>
                        </div>
                        <button type="submit" class="submit_button">追加</button>
                    </form>
                </div>
            </div>

            <!-- 編集用モーダル -->
            <div id="edit_modal" class="modal_wrapper">
                <div class="modal_content">
                    <span id="edit_modal_close" class="modal_close">&times;</span>
                    <form method="post" class="modal_form" id="edit_form">
                        <input type="hidden" id="pass_id" name="pass_id" />
                        <div class="form_group">
                            <label for="edit_url">URL:</label>
                            <input type="text" class="url" id="edit_url" name="url">
                        </div>
                        <div class="form_group">
                            <label for="edit_passName">ユーザーID:</label>
                            <input type="text" class="passName" id="edit_passName" name="passName">
                        </div>
                        <div class="form_group">
                            <label for="edit_passtxt">パスワード:</label>
                            <input type="password" class="passtxt" id="edit_passtxt" name="passtxt" minlength="4">
                        </div>
                        <button type="submit" class="submit_button">変更を保存</button>
                    </form>
                </div>
            </div>

            <!-- 認証用モーダル -->
            <div id="auth_modal" class="modal_wrapper">
                <div class="modal_content">
                    <span id="auth_modal_close" class="modal_close">&times;</span>
                    <form method="post" class="modal_form" id="auth_form">
                        <div class="form_group" id="auth_form_group_passName">
                            <label for="auth_passName">ユーザーID:</label>
                            <input type="text" class="passName" id="auth_passName" name="user_id" placeholder="メールアドレス" required>
                        </div>
                        <div class="form_group">
                            <label for="auth_passtxt">パスワード:</label>
                            <input type="password" class="passtxt" id="auth_passtxt" name="passtxt" placeholder="パスワード" required>
                        </div>
                        <button type="submit" class="submit_button">認証</button>
                    </form>
                </div>
            </div>

        </main>
        <footer></footer>
        <span id="feedback"></span>

        <!-- スクリプト導入 -->
        <div id="hidden_container" style="display: none">
            <script>
                const isLoggedIn = <?php echo json_encode($_SESSION['is_logged_in']); ?>;
                let userId;
                
                <?php if (isset($_SESSION['user_id'])): ?>
                userId = <?php echo json_encode($_SESSION['user_id']); ?>;
                <?php endif; ?>
            </script>
        </div> 
        <script src="js/modal.js"></script>
        <script src="js/transmission.js"></script>
        <script src="js/manipulate.js"></script>
        <script src="js/auth_check.js"></script>
    </body>
</html>