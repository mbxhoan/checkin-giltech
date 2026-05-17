"use strict";

import Chart from 'chart.js/auto';

const registryKey = '__videcTicketAnalyticsCharts';

const getRegistry = () => {
  if (!window[registryKey]) {
    window[registryKey] = {};
  }

  return window[registryKey];
};

const parseAnalytics = () => {
  const root = document.getElementById('videc-ticket-analytics');

  if (!root) {
    return null;
  }

  const raw = root.getAttribute('data-analytics');

  if (!raw) {
    return null;
  }

  try {
    return JSON.parse(raw);
  } catch (error) {
    console.error('Unable to parse videc ticket analytics payload', error);
    return null;
  }
};

const destroyChart = (chartId) => {
  const registry = getRegistry();

  if (registry[chartId]) {
    registry[chartId].destroy();
    delete registry[chartId];
  }
};

const createChart = (chartId, config) => {
  const canvas = document.getElementById(chartId);

  if (!canvas) {
    return;
  }

  destroyChart(chartId);

  const registry = getRegistry();
  registry[chartId] = new Chart(canvas, config);
};

const formatVnd = (value) => {
  const number = Number(value ?? 0);
  return `${number.toLocaleString('vi-VN')} VND`;
};

const getParsedValue = (context) => {
  if (typeof context.parsed === 'number') {
    return context.parsed;
  }

  if (typeof context.parsed?.x === 'number') {
    return context.parsed.x;
  }

  if (typeof context.parsed?.y === 'number') {
    return context.parsed.y;
  }

  return 0;
};

export const renderTicketAnalytics = () => {
  const analytics = parseAnalytics();

  if (!analytics) {
    return;
  }

  const charts = analytics.charts ?? {};
  const revenueStatus = charts.revenue_status ?? {};
  const orderStatus = charts.order_status ?? {};
  const ticketQuantity = charts.ticket_quantity ?? {};
  const ticketRevenue = charts.ticket_revenue ?? {};

  createChart('videc-revenue-status-chart', {
    type: 'doughnut',
    data: {
      labels: revenueStatus.labels ?? [],
      datasets: [{
        data: revenueStatus.values ?? [],
        backgroundColor: revenueStatus.colors ?? [],
        borderWidth: 0,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
        },
        tooltip: {
          callbacks: {
            label: (context) => `${context.label}: ${formatVnd(context.parsed)}`,
          },
        },
      },
    },
  });

  createChart('videc-order-status-chart', {
    type: 'bar',
    data: {
      labels: orderStatus.labels ?? [],
      datasets: [{
        label: 'Số lượng đơn',
        data: orderStatus.values ?? [],
        backgroundColor: orderStatus.colors ?? [],
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: (context) => `${context.label}: ${getParsedValue(context)}`,
          },
        },
      },
    },
  });

  createChart('videc-ticket-quantity-chart', {
    type: 'bar',
    data: {
      labels: ticketQuantity.labels ?? [],
      datasets: [{
        label: 'Số lượng vé',
        data: ticketQuantity.values ?? [],
        backgroundColor: ticketQuantity.colors ?? [],
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            precision: 0,
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: (context) => `${context.label}: ${getParsedValue(context)} vé`,
          },
        },
      },
    },
  });

  createChart('videc-ticket-revenue-chart', {
    type: 'bar',
    data: {
      labels: ticketRevenue.labels ?? [],
      datasets: [{
        label: 'Doanh thu',
        data: ticketRevenue.values ?? [],
        backgroundColor: ticketRevenue.colors ?? [],
        borderRadius: 6,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: 'y',
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            callback: (value) => Number(value).toLocaleString('vi-VN'),
          },
        },
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          callbacks: {
            label: (context) => `${context.label}: ${formatVnd(getParsedValue(context))}`,
          },
        },
      },
    },
  });
};
