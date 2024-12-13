// メッセージを設定する関数
function setFeedbackMessage(message) {
    sessionStorage.setItem('feedbackMessage', message);
}

// メッセージを表示して削除する関数
function displayFeedbackMessage() {
    const feedbackMessage = sessionStorage.getItem('feedbackMessage');
    if (feedbackMessage) {
        const feedback_element = document.getElementById('feedback');
        feedback_element.textContent = feedbackMessage;
        clearFeedback(feedback_element); // 既存のクリア関数
        sessionStorage.removeItem('feedbackMessage');
    }
}

// 追加・更新
function handleSubmit(e) {
    const feedback_element = document.getElementById('feedback');
    e.preventDefault();
    const formData = new FormData(this);

    sendData('update_data.php', formData)
        .then(data => {
            if (data.success) {
                setFeedbackMessage(data.message);
                window.location.replace('passlist.php');
            } else {
                feedback_element.textContent = "更新に失敗しました";
                clearFeedback(feedback_element);
            }
        })
        .catch(error => {
            console.error("エラー:", error);
            feedback_element.textContent = "エラーが発生しました";
            clearFeedback(feedback_element);
        });
}

// 削除
async function handleDelete(e) {
    const feedback_element = document.getElementById('feedback');
    const row = this.closest("tr");
    const pass_id = row.querySelector(".pass_id").value;

    if (await mainProcess() && confirm("本当にこのアイテムを削除しますか？")) {
        const params = new URLSearchParams({ pass_id: pass_id });

        sendData('delete_data.php', params)
            .then(data => {
                if (data.success) {
                    setFeedbackMessage('削除が成功しました');
                    window.location.replace('passlist.php');
                } else {
                    feedback_element.textContent = "削除に失敗しました";
                    clearFeedback(feedback_element);
                }
            })
            .catch(error => {
                console.error("エラー:", error);
                feedback_element.textContent = "エラーが発生しました";
                clearFeedback(feedback_element);
            });
    }
}

// ページ読み込み時にフィードバックメッセージを表示
document.addEventListener('DOMContentLoaded', displayFeedbackMessage);

// 認証処理
function handleAuth(e) {
    e.preventDefault();
    const feedback_element = document.getElementById('feedback'); // フィードバック要素の取得
    const formData = new FormData(e.target);

    if (!(typeof userId === 'undefined')) {
        formData.set("user_id", userId);
    }

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
            clearFeedback(feedback_element);
            return false; // エラーが発生した場合も失敗
        });
}

// イベントの呼び出し
document.getElementById("add_form").addEventListener("submit", handleSubmit);
document.getElementById("edit_form").addEventListener("submit", handleSubmit);
document.querySelectorAll(".del_button").forEach(button => {
    button.addEventListener("click", handleDelete);
});