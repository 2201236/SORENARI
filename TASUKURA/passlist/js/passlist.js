// モーダルとボタン、閉じる要素の取得
const addModal = document.getElementById("add_modal");
const openAddModalBtn = document.getElementById("open_add_modal");
const closeAddModalBtn = addModal.getElementsByClassName("close")[0];

// モーダルを開く
openAddModalBtn.onclick = function() {
    addModal.style.display = "block";
}

// モーダルを閉じる
closeAddModalBtn.onclick = function() {
    addModal.style.display = "none";
}

// モーダル外をクリックしたときに閉じる
// window.onclick = function(event) {
//     if (event.target == addModal) {
//         addModal.style.display = "none";
//     }
// }