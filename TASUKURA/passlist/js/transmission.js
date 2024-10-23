// イベントの呼び出し
document.getElementById("add_form").addEventListener("submit", handle_submit);
document.getElementById("edit_form").addEventListener("submit", handle_submit);
document.querySelectorAll(".del_button").forEach(button => {
    button.addEventListener("click", handle_delete);
});


// 追加・更新
function handle_submit(e) {
    e.preventDefault(); // デフォルトのフォーム送信を防ぐ

    const formData = new FormData(this);

    send_data('update_data.php', formData)
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
function handle_delete(e) {
    const row = this.closest("tr");
    const pass_id = row.querySelector(".pass_id").value;

    if (confirm("本当にこのアイテムを削除しますか？")) {
        const params = new URLSearchParams({ pass_id: pass_id });

        send_data('delete_data.php', params)
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
function send_data(url, body) {
    return fetch(url, {
        method: 'POST',
        body: body
    })
    .then(response => response.json());
}