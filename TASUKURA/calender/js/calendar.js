async function fetchHolidays(year) {
    try {
        const response = await fetch(`https://holidays-jp.github.io/api/v1/${year}/date.json`);
        if (!response.ok) throw new Error('祝日の取得に失敗しました');
        return await response.json();
    } catch (error) {
        console.error("Error fetching holidays:", error);
        return {};
    }
}

async function fetchSchedules(date) {
    try {
        const response = await fetch(`get_schedule.php?date=${date}`);
        if (!response.ok) throw new Error('スケジュールの取得に失敗しました');
        return await response.json();
    } catch (error) {
        console.error("Error fetching schedules:", error);
        return [];
    }
}

async function showSchedules(date) {
    const schedules = await fetchSchedules(date);
    const scheduleList = document.getElementById('schedule-list');
    scheduleList.innerHTML = '';

    if (schedules.length > 0) {
        schedules.forEach(schedule => {
            const scheduleItem = document.createElement('li');
            scheduleItem.textContent = `${schedule.title} (${schedule.starttime} - ${schedule.endtime})`;
            scheduleList.appendChild(scheduleItem);
        });
    } else {
        scheduleList.innerHTML = '<li>予定はありません。</li>';
    }
}

// ページ読み込み時に当日のスケジュールを表示
window.onload = function() {
    const today = new Date();
    const formattedToday = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    showSchedules(formattedToday);
};

async function generateCalendar(year, month) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    let holidays = await fetchHolidays(year);

    let date = 1;
    for (let i = 0; i < 6; i++) {
        const row = document.createElement('tr');

        for (let j = 0; j < 7; j++) {
            const cell = document.createElement('td');

            if (i === 0 && j < firstDay.getDay()) {
                row.appendChild(cell);
                continue;
            }

            if (date > lastDay.getDate()) {
                row.appendChild(cell);
                continue;
            }

            const dateDiv = document.createElement('div');
            dateDiv.textContent = date;
            cell.appendChild(dateDiv);

            const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
            
            // 日付クリックイベントを追加
            cell.addEventListener('click', () => showSchedules(formattedDate));

            if (holidays[formattedDate]) {
                const holidayDiv = document.createElement('div');
                holidayDiv.textContent = holidays[formattedDate];
                holidayDiv.classList.add('holiday');
                cell.classList.add('holiday-background');
                cell.appendChild(holidayDiv);
            }

            if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
                cell.classList.add('today');
            }

            row.appendChild(cell);
            date++;
        }
        calendarBody.appendChild(row);
    }
}

// カレンダー操作用イベント
const yearSelect = document.getElementById('year-select');
const monthSelect = document.getElementById('month-select');
const currentYear = new Date().getFullYear();

for (let i = currentYear - 50; i <= currentYear + 50; i++) {
    const option = document.createElement('option');
    option.value = i;
    option.textContent = i;
    yearSelect.appendChild(option);
}

yearSelect.value = currentYear;
monthSelect.value = new Date().getMonth();

document.getElementById('generate-button').addEventListener('click', async () => {
    const selectedYear = parseInt(yearSelect.value);
    const selectedMonth = parseInt(monthSelect.value);
    await generateCalendar(selectedYear, selectedMonth);
});

document.getElementById('prev-month').addEventListener('click', async () => {
    let month = parseInt(monthSelect.value);
    let year = parseInt(yearSelect.value);
    if (month === 0) {
        month = 11;
        year--;
    } else {
        month--;
    }
    monthSelect.value = month;
    yearSelect.value = year;
    await generateCalendar(year, month);
});

document.getElementById('next-month').addEventListener('click', async () => {
    let month = parseInt(monthSelect.value);
    let year = parseInt(yearSelect.value);
    if (month === 11) {
        month = 0;
        year++;
    } else {
        month++;
    }
    monthSelect.value = month;
    yearSelect.value = year;
    await generateCalendar(year, month);
});

// 初期表示で当月のカレンダーを生成
generateCalendar(currentYear, new Date().getMonth());
