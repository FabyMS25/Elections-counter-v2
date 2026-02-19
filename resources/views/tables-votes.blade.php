@extends('layouts.master')
@section('title')
    Election Dashboard
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
    <div class="row">
        <div class="col">
            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Election Monitoring Dashboard</h4>
                                <p class="text-muted mb-0">Real-time presidential election results and analytics.</p>
                            </div>
                            <div class="mt-3 mt-lg-0">
                                <form action="javascript:void(0);">
                                    <div class="row g-3 mb-0 align-items-center">
                                        <div class="col-sm-auto">
                                            <div class="input-group">
                                                <input type="text"
                                                    class="form-control border-0 dash-filter-picker shadow"
                                                    data-provider="flatpickr" data-range-date="true"
                                                    data-date-format="d M, Y"
                                                    data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                                <div class="input-group-text bg-primary border-primary text-white">
                                                    <i class="ri-calendar-2-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-soft-secondary">Refresh Data</button>
                                        </div>
                                        <!--end col-->
                                    </div>
                                    <!--end row-->
                                </form>
                            </div>
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->

                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Total Votes Cast</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-20 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{ $candidateVotes->sum('total_votes') }}">0</span></h4>
                                        <a href="" class="text-decoration-underline">View details</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-secondary-subtle rounded fs-3">
                                            <i class="bx bx-vote text-secondary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Voting Tables Reported</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-20 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{ $reportedTables }}">0</span>/<span class="counter-value"
                                                data-target="{{ $totalTables }}">0</span></h4>
                                        <a href="" class="text-decoration-underline">View all tables</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="bx bx-check-circle text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Reporting Progress</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-20 fw-semibold ff-secondary mb-4"><span class="counter-value"
                                                data-target="{{ round($progressPercentage, 2) }}">0</span>% </h4>
                                        <a href="" class="text-decoration-underline">See details</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="bx bx-trending-up text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Leading Candidate</p>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        @php
                                            $leadingCandidate = $candidateVotes->sortByDesc('total_votes')->first();
                                        @endphp
                                        <h4 class="fs-20 fw-semibold ff-secondary mb-4">{{ $leadingCandidate->candidate->name ?? 'N/A' }}</h4>
                                        <a href="" class="text-decoration-underline">View candidate details</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div> <!-- end row-->

                <div class="row">
                    <div class="col-xl-8">
                        <div class="card">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Vote Distribution by Candidate</h4>
                                <div>
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        Live
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="candidate_votes_chart" 
                                         data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]' 
                                         class="apex-charts" dir="ltr"></div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-4">
                        <div class="card card-height-100">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Votes by Department</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        Export Report
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="votes_by_department" data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                    style="height: 269px" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Vote Progress Over Time</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <span class="text-muted">Last 7 Days<i
                                                    class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Last 7 Days</a>
                                            <a class="dropdown-item" href="#">Last 30 Days</a>
                                            <a class="dropdown-item" href="#">This Month</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="vote_progress_chart" 
                                     data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                     class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Votes by Hour (Today)</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        Live
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="votes_by_hour_chart" 
                                     data-colors='["--vz-primary"]'
                                     class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Voter Turnout by Department</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        Export
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="voter_turnout_chart" 
                                     data-colors='["--vz-success", "--vz-primary", "--vz-warning", "--vz-danger", "--vz-info"]'
                                     class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Candidate Performance by Hour</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        Today
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="candidate_performance_hour_chart" 
                                     data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'
                                     class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Comparison with Previous Election</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        View Report
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="election_comparison_chart" 
                                     data-colors='["--vz-primary", "--vz-secondary"]'
                                     class="apex-charts" dir="ltr"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Vote Distribution by Party</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        View Details
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <canvas id="party_votes_doughnut" class="chartjs-chart" data-colors='["--vz-primary", "--vz-success", "--vz-warning", "--vz-danger", "--vz-info"]'></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Geographical Distribution</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-primary btn-sm">
                                        View Map
                                    </button>
                                </div>
                            </div><!-- end card header -->
                            <div class="card-body">
                                <div id="election_map" style="height: 300px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- end .h-100-->
        </div> <!-- end col -->
    </div>
