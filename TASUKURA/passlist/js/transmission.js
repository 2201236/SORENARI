document.getElementById("edit_form", "add_form").addEventListener("submit", function(e) {
    e.preventDefault(); // デフォルトのフォーム送信を防ぐ

    const formData = new FormData(this);

    // AJAXでPHPにデータを送信
    fetch('update_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // データの更新が成功したらページをリロード
            window.location.reload();
        } else {
            alert("データの更新に失敗しました");
        }
    })
    .catch(error => {
        console.error("エラー:", error);
        alert("エラーが発生しました");
    });
});
