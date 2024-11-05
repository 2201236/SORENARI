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

    if (!authResult) { // 認証失敗
        feedback_element.textContent = '認証に失敗しました';
        clearFeedback(feedback_element, 3000);
    } else if (sessionStorage.getItem('is_logged_in')) {
        // 認証成功の場合にリダイレクト
        window.location.replace('passlist.php'); // replaceを使って履歴に残さないリダイレクト
    } else {
        feedback_element.textContent = 'エラーが発生しました';
        clearFeedback(feedback_element, 3000);
    }
}
