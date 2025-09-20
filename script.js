// Global variables for charts
let ordersChart, salesChart, paymentsChart;
let topProductsChart, topCategoriesChart, topCustomersChart;
let currentSelectedDate = new Date().toISOString().split('T')[0]; // Current date in YYYY-MM-DD format
let currentTimePeriod = 'day'; // Current time period (day, week, month)

// Function to fetch dashboard data from API
async function fetchDashboardData(date = null, period = null) {
    try {
        const params = new URLSearchParams();
        if (date) params.append('date', date);
        if (period) params.append('period', period);
        
        const url = `api/dashboard-data.php?${params.toString()}&t=${Date.now()}`;
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.status === 'success') {
            updateDashboard(result.data);
            // Update the date picker to show the selected date
            if (date) {
                document.getElementById('datePicker').value = date;
                currentSelectedDate = date;
            }
            // Update the time period
            if (period) {
                document.getElementById('timePeriod').value = period;
                currentTimePeriod = period;
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
    // Update summary numbers (daily data for orders, sales, payments)
    document.getElementById('ordersCount').textContent = 'Total Orders: ' + formatNumber(data.orders.total);
    document.getElementById('salesAmount').textContent = 'EGP ' + formatCurrencyNumber(data.sales.total);
    document.getElementById('paymentsAmount').textContent = 'EGP ' + formatCurrencyNumber(data.payments.total);
    
    // Always use all-time discount data to ensure it always appears
    if (data.discounts && typeof data.discounts.allTime !== 'undefined') {
        const el = document.getElementById('discountsAmount');
        if (el) el.textContent = 'EGP ' + formatCurrencyNumber(data.discounts.allTime);
    }
    
    // Update charts (daily data)
    updateCharts(data);
    
    // Use all-time data for top performers
    if (data.allTimeTopPerformers) {
        updateTopPerformers(data.allTimeTopPerformers);
    } else {
        updateTopPerformers(data.topPerformers);
    }
    
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
    const n = Number(num);
    if (!isFinite(n)) return '0';
    const parts = Math.trunc(n).toString().split('');
    return parts.reverse().join('').replace(/(\d{3})(?=\d)/g, '$1,').split('').reverse().join('');
}

// Format to 2 decimals and add thousands separators only to integer part
function formatCurrencyNumber(num) {
    if (num === null || num === undefined) return '0.00';
    const n = Number(num);
    if (!isFinite(n)) return '0.00';
    const fixed = n.toFixed(2);
    const [intPart, decPart] = fixed.split('.');
    const intWithCommas = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return intWithCommas + '.' + decPart;
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

// Render pie charts for top performers
function updateTopPerformers(topPerformers) {
    if (!topPerformers) return;
    
    // Destroy existing charts first
    if (topProductsChart) topProductsChart.destroy();
    if (topCategoriesChart) topCategoriesChart.destroy();
    if (topCustomersChart) topCustomersChart.destroy();
    
    const palettes = [
        '#4dc9f6', '#f67019', '#f53794', '#537bc4', '#acc236',
        '#166a8f', '#00a950', '#58595b', '#8549ba'
    ];

    const cfg = (labels, values, title) => ({
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: labels.map((_, i) => palettes[i % palettes.length])
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: { display: false, text: title }
            }
        }
    });

    // Top products
    const tp = topPerformers.products || [];
    const tpLabels = tp.map(x => x.label);
    const tpValues = tp.map(x => Number(x.value || 0));
    const tpCtx = document.getElementById('topProductsChart');
    if (tpCtx) topProductsChart = new Chart(tpCtx, cfg(tpLabels, tpValues, 'Top Products'));

    // Top categories
    const tc = topPerformers.categories || [];
    const tcLabels = tc.map(x => x.label);
    const tcValues = tc.map(x => Number(x.value || 0));
    const tcCtx = document.getElementById('topCategoriesChart');
    if (tcCtx) topCategoriesChart = new Chart(tcCtx, cfg(tcLabels, tcValues, 'Top Categories'));

    // Top customers
    const tcu = topPerformers.customers || [];
    const tcuLabels = tcu.map(x => x.label);
    const tcuValues = tcu.map(x => Number(x.value || 0));
    const tcuCtx = document.getElementById('topCustomersChart');
    if (tcuCtx) topCustomersChart = new Chart(tcuCtx, cfg(tcuLabels, tcuValues, 'Top Customers'));
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
    console.log('Refreshing dashboard for date:', currentSelectedDate, 'period:', currentTimePeriod);
    fetchDashboardData(currentSelectedDate, currentTimePeriod);
}

// Function to update charts with new time period
function updateChartsPeriod() {
    const selectedPeriod = document.getElementById('timePeriod').value;
    console.log('Updating charts to period:', selectedPeriod);
    fetchDashboardData(currentSelectedDate, selectedPeriod);
}

// Function to load data for a specific date
function loadDateData() {
    const selectedDate = document.getElementById('datePicker').value;
    const selectedPeriod = document.getElementById('timePeriod').value;
    if (selectedDate) {
        console.log('Loading data for date:', selectedDate, 'period:', selectedPeriod);
        fetchDashboardData(selectedDate, selectedPeriod);
    } else {
        alert('Please select a date first');
    }
}

// Function to load today's data
function loadTodayData() {
    const today = new Date().toISOString().split('T')[0];
    const selectedPeriod = document.getElementById('timePeriod').value;
    console.log('Loading today\'s data:', today, 'period:', selectedPeriod);
    fetchDashboardData(today, selectedPeriod);
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

// Function to initialize submenu functionality
function initializeSubmenus() {
    const submenuToggles = document.querySelectorAll('.reports-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuId = this.getAttribute('data-submenu');
            toggleSubmenu(submenuId);
        });
    });
    console.log('Submenus initialized');
}

// Initialize dashboard when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page initializing...');
    
    // Initialize submenus on all pages
    initializeSubmenus();
    
    // Check if we're on the dashboard page
    const datePicker = document.getElementById('datePicker');
    if (datePicker) {
        console.log('Dashboard initializing...');
        
        // Set September 5, 2025 as default in the date picker (when orders were created)
        const defaultDate = '2025-09-05';
        datePicker.value = defaultDate;
        currentSelectedDate = defaultDate;
        
        // Fetch initial data for the default date
        fetchDashboardData(defaultDate);
        
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
        
        // Add time period selection functionality
        document.getElementById('updateChartsBtn').addEventListener('click', updateChartsPeriod);
        
        console.log('Dashboard initialized successfully');
    } else {
        console.log('Non-dashboard page initialized');
    }
});
