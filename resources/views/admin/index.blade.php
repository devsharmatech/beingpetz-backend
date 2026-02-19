@extends('admin.layouts.master')
@section('title')
    Dashboard
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f9f9ff;
        }

        /* Header Section */
        .dashboard-header {
            color: #333;
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 30px;
        }

        .dashboard-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .dashboard-header p {
            margin: 5px 0 0;
            font-size: 12px;
            color: gray;
        }

        /* Cards Section */
        .dashboard-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 4px 10px rgba(131, 55, 178, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }

        .dashboard-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 6px 14px rgba(131, 55, 178, 0.2);
            color: inherit;
            text-decoration: none;
        }

        .card-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgb(131, 55, 178);
            color: white;
            font-size: 2rem;
        }

        .card-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: rgb(131, 55, 178);
        }

        .card-title {
            font-size: 1.1rem;
            color: #555;
            font-weight: 600;
        }

        /* Chart Card */
        .chart-card {
            border: none;
            border-radius: 15px;
            background: white;
            box-shadow: 0 4px 10px rgba(131, 55, 178, 0.1);
            padding: 20px;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: rgb(131, 55, 178);
            margin-bottom: 10px;
        }

        .chart-controls {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .btn-filter {
            background: white;
            border: 2px solid rgb(131, 55, 178);
            color: rgb(131, 55, 178);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-filter.active {
            background: rgb(131, 55, 178);
            color: white;
        }

        .btn-download {
            background: rgb(131, 55, 178);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-download:hover {
            background: rgb(100, 40, 140);
        }

        /* Responsive */
        @media (max-width: 767px) {
            .dashboard-header {
                text-align: center;
            }

            .chart-controls {
                justify-content: center;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt-3">

        <!-- Header -->
        <div class="dashboard-header">
            <h2>Welcome, Admin!</h2>
            <p>This is your Being Petz dashboard overview.</p>
        </div>

        <!-- Dashboard Cards -->
        <div class="row g-4 mb-4">
            <!-- Total Pets -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.pets.list') }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $totalPets }}</div>
                            <div class="card-title">Total Pets</div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-paw"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Users -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.parents.index') }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $totalUsers }}</div>
                            <div class="card-title">Total Users</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Posts -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.post.index') }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $totalPosts }}</div>
                            <div class="card-title">Total Posts</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-file-post"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Total Blogs -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.blogs.index') }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $totalBlogs }}</div>
                            <div class="card-title">Total Blogs</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- NEW: DAU Card -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.parents.index', ['filter' => 'daily']) }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $dau }}</div>
                            <div class="card-title">Daily Active Users</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-calendar-day"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Weekly Active Users -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.parents.index', ['filter' => 'weekly']) }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $wau }}</div>
                            <div class="card-title">Weekly Active Users</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-calendar-week"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Monthly Active Users -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.parents.index', ['filter' => 'monthly']) }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $mau }}</div>
                            <div class="card-title">Monthly Active Users</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-calendar-month"></i>
                        </div>
                    </div>
                </a>
            </div>

            <!-- NEW: Deleted Users Card -->
            <div class="col-xl-3 col-lg-4 col-md-6">
                <a href="{{ route('admin.users.deleted') }}" class="dashboard-card p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="card-value">{{ $deletedUsers }}</div>
                            <div class="card-title">Deleted Users</div>
                        </div>
                        <div class="card-icon">
                            <i class="bi bi-trash"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4">
            <div class="col-lg-8 col-md-12">
                <div class="chart-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="chart-title mb-0">Registrations Overview</h5>
                        <div class="chart-controls">
                            <button class="btn-filter active" data-type="monthly">Monthly</button>
                            <button class="btn-filter" data-type="weekly">Weekly</button>
                            <button class="btn-filter" data-type="daily">Daily</button>

                            <!-- Download Buttons Group -->
                            <div class="btn-group">
                                <button class="btn-download dropdown-toggle" type="button" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="bi bi-download me-1"></i> Download
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item download-btn" href="#" data-type="csv">
                                            <i class="bi bi-file-earmark-spreadsheet me-2"></i> Download as CSV
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item download-btn" href="#" data-type="image">
                                            <i class="bi bi-image me-2"></i> Download as Image
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <canvas id="petChart" height="100"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart -->
            <div class="col-lg-4 col-md-12">
                <div class="chart-card">
                    <h5 class="chart-title">User Activity Overview</h5>
                    <canvas id="userChart" height="200"></canvas>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Line Chart for Pets and Pet Parents - Dynamic Data
        const ctx1 = document.getElementById('petChart').getContext('2d');
        let petChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: @json($months),
                datasets: [{
                        label: 'New Pets',
                        data: @json($petData),
                        borderColor: 'rgb(131, 55, 178)',
                        backgroundColor: 'rgba(131, 55, 178, 0.2)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                        pointBackgroundColor: 'rgb(131, 55, 178)'
                    },
                    {
                        label: 'New Pet Parents',
                        data: @json($petParentData),
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        borderWidth: 3,
                        fill: true,
                        pointBackgroundColor: 'rgb(255, 99, 132)'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Doughnut Chart for Users - Dynamic Data
        const ctx2 = document.getElementById('userChart').getContext('2d');
        const userChart = new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Active Users', 'Inactive Users'],
                datasets: [{
                    data: [{{ $activeUsers }}, {{ $inactiveUsers }}],
                    backgroundColor: ['rgb(131, 55, 178)', '#d3d3d3'],
                    borderWidth: 2
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Filter functionality
        document.querySelectorAll('.btn-filter').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.btn-filter').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked button
                this.classList.add('active');

                // Update chart based on filter
                const filterType = this.getAttribute('data-type');
                updateChartData(filterType);
            });
        });

        // Function to update chart data based on filter
        function updateChartData(type) {
            // Show loading state
            const chartTitle = document.querySelector('.chart-title');
            chartTitle.textContent = `Registrations Overview - ${type.charAt(0).toUpperCase() + type.slice(1)}`;

            // In a real application, you would make an AJAX call here
            // For demo purposes, we'll just update with sample data
            let newLabels, newPetData, newParentData;

            if (type === 'weekly') {
                newLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6',
                    'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12'
                ];
                newPetData = [12, 19, 8, 15, 12, 16, 10, 14, 18, 11, 13, 17];
                newParentData = [8, 12, 6, 10, 9, 11, 7, 13, 10, 8, 9, 12];
            } else if (type === 'daily') {
                newLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                newPetData = [3, 5, 2, 4, 6, 8, 4];
                newParentData = [2, 3, 1, 3, 4, 5, 2];
            } else {
                // Monthly (default)
                newLabels = @json($months);
                newPetData = @json($petData);
                newParentData = @json($petParentData);
            }

            // Update chart
            petChart.data.labels = newLabels;
            petChart.data.datasets[0].data = newPetData;
            petChart.data.datasets[1].data = newParentData;
            petChart.update();
        }

        // Download functionality
        document.getElementById('downloadChart').addEventListener('click', function() {
            const activeFilter = document.querySelector('.btn-filter.active').getAttribute('data-type');

            // Download CSV
            const link = document.createElement('a');
            link.href = "{{ route('admin.export.chart') }}?type=" + activeFilter;
            link.click();
        });
    </script>

    <script>
        // Download functionality
        document.querySelectorAll('.download-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                const downloadType = this.getAttribute('data-type');
                const activeFilter = document.querySelector('.btn-filter.active').getAttribute('data-type');

                let downloadUrl;

                if (downloadType === 'csv') {
                    downloadUrl = "{{ route('admin.export.chart') }}?type=" + activeFilter;
                } else {
                    downloadUrl = "{{ route('admin.export.chart.image') }}?type=" + activeFilter;
                }

                // Create temporary link for download
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });

        // Filter functionality - AJAX implementation for real data
        document.querySelectorAll('.btn-filter').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.btn-filter').forEach(btn => {
                    btn.classList.remove('active');
                });

                // Add active class to clicked button
                this.classList.add('active');

                // Update chart with real data via AJAX
                const filterType = this.getAttribute('data-type');
                fetchChartData(filterType);
            });
        });

        // Function to fetch real chart data via AJAX
        function fetchChartData(type) {
            fetch(`/admin/get-chart-data?type=${type}`)
                .then(response => response.json())
                .then(data => {
                    // Update chart with real data
                    petChart.data.labels = data.labels;
                    petChart.data.datasets[0].data = data.petData;
                    petChart.data.datasets[1].data = data.parentData;
                    petChart.update();

                    // Update chart title
                    const chartTitle = document.querySelector('.chart-title');
                    chartTitle.textContent = `Registrations Overview - ${type.charAt(0).toUpperCase() + type.slice(1)}`;
                })
                .catch(error => {
                    console.error('Error fetching chart data:', error);
                });
        }
    </script>
@endsection
