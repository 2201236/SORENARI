<!-- <?php
    session_start();

     const SERVER = 'mysql310.phy.lolipop.lan';
     const DBNAME = 'LAA1517469-taskura';
     const USER ='LAA1517469';
     const PASS ='1234';
    $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
    $pdo=new PDO('mysql:host=mysql310.phy.lolipop.lan;dbname=LAA1517469-taskura;charset=utf8','LAA1517469','1234');

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

?> -->
<!DOCTYPE html>
<html lang="js">
    <head>
        <meta charset="UTF-8" />
        <title></title>
    </head>
    <body>
        <header>
            <?php require '../header/header.php' ?>
        </header>
        <main>
            <div class="container">
                <div class="left_side">
                    <div class="search_window"></div>
                    <div class="search_preset"></div>
                </div>
                <div class="right_side">
                    <div class="table_wrapper">
                        <table class="pass_table">
                            <thead>
                                <tr>
                                    <th>URL</th>
                                    <th>ユーザーID</th>
                                    <th>パスワード</th>
                                    <th>安全性</th>
                                </tr>
                            </thead>
                            <tbody class="table_container">
                                <!-- <?php foreach($list as $item):?> -->
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
                                            <?php htmlspecialchars($item['passtxt'], ENT_QUOTES, 'UTF-8');?>
                                            <!-- コピー用button -->
                                            <!-- 再認証しないと機能しない -->
                                            <div class="copy_button"></div>
                                        </td>
                                        <td>
                                            {{safe_param}}
                                        </td>
                                    </tr>
                                    <!-- 何処かに編集用ボタンを置く -->
                                <!-- <?php endforeach;?> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <footer></footer>
        <script src="passlist.js"></script>
    </body>
