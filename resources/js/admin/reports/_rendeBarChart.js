"use strict";

import Chart from 'chart.js/auto';

export const rendeBarChart = () => {
  if (!document.getElementById('checkinChart')) {
    return; // Do nothing if chart element not found
  }
  
  let chartDatas = [];
  let xArray = [];
  let max = 0;

  let yyy = $('#checkin-chart').data('y');
  let dateTimes = $('#checkin-chart').data('x');;
  let timesPerDay = getFirstElement(dateTimes);

  $.each(timesPerDay, function (keyTime, valueTime) {
    if (keyTime != '18:00') {
      xArray.push(keyTime);
    }
  })

  let dataSet = getDataSet(yyy);
  chartDatas = dataSet.dataSet;
  max = dataSet.max;

  if (max == 0) {
    max = 10;
  } else {
    max = Math.ceil(max / 10) * 10;
  }

  new Chart("checkinChart", {
    type: "bar",
    data: {
      labels: xArray,
      datasets: chartDatas,
    },
    options: {
      responsive: true,
      legend: {
        display: true
      },
      title: {
        display: false,
        text: ""
      },
      scales: {
        y: {
          suggestedMax: max, // Set your maximum value here
          beginAtZero: true, // Start the y-axis at zero
          stacked: false,
          beginAtZero: true,
          scaleLabel: {
            display: true,
            labelString: 'số lượng (người)'
          }
        },
        x: {
          stacked: true,
          categoryPercentage: 0.8, // Adjust the width of the bars (0.6 means 60% of the available space)
          // barPercentage: 0.6, // Adjust the width of the bars within each category
          scaleLabel: {
            display: true,
            labelString: 'thời gian (giờ)'
          }
        }
      },
      plugins: {
        datalabels: {
          anchor: 'end', // Show the labels at the end of the bars
          align: 'end',
          color: 'black', // Label color
          formatter: function (value, context) {
            return context.dataset.data[context.dataIndex];
          }
        }
      }
    }
  });
}

const getDataSet = (chartDatas) => {
  let dataSet = [];
  let yArray = [];
  let maxValues = [];
  let colorCodeIndex = 0;
  let max = null;

  $.each(chartDatas, function (date, times) {
    let hexColorCode = getHexColor(colorCodeIndex);
    colorCodeIndex++;
    yArray = [];

    $.each(times, function (time, clientCount) {
      let currentCount = clientCount;

      if (max === null || currentCount > max) {
        max = currentCount;
      }

      yArray.push(clientCount);
    })

    yArray.forEach((value, index) => {
      if (!maxValues[index] || value > maxValues[index]) {
        maxValues[index] = value;
      }
    });

    let data = {
      label: date,
      fill: false, // mountain
      lineTension: 0,
      backgroundColor: hexColorCode,
      borderColor: hexColorCode,
      data: yArray,
      stack: 'Stack 0'
    };

    dataSet.push(data);
  });

  let lineChartData = {
    label: 'Peak',
    type: 'line',
    borderColor: "green",
    data: maxValues,
    fill: true
  };

  dataSet.push(lineChartData);

  return {
    'max': max,
    'dataSet': dataSet,
    'maxValues': maxValues
  };
}

const getFirstElement = (array) => {
  let firstKey = Object.keys(array)[0];
  let firstValue = array[firstKey];
  return firstValue;
}

const getHexColor = (index) => {
  let colors = [
    'rgba(255,0,0,0.8)',
    'rgba(255,0,0,0.6',
    'rgba(255,0,0,0.4',
    'rgba(255,0,0,0.2',
    'rgba(0,255,0,0.8',
    'rgba(0,255,0,0.6',
    'rgba(0,255,0,0.4',
    'rgba(0,255,0,0.2',
  ];

  return colors[index];
}
