<?php
// index.php
require_once 'db_setup.php';     // 包含数据库索引和计算逻辑设置
require_once 'data_processor.php'; // 包含数据处理逻辑
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据看板</title>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1><i class="fas fa-chart-line"></i>数据看板</h1>
        <div class="datetime-info">
            <div id="current-datetime"></div>
            <div id="data-collection-date"></div>
        </div>
    </div>

    <hr>

    <div class="dashboard-grid">
        <div class="card-container">
            <div class="glass-card card" id="total-task-card">
                <i class="fas fa-tasks"></i>
                <div class="value" id="total-task-volume"></div>
                <div class="label">总任务量</div>
            </div>
            <div class="glass-card card" id="monthly-task-card">
                <i class="fas fa-calendar-alt"></i>
                <div class="value" id="monthly-task-volume"></div>
                <div class="label">月任务量</div>
            </div>
            <div class="glass-card card" id="monthly-renewal-card-summary">
                <i class="fas fa-handshake"></i>
                <div class="value" id="monthly-renewal-volume"></div>
                <div class="label">月续保量</div>
            </div>
            <div class="glass-card card" id="daily-renewal-card-summary">
                <i class="fas fa-check-circle"></i>
                <div class="value" id="daily-renewal-volume"></div>
                <div class="label">当日续保量</div>
            </div>
            <div class="glass-card card">
                <i class="fas fa-chart-bar"></i>
                <div class="value" id="day-on-day-ratio"></div>
                <div class="label">与上日环比</div>
            </div>
        </div>

        <div class="card-container" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
            <div class="glass-card card" id="quarterly-renewal-card">
                <i class="fas fa-percentage"></i>
                <div class="value" id="quarterly-renewal-rate"></div>
                <div class="label">季度续保率</div>
            </div>
            <div class="glass-card card" id="monthly-renewal-card">
                <i class="fas fa-chart-pie"></i>
                <div class="value" id="monthly-renewal-rate"></div>
                <div class="label">月度续保率</div>
            </div>
            <div class="glass-card card" id="weekly-renewal-card">
                <i class="fas fa-calendar-week"></i>
                <div class="value" id="weekly-renewal-rate"></div>
                <div class="label">周续保率</div>
            </div>
        </div>

        <div class="chart-row top-charts">
            <div class="glass-chart-container chart-container main-chart">
                <h3><i class="fas fa-chart-line chart-title-icon"></i>续保走势</h3>
                <div id="insurance-trend-chart" style="width: 100%; height: 350px;"></div>
            </div>
            <div class="glass-chart-container chart-container">
                <h3><i class="fas fa-users chart-title-icon"></i>录单员当月续保量占比</h3>
                <div id="recorder-renewal-pie-chart" style="width: 100%; height: 280px;"></div>
            </div>
        </div>

        <div class="chart-row middle-charts">
            <div class="glass-chart-container chart-container main-chart">
                <h3><i class="fas fa-chart-area chart-title-icon"></i>当前日期与上年同期数据对比</h3>
                <div id="year-on-year-comparison-chart" style="width: 100%; height: 350px;"></div>
            </div>
            <div class="glass-chart-container chart-container">
                <h3><i class="fas fa-calendar-check chart-title-icon"></i>续保周期天数统计</h3>
                <div id="renewal-cycle-donut-chart" style="width: 100%; height: 280px;"></div>
            </div>
        </div>

        <div class="chart-row bottom-chart">
            <div class="glass-chart-container chart-container main-chart">
                <h3><i class="fas fa-hourglass-half chart-title-icon"></i>续保到期数量</h3>
                <div id="renewal-due-bar-chart" style="width: 100%; height: 280px;"></div>
            </div>
            <div class="vertical-cards-container">
                <div class="glass-card card vertical-card" id="monthly-calculation-card">
                    <i class="fas fa-calculator"></i>
                    <div class="value" id="monthly-calculation-volume"></div>
                    <div class="label">本月算单量</div>
                </div>
                <div class="glass-card card vertical-card" id="renewal-calculation-card">
                    <i class="fas fa-redo"></i>
                    <div class="value" id="renewal-calculation-volume"></div>
                    <div class="label">续保算单量</div>
                </div>
            </div>
        </div>

        <div class="glass-table-container table-container">
            <h3><i class="fas fa-file-invoice chart-title-icon"></i>录单员详情</h3>
            <table id="recorder-detail-table">
                <thead>
                    <tr>
                        <th class="sortable-header" data-sort-column="0" data-table-id="recorder-detail-table">
                            录单员 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="1" data-table-id="recorder-detail-table">
                            上月保费 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="2" data-table-id="recorder-detail-table">
                            当月保费 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="3" data-table-id="recorder-detail-table">
                            保费进度 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="4" data-table-id="recorder-detail-table">
                            上月续保量 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="5" data-table-id="recorder-detail-table">
                            当月续保量 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="6" data-table-id="recorder-detail-table">
                            当日续保量 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="7" data-table-id="recorder-detail-table">
                            同期续保量 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="8" data-table-id="recorder-detail-table">
                            续保占比 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="9" data-table-id="recorder-detail-table">
                            贡献值 <span class="sort-icon fas"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>

        <div class="glass-table-container table-container">
            <h3><i class="fas fa-user-tie chart-title-icon"></i>业务员详情</h3>
            <table id="salesperson-detail-table">
                <thead>
                    <tr>
                        <th class="sortable-header" data-sort-column="0" data-table-id="salesperson-detail-table">
                            业务员 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="1" data-table-id="salesperson-detail-table">
                            可续台次 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="2" data-table-id="salesperson-detail-table">
                            已续台次 <span class="sort-icon fas"></span>
                        </th>
                        <th class="sortable-header" data-sort-column="3" data-table-id="salesperson-detail-table">
                            脱保台次 <span class="sort-icon fas"></span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
            </table>
        </div>
    </div>

    <div id="chartModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 id="modal-title"></h3>
            <div id="modal-chart" class="modal-body"></div>
        </div>
    </div>

    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-button detail-close-button">&times;</span>
            <div class="modal-title-container"> <h3 id="detail-modal-title"></h3>
                <button id="copyTableButton" class="copy-button" style="display: none;">
                    <i class="fas fa-copy"></i> 复制
                </button>
            </div>
            <div id="detail-modal-body" class="modal-body">
                <table id="detail-modal-table">
                    <thead>
                        <tr id="detail-modal-table-header"></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // 将 PHP 生成的 JSON 数据赋值给 JavaScript 变量
        const mockData = <?php echo $mockDataJson; ?>;

        // --- 背景颜色随机化函数 ---
        function setRandomDarkBackgroundColor() {
            const darkColors = [
                '#2C3E50', '#34495E', '#1A2B3C', '#4A4A4A', '#36454F', '#2F4F4F', '#2D3436'
            ];
            const randomIndex = Math.floor(Math.random() * darkColors.length);
            document.body.style.backgroundColor = darkColors[randomIndex];
        }

        // --- 时间更新函数 ---
        function updateDateTime() {
            const now = new Date();
            const year = now.getFullYear();
            const month = (now.getMonth() + 1).toString().padStart(2, '0');
            const day = now.getDate().toString().padStart(2, '0');
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('current-datetime').textContent = `${year}年${month}月${day}日 ${hours}:${minutes}:${seconds}`;

            const yesterday = new Date();
            yesterday.setDate(now.getDate() - 1);
            const yYear = yesterday.getFullYear();
            const yMonth = (yesterday.getMonth() + 1).toString().padStart(2, '0');
            const yDay = yesterday.getDate().toString().padStart(2, '0');
            document.getElementById('data-collection-date').textContent = `采集时间：${yYear}年${yMonth}月${yDay}日 (实时数据)`;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        // --- 更新数字看板数据 ---
        function updateNumberCards() {
            document.getElementById('total-task-volume').textContent = mockData.totalTaskVolume.toLocaleString();
            document.getElementById('monthly-task-volume').textContent = mockData.monthlyTaskVolume.toLocaleString();
            document.getElementById('monthly-renewal-volume').textContent = mockData.monthlyRenewalVolume.toLocaleString();
            document.getElementById('daily-renewal-volume').textContent = mockData.dailyRenewalVolume.toLocaleString();
            
            const dayOnDayRatioElement = document.getElementById('day-on-day-ratio');
            dayOnDayRatioElement.textContent = mockData.dayOnDayRatio;
            // 根据环比值的正负设置颜色
            // 检查是否为“+INF%”特殊字符串，或实际数值是否小于0
            if (mockData.dayOnDayRatio === '+INF%') {
                dayOnDayRatioElement.style.color = '#8be9fd'; // 无穷大通常视为积极，使用默认蓝色
            } else if (parseFloat(mockData.dayOnDayRatio) < 0) {
                dayOnDayRatioElement.style.color = '#ff6b6b'; // 负数使用红色
            } else {
                dayOnDayRatioElement.style.color = '#8be9fd'; // 其他情况（0或正数）使用默认蓝色
            }


            document.getElementById('quarterly-renewal-rate').textContent = mockData.quarterlyRenewalRate;
            document.getElementById('monthly-renewal-rate').textContent = mockData.monthlyRenewalRate;
            document.getElementById('weekly-renewal-rate').textContent = mockData.weeklyRenewalRate;

            document.getElementById('monthly-calculation-volume').textContent = mockData.monthlyCalculationVolume.toLocaleString();
            document.getElementById('renewal-calculation-volume').textContent = mockData.renewalCalculationVolume.toLocaleString();
        }

        // --- 渲染 ECharts 图表 ---
        function renderECharts() {
            const chartTextColor = '#ecf0f1';
            const chartLineColor = 'rgba(255, 255, 255, 0.3)';
            const chartAreaColor = 'rgba(139, 233, 253, 0.1)';

            const insuranceTrendChart = echarts.init(document.getElementById('insurance-trend-chart'));
            const insuranceTrendOption = {
                color: ['#8be9fd'], // 只有一条线，所以只需要一个颜色
                tooltip: { trigger: 'axis' },
                legend: {
                    data: ['已续保数量'], // 更名为已续保数量
                    textStyle: { color: chartTextColor }
                },
                xAxis: {
                    type: 'category',
                    data: mockData.insuranceTrend.dates,
                    axisLabel: {
                        color: chartTextColor,
                        rotate: 45,
                        interval: 0
                    },
                    axisLine: { lineStyle: { color: chartLineColor } }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: { color: chartTextColor },
                    axisLine: { lineStyle: { color: chartLineColor } },
                    splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.1)' } }
                },
                series: [
                    { name: '已续保数量', type: 'line', data: mockData.insuranceTrend.renewed, smooth: true, areaStyle: { color: chartAreaColor } }
                ]
            };
            insuranceTrendChart.setOption(insuranceTrendOption);


            const recorderRenewalPieChart = echarts.init(document.getElementById('recorder-renewal-pie-chart'));
            const recorderRenewalPieOption = {
                color: ['#ff6b6b', '#feca57', '#48dbfb', '#1dd1a1', '#ff9ff3'],
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: {
                    show: false
                },
                series: [
                    {
                        name: '续保量',
                        type: 'pie',
                        radius: '70%',
                        center: ['50%', '50%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: true,
                            position: 'outer',
                            formatter: '{b} ({d}%)',
                            color: chartTextColor
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '20',
                                fontWeight: 'bold',
                                color: '#fff'
                            }
                        },
                        labelLine: { show: true },
                        data: mockData.recorderRenewal
                    }
                ]
            };
            recorderRenewalPieChart.setOption(recorderRenewalPieOption);

            recorderRenewalPieChart.on('click', function (params) {
                if (params.componentType === 'series' && params.seriesType === 'pie') {
                    const recorderName = params.name;
                    const detailData = mockData.recorderRenewalDetails[recorderName];

                    if (detailData) {
                        showDetailModal(
                            `${recorderName} 当月续保详单`,
                            [
                                { label: '业务员名称', field: 'salespersonName' },
                                { label: '车牌号', field: 'licensePlate' },
                                { label: '投保人', field: 'applicantName' },
                                { label: '净保费', field: 'netPremium' },
                                { label: '利润', field: 'profit' }
                            ],
                            detailData
                        );
                    } else {
                        alert(`没有找到 ${recorderName} 的详细续保数据。`);
                    }
                }
            });


            const renewalDueBarChart = echarts.init(document.getElementById('renewal-due-bar-chart'));
            const renewalDueOption = {
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                xAxis: {
                    type: 'category',
                    data: mockData.renewalDue.map(item => item.name),
                    axisLabel: { color: chartTextColor },
                    axisLine: { lineStyle: { color: chartLineColor } }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: { color: chartTextColor },
                    axisLine: { lineStyle: { color: chartLineColor } },
                    splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.1)' } }
                },
                series: [{
                    name: '到期数量',
                    type: 'bar',
                    data: mockData.renewalDue.map(item => item.value),
                    itemStyle: {
                        color: new echarts.graphic.LinearGradient(
                            0, 0, 0, 1,
                            [
                                {offset: 0, color: '#00b894'},
                                {offset: 1, color: '#00cec9'}
                            ]
                        ),
                        borderRadius: [5, 5, 0, 0]
                    },
                    barWidth: '60%',
                    emphasis: {
                        itemStyle: {
                            color: new echarts.graphic.LinearGradient(
                                0, 0, 0, 1,
                                [
                                    {offset: 0, color: '#00d1a9'},
                                    {offset: 1, color: '#00e0d5'}
                                ]
                            )
                        }
                    }
                }]
            };
            renewalDueBarChart.setOption(renewalDueOption);

            const yearOnYearComparisonChart = echarts.init(document.getElementById('year-on-year-comparison-chart'));
            const yearOnYearOption = {
                color: ['#ffeaa7', '#a29bfe'],
                tooltip: { trigger: 'axis' },
                legend: {
                    data: ['本年', '上年同期'],
                    textStyle: { color: chartTextColor }
                },
                xAxis: {
                    type: 'category',
                    data: mockData.yearOnYearComparison.dates,
                    axisLabel: {
                        color: chartTextColor,
                        rotate: 45,
                        interval: 0
                    },
                    axisLine: { lineStyle: { color: chartLineColor } }
                },
                yAxis: {
                    type: 'value',
                    axisLabel: { color: chartTextColor },
                    axisLine: { lineStyle: { color: chartLineColor } },
                    splitLine: { lineStyle: { color: 'rgba(255, 255, 255, 0.1)' } }
                },
                series: [
                    { name: '本年', type: 'line', data: mockData.yearOnYearComparison.currentYear, smooth: true, areaStyle: { color: 'rgba(255, 234, 167, 0.1)' } },
                    { name: '上年同期', type: 'line', data: mockData.yearOnYearComparison.lastYear, smooth: true, lineStyle: { type: 'dashed', color: '#a29bfe' }, areaStyle: { color: 'rgba(162, 155, 254, 0.1)' } }
                ]
            };
            yearOnYearComparisonChart.setOption(yearOnYearOption);

            const renewalCycleDonutChart = echarts.init(document.getElementById('renewal-cycle-donut-chart'));
            const renewalCycleOption = {
                color: ['#ff7675', '#fdcb6e', '#55efc4', '#74b9ff', '#a29bfe', '#e17055'],
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    textStyle: { color: chartTextColor }
                },
                series: [
                    {
                        name: '续保周期',
                        type: 'pie',
                        radius: ['40%', '70%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: false,
                            position: 'center'
                        },
                        emphasis: {
                            label: {
                                show: true,
                                fontSize: '20',
                                fontWeight: 'bold',
                                color: '#fff'
                            }
                        },
                        labelLine: { show: false },
                        data: mockData.renewalCycle
                    }
                ]
            };
            renewalCycleDonutChart.setOption(renewalCycleOption);

            window.addEventListener('resize', () => {
                insuranceTrendChart.resize();
                recorderRenewalPieChart.resize();
                renewalDueBarChart.resize();
                yearOnYearComparisonChart.resize();
                renewalCycleDonutChart.resize();
                if (modalChartInstance) {
                    modalChartInstance.resize();
                }
            });
        }

        // --- 通用表格填充函数 (用于录单员详情) ---
        // 此函数不再直接使用，因为MakeSortable会调用renderTableRows
        // function fillTable(tableId, data) {
        //     const tableBody = document.querySelector(`#${tableId} tbody`);
        //     tableBody.innerHTML = '';
        //     data.forEach(rowData => {
        //         const row = tableBody.insertCell();
        //         rowData.forEach(cellData => {
        //             const cell = row.insertCell();
        //             cell.textContent = cellData;
        //         });
        //     });
        // }

        // --- ECharts图表弹窗相关逻辑 ---
        const chartModal = document.getElementById('chartModal');
        const closeButton = document.querySelector('.close-button:not(.detail-close-button)');
        const modalTitle = document.getElementById('modal-title');
        const modalChartDiv = document.getElementById('modal-chart');
        let modalChartInstance = null;

        closeButton.onclick = function() {
            chartModal.style.display = 'none';
            if (modalChartInstance) {
                modalChartInstance.dispose();
                modalChartInstance = null;
            }
        }

        // --- 详单模式弹窗相关逻辑 ---
        const detailModal = document.getElementById('detailModal');
        const detailCloseButton = document.querySelector('.detail-close-button');
        const detailModalTitle = document.getElementById('detail-modal-title');
        const detailModalTable = document.getElementById('detail-modal-table');
        const copyTableButton = document.getElementById('copyTableButton'); // 获取复制按钮

        detailCloseButton.onclick = function() {
            detailModal.style.display = 'none';
            copyTableButton.style.display = 'none'; // 关闭弹窗时隐藏复制按钮
        }

        window.onclick = function(event) {
            if (event.target == chartModal) {
                chartModal.style.display = 'none';
                if (modalChartInstance) {
                    modalChartInstance.dispose();
                    modalChartInstance = null;
                }
            }
            if (event.target == detailModal) {
                detailModal.style.display = 'none';
                copyTableButton.style.display = 'none'; // 关闭弹窗时隐藏复制按钮
            }
        }

        function showChartModal(title, chartType, data, xData, yAxisLabel) {
            modalTitle.textContent = title;
            chartModal.style.display = 'flex';

            if (modalChartInstance) {
                modalChartInstance.dispose();
            }
            modalChartInstance = echarts.init(modalChartDiv);

            const chartTextColor = '#ecf0f1';
            const chartLineColor = 'rgba(255, 255, 255, 0.4)';
            const gridColor = 'rgba(255, 255, 255, 0.1)';

            let option;
            if (chartType === 'bar') {
                option = {
                    color: ['#00b894'],
                    tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                    xAxis: {
                        type: 'category',
                        data: xData,
                        axisLabel: { color: chartTextColor },
                        axisLine: { lineStyle: { color: chartLineColor } }
                    },
                    yAxis: {
                        type: 'value',
                        name: yAxisLabel,
                        axisLabel: {
                            formatter: (value) => {
                                if (yAxisLabel === '续保率') {
                                    return value + '%';
                                }
                                return value;
                            },
                            color: chartTextColor
                        },
                        axisLine: { lineStyle: { color: chartLineColor } },
                        splitLine: { lineStyle: { color: gridColor } }
                    },
                    series: [{
                        name: yAxisLabel,
                        type: 'bar',
                        data: data,
                        itemStyle: {
                            borderRadius: [5, 5, 0, 0],
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0, color: '#00d1a9'
                            }, {
                                offset: 1, color: '#00cec9'
                            }])
                        },
                        emphasis: {
                            itemStyle: {
                                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                    offset: 0, color: '#00e0d5'
                                }, {
                                    offset: 1, color: '#00d1a9'
                                }])
                            }
                        }
                    }]
                };
            } else if (chartType === 'line') {
                option = {
                    color: ['#8be9fd'],
                    tooltip: { trigger: 'axis' },
                    xAxis: {
                        type: 'category',
                        data: xData,
                        axisLabel: { color: chartTextColor, rotate: 45, interval: Math.ceil(xData.length / 10) },
                        axisLine: { lineStyle: { color: chartLineColor } }
                    },
                    yAxis: {
                        type: 'value',
                        name: yAxisLabel,
                        axisLabel: {
                            formatter: (value) => {
                                if (yAxisLabel === '续保率') {
                                    return value + '%';
                                }
                                return value;
                            },
                            color: chartTextColor
                        },
                        axisLine: { lineStyle: { color: chartLineColor } },
                        splitLine: { lineStyle: { color: gridColor } }
                    },
                    series: [{
                        name: yAxisLabel,
                        type: 'line',
                        data: data,
                        smooth: true,
                        areaStyle: {
                            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [{
                                offset: 0, color: 'rgba(139, 233, 253, 0.4)'
                            }, {
                                offset: 1, color: 'rgba(139, 233, 253, 0)'
                            }])
                        },
                        itemStyle: {
                            color: '#8be9fd'
                        }
                    }]
                };
            }
            modalChartInstance.setOption(option);
            modalChartInstance.resize();
        }

        // 函数：复制表格数据到剪贴板
        async function copyTableToClipboard(tableElement, columnsConfig) {
            // 获取模板数据
            const welcomeMessages = <?php echo file_get_contents('templates/welcome_messages.json'); ?>;
            const closingMessages = <?php echo file_get_contents('templates/closing_messages.json'); ?>;
            
            // 随机选择欢迎语和结束语
            const randomWelcome = welcomeMessages[Math.floor(Math.random() * welcomeMessages.length)];
            const randomClosing = closingMessages[Math.floor(Math.random() * closingMessages.length)];
            
            let copiedText = randomWelcome + '\n\n';
            const rows = tableElement.querySelectorAll('thead tr, tbody tr');
            const columnWidths = {}; // 存储每列最大宽度
            
            // 获取复制按钮引用
            const copyBtn = document.getElementById('copyTableButton');
            const originalBtnText = copyBtn.innerHTML;

            // 第一遍：计算每列最大宽度并收集数据
            const allRowData = [];
            rows.forEach((row, rowIndex) => {
                const cells = row.querySelectorAll('th, td');
                const rowValues = [];
                cells.forEach((cell, colIndex) => {
                    let textContent = cell.textContent.trim();
                    const columnField = columnsConfig[colIndex] ? columnsConfig[colIndex].field : null;
                    const columnLabel = columnsConfig[colIndex] ? columnsConfig[colIndex].label : null;

                    // 首列内容处理（大于5字则截断）
                    if (colIndex === 0 && textContent.length > 5) {
                        textContent = textContent.substring(0, 5) + '...';
                    }
                    
                    // 复选框列不复制内容
                    if (cell.querySelector('input[type="checkbox"]')) {
                        textContent = '';
                    }

                    rowValues.push(textContent);
                    columnWidths[colIndex] = Math.max(columnWidths[colIndex] || 0, textContent.length);
                });
                allRowData.push(rowValues);
                
                // 在表头后添加分线符
                if (rowIndex === 0) {
                    allRowData.push(['------']);
                }
            });

            // 第二遍：格式化文本
            allRowData.forEach(rowValues => {
                if (rowValues[0] === '------') {
                    copiedText += '-'.repeat(30) + '\n'; // 分线符
                } else {
                    rowValues.forEach((text, colIndex) => {
                        copiedText += text.padEnd(columnWidths[colIndex] + 2); // 添加间距
                    });
                    copiedText += '\n';
                }
            });

            // 添加结束语
            copiedText += '\n' + randomClosing;

            try {
                await navigator.clipboard.writeText(copiedText);
                
                // 更新按钮状态
                copyBtn.innerHTML = '<i class="fas fa-check"></i> 已复制';
                copyBtn.style.backgroundColor = '#07C160';
                copyBtn.style.color = 'white';
                
                // 2秒后恢复按钮状态
                setTimeout(() => {
                    copyBtn.innerHTML = originalBtnText;
                    copyBtn.style.backgroundColor = '';
                    copyBtn.style.color = '';
                }, 2000);
                
            } catch (err) {
                console.error('复制失败:', err);
                
                // 更新按钮状态
                copyBtn.innerHTML = '<i class="fas fa-times"></i> 复制失败';
                copyBtn.style.backgroundColor = '#ff4d4f';
                copyBtn.style.color = 'white';
                
                // 2秒后恢复按钮状态
                setTimeout(() => {
                    copyBtn.innerHTML = originalBtnText;
                    copyBtn.style.backgroundColor = '';
                    copyBtn.style.color = '';
                }, 2000);
            }
        }


        // 函数：显示详细表格弹窗
        function showDetailModal(title, columns, data, isExpiredTable = false) {
            detailModalTitle.textContent = title;
            detailModal.style.display = 'flex';

            const tableHeaderRow = detailModalTable.querySelector('thead tr');
            const tableBody = detailModalTable.querySelector('tbody');

            tableHeaderRow.innerHTML = '';
            tableBody.innerHTML = '';

            // 检查是否是可续保、已续保等需要复制功能的弹窗 (非脱保，且不包含标注选项列)
            // 修改条件：只要不是脱保表格，就显示复制按钮
            if (!isExpiredTable) {
                copyTableButton.style.display = 'inline-flex'; // Show copy button
                // Remove any existing click listeners before adding a new one
                copyTableButton.onclick = null; // Clear previous listener
                copyTableButton.onclick = () => copyTableToClipboard(detailModalTable, columns);
            } else {
                copyTableButton.style.display = 'none'; // Hide copy button for expired table
            }


            columns.forEach(col => {
                const th = document.createElement('th');
                if (isExpiredTable && col.field === 'markOption') {
                    const labelDiv = document.createElement('div');
                    labelDiv.textContent = col.label; // "标注选项"
                    th.appendChild(labelDiv);

                    const selectAllCheckbox = document.createElement('input');
                    selectAllCheckbox.type = 'checkbox';
                    selectAllCheckbox.id = 'selectAllExpired';
                    selectAllCheckbox.style.marginLeft = '10px';
                    
                    const labelForSelectAll = document.createElement('label');
                    labelForSelectAll.setAttribute('for', 'selectAllExpired');
                    labelForSelectAll.textContent = '全选';
                    labelForSelectAll.style.cursor = 'pointer';
                    labelForSelectAll.style.color = '#a8dadc'; // Match header text color

                    const headerCheckboxContainer = document.createElement('div');
                    headerCheckboxContainer.style.display = 'flex';
                    headerCheckboxContainer.style.alignItems = 'center';
                    headerCheckboxContainer.style.justifyContent = 'center'; // Center the whole header content
                    headerCheckboxContainer.appendChild(selectAllCheckbox);
                    headerCheckboxContainer.appendChild(labelForSelectAll);

                    th.appendChild(headerCheckboxContainer);

                    selectAllCheckbox.addEventListener('change', function() {
                        const checkboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                            // Trigger change event on individual checkboxes to update UI
                            // This part is for visual feedback. Actual marking would involve an API call.
                            console.log(`Setting checkbox for ${checkbox.dataset.licensePlate} to ${this.checked}`);
                            const event = new Event('change');
                            checkbox.dispatchEvent(event);
                        });
                    });

                } else {
                    th.textContent = col.label;
                }
                tableHeaderRow.appendChild(th);
            });

            data.forEach(rowData => {
                const tr = tableBody.insertRow();
                columns.forEach((col, colIndex) => {
                    const td = tr.insertCell();
                    if (isExpiredTable && col.field === 'markOption') {
                        const checkboxContainer = document.createElement('div');
                        checkboxContainer.className = 'checkbox-container';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.dataset.licensePlate = rowData.licensePlate; // Store license plate for future use
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                // Simulate removal from calculations visually
                                tr.style.opacity = '0.5'; // Visually fade out
                                tr.style.textDecoration = 'line-through'; // Cross out text
                                // In a real application, you would send an API request here to mark as "不再计数"
                                console.log(`车辆 ${this.dataset.licensePlate} 已被标记为“不再计数”。`);
                            } else {
                                tr.style.opacity = '1';
                                tr.style.textDecoration = 'none';
                                // In a real application, you would send an API request here to unmark
                                console.log(`车辆 ${this.dataset.licensePlate} 已取消标记“不再计数”。`);
                            }
                            // Update "全选" checkbox state based on individual checkboxes
                            const allCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]');
                            const checkedCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]:checked');
                            const selectAllCheckbox = document.getElementById('selectAllExpired');
                            if (selectAllCheckbox) {
                                selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
                            }
                        });
                        checkboxContainer.appendChild(checkbox);
                        td.appendChild(checkboxContainer);
                    } else if (col.field === 'applicantName' || (col.label === '投保人名称' && !isExpiredTable)) { // Apply truncation for '投保人名称' in applicable tables
                        let displayValue = rowData[col.field] || '';
                        if (displayValue.length > 5) {
                            displayValue = displayValue.substring(0, 5) + '...';
                        }
                        td.textContent = displayValue;
                    }
                    else {
                        td.textContent = rowData[col.field] || '';
                    }
                });
            });

            // After populating the table, if it's an expired table, set initial indeterminate state for selectAll checkbox
            if (isExpiredTable) {
                const selectAllCheckbox = document.getElementById('selectAllExpired');
                if (selectAllCheckbox) {
                    const allCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]');
                    const checkedCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]:checked');
                    selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                    selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
                }
            }
        }


        // --- 通用排序函数 ---
        /**
         * 为指定的HTML表格添加可排序功能。
         * @param {string} tableId 表格的ID。
         * @param {Array<Array<any>>} initialData 初始表格数据，用于排序。
         */
        function makeSortable(tableId, initialData) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const headers = table.querySelectorAll('.sortable-header');
            const tableBody = table.querySelector('tbody');
            let currentData = [...initialData]; // 处理数据的副本
            const localStorageKey = `sortState_${tableId}`; // 用于存储排序状态的本地存储键

            /**
             * 获取用于排序的实际值，处理逗号和百分比。
             * @param {any} value 单元格的原始值。
             * @returns {number|string} 转换后的可排序值。
             */
            function getSortableValue(value) {
                if (typeof value === 'string') {
                    // 移除逗号和百分号，尝试转换为浮点数
                    const cleanedValue = value.replace(/,/g, '').replace(/%/g, '');
                    if (!isNaN(cleanedValue) && cleanedValue.trim() !== '') {
                        return parseFloat(cleanedValue);
                    }
                }
                return value; // 对于纯字符串（如姓名）或数字，直接返回
            }

            /**
             * 根据当前数据渲染表格行。
             * @param {Array<Array<any>>} dataToRender 需要渲染的数据。
             */
            function renderTableRows(dataToRender) {
                tableBody.innerHTML = ''; // 清空现有行
                dataToRender.forEach((rowDataArray, rowIndex) => {
                    const row = tableBody.insertRow();
                    rowDataArray.forEach((cellData, colIndex) => {
                        const cell = row.insertCell();
                        cell.textContent = cellData;

                        // 为业务员详情表格的政策数量单元格重新绑定点击事件
                        if (tableId === 'salesperson-detail-table' && colIndex > 0) {
                            cell.classList.add('clickable-policy-count');
                            const salespersonName = rowDataArray[0]; // 假设业务员名称是第一列
                            // 映射列索引到数据类型
                            const dataTypeMap = {1: 'renewable', 2: 'renewed', 3: 'expired'};
                            const dataType = dataTypeMap[colIndex];
                            if (dataType) {
                                cell.dataset.salesperson = salespersonName;
                                cell.dataset.type = dataType;
                                // 移除旧的事件监听器以防止重复绑定
                                cell.removeEventListener('click', handleSalespersonCellClick);
                                // 添加新的事件监听器
                                cell.addEventListener('click', handleSalespersonCellClick);
                            }
                        }
                    });
                });
            }

            // 业务员详情单元格点击事件处理函数
            const handleSalespersonCellClick = function() {
                const salespersonName = this.dataset.salesperson;
                const dataType = this.dataset.type;

                const details = mockData.salespersonPolicyDetails[salespersonName][dataType];
                let title = `${salespersonName} - `;
                let columns = [];
                let isExpired = false;

                switch (dataType) {
                    case 'renewable':
                        title += '可续保车辆明细';
                        columns = [
                            { label: '投保人名称', field: 'applicantName' },
                            { label: '车牌号', field: 'licensePlate' },
                            { label: '保险止期', field: 'insuranceEndDate' }
                        ];
                        break;
                    case 'renewed':
                        title += '已续保车辆明细';
                        columns = [
                            { label: '投保人名称', field: 'applicantName' },
                            { label: '车牌号', field: 'licensePlate' },
                            { label: '保险止期', field: 'insuranceEndDate' }
                        ];
                        break;
                    case 'expired':
                        title += '脱保车辆明细';
                        columns = [
                            { label: '投保人名称', field: 'applicantName' },
                            { label: '车牌号', field: 'licensePlate' },
                            { label: '脱保天数', field: 'daysExpired' },
                            { label: '标注选项', field: 'markOption' }
                        ];
                        isExpired = true;
                        break;
                }
                showDetailModal(title, columns, details, isExpired);
            };


            headers.forEach(header => {
                let sortIcon = header.querySelector('.sort-icon');
                if (!sortIcon) { // 确保排序图标存在
                    sortIcon = document.createElement('span');
                    sortIcon.className = 'sort-icon fas';
                    header.appendChild(sortIcon);
                }

                header.addEventListener('click', function() {
                    const columnIndex = parseInt(this.dataset.sortColumn);
                    let sortOrder = this.dataset.sortOrder === 'asc' ? 'desc' : 'asc';

                    // 重置其他表头的排序状态
                    headers.forEach(h => {
                        h.classList.remove('asc', 'desc');
                        h.querySelector('.sort-icon').className = 'sort-icon fas'; // 重置图标类
                        h.querySelector('.sort-icon').classList.remove('fa-sort-up', 'fa-sort-down');
                    });

                    // 设置当前表头的排序状态和图标
                    this.classList.add(sortOrder);
                    this.querySelector('.sort-icon').classList.add(sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    this.dataset.sortOrder = sortOrder;

                    // 排序数据
                    currentData.sort((a, b) => {
                        const valA = getSortableValue(a[columnIndex]);
                        const valB = getSortableValue(b[columnIndex]);

                        // 对于字符串列（如“录单员”或“业务员”），使用 localeCompare 进行A-Z排序
                        if (typeof valA === 'string' && typeof valB === 'string') {
                            return sortOrder === 'asc' ? valA.localeCompare(valB, 'zh-CN') : valB.localeCompare(valA, 'zh-CN');
                        } else {
                            // 对于数字或其他类型
                            if (valA < valB) {
                                return sortOrder === 'asc' ? -1 : 1;
                            }
                            if (valA > valB) {
                                return sortOrder === 'asc' ? 1 : -1;
                            }
                            return 0;
                        }
                    });

                    renderTableRows(currentData); // 重新渲染排序后的数据

                    // 将排序状态保存到 localStorage
                    localStorage.setItem(localStorageKey, JSON.stringify({ columnIndex, sortOrder }));
                });
            });

            // 页面加载时尝试从 localStorage 恢复排序状态，或渲染默认数据
            const savedSortState = JSON.parse(localStorage.getItem(localStorageKey));
            if (savedSortState) {
                const { columnIndex, sortOrder } = savedSortState;
                const headerToClick = table.querySelector(`.sortable-header[data-sort-column="${columnIndex}"]`);
                if (headerToClick) {
                    // 临时将 data-sort-order 设置为相反，确保 click 事件能正确切换到保存的状态
                    headerToClick.dataset.sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                    headerToClick.click(); // 触发点击以应用排序并更新UI
                } else {
                    renderTableRows(currentData); // 如果保存状态无效，渲染默认数据
                }
            } else {
                renderTableRows(currentData); // 如果没有保存状态，渲染默认数据
            }
        }


        // --- 页面加载完成时执行 ---
        document.addEventListener('DOMContentLoaded', () => {
            setRandomDarkBackgroundColor(); // 设置随机背景色
            updateNumberCards(); // 更新数字看板数据
            renderECharts(); // 渲染ECharts图表
            
            // 初始化录单员详情表格的排序功能
            makeSortable('recorder-detail-table', mockData.recorderDetails);

            // 初始化业务员详情表格的排序功能，只显示可续台次大于0的数据
            const filteredSalespersonDetails = mockData.salespersonDetails.filter(row => parseInt(row[1]) > 0);
            makeSortable('salesperson-detail-table', filteredSalespersonDetails);

            // 数字卡片点击事件 (显示详细图表或数据)
            document.getElementById('quarterly-renewal-card').addEventListener('click', () => {
                showChartModal('近4个季度续保率', 'bar', mockData.quarterlyRenewalData.rates, mockData.quarterlyRenewalData.quarters, '续保率');
            });

            document.getElementById('monthly-renewal-card').addEventListener('click', () => {
                showChartModal('近12个月续保率', 'line', mockData.monthlyRenewalData.rates, mockData.monthlyRenewalData.months, '续保率');
            });

            document.getElementById('weekly-renewal-card').addEventListener('click', () => {
                showChartModal('近24周续保率', 'line', mockData.weeklyRenewalData.rates, mockData.weeklyRenewalData.weeks, '续保率');
            });

            document.getElementById('monthly-calculation-card').addEventListener('click', () => {
                showChartModal('近30天每日算单量', 'bar', mockData.dailyCalculationVolume.data, mockData.dailyCalculationVolume.dates, '算单量');
            });

            document.getElementById('renewal-calculation-card').addEventListener('click', () => {
                showChartModal('近30天每日续保算单量', 'bar', mockData.dailyRenewalCalculationVolume.data, mockData.dailyRenewalCalculationVolume.dates, '续保算单量');
            });

            // 以下这些总任务量、月任务量、月续保量、当日续保量卡片的点击事件
            // 需要您根据实际数据库中是否存在对应的“详单”数据来决定如何获取和展示
            // 例如，如果点击“总任务量”，您希望显示所有任务的列表，则需要查询所有任务数据。
            // 鉴于您没有提供这些详单的数据库结构和字段映射，此处的点击事件已被注释，
            // 因为它们在 PHP 中目前没有直接对应的数据库
        });
    </script>
</body>
</html>