// 追加用
const addModal = document.getElementById("add_modal");
const openAddModalBtn = document.getElementById("open_add_modal");
const closeAddModalBtn = addModal.getElementsByClassName("add_modal_close")[0];

// モーダルを開く
openAddModalBtn.onclick = () => {
    addModal.style.display = "block";
}

// モーダルを閉じる
closeAddModalBtn.onclick = () => {
    addModal.style.display = "none";
}

// 編集用
const editModal = document.getElementById("edit_modal");
const openEditModalBtns = document.querySelectorAll(".open_edit_modal");
const closeEditModalBtn = editModal.getElementsByClassName("edit_modal_close")[0];

// Setting modal data
const urlInput = document.getElementById("edit_url");
const passNameInput = document.getElementById("edit_passName");
const passtxtInput = document.getElementById("edit_passtxt");
const passIdInput = document.getElementById("pass_id"); // pass_idのhiddenフィールド

// モーダルを開く
Array.from(openEditModalBtns).forEach((button, index) => {
    button.onclick = () => {
        // 押されたボタンの行データを取得
        const row = button.closest("tr");
        const url = row.querySelector("td:nth-child(1)").textContent.trim(); // URLを取得
        const userId = row.querySelector("td:nth-child(2)").textContent.trim(); // passNameを取得
        const passId = row.querySelector(".pass_id").value; // pass_idを取得

        // フォームにデータをセット
        urlInput.value = url;
        passNameInput.value = userId;
        passIdInput.value = passId; // pass_idもhiddenフィールドにセット
        passtxtInput.value = ""; // パスワードは空で表示

        editModal.style.display = "block"; // モーダルを表示
    };
});

// モーダルを閉じる
closeEditModalBtn.onclick = () => {
    editModal.style.display = "none";
}


// 認証用
const reAuthModal = document.getElementById("re_auth_modal");
// 後で作る
const closeReAuthModalBtn = reAuthModal.getElementsByClassName("re_auth_modal_close")[0];

// モーダルを開く
//未確定処理

// モーダルを閉じる
closeReAuthModalBtn.onclick = () => {
    reAuthModal.style.display = "none";
}