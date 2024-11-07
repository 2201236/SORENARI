// AJAXリクエストを処理するヘルパー関数
function sendData(url, body) {
    return fetch(url, {
        method: 'POST',
        body: body
    }).then(response => response.json());
}

function clearFeedback(element, timeout) {
    setTimeout(() => {
        element.textContent = "";
    }, timeout);
}


// 追加・更新
function handleSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this);

    sendData('update_data.php', formData)
        .then(data => {
            if (data.success) {
                window.location.replace('passlist.php');
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
                    window.location.replace('passlist.php');
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

// 認証処理
function handleAuth(e) {
    e.preventDefault();
    const feedback_element = document.getElementById('feedback'); // フィードバック要素の取得
    const formData = new FormData(e.target);

    return sendData('auth.php', formData)
        .then(result => {
            if (result.success) {
                return true; // 認証成功
            } else {
                return false; // 認証失敗
            }
        })
        .catch(error => {
            console.error("エラー:", error);
            feedback_element.textContent = "エラーが発生しました";
            clearFeedback(feedback_element, 3000);
            return false; // エラーが発生した場合も失敗
        });
}


// イベントの呼び出し
document.getElementById("add_form").addEventListener("submit", handleSubmit);
document.getElementById("edit_form").addEventListener("submit", handleSubmit);
document.querySelectorAll(".del_button").forEach(button => {
    button.addEventListener("click", handleDelete);
});