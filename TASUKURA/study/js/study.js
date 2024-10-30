let startTime, interval;

// ストップウォッチの処理
document.getElementById('start-button').addEventListener('click', function() {
    startTime = Date.now();
    interval = setInterval(updateStopwatch, 1000);
    document.getElementById('start-button').disabled = true;
    document.getElementById('stop-button').disabled = false;
});

document.getElementById('stop-button').addEventListener('click', function() {
    clearInterval(interval);
    document.getElementById('start-button').disabled = false;
    document.getElementById('stop-button').disabled = true;
});

// リセットボタンの`submit`イベントをリセット処理に変更
document.getElementById('reset-button').addEventListener('click', function(event) {
    event.preventDefault(); // デフォルトのフォーム送信をキャンセル
    clearInterval(interval);
    elapsedSeconds = 0;
    document.getElementById('time-display').textContent = "00:00:00";
    document.getElementById('elapsed_time').value = 0;
    document.getElementById('start-button').disabled = false;
    document.getElementById('stop-button').disabled = true;
});

function updateStopwatch() {
    const elapsed = Math.floor((Date.now() - startTime) / 1000);
    const hours = String(Math.floor(elapsed / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((elapsed % 3600) / 60)).padStart(2, '0');
    const seconds = String(elapsed % 60).padStart(2, '0');
    document.getElementById('time-display').textContent = `${hours}:${minutes}:${seconds}`;
    document.getElementById('elapsed_time').value = elapsed;
}

// 時間形式を日本語形式に変換する関数
function formatTimeToJapanese(timeStr) {
    const [hours, minutes, seconds] = timeStr.split(':').map(Number);
    let result = '';
    if (hours > 0) {
        result += hours + '時間';
    }
    if (minutes > 0) {
        result += minutes + '分';
    }
    if (seconds > 0) {
        result += seconds + '秒';
    }
    return result || '0秒';
}

// 祝日を取得する関数
async function fetchHolidays(year) {
    try {
        const response = await fetch(`https://holidays-jp.github.io/api/v1/${year}/date.json`);
        if (!response.ok) throw new Error('祝日の取得に失敗しました');
        const holidays = await response.json();
        return holidays;
    } catch (error) {
        console.error("Error fetching holidays:", error);
        return {};
    }
}

// 学習データを取得する関数
async function fetchStudyData(year, month) {
    try {
        const response = await fetch(`get_study_data.php?year=${year}&month=${month}`);
        if (!response.ok) throw new Error('学習データの取得に失敗しました');
        const data = await response.json();
        console.log('Study Data:', data); // デバッグ用
        return data;
    } catch (error) {
        console.error('Error fetching study data:', error);
        return [];
    }
}

// ポップアップで詳細を表示する関数
function showPopup(dateStr, subjects) {
    const popup = document.getElementById('popup');
    const popupDetails = document.getElementById('popup-details');
    const popupDate = document.getElementById('popup-date');

    popupDate.textContent = `勉強詳細 (${dateStr})`;
    popupDetails.innerHTML = '';  // 既存の内容をクリア
    subjects.forEach(subject => {
        const listItem = document.createElement('li');
        listItem.textContent = `${subject.subject_name} (${formatTimeToJapanese(subject.total_time)})`;
        popupDetails.appendChild(listItem);
    });
    popup.style.display = 'block';  // ポップアップ表示
}

// カレンダーを生成する関数
async function generateCalendar(year, month) {
    const calendarBody = document.getElementById('calendar-body');
    calendarBody.innerHTML = '';

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const holidays = await fetchHolidays(year); // 祝日データ取得
    const studyData = await fetchStudyData(year, month); // 学習データ取得

    // 日付ごとに科目の学習時間をグループ化する
    const studyMap = {};
    studyData.forEach(item => {
        const { study_date, subject_name, total_time } = item;
        if (!studyMap[study_date]) studyMap[study_date] = [];
        studyMap[study_date].push({ subject_name, total_time });
    });

    let date = 1;

    for (let i = 0; i < 6; i++) {
        const row = document.createElement('tr');
        for (let j = 0; j < 7; j++) {
            const cell = document.createElement('td');
            if (i === 0 && j < firstDay.getDay() || date > lastDay.getDate()) {
                row.appendChild(cell);
                continue;
            }

            const dateDiv = document.createElement('div');
            dateDiv.textContent = date;
            cell.appendChild(dateDiv);

            const formattedDate = `${year}-${String(month + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;

            // 祝日であれば赤文字にする
            if (holidays[formattedDate]) {
                const holidayDiv = document.createElement('div');
                holidayDiv.textContent = holidays[formattedDate];
                holidayDiv.classList.add('holiday');
                cell.style.color = 'red';
                cell.appendChild(holidayDiv);
            }

            // 今日の日付に強調表示
            if (year === new Date().getFullYear() && month === new Date().getMonth() && date === new Date().getDate()) {
                cell.classList.add('today');
            }

            // 学習データが存在する場合、科目ごとに表示する
            if (studyMap[formattedDate]) {
                const subjects = studyMap[formattedDate];
                const subjectsDiv = document.createElement('div');
                subjectsDiv.classList.add('subjects');

                // 2つまで表示
                subjects.slice(0, 2).forEach(subject => {
                    const subjectDiv = document.createElement('div');
                    const formattedTime = formatTimeToJapanese(subject.total_time); // 日本語形式に変換
                    subjectDiv.textContent = `${subject.subject_name}: ${formattedTime}`;
                    subjectDiv.classList.add('subject-item');
                    subjectsDiv.appendChild(subjectDiv);
                });

                // 2つ以上ある場合は「...」を表示
                if (subjects.length > 2) {
                    const moreDiv = document.createElement('div');
                    moreDiv.textContent = '...';
                    moreDiv.classList.add('more-items');
                    moreDiv.addEventListener('click', () => showPopup(formattedDate, subjects));
                    subjectsDiv.appendChild(moreDiv);
                }

                cell.appendChild(subjectsDiv);
            }

            // 日付をクリックしたときのイベント
            cell.addEventListener('click', () => {
                showPopup(formattedDate, studyMap[formattedDate] || []);
            });

            row.appendChild(cell);
            date++;
        }
        calendarBody.appendChild(row);
    }
}

// 初期表示のカレンダーを生成
(async function() {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth();
    await generateCalendar(currentYear, currentMonth);
})();

// ポップアップを閉じる処理
document.getElementById('close-popup').addEventListener('click', () => {
    document.getElementById('popup').style.display = 'none';
});

// 月変更時のイベントリスナー
document.getElementById('generate-button').addEventListener('click', async () => {
    const year = parseInt(document.getElementById('year-select').value);
    const month = parseInt(document.getElementById('month-select').value);
    await generateCalendar(year, month);
});

// 年と月の選択肢を作成
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

// 前月と次月のボタンイベント
document.getElementById('prev-month').addEventListener('click', async () => {
    let month = parseInt(monthSelect.value);
    let year = parseInt(yearSelect.value);
    if (month === 0) { month = 11; year--; } else { month--; }
    monthSelect.value = month;
    yearSelect.value = year;
    await generateCalendar(year, month);
});

document.getElementById('next-month').addEventListener('click', async () => {
    let month = parseInt(monthSelect.value);
    let year = parseInt(yearSelect.value);
    if (month === 11) { month = 0; year++; } else { month++; }
    monthSelect.value = month;
    yearSelect.value = year;
    await generateCalendar(year, month);
});
