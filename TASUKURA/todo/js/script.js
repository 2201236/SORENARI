// 項目を追加する関数
function addItem() {
    const itemsDiv = document.getElementById("items");
    const newItem = document.createElement("div");
    newItem.innerHTML = '<input type="text" name="content[]" required>';
    itemsDiv.appendChild(newItem);
}

// タスク詳細を展開・非表示する関数
function toggleDetails(element) {
    const details = element.nextElementSibling;
    if (details.style.display === "none") {
        details.style.display = "block";
    } else {
        details.style.display = "none";
    }
}
