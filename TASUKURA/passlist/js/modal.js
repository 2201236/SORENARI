// モーダルを開く機能
const openModal = (modal) => {
    modal.style.display = "block";
};

// モーダルを閉じる関数
const closeModal = (modal) => {
    modal.style.display = "none";
};

// 追加用
const addModal = document.getElementById("add_modal");
const openAddModalBtn = document.getElementById("open_add_modal");
const closeAddModalBtn = document.getElementById("add_modal_close");

// モーダルを開く
openAddModalBtn.onclick = () => openModal(addModal);

// モーダルを閉じる
closeAddModalBtn.onclick = () => closeModal(addModal);

// 編集用
const editModal = document.getElementById("edit_modal");
const openEditModalBtns = document.querySelectorAll(".open_edit_modal");
const closeEditModalBtn = document.getElementById("edit_modal_close");

// 編集データのセット
const urlInput = document.getElementById("edit_url");
const passNameInput = document.getElementById("edit_passName");
const passtxtInput = document.getElementById("edit_passtxt");
const passIdInput = document.getElementById("pass_id"); // pass_idのhiddenフィールド

// モーダルを開く
Array.from(openEditModalBtns).forEach((button) => {
    button.onclick = async() => {
        // 押されたボタンの行データを取得
        const row = button.closest("tr");
        const url = row.querySelector("td:nth-child(1)").textContent.trim(); // URLを取得
        const userId = row.querySelector("td:nth-child(2)").textContent.trim(); // passNameを取得
        const passId = row.querySelector(".pass_id").value; // pass_idを取得

        if (!(await mainProcess())) {
            return;
        }

        // フォームにデータをセット
        urlInput.value = url;
        passNameInput.value = userId;
        passIdInput.value = passId; // pass_idもhiddenフィールドにセット
        passtxtInput.value = ""; // パスワードは空で表示

        openModal(editModal); // モーダルを表示
    };
});

// モーダルを閉じる
closeEditModalBtn.onclick = () => closeModal(editModal);

// 認証用
const authModal = document.getElementById("auth_modal");
const closeAuthModalBtn = document.getElementById("auth_modal_close");

// モーダルを閉じる
closeAuthModalBtn.onclick = () => closeModal(authModal);

document.addEventListener('DOMContentLoaded', async function() {
    const feedback_element = document.getElementById('feedback');

    if (!isLoggedIn) {
        try {
            // 認証プロセスを実行
            if (await mainProcess()) {
                // 認証成功
                window.location.replace('passlist.php');
            }
        } catch (error) {
            console.error('認証プロセスエラー:', error);
            feedback_element.textContent = 'エラーが発生しました';
        }
    }
});
