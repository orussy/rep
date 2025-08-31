// Global variables for charts
let ordersChart, salesChart, paymentsChart;
let currentSelectedDate = new Date().toISOString().split('T')[0]; // Current date in YYYY-MM-DD format

// Function to fetch dashboard data from API
async function fetchDashboardData(date = null) {
    try {
        const url = date ? `api/dashboard-data.php?date=${date}` : 'api/dashboard-data.php';
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.status === 'success') {
            updateDashboard(result.data);
            // Update the date picker to show the selected date
            if (date) {
                document.getElementById('datePicker').value = date;
                currentSelectedDate = date;
            }
        } else {
            console.error('Failed to fetch data:', result.message);
            handleDatabaseError(result.message);
        }
    } catch (error) {
        console.error('Error fetching dashboard data:', error);
        handleDatabaseError(error);
    }
}

// Function to update dashboard with real data
function updateDashboard(data) {
    // Update summary numbers
    document.getElementById('ordersCount').textContent = 'Total Orders: ' + formatNumber(data.orders.total);
    document.getElementById('salesAmount').textContent = 'EGP ' + formatNumber(data.sales.total);
    document.getElementById('paymentsAmount').textContent = 'EGP ' + formatNumber(data.payments.total);
    
    // Update charts
    updateCharts(data);
    
    // Log the data for debugging
    console.log('Dashboard data updated for date:', data.date, data);
}

// Function to update charts with new data
function updateCharts(data) {
    // Destroy existing charts if they exist
    if (ordersChart) ordersChart.destroy();
    if (salesChart) salesChart.destroy();
    if (paymentsChart) paymentsChart.destroy();
    
    // Create new charts with real data
    ordersChart = createChart(document.getElementById("ordersChart"), data.orders.hourly, "Orders", "rgba(75, 192, 192, 1)", data.timeLabels);
    salesChart = createChart(document.getElementById("salesChart"), data.sales.hourly, "Net Sales", "rgba(255, 99, 132, 1)", data.timeLabels);
    paymentsChart = createChart(document.getElementById("paymentsChart"), data.payments.hourly, "Net Payments", "rgba(54, 162, 235, 1)", data.timeLabels);
}

// Function to format numbers with commas
function formatNumber(num) {
    if (num === null || num === undefined) return '0';
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Function to create chart
function createChart(ctx, data, label, color = "purple", timeLabels = null) {
    // Use time labels from API if provided, otherwise generate default ones
    if (!timeLabels) {
        timeLabels = [];
        for (let i = 0; i < 24; i++) {
            if (i === 0) {
                timeLabels.push("12AM");
            } else if (i === 12) {
                timeLabels.push("12PM");
            } else if (i < 12) {
                timeLabels.push(i + "AM");
            } else {
                timeLabels.push((i - 12) + "PM");
            }
        }
    }
    
    // Debug: Log the time labels and data to verify mapping
    console.log('Chart:', label);
    console.log('Time labels from API:', timeLabels);
    console.log('Data array:', data);
    console.log('Data length:', data.length);
    
    // Verify data array has 24 elements
    if (data.length !== 24) {
        console.error('Data array should have 24 elements, but has:', data.length);
    }
    
    // Log each hour's data for debugging
    for (let i = 0; i < Math.min(data.length, 24); i++) {
        if (data[i] > 0) {
            console.log(`Hour ${i} (${timeLabels[i]}): ${data[i]}`);
        }
    }
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: timeLabels,
            datasets: [{
                label: label,
                data: data,
                borderColor: color,
                backgroundColor: color.replace('1)', '0.1)'),
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: color,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: color,
                    borderWidth: 1,
                    callbacks: {
                        title: function(context) {
                            // Show the actual time in tooltip
                            const hour = context[0].dataIndex;
                            const timeLabel = timeLabels[hour];
                            return `${timeLabel} (Hour ${hour}:00)`;
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.1)'
                    }
                },
                x: { 
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: 12,
                        callback: function(value, index) {
                            // Show time labels on x-axis
                            return timeLabels[index];
                        }
                    }
                }
            },
            interaction: {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            }
        }
    });
}

// Function to handle database connection errors
function handleDatabaseError(error) {
    console.error('Database connection failed:', error);
    
    // Show error message to user
    const errorMessage = 'Unable to connect to database. Please check your connection and try again.';
    
    // Update dashboard with error state
    document.getElementById('ordersCount').textContent = 'Total Orders: Error';
    document.getElementById('salesAmount').textContent = 'Error';
    document.getElementById('paymentsAmount').textContent = 'Error';
    
    // Show error notification (you can customize this)
    if (typeof showNotification === 'function') {
        showNotification(errorMessage, 'error');
    } else {
        alert(errorMessage);
    }
}

// Function to refresh dashboard data
function refreshDashboard() {
    console.log('Refreshing dashboard for date:', currentSelectedDate);
    fetchDashboardData(currentSelectedDate);
}

// Function to load data for a specific date
function loadDateData() {
    const selectedDate = document.getElementById('datePicker').value;
    if (selectedDate) {
        console.log('Loading data for date:', selectedDate);
        fetchDashboardData(selectedDate);
    } else {
        alert('Please select a date first');
    }
}

// Function to load today's data
function loadTodayData() {
    const today = new Date().toISOString().split('T')[0];
    console.log('Loading today\'s data:', today);
    fetchDashboardData(today);
}

// Function to toggle submenu
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const toggleButton = document.querySelector(`[data-submenu="${submenuId}"]`);
    
    if (submenu && toggleButton) {
        // Close all other submenus first
        const allSubmenus = document.querySelectorAll('.submenu');
        const allToggleButtons = document.querySelectorAll('.reports-toggle');
        
        allSubmenus.forEach(menu => {
            if (menu.id !== submenuId) {
                menu.classList.remove('active');
            }
        });
        
        allToggleButtons.forEach(btn => {
            if (btn.getAttribute('data-submenu') !== submenuId) {
                btn.classList.remove('active');
            }
        });
        
        // Toggle the clicked submenu
        submenu.classList.toggle('active');
        toggleButton.classList.toggle('active');
    }
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard initializing...');
    
    // Set today's date as default in the date picker
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('datePicker').value = today;
    currentSelectedDate = today;
    
    // Fetch initial data for today
    fetchDashboardData(today);
    
    // Set up auto-refresh every 5 minutes
    setInterval(refreshDashboard, 5 * 60 * 1000);
    
    // Add refresh button functionality
    const refreshButtons = document.querySelectorAll('.refresh-btn');
    refreshButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Manual refresh triggered');
            refreshDashboard();
        });
    });
    
    // Add date selection functionality
    document.getElementById('loadDateBtn').addEventListener('click', loadDateData);
    document.getElementById('todayBtn').addEventListener('click', loadTodayData);
    
    // Add submenu toggle functionality for all submenus
    const submenuToggles = document.querySelectorAll('.reports-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            toggleSubmenu(submenuId);
        });
    });
    
    console.log('Dashboard initialized successfully');
});
