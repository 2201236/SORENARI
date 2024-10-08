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
  function generateCalendar(year, month) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    let date = 1;
    for (let i = 0; i < 6; i++) {
      const row = document.createElement('tr');

      for (let j = 0; j < 7; j++) {
        if (i === 0 && j < firstDay.getDay()) {
          const cell = document.createElement('td');
          row.appendChild(cell);
        } else if (date > lastDay.getDate()) {
          break;
        } else {
          const cell = document.createElement('td');
          
          // 日付を表示
          const dateDiv = document.createElement('div');
          dateDiv.textContent = date;
          cell.appendChild(dateDiv);

          const currentDay = date;  // 日付をキャプチャ
          
          // 「予定追加」ボタンを追加
          const addButton = document.createElement('button');
          addButton.textContent = 'todo';
          addButton.addEventListener('click', (event) => {
            event.stopPropagation(); // 日付のクリックイベントが発火しないようにする
            window.location.href = `add-schedule.html?date=${currentDay}&year=${year}&month=${month + 1}`;
          });
          cell.appendChild(addButton);

          // 今日の日付を強調表示
          if (year === new Date().getFullYear() && month === new Date().getMonth() && currentDay === new Date().getDate()) {
            cell.classList.add('today');
          } else {
            // 日付をクリックしたときのイベント
            cell.addEventListener('click', () => {
              window.location.href = `view-schedule.html?date=${currentDay}&year=${year}&month=${month + 1}`;
            });
          }

          row.appendChild(cell);
          date++;
        }
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
  document.getElementById('generate-button').addEventListener('click', () => {
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value);
    generateCalendar(selectedYear, selectedMonth);
  });

  // 初期表示のカレンダー生成
  generateCalendar(currentYear, new Date().getMonth());
</script>

</body>
</html>

