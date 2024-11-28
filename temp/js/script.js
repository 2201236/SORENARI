// タブ切り替え
document.querySelectorAll('.tab-button').forEach(button => {
    button.addEventListener('click', () => {
        const tabName = button.dataset.tab;

        // タブボタンのアクティブ状態を更新
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        // タブコンテンツの表示を切り替え
        document.querySelectorAll('.tab-content').forEach(content => {
            if (content.id === tabName) {
                content.classList.add('active');
            } else {
                content.classList.remove('active');
            }
        });
    });
});

// メッセージ送信時の処理
document.querySelector('footer button').addEventListener('click', () => {
    const input = document.querySelector('.chat-input');
    const message = input.value.trim();
    if (message) {
        const chatContent = document.getElementById('main');
        const newMessage = document.createElement('div');
        newMessage.classList.add('chat-message');
        newMessage.innerHTML = `
            <span class="text-sm text-gray-400">あなた・今日 ${new Date().getHours()}:${new Date().getMinutes()}</span>
            <p class="mt-1">${message}</p>
        `;
        chatContent.appendChild(newMessage);
        chatContent.scrollTop = chatContent.scrollHeight; // 最新メッセージにスクロール
        input.value = '';
    }
});
