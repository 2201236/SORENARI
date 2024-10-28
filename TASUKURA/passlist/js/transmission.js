// イベントの呼び出し
document.getElementById("add_form").addEventListener("submit", handleSubmit);
document.getElementById("edit_form").addEventListener("submit", handleSubmit);
document.querySelectorAll(".del_button").forEach(button => {
    button.addEventListener("click", handleDelete);
});


// 追加・更新
function handleSubmit(e) {
    e.preventDefault(); // デフォルトのフォーム送信を防ぐ

    const formData = new FormData(this);

    sendData('update_data.php', formData)
        .then(data => {
            if (data.success) {
                // データの更新が成功したらリダイレクト
                window.location.href = 'passlist.php';
            } else {
                alert("データの更新に失敗しました");
            }
        })
        .catch(error => {
            console.error("エラー:", error);
            alert("エラーが発生しました");
        });
}

// 削除
function handleDelete(e) {
    const row = this.closest("tr");
    const pass_id = row.querySelector(".pass_id").value;

    if (confirm("本当にこのアイテムを削除しますか？")) {
        const params = new URLSearchParams({ pass_id: pass_id });

        sendData('delete_data.php', params)
            .then(data => {
                if (data.success) {
                    // 削除後、passlist.phpにリダイレクトする。
                    window.location.href = 'passlist.php';
                } else {
                    alert("削除に失敗しました: " + data.error);
                }
            })
            .catch(error => {
                console.error("エラー:", error);
                alert("エラーが発生しました");
            });
    }
}

// AJAXリクエストを処理するヘルパー関数
function sendData(url, body) {
    return fetch(url, {
        method: 'POST',
        body: body
    })
    .then(response => response.json());
}