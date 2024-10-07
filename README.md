# SORENARI

# 命名規則<br>
スネークケースでお願いします。(例: name_kurihara)

URL
ログイン
https://aso2201143.zombie.jp/TASUKURA/login/input.php
 
新規登録画面
https://aso2201143.zombie.jp/TASUKURA/new_registration/input.php
 
ホーム画面
https://aso2201143.zombie.jp/TASUKURA/home/home.

データベース
ログイン情報
aso2201143　zombie.jp
パスワード
Pass0828

サーバー名
mysql304.phy.lolipop.lan
DB名
LAA1517469-taskura
パスワード
Pass1234

<?php
     const SERVER = 'mysql304.phy.lolipop.lan';
     const DBNAME = 'LAA1517469-sistem';
     const USER ='LAA1517469';
     const PASS ='Pass1234';
    $connect = 'mysql:host='. SERVER . ';dbname='. DBNAME . ';charset=utf8';
    $pdo=new PDO('mysql:host=mysql304.phy.lolipop.lan;dbname=LAA1517469-taskura;charset=utf8','LAA1517469','1234');

?>
