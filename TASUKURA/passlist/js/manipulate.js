// パスワード表示/非表示ボタン
document.querySelectorAll(".toggle_passtxt_button").forEach(button => {
    button.addEventListener("click", function() {
        toggle_passtxt(this);
    });
});

// コピーを実行ボタン
document.querySelectorAll(".copy_button").forEach(button => {
    button.addEventListener("click", function() {
        copy(this);
    });
});

// パスワード取得の共通処理
async function get_passtxt(row) {
    const pass_id = row.querySelector(".pass_id").value;
    const feedback_element = row.querySelector(".feedback");
    
    try {
        const formData = new FormData();
        formData.append('pass_id', pass_id);

        const data = await send_data("get_passtxt.php", formData);
        if (data.success) {
            return data.passtxt;
        } else {
            feedback_element.textContent = "パスワードの取得に失敗しました";
            clear_feedback(feedback_element, 5000);
            return null;
        }
    } catch (error) {
        console.error("エラー:", error);
        feedback_element.textContent = "パスワードの取得に失敗しました";
        clear_feedback(feedback_element, 5000);
        return null;
    }
}

// パスワードの表示/非表示処理
async function toggle_passtxt(button) {
    const row = button.closest("tr");
    const toggle = button.textContent === "表示" ? "hide" : "show";
    
    switch (toggle) {
        case "hide":
            const passtxt = await get_passtxt(row);
            if (passtxt) {
                row.querySelector(".passtxt").textContent = passtxt;
                button.textContent = "非表示";
            }
            break;

        case "show":
            row.querySelector(".passtxt").textContent = "***************";
            button.textContent = "表示";
            break;
    }
}

// コピー処理
async function copy(button) {
    const row = button.closest("tr");
    const feedback_element = row.querySelector(".feedback");

    const passtxt = await get_passtxt(row);
    if (!passtxt) return;

    // Clipboard APIを使ってクリップボードにテキストをコピー
    try {
        await navigator.clipboard.writeText(passtxt);
        feedback_element.textContent = "コピーしました";
        clear_feedback(feedback_element, 2000);
    } catch (err) {
        feedback_element.textContent = "コピーに失敗しました";
        clear_feedback(feedback_element, 5000);
        console.error("コピーエラー: ", err);
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

// フィードバックのクリア処理
function clear_feedback(element, timeout) {
    setTimeout(() => {
        element.textContent = "";
    }, timeout);
}