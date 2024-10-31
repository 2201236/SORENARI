// 認証モーダルを開き、認証が完了するまで待つ関数
async function openAuthModalAndWaitForAuth() {
    openModal(authModal); // モーダルを開く
    
    return new Promise((resolve) => {
        // 認証フォームのsubmitイベントリスナーを設定
        document.getElementById('auth_form').addEventListener('submit', async (e) => {
            const isAuthenticated = await handleAuth(e);
            closeModal(authModal); // 認証が完了したらモーダルを閉じる
            resolve(isAuthenticated); // 認証結果をresolveで返す
        }, { once: true }); // once: trueで一度だけイベントが発生するように設定
    });
}

// 実行する関数
async function mainProcess() {
    const feedback_element = document.getElementById('feedback');
    const authResult = await openAuthModalAndWaitForAuth(); // 認証結果を待つ

    if (!authResult) {
        feedback_element.textContent = '認証に失敗しました';
        clearFeedback(feedback_element, 3000);
    }
}
