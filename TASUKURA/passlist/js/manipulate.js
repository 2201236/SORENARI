// AJAXリクエストを処理するヘルパー関数
function sendData(url, body) {
    return fetch(url, {
        method: 'POST',
        body: body
    })
    .then(response => response.json());
}

// フィードバックのクリア処理
function clearFeedback(element, timeout = 3000) {
    if (!element) {
        console.error('要素が見つかりません');
        return;
    }
    
    // フィードバックを表示
    element.classList.add('show');

    // 指定した時間後にフェードアウトを開始
    setTimeout(() => {
        element.classList.remove('show'); // フェードアウト開始

        // フェードアウトが完了するまで待ってからテキストをクリア
        setTimeout(() => {
            element.textContent = "";
        }, 500); // フェードアウトアニメーションの時間と一致
    }, timeout);
}



// パスワード取得の共通処理
async function getPasstxt(row) {
    const pass_id = row.querySelector(".pass_id").value;
    const feedback_element = document.getElementById("feedback");
    
    try {
        const formData = new FormData();
        formData.append('pass_id', pass_id);

        const data = await sendData("get_passtxt.php", formData);
        if (data.success) {
            return data.passtxt;
        } else {
            feedback_element.textContent = "パスワードの取得に失敗しました";
            clearFeedback(feedback_element);
            return null;
        }
    } catch (error) {
        console.error("エラー:", error);
        feedback_element.textContent = "パスワードの取得に失敗しました";
        clearFeedback(feedback_element);
        return null;
    }
}

// パスワードの表示/非表示処理
async function togglePasstxt(button) {
    const row = button.closest("tr");
    const toggle = button.textContent === "表示" ? "hide" : "show";
    
    switch (toggle) {
        case "hide":
            if (await mainProcess()) {
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
    const feedback_element = document.getElementById("feedback");

    if (await mainProcess()) {
        const passtxt = await getPasstxt(row);
        if (!passtxt) return;

        // Clipboard APIを使ってクリップボードにテキストをコピー
        try {
            await navigator.clipboard.writeText(passtxt);
            feedback_element.textContent = "コピーしました";
            clearFeedback(feedback_element);
        } catch (err) {
            feedback_element.textContent = "コピーに失敗しました";
            clearFeedback(feedback_element);
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

document.addEventListener('DOMContentLoaded', () => {
    const menuToggleButton = document.querySelector('.menu_toggle_button');
    const buttonsWrapper = document.querySelector('.buttons_wrapper');

    menuToggleButton.addEventListener('click', () => {
        buttonsWrapper.classList.toggle('active');
    });

    // 画面外クリックで閉じる
    document.addEventListener('click', (e) => {
        if (!menuToggleButton.contains(e.target) && !buttonsWrapper.contains(e.target)) {
            buttonsWrapper.classList.remove('active');
        }
    });
});
