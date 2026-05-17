
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

export const renderPieChart = () => {
  var totalChecked = $('#checked-chart').data('checked');
  var total = $('#checked-chart').data('total');
  const xValues = ["Đã checkin", "Chưa checkin"];
  const yValues = [totalChecked, total];
  const barColors = [
    "rgba(25, 212, 28, 0.94)",
    "rgba(207, 24, 24, 0.8)",
  ];

  // Create pie chart
  new Chart(document.getElementById('pieChart'), {
      type: 'pie',
      data: {
        labels: xValues,
        datasets: [{
          backgroundColor: barColors,
          data: yValues
        }]
      },
      options: {
          responsive: true,
          legend: {
            display: true
          },
          title: {
            display: true,
            text: ""
          },
          plugins: {
              tooltip: {
                  callbacks: {
                      label: function(context) {
                        const value = context.parsed;
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${context.label}: ${value} khách (${percentage}%)`;
                      }
                  }
              },
              // datalabels: {
              //   color: '#fff',
              //   formatter: function(value, context) {
              //     const percentage = total > 0 ? (value / total * 100).toFixed(1) : 0;
              //     return `${percentage}%`;
              //   }
              // }
          }
      },
      // plugins: [ChartDataLabels]
  });
}
