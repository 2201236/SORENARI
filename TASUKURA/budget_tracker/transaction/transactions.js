let currentDeleteData = null;

// タブ切り替え
function switchTab(tab) {
    const incomeTab = document.getElementById('incomeTab');
    const expenseTab = document.getElementById('expenseTab');
    const incomeContent = document.getElementById('incomeContent');
    const expenseContent = document.getElementById('expenseContent');
    const incomeLine = incomeTab.querySelector('.bg-blue-500');
    const expenseLine = expenseTab.querySelector('.bg-blue-500');

    if (tab === 'income') {
        incomeContent.classList.remove('hidden');
        expenseContent.classList.add('hidden');
        incomeLine.classList.remove('opacity-0');
        expenseLine.classList.add('opacity-0');
        incomeTab.classList.add('text-blue-600');
        expenseTab.classList.remove('text-blue-600');
    } else {
        incomeContent.classList.add('hidden');
        expenseContent.classList.remove('hidden');
        incomeLine.classList.add('opacity-0');
        expenseLine.classList.remove('opacity-0');
        incomeTab.classList.remove('text-blue-600');
        expenseTab.classList.add('text-blue-600');
    }
}

// 編集モーダル関連
function openEditModal(type, id, content, amount, daily) {
    const modal = document.getElementById('editModal');
    const modalContent = modal.querySelector('.modal-content');
    
    document.getElementById('editId').value = id;
    document.getElementById('editType').value = type;
    document.getElementById('editContent').value = content;
    document.getElementById('editAmount').value = amount;
    document.getElementById('editDaily').value = daily;

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('bg-opacity-50');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.classList.remove('bg-opacity-50');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

// 削除モーダル関連
function openDeleteModal(type, id) {
    const modal = document.getElementById('deleteModal');
    const modalContent = modal.querySelector('.modal-content');
    
    currentDeleteData = { type, id };

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.add('bg-opacity-50');
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    const modalContent = modal.querySelector('.modal-content');
    
    modal.classList.remove('bg-opacity-50');
    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.add('hidden');
        currentDeleteData = null;
    }, 300);
}

// フォーム送信処理
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const type = formData.get('type');
    
    fetch(`update_${type}.php`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('更新に失敗しました。もう一度お試しください。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました。もう一度お試しください。');
    });
});

function deleteTransaction() {
    if (!currentDeleteData) return;
    
    const { type, id } = currentDeleteData;
    fetch(`delete_${type}.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('削除に失敗しました。もう一度お試しください。');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('エラーが発生しました。もう一度お試しください。');
    })
    .finally(() => {
        closeDeleteModal();
    });
}

// URLのクエリパラメータからアクティブなタブを取得
function getActiveTabFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('tab') || 'income';
}

// 初期表示時の処理
document.addEventListener('DOMContentLoaded', () => {
    const activeTab = getActiveTabFromURL();
    switchTab(activeTab);
    
    // タブクリックイベントの設定
    document.getElementById('incomeTab').addEventListener('click', () => switchTab('income'));
    document.getElementById('expenseTab').addEventListener('click', () => switchTab('expense'));
});