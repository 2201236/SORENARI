<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Full Screen Calendar</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #f0f0f0;
  }
  #calendar {
    width: 80%;
    max-width: 800px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    padding: 20px;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
  }
  th, td {
    text-align: center;
    padding: 10px 0;
    border: 1px solid #ddd;
  }
  th {
    background-color: #f2f2f2;
  }
  td {
    cursor: pointer;
  }
  .today {
    background-color: #e0f7fa;
  }
</style>
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
          if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
            cell.classList.add('today');
          }
          row.appendChild(cell);
          date++;
        }
      }
      calendarBody.appendChild(row);
    }
  }

  // Initialize calendar with current month
  const today = new Date();
  generateCalendar(today.getFullYear(), today.getMonth());

  // Add event listener to each day cell (optional)
  document.querySelectorAll('#calendar-body td').forEach(cell => {
    cell.addEventListener('click', () => {
      alert(`Clicked on ${cell.textContent}`);
      // Add your logic for handling click events here
    });
  });
</script>
</body>
</html>
