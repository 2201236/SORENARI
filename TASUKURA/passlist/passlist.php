<?php
session_start();

const SERVER = 'mysql310.phy.lolipop.lan';
const DBNAME = 'LAA1517469-taskura';
const USER = 'LAA1517469';
const PASS = '1234';

$_SESSION['is_logged_in'] = isset($_SESSION['user_id']) ? true : false;

if ($_SESSION['is_logged_in']) {
    $connect = 'mysql:host=' . SERVER . ';dbname=' . DBNAME . ';charset=utf8';

    try {
        $pdo = new PDO($connect, USER, PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラーモードを例外に設定
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC // デフォルトのフェッチモード
        ]);
    } catch (PDOException $e) {
        echo "接続エラー: " . $e->getMessage();
        exit;
    }

    // 検索ワード処理の関数
    function getSearchWord(): string
    {
        if (isset($_GET['s'])) {
            // 文字エンコーディングをUTF-8にし、HTMLエスケープ
            return htmlspecialchars(mb_convert_encoding(trim($_GET['s']), 'UTF-8', 'AUTO'), ENT_QUOTES, 'UTF-8');
        }
        return '';
    }
  
    // パスワードリスト取得の関数
    function fetchPassList(PDO $pdo, string $user_id, string $search_word = ''): array
    {
        try {
            if ($search_word) {
                $sql = "SELECT pass_id, URL, passName 
                        FROM PassList 
                        WHERE user_id = ? AND (URL LIKE ? OR passName LIKE ?)";
                $stmt = $pdo->prepare($sql);
                $search_term = '%' . $search_word . '%';
                $stmt->execute([$user_id, $search_term, $search_term]);
            } else {
                $sql = "SELECT pass_id, URL, passName 
                        FROM PassList 
                        WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$user_id]);
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("データ取得エラー: " . $e->getMessage());
            return [];
        }
    }

    // 検索処理とリスト取得
    $search_word = getSearchWord();
    $list = fetchPassList($pdo, $_SESSION['user_id'], $search_word);
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
                    <table> 
                        <thead>
                            <tr>
                                <th class="thead_row1">URL</th>
                                <th class="thead_row2">ユーザーID</th>
                                <th class="thead_row3">パスワード</th>
                                <th class="thead_row4"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($list as $item):?>
                                <tr>
                                    <td data-label="URL" class="tbody_row">
                                        <input type="hidden" class="pass_id" value="<?php echo $item['pass_id']; ?>" />
                                        <span>
                                            <?php echo htmlspecialchars($item['URL'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td data-label="ユーザーID" class="tbody_row">
                                        <span>
                                            <?php echo htmlspecialchars($item['passName'], ENT_QUOTES, 'UTF-8');?>
                                        </span>
                                    </td>
                                    <td data-label="パスワード">
                                        <span class="passtxt">***************</span>
                                    </td>
                                    <td data-label="操作ボタン群" class="buttons_view">
                                        <button class="menu_toggle">⋮</button>
                                        <div class="menu_dropdown">
                                            <button type="button" class="toggle_passtxt_button">表示</button>
                                            <button type="button" class="copy_button">コピー</button>
                                            <button class="open_edit_modal">編集</button>
                                            <button class="del_button">削除</button>
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
                const feedback_element = document.getElementById('feedback');
                const feedback = localStorage.getItem('feedback');
                const isLoggedIn = <?php echo json_encode($_SESSION['is_logged_in']); ?>;
                let userId;
                
                if (feedback) {
                    feedback_element.textContent = feedback;
                    localStorage.removeItem('feedback'); // メッセージを消去
                }

                <?php if (isset($_SESSION['user_id'])): ?>
                userId = <?php echo json_encode($_SESSION['user_id']); ?>;
                <?php endif; ?>

                document.addEventListener('click', (event) => {
                    const dropdown = document.querySelector('.menu_dropdown.active');
                    if (dropdown && !dropdown.contains(event.target) && !event.target.classList.contains('menu_toggle')) {
                        dropdown.classList.remove('active');
                    }
                });

                document.addEventListener('DOMContentLoaded', () => {
                    const toggles = document.querySelectorAll('.menu_toggle');

                    toggles.forEach(toggle => {
                        toggle.addEventListener('click', () => {
                        const dropdown = toggle.nextElementSibling; // ボタンに対応するドロップダウン
                        if (!dropdown) return;

                        // 他のすべてのドロップダウンを閉じる
                        document.querySelectorAll('.menu_dropdown').forEach(menu => {
                            if (menu !== dropdown) {
                            menu.classList.remove('active');
                            menu.classList.remove('top', 'bottom');
                            }
                        });

                        // 現在のドロップダウンを切り替え
                        dropdown.classList.toggle('active');

                        // ウィンドウの高さやボタンの位置を取得
                        const toggleRect = toggle.getBoundingClientRect();
                        const dropdownHeight = dropdown.offsetHeight;
                        const viewportHeight = window.innerHeight;

                        const offset = toggleRect.height;

                        // 上に出すか下に出すか判定
                        if (toggleRect.bottom + dropdownHeight > viewportHeight - offset) {
                            dropdown.classList.add('top');
                            dropdown.classList.remove('bottom');
                        } else {
                            dropdown.classList.add('bottom');
                            dropdown.classList.remove('top');
                        }
                        });
                    });
                });

                // イベントリスナーを画面リサイズ時に追加
                window.addEventListener('resize', () => {
                    // 現在の画面幅を取得
                    const screenWidth = window.innerWidth;

                    if (screenWidth <= 640) {
                        // すべてのトグル要素を取得
                        document.querySelectorAll('.toggle').forEach(toggle => {
                            toggle.addEventListener('click', () => {
                                const dropdown = toggle.nextElementSibling; // ボタンに対応するドロップダウン
                                if (!dropdown) return;

                                // 他のすべてのドロップダウンを閉じる
                                document.querySelectorAll('.menu_dropdown').forEach(menu => {
                                    if (menu !== dropdown) {
                                        menu.classList.remove('active');
                                        menu.classList.remove('top', 'bottom');
                                    }
                                });

                                // 現在のドロップダウンをトグルする
                                dropdown.classList.toggle('active');
                            });
                        });
                    }
                });

                document.querySelectorAll('.tbody_row1').forEach(td => {
                    const content = td.textContent;
                    if (td.scrollWidth > td.clientWidth) {
                        td.title = content; // 全内容のツールチップを表示
                    }
                });
            </script>
        </div> 
        <script src="js/modal.js"></script>
        <script src="js/transmission.js"></script>
        <script src="js/manipulate.js"></script>
        <script src="js/auth_check.js"></script>
    </body>
</html>