<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>全画面カレンダー</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="controls">
    <label for="year-select">年: </label>
    <select id="year-select"></select>

    <label for="month-select">月: </label>
    <select id="month-select">
        <option value="0">1月</option>
        <option value="1">2月</option>
        <option value="2">3月</option>
        <option value="3">4月</option>
        <option value="4">5月</option>
        <option value="5">6月</option>
        <option value="6">7月</option>
        <option value="7">8月</option>
        <option value="8">9月</option>
        <option value="9">10月</option>
        <option value="10">11月</option>
        <option value="11">12月</option>
    </select>

    <button id="generate-button">更新</button>
</div>

<div id="calendar">
    <h2>カレンダー</h2>
    <table id="calendar-table">
        <thead>
            <tr>
                <th>日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th>土</th>
            </tr>
        </thead>
        <tbody id="calendar-body">
        </tbody>
    </table>
</div>

<script>
    async function fetchHolidays(year) {
        try {
            const response = await fetch(`https://holidays-jp.github.io/api/v1/${year}/date.json`);
            if (!response.ok) {
                throw new Error('祝日の取得に失敗しました');
            }
            return await response.json();
        } catch (error) {
            console.error("Error fetching holidays:", error);
            return {};
        }
    }

    async function generateCalendar(year, month) {
        const calendarBody = document.getElementById('calendar-body');
        calendarBody.innerHTML = '';  // カレンダー部分のみクリア

        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        // 祝日データの取得
        let holidays = await fetchHolidays(year);

        let date = 1;
        for (let i = 0; i < 6; i++) {
            const row = document.createElement('tr');

            for (let j = 0; j < 7; j++) {
                const cell = document.createElement('td');

                // 月の最初の日の位置に合わせて空のセルを追加
                if (i === 0 && j < firstDay.getDay()) {
                    row.appendChild(cell);
                    continue; // 空セルの場合、日付を増やさない
                }

                // 日付が月の最後の日を超えた場合、次の行に移動
                if (date > lastDay.getDate()) {
                    row.appendChild(cell);
                    continue; // 最後の日を超えたら空セルを追加
                }

                // 日付を表示
                const dateDiv = document.createElement('div');
                dateDiv.textContent = date;

                const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;

                // 祝日かどうかを確認
                if (holidays[formattedDate]) {
                    const holidayDiv = document.createElement('div');
                    holidayDiv.textContent = holidays[formattedDate];
                    holidayDiv.classList.add('holiday'); // 祝日を赤字で表示
                    cell.classList.add('holiday-background'); // 背景色を変更（祝日の場合）
                    cell.appendChild(holidayDiv);
                }

                cell.appendChild(dateDiv);

                // 今日の日付を強調表示
                if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
                    cell.classList.add('today');
                }

                // 日付をクリックしたときのイベント
                cell.addEventListener('click', () => {
                    window.location.href = `view-schedule.html?date=${date}&year=${year}&month=${month + 1}`;
                });

                // TODOボタンを追加
                const todoButton = document.createElement('button');
                todoButton.textContent = 'TODO';
                todoButton.className = 'todo-button';
                todoButton.addEventListener('click', (event) => {
                    event.stopPropagation(); // セルのクリックイベントを停止
                    window.location.href = `todo.html?date=${date}&year=${year}&month=${month + 1}`;
                });

                cell.appendChild(todoButton); // ボタンをセルに追加
                row.appendChild(cell);
                date++; // 日付を増やす
            }
            calendarBody.appendChild(row);
        }
    }

    const yearSelect = document.getElementById('year-select');
    const monthSelect = document.getElementById('month-select');
    const currentYear = new Date().getFullYear();

    // 年の選択肢を生成
    for (let i = currentYear - 50; i <= currentYear + 50; i++) {
        const option = document.createElement('option');
        option.value = i;
        option.textContent = i;
        yearSelect.appendChild(option);
    }

    // 現在の年と月をセット
    yearSelect.value = currentYear;
    monthSelect.value = new Date().getMonth();

    // 更新ボタンがクリックされたときのイベント
    document.getElementById('generate-button').addEventListener('click', async () => {
        const selectedYear = parseInt(yearSelect.value);
        const selectedMonth = parseInt(monthSelect.value);
        await generateCalendar(selectedYear, selectedMonth);  // 祝日も含めてカレンダーを生成
    });

    // 初期表示のカレンダー生成
    (async function() {
        await generateCalendar(currentYear, new Date().getMonth());
    })();
</script>

</body>
</html>