@endsection

@section('script')
    <!-- apexcharts -->
    <script src="{{ URL::asset('build/libs/apexcharts/apexcharts.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jsvectormap/maps/world-merc.js') }}"></script>
    <script src="{{ URL::asset('build/libs/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/chart.js/chart.umd.js') }}"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Candidate Votes Chart (Pie Chart)
            var candidateVotesData = {!! json_encode($candidateVotes->map(function($vote) {
                return [
                    'candidate' => $vote->candidate->name,
                    'party' => $vote->candidate->party,
                    'votes' => $vote->total_votes
                ];
            })) !!};
            
            var candidateLabels = candidateVotesData.map(item => item.candidate + ' (' + item.party + ')');
            var candidateSeries = candidateVotesData.map(item => item.votes);
            var candidateColors = ['--vz-primary', '--vz-success', '--vz-warning', '--vz-danger', '--vz-info'];
            
            var candidateOptions = {
                series: candidateSeries,
                chart: {
                    height: 350,
                    type: 'pie',
                },
                labels: candidateLabels,
                colors: candidateColors.map(color => getComputedStyle(document.documentElement).getPropertyValue(color)),
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            var candidateChart = new ApexCharts(document.querySelector("#candidate_votes_chart"), candidateOptions);
            candidateChart.render();

            // Votes by Department (Donut Chart)
            var departmentData = {!! json_encode($votesByDepartment->groupBy('department_name')->map(function($group) {
                return $group->sum('total_votes');
            })) !!};
            
            var departmentLabels = Object.keys(departmentData);
            var departmentSeries = Object.values(departmentData);
            
            var departmentOptions = {
                series: departmentSeries,
                chart: {
                    type: 'donut',
                    height: 250,
                },
                labels: departmentLabels,
                colors: candidateColors.map(color => getComputedStyle(document.documentElement).getPropertyValue(color)),
                legend: {
                    position: 'bottom'
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            width: 200
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };

            var departmentChart = new ApexCharts(document.querySelector("#votes_by_department"), departmentOptions);
            departmentChart.render();

            // Vote Progress Over Time (Line Chart)
            var timeSeriesData = {!! json_encode($timeSeriesData) !!};
            
            // Process time series data for chart
            var candidates = [...new Set(timeSeriesData.map(item => item.candidate_name))];
            var dates = [...new Set(timeSeriesData.map(item => item.date))].sort();
            
            var seriesData = candidates.map(candidate => {
                return {
                    name: candidate,
                    data: dates.map(date => {
                        var record = timeSeriesData.find(d => d.date === date && d.candidate_name === candidate);
                        return record ? record.daily_votes : 0;
                    })
                };
            });
            
            var progressOptions = {
                series: seriesData,
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                title: {
                    text: 'Vote Accumulation Over Time',
                    align: 'left'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: dates,
                }
            };

            var progressChart = new ApexCharts(document.querySelector("#vote_progress_chart"), progressOptions);
            progressChart.render();

            // Votes by Hour Chart
            var votesByHourData = {!! json_encode($votesByHour) !!};
            
            var votesByHourOptions = {
                series: [{
                    name: "Votes",
                    data: votesByHourData.map(item => item.votes)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: votesByHourData.map(item => item.hour + ':00'),
                    position: 'top',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        show: false,
                    }
                },
                title: {
                    text: 'Votes by Hour (Today)',
                    floating: true,
                    offsetY: 330,
                    align: 'center',
                    style: {
                        color: '#444'
                    }
                }
            };

            var votesByHourChart = new ApexCharts(document.querySelector("#votes_by_hour_chart"), votesByHourOptions);
            votesByHourChart.render();

            // Voter Turnout by Department
            var voterTurnoutData = {!! json_encode($voterTurnout) !!};
            
            var voterTurnoutOptions = {
                series: [{
                    name: 'Turnout Percentage',
                    data: voterTurnoutData.map(item => item.turnout_percentage)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + "%";
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                xaxis: {
                    categories: voterTurnoutData.map(item => item.department_name),
                    position: 'top',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        show: false,
                        formatter: function (val) {
                            return val + "%";
                        }
                    }
                },
                title: {
                    text: 'Voter Turnout by Department',
                    floating: true,
                    offsetY: 330,
                    align: 'center',
                    style: {
                        color: '#444'
                    }
                }
            };

            var voterTurnoutChart = new ApexCharts(document.querySelector("#voter_turnout_chart"), voterTurnoutOptions);
            voterTurnoutChart.render();

            // Candidate Performance by Hour
            var candidatePerformanceData = {!! json_encode($candidatePerformanceByHour) !!};
            
            // Group data by candidate
            var candidatePerformanceGrouped = {};
            candidatePerformanceData.forEach(item => {
                if (!candidatePerformanceGrouped[item.candidate_name]) {
                    candidatePerformanceGrouped[item.candidate_name] = [];
                }
                candidatePerformanceGrouped[item.candidate_name].push(item);
            });
            
            // Prepare series data
            var performanceSeries = [];
            var hours = [...new Set(candidatePerformanceData.map(item => item.hour))].sort();
            
            for (const [candidate, data] of Object.entries(candidatePerformanceGrouped)) {
                performanceSeries.push({
                    name: candidate,
                    data: hours.map(hour => {
                        const hourData = data.find(d => d.hour === hour);
                        return hourData ? hourData.votes : 0;
                    })
                });
            }
            
            var candidatePerformanceOptions = {
                series: performanceSeries,
                chart: {
                    type: 'line',
                    height: 350,
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                title: {
                    text: 'Candidate Performance by Hour',
                    align: 'left'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: hours.map(hour => hour + ':00')
                }
            };

            var candidatePerformanceChart = new ApexCharts(document.querySelector("#candidate_performance_hour_chart"), candidatePerformanceOptions);
            candidatePerformanceChart.render();

            // Election Comparison Chart
            var comparisonData = {!! json_encode($previousElectionComparison) !!};
            
            var comparisonOptions = {
                series: [{
                    name: 'Current Election',
                    data: comparisonData.current
                }, {
                    name: 'Previous Election',
                    data: comparisonData.previous
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        endingShape: 'rounded'
                    },
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: comparisonData.labels,
                },
                yaxis: {
                    title: {
                        text: 'Votes'
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " votes"
                        }
                    }
                }
            };

            var comparisonChart = new ApexCharts(document.querySelector("#election_comparison_chart"), comparisonOptions);
            comparisonChart.render();

            // Party Votes Doughnut Chart
            var partyVotesData = {!! json_encode($candidateVotes->groupBy('candidate.party')->map(function($group) {
                return $group->sum('total_votes');
            })) !!};
            
            var partyLabels = Object.keys(partyVotesData);
            var partyData = Object.values(partyVotesData);
            
            var ctx = document.getElementById('party_votes_doughnut').getContext('2d');
            var partyVotesChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: partyLabels,
                    datasets: [{
                        data: partyData,
                        backgroundColor: candidateColors.map(color => 
                            getComputedStyle(document.documentElement).getPropertyValue(color)
                        ),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        title: {
                            display: true,
                            text: 'Vote Distribution by Party'
                        }
                    }
                }
            });

            // Initialize map (simplified version)
            var mapContainer = document.getElementById('election_map');
            if (mapContainer) {
                mapContainer.innerHTML = '<div class="text-center p-5"><i class="ri-map-2-line display-4 text-muted"></i><p class="mt-2">Election results map visualization</p><p class="text-muted">Map would show geographical distribution of votes</p></div>';
            }
        });
    </script>
@endsection