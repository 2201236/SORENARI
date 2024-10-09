// モーダルとボタン、閉じる要素の取得
// 追加用
const addModal = document.getElementById("add_modal");
const openAddModalBtn = document.getElementById("open_add_modal");
const closeAddModalBtn = addModal.getElementsByClassName("add_modal_close")[0];

// モーダルを開く
openAddModalBtn.onclick = function() {
    addModal.style.display = "block";
}

// モーダルを閉じる
closeAddModalBtn.onclick = function() {
    addModal.style.display = "none";
}

// 編集用
const editModal = document.getElementById("edit_modal");
const openEditModalBtn = document.getElementById("open_edit_modal");
const closeEditModalBtn = editModal.getElementsByClassName("edit_modal_close")[0];

// モーダルを開く
openEditModalBtn.onclick = function() {
    editModal.style.display = "block";
}

// モーダルを閉じる
closeEditModalBtn.onclick = function() {
    editModal.style.display = "none";
}

function safe_param(passtxt) {
    let credit = 10;
    if (passtxt.length >= 4) {
        for (let i = 0; i < passtxt.length; i++) {
            if (passtxt.charCodeAt(i) >= 48 && passtxt.charCodeAt(i) <= 57) {
                credit--;
            }
            
        }
        
    }
    
}