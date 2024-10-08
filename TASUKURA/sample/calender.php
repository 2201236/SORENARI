<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Full Screen Calendar</title>
<link rel="stylesheet" href="./css/style.css">
</head>
<body>
<div id="calendar">
  <h2>Calendar</h2>
  <table id="calendar-table">
    <thead>
      <tr>
        <th>Sun</th>
        <th>Mon</th>
        <th>Tue</th>
        <th>Wed</th>
        <th>Thu</th>
        <th>Fri</th>
        <th>Sat</th>
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
          cell.textContent = date;

          // 今日の日付を強調表示
          if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
            cell.classList.add('today');
          }

          // クリックイベントリスナーを追加
          cell.addEventListener('click', () => {
            alert(`Clicked on ${cell.textContent}`);
          });

          row.appendChild(cell);
          date++;
        }
      }
      calendarBody.appendChild(row);
    }
  }

  // 現在の月でカレンダーを初期化
  const today = new Date();
  generateCalendar(today.getFullYear(), today.getMonth());
</script>
</body>
</html>
