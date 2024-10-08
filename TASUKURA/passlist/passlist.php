<?php
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

?>
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

                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
        <footer></footer>
    </body>
