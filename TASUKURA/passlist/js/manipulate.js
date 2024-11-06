// AJAXリクエストを処理するヘルパー関数
function sendData(url, body) {
    return fetch(url, {
        method: 'POST',
        body: body
    })
    .then(response => response.json());
}

// フィードバックのクリア処理
function clearFeedback(element, timeout) {
    setTimeout(() => {
        element.textContent = "";
    }, timeout);
}

// セッション期限の確認と認証処理
async function sessionCheck() {
    if (!limitedSession) {
        return mainProcess();
    } else if (limitedSession) {
        return true;
    }
}


// パスワード取得の共通処理
async function getPasstxt(row) {
    const pass_id = row.querySelector(".pass_id").value;
    const feedback_element = row.querySelector(".feedback");
    
    try {
        const formData = new FormData();
        formData.append('pass_id', pass_id);

        const data = await sendData("get_passtxt.php", formData);
        if (data.success) {
            return data.passtxt;
        } else {
            feedback_element.textContent = "パスワードの取得に失敗しました";
            clearFeedback(feedback_element, 5000);
            return null;
        }
    } catch (error) {
        console.error("エラー:", error);
        feedback_element.textContent = "パスワードの取得に失敗しました";
        clearFeedback(feedback_element, 5000);
        return null;
    }
}

// パスワードの表示/非表示処理
async function togglePasstxt(button) {
    const row = button.closest("tr");
    const toggle = button.textContent === "表示" ? "hide" : "show";
    
    switch (toggle) {
        case "hide":
            if (await sessionCheck()) {
                const passtxt = await getPasstxt(row);
                if (passtxt) {
                    row.querySelector(".passtxt").textContent = passtxt;
                    button.textContent = "非表示";
                }
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

    if (await sessionCheck()) {
        const passtxt = await getPasstxt(row);
        if (!passtxt) return;

        // Clipboard APIを使ってクリップボードにテキストをコピー
        try {
            await navigator.clipboard.writeText(passtxt);
            feedback_element.textContent = "コピーしました";
            clearFeedback(feedback_element, 2000);
        } catch (err) {
            feedback_element.textContent = "コピーに失敗しました";
            clearFeedback(feedback_element, 5000);
            console.error("コピーエラー: ", err);
        }
    }
}

// パスワード表示/非表示ボタン
document.querySelectorAll(".toggle_passtxt_button").forEach(button => {
    button.addEventListener("click", function() {
        togglePasstxt(this);
    });
});

// コピーを実行ボタン
document.querySelectorAll(".copy_button").forEach(button => {
    button.addEventListener("click", function() {
        copy(this);
    });
});