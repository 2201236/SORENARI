function generateRandomString(length) {
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    return result;
}

// 認証モーダルを開き、認証が完了するまで待つ関数
async function openAuthModalAndWaitForAuth() {
    if (typeof userId === 'undefined') {
        document.getElementById("auth_modal_close").style.display = "none";
    } else {
        document.getElementById('auth_form_group_passName').style.display = "none";
        document.getElementById('auth_passName').value = generateRandomString(20);
        document.getElementById('auth_passtxt').value = '';
        document.getElementById('auth_passtxt').placeholder = 'ログイン時のパスワードを入力してください';
    }
    
    openModal(authModal); // モーダルを開く

    if (typeof userId === 'undefined') {
        document.getElementById('auth_passName').focus();
        document.getElementById('auth_passName').select();
    } else {
        document.getElementById('auth_passtxt').focus();
        document.getElementById('auth_passtxt').select();
    }

    // 既存の submit イベントリスナーを削除して、重複を防ぐ
    const authForm = document.getElementById('auth_form');
    authForm.removeEventListener('submit', authForm._submitListener);

    // 認証処理のための新しい submit イベントリスナーを追加
    authForm._submitListener = async (e) => {
        const isAuthenticated = await handleAuth(e);
        if (isAuthenticated) {
            closeModal(authModal); // 認証が完了したらモーダルを閉じる
        }
    };
    
    return new Promise((resolve) => {
        // 認証フォームのsubmitイベントリスナーを設定
        document.getElementById('auth_form').addEventListener('submit', async (e) => {
            const isAuthenticated = await handleAuth(e);
            if (isAuthenticated) closeModal(authModal); // 認証が完了したらモーダルを閉じる
            resolve(isAuthenticated); // 認証結果をresolveで返す
        }); // once: trueで一度だけイベントが発生するように設定
    });
}

// 実行する関数
async function mainProcess() {
    const feedback_element = document.getElementById('feedback');
    const authResult = await openAuthModalAndWaitForAuth(); // 認証結果を待つ

    if (!authResult) { // 認証失敗
        feedback_element.textContent = '認証に失敗しました';
        clearFeedback(feedback_element);
    } else {
        return new Promise((resolve) => {
            resolve(authResult); 
        });
    }
}
