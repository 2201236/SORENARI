// コピーを実行するボタン
document.querySelectorAll(".copy_button").forEach(button => {
    button.addEventListener("click", function() {
        copy(this);  // thisはクリックされたボタン要素
    });
});

// コピー
function copy(button) {
    // コピーする入力フィールドの値を取得
    const row = button.closest("tr");
    const text = row.querySelector("td:nth-child(3)").textContent.trim();

    // Clipboard APIを使ってクリップボードにテキストをコピー
    navigator.clipboard.writeText(text)
    .then(() => {
        // コピー成功時のフィードバック
        document.getElementById("feedback").textContent = "コピーしました";
    })
    .catch(err => {
        // コピー失敗時のエラー処理
        document.getElementById("feedback").textContent = "コピーに失敗しました";
        console.error("コピーエラー: ", err);
    });
}