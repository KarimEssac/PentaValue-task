<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            width: 250px;
        }
        .sidebar-sticky {
            position: relative;
            top: 0;
            height: calc(100vh - 48px);
            padding-top: .5rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #ced4da;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .realtime-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="sidebar">
                <div class="sidebar-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="/dashboard">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/ai-integration">
                                AI Integration
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/weather-integration">
                                Weather Integration
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>

                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <h2 class="card-text" id="total-revenue">$0.00</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Orders (Last Min)</h5>
                                <h2 class="card-text" id="orders-count">0</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Revenue Change</h5>
                                <h2 class="card-text" id="revenue-change">0%</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Active Products</h5>
                                <h2 class="card-text" id="active-products">0</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product ID</th>
                                                <th>Quantity</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recent-orders">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Top Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Product ID</th>
                                                <th>Total Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody id="top-products">
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script>
        const pusher = new Pusher('{{ env("PUSHER_APP_KEY") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            encrypted: true
        });
        const orderChannel = pusher.subscribe('orders');
        orderChannel.bind('order.created', function(data) {
            addNewOrder(data.order);
            fetchAnalytics(); 
        });

        const analyticsChannel = pusher.subscribe('analytics');
        analyticsChannel.bind('analytics.updated', function(data) {
            updateAnalytics(data.analytics);
        });
        function addNewOrder(order) {
            const ordersTable = document.getElementById('recent-orders');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>${order.product_id}</td>
                <td>${order.quantity}</td>
                <td>$${parseFloat(order.price).toFixed(2)}</td>
                <td>$${parseFloat(order.total).toFixed(2)}</td>
            `;
            
            newRow.classList.add('table-success');
            if (ordersTable.firstChild) {
                ordersTable.insertBefore(newRow, ordersTable.firstChild);
            } else {
                ordersTable.appendChild(newRow);
            }
            setTimeout(() => {
                newRow.classList.remove('table-success');
            }, 2000);
            if (ordersTable.children.length > 10) {
                ordersTable.removeChild(ordersTable.lastChild);
            }
        }

        function updateAnalytics(analytics) {
            document.getElementById('total-revenue').textContent = `$${parseFloat(analytics.total_revenue).toFixed(2)}`;
            document.getElementById('orders-count').textContent = analytics.orders_count_last_minute;
            const revenueChangeElem = document.getElementById('revenue-change');
            const change = parseFloat(analytics.revenue_changes_last_minute.percentage_change);
            revenueChangeElem.textContent = `${change > 0 ? '+' : ''}${change.toFixed(2)}%`;
            revenueChangeElem.className = `card-text ${change > 0 ? 'text-success' : change < 0 ? 'text-danger' : 'text-muted'}`;
            const topProductsTable = document.getElementById('top-products');
            topProductsTable.innerHTML = '';
            
            analytics.top_products.forEach(product => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${product.product_id}</td>
                    <td>${product.total_quantity}</td>
                `;
                topProductsTable.appendChild(row);
            });
        
            document.getElementById('active-products').textContent = analytics.top_products.length;
        }
        function fetchAnalytics() {
            fetch('/api/analytics')
                .then(response => response.json())
                .then(data => updateAnalytics(data))
                .catch(error => console.error('Error fetching analytics:', error));
        }
        document.addEventListener('DOMContentLoaded', function() {
            fetchAnalytics();
            fetch('/api/orders/recent')
                .then(response => response.json())
                .then(orders => {
                    orders.forEach(order => addNewOrder(order));
                })
                .catch(error => console.error('Error fetching recent orders:', error));
        });
    </script>
</body>
</html>