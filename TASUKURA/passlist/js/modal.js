

// モーダルとボタン、閉じる要素の取得
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

// モーダルを開く
Array.from(openEditModalBtns).forEach(button => {
    button.onclick = () => {
        editModal.style.display = "block"; // モーダルを表示
    };
});

// モーダルを閉じる
closeEditModalBtn.onclick = () => {
    editModal.style.display = "none";
}
