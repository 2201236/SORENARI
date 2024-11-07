// 初期の月々の支出データ
let currentData = [200, 300, 150, 400, 250, 350];
let pastData = [300, 400, 500, 600, 200, 100];

let monthlyData = {
  labels: ["1月", "2月", "3月", "4月", "5月", "6月"],
  datasets: [{
    label: "月々の支出",
    data: currentData,
    backgroundColor: 'rgba(75, 192, 192, 0.2)',
    borderColor: 'rgba(75, 192, 192, 1)',
    borderWidth: 1
  }]
};

// 縦棒グラフを作成
let ctx = document.getElementById('monthly-expenses-chart').getContext('2d');
let monthlyChart = new Chart(ctx, {
  type: 'bar',
  data: monthlyData,
  options: {
    onClick: function (evt, item) {
      if (item.length) {
        let monthIndex = item[0].index;
        let selectedMonth = monthlyData.labels[monthIndex];
        displayMonthlyDetails(selectedMonth, currentData[monthIndex]);
      }
    },
    scales: {
      y: {
        beginAtZero: true
      }
    }
  }
});

// 過去のデータを切り替えるボタン
document.getElementById('toggle-past-expenses').addEventListener('click', function() {
  // 過去のデータに切り替える
  monthlyData.datasets[0].data = pastData;
  monthlyChart.update();
});

// 月々の詳細を表示する
function displayMonthlyDetails(month, expense) {
  let details = `${month}の支出: ¥${expense}`;
  document.getElementById('monthly-details').innerHTML = details;
}
