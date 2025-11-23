// Dashboard Analytics Charts - Activity Log functions removed

// Color palettes
const complaintColors = ["#b22222", "#8b0000", "#d32f2f", "#9e9e9e", "#757575", "#dc3545", "#6c757d", "#b0b0b0"];
const suggestionColors = ["#b22222", "#8b0000", "#d32f2f", "#9e9e9e", "#757575", "#dc3545", "#6c757d", "#b0b0b0"];
const statusColors = ["#ffc107", "#0dcaf0", "#198754", "#dc3545"];

// Initialize charts when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  // Check if phpData is available
  if (typeof phpData === 'undefined') {
    console.error('phpData is not defined. Charts cannot be initialized.');
    return;
  }
  
  initializeTrendChart();
  initializeCategoryCharts();
  initializeStatusCharts();
  // Activity log initialization removed - now in activity_logs.js
});

// Monthly Trend Chart
function initializeTrendChart() {
  const trendCtx = document.getElementById("trendChart");
  if (!trendCtx) {
    console.error('Trend chart canvas not found');
    return;
  }

  const fullTrendData = {
    months: phpData.months || [],
    complaints: phpData.complaintTrend || [],
    suggestions: phpData.suggestionTrend || [],
  };

  // Validate data
  if (fullTrendData.months.length === 0) {
    console.warn('No trend data available');
    return;
  }

  function getLastMonths(count) {
    return {
      months: fullTrendData.months.slice(-count),
      complaints: fullTrendData.complaints.slice(-count),
      suggestions: fullTrendData.suggestions.slice(-count),
    };
  }

  const initialData = getLastMonths(5);

  const trendChart = new Chart(trendCtx, {
    type: "line",
    data: {
      labels: initialData.months,
      datasets: [
        {
          label: "Complaints",
          data: initialData.complaints,
          borderColor: "#b22222",
          backgroundColor: "rgba(178, 34, 34, 0.1)",
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 6,
        },
        {
          label: "Suggestions",
          data: initialData.suggestions,
          borderColor: "#0d6efd",
          backgroundColor: "rgba(13, 110, 253, 0.1)",
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 4,
          pointHoverRadius: 6,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: "top",
          labels: {
            usePointStyle: true,
            padding: 15,
          }
        },
        tooltip: {
          mode: 'index',
          intersect: false,
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1,
          }
        },
        x: {
          grid: {
            display: false,
          }
        }
      },
      interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
      }
    },
  });

  // Trend filter dropdowns
  document.querySelectorAll(".trend-filter").forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const months = parseInt(this.dataset.months);
      const updated = getLastMonths(months);
      trendChart.data.labels = updated.months;
      trendChart.data.datasets[0].data = updated.complaints;
      trendChart.data.datasets[1].data = updated.suggestions;
      trendChart.update();
      
      const button = this.closest(".dropdown").querySelector("button");
      if (button) {
        button.innerText = "Last " + months + " Months";
      }
    });
  });
}

// Category Charts (Complaints and Suggestions)
function initializeCategoryCharts() {
  // Complaints by Category
  const catCtx = document.getElementById("catChart");
  if (catCtx) {
    const categories = phpData.categories || [];
    const categoryCounts = phpData.categoryCounts || [];
    
    // Filter out empty categories
    const filteredData = categories.reduce((acc, cat, index) => {
      if (categoryCounts[index] > 0) {
        acc.labels.push(cat);
        acc.data.push(categoryCounts[index]);
      }
      return acc;
    }, { labels: [], data: [] });

    if (filteredData.data.length === 0) {
      console.warn('No complaint category data available');
      catCtx.parentElement.innerHTML = '<p class="text-muted text-center py-4">No data available</p>';
      return;
    }

    new Chart(catCtx, {
      type: "doughnut",
      data: {
        labels: filteredData.labels,
        datasets: [
          {
            data: filteredData.data,
            backgroundColor: complaintColors,
            borderWidth: 2,
            borderColor: '#fff',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "right",
            labels: {
              boxWidth: 15,
              padding: 10,
              font: {
                size: 11
              }
            },
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
      },
    });
  }

  // Suggestions by Category
  const suggestionCatCtx = document.getElementById("suggestionCatChart");
  if (suggestionCatCtx) {
    const categories = phpData.suggestionCategories || [];
    const categoryCounts = phpData.suggestionCategoryCounts || [];
    
    // Filter out empty categories
    const filteredData = categories.reduce((acc, cat, index) => {
      if (categoryCounts[index] > 0) {
        acc.labels.push(cat);
        acc.data.push(categoryCounts[index]);
      }
      return acc;
    }, { labels: [], data: [] });

    if (filteredData.data.length === 0) {
      console.warn('No suggestion category data available');
      suggestionCatCtx.parentElement.innerHTML = '<p class="text-muted text-center py-4">No data available</p>';
      return;
    }

    new Chart(suggestionCatCtx, {
      type: "doughnut",
      data: {
        labels: filteredData.labels,
        datasets: [
          {
            data: filteredData.data,
            backgroundColor: suggestionColors,
            borderWidth: 2,
            borderColor: '#fff',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "right",
            labels: {
              boxWidth: 15,
              padding: 10,
              font: {
                size: 11
              }
            },
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((value / total) * 100).toFixed(1);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
      },
    });
  }
}

// Status Charts (Complaints and Suggestions)
function initializeStatusCharts() {
  // Complaints by Status
  const complaintStatusCtx = document.getElementById("complaintStatusChart");
  if (complaintStatusCtx) {
    const labels = phpData.complaintStatusLabels || [];
    const data = phpData.complaintStatuses || [];

    if (data.length === 0) {
      console.warn('No complaint status data available');
      return;
    }

    new Chart(complaintStatusCtx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Complaints",
            data: data,
            backgroundColor: statusColors,
            borderWidth: 0,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.label}: ${context.parsed.y}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
            }
          },
          x: {
            grid: {
              display: false,
            }
          }
        },
      },
    });
  }

  // Suggestions by Status
  const suggestionStatusCtx = document.getElementById("suggestionStatusChart");
  if (suggestionStatusCtx) {
    const labels = phpData.suggestionStatusLabels || [];
    const data = phpData.suggestionStatuses || [];

    if (data.length === 0) {
      console.warn('No suggestion status data available');
      return;
    }

    new Chart(suggestionStatusCtx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Suggestions",
            data: data,
            backgroundColor: statusColors,
            borderWidth: 0,
            borderRadius: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return `${context.label}: ${context.parsed.y}`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
            }
          },
          x: {
            grid: {
              display: false,
            }
          }
        },
      },
    });
  }
}

console.log('[Dashboard] Charts initialized successfully');