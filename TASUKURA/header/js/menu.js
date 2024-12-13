document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.getElementById('menu');

    // メニューの表示/非表示を切り替える
    menuToggle.addEventListener('click', function() {
        menu.classList.toggle('show');
    });
});
