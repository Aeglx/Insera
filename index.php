<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据看板</title>
    <!-- 引入 ECharts 库用于图表绘制 -->
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    <!-- 引入 Font Awesome 图标库 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* 页面基础样式 */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #ecf0f1; /* 字体颜色 */
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 1s ease-in-out; /* 背景色过渡效果 */
        }

        /* 通用的磨砂玻璃效果和鼠标悬停特效 */
        .glass-card, .glass-chart-container, .glass-table-container {
            background-color: rgba(255, 255, 255, 0.15); /* 背景色带透明度 */
            border-radius: 12px; /* 圆角 */
            border: 1px solid rgba(255, 255, 255, 0.2); /* 边框 */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* 阴影 */
            backdrop-filter: blur(15px); /* 磨砂玻璃效果 */
            -webkit-backdrop-filter: blur(15px); /* Safari 兼容 */
            transition: all 0.3s ease-in-out; /* 所有属性的过渡效果 */
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        /* 鼠标悬停时的效果 */
        .glass-card:hover, .glass-chart-container:hover, .glass-table-container:hover {
            background-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-8px); /* 向上移动 */
            border-color: rgba(255, 255, 255, 0.4);
        }

        /* 页面头部样式 */
        .header {
            width: calc(100% - 40px);
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px 30px;
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            text-shadow: 0 0 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease-in-out;
        }
        .header:hover {
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
            transform: translateY(-3px);
        }

        .header h1 {
            margin: 0;
            font-size: 32px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .header h1 i {
            margin-right: 15px;
            color: #a8dadc;
        }
        .datetime-info {
            text-align: right;
            font-size: 17px;
            line-height: 1.5;
        }
        .datetime-info #current-datetime {
            font-weight: bold;
            color: #fff;
        }
        .datetime-info #data-collection-date {
            color: #eee;
        }

        hr {
            border: none;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            width: calc(100% - 40px);
            max-width: 1400px;
            margin: 20px 0 40px 0;
        }

        /* 仪表盘网格布局 */
        .dashboard-grid {
            display: grid;
            gap: 25px;
            width: calc(100% - 40px);
            max-width: 1400px;
        }
        .card-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
        }

        /* 单个数字卡片样式 */
        .card {
            padding: 25px 20px;
            height: auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            cursor: pointer; /* 可点击指示 */
        }

        .card .value {
            font-size: 38px;
            font-weight: 700;
            color: #8be9fd; /* 主色调 */
            text-shadow: 0 0 10px rgba(139, 233, 253, 0.5); /* 文字阴影 */
            margin-bottom: 8px;
        }
        .card .label {
            font-size: 18px;
            color: #f8f8f2;
            opacity: 0.9;
        }
        .card i {
            font-size: 30px;
            color: rgba(255, 255, 255, 0.7);
            margin-bottom: 10px;
        }

        /* 图表行布局 */
        .chart-row {
            display: grid;
            gap: 25px;
            margin-top: 25px;
            grid-template-columns: repeat(3, 1fr); /* 默认三列 */
        }

        .chart-row.top-charts,
        .chart-row.middle-charts {
            grid-template-columns: 2fr 1fr; /* 两列布局，左侧宽，右侧窄 */
        }

        .chart-row.bottom-chart {
            grid-template-columns: 2fr 1fr; /* 两列布局 */
        }

        .glass-chart-container.main-chart {
            min-height: 400px; /* 主图表的最小高度 */
        }

        /* 图表容器样式 */
        .chart-container {
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
        }
        .chart-container h3 {
            margin-top: 5px;
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
            font-size: 22px;
            font-weight: 500;
        }
        .chart-container > div[id$="-chart"] { /* 匹配所有以 "-chart" 结尾的 id */
            width: 100%;
            height: 100%;
            flex-grow: 1; /* 填充可用空间 */
        }

        /* 表格容器样式 */
        .table-container {
            overflow-x: auto; /* 横向滚动 */
            margin-top: 25px;
            padding: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse; /* 合并边框 */
            margin-top: 15px;
            color: #f8f8f2;
            font-size: 15px;
        }
        th, td {
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            text-align: left;
            white-space: nowrap; /* 不换行 */
        }
        th {
            background-color: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: #a8dadc;
        }
        tbody tr:nth-child(even) { /* 隔行换色 */
            background-color: rgba(255, 255, 255, 0.05);
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: scale(1.01); /* 鼠标悬停时略微放大 */
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        /* 图表标题图标 */
        .chart-title-icon {
            margin-right: 10px;
            color: #a8dadc;
        }

        /* 模态框（弹窗）样式 */
        .modal {
            display: none; /* 默认隐藏 */
            position: fixed; /* 固定定位 */
            z-index: 1000; /* 置于顶层 */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* 内容溢出时滚动 */
            background-color: rgba(0, 0, 0, 0.7); /* 半透明黑色背景 */
            justify-content: center; /* 水平居中 */
            align-items: center; /* 垂直居中 */
        }

        .modal-content {
            background-color: rgba(44, 62, 80, 0.9); /* 模态框背景色 */
            margin: auto;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s ease-out; /* 渐入动画 */
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .modal-content h3 {
            color: #8be9fd;
            margin-top: 0;
            margin-bottom: 25px;
            font-size: 24px;
        }

        .modal-body {
            width: 100%;
            height: 400px; /* 图表弹窗高度 */
            overflow-y: auto; /* 确保表格内容可滚动 */
        }

        /* 关闭按钮样式 */
        .close-button {
            color: #aaa;
            position: absolute;
            top: 15px;
            right: 25px;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close-button:hover,
        .close-button:focus {
            color: #fff;
            text-decoration: none;
            transform: rotate(90deg); /* 鼠标悬停时旋转 */
        }

        /* 渐入动画 */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* 新增的垂直卡片容器样式 */
        .vertical-cards-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
            height: 100%;
        }

        .vertical-card {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* 模态框内表格样式 */
        #detail-modal-table {
            width: 100%;
            border-collapse: collapse;
            color: #f8f8f2;
            font-size: 14px;
        }
        #detail-modal-table th,
        #detail-modal-table td {
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 12px;
            text-align: left;
            white-space: nowrap;
        }
        #detail-modal-table th {
            background-color: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: #a8dadc;
        }
        #detail-modal-table tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* 复选框样式 */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center; /* 居中复选框 */
            height: 100%; /* 确保占据单元格的完整高度 */
        }
        .checkbox-container input[type="checkbox"] {
            transform: scale(1.2); /* 略微放大复选框 */
            cursor: pointer;
            accent-color: #55efc4; /* 复选框选中时的颜色 */
        }

        /* 复制按钮样式 */
        .copy-button {
            background-color: #007bff; /* 主蓝色 */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 20px;
            display: flex;
            align-items: center;
            gap: 5px; /* 图标和文字之间的间距 */
        }

        .copy-button:hover {
            background-color: #0056b3;
        }

        /* 模态框标题容器样式，允许按钮与标题并排 */
        .modal-title-container {
            display: flex;
            align-items: center;
            justify-content: center; /* 标题和按钮居中 */
            width: 100%;
            margin-bottom: 25px;
        }
        .modal-title-container h3 {
            margin: 0; /* 移除默认外边距，以与按钮对齐 */
        }

        /* 可排序表头样式 */
        .sortable-header {
            cursor: pointer;
            position: relative;
            padding-right: 20px; /* 为排序图标留出空间 */
        }

        .sortable-header .sort-icon {
            position: absolute;
            right: 0px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.5); /* 默认淡色 */
        }

        /* 排序图标激活状态 */
        .sortable-header.asc .sort-icon,
        .sortable-header.desc .sort-icon {
            color: #8be9fd; /* 激活时颜色更亮 */
        }

        /* 响应式调整 */
        @media (max-width: 992px) {
            .chart-row.top-charts,
            .chart-row.middle-charts {
                grid-template-columns: 1fr; /* 在中等屏幕下变为单列 */
            }
            .glass-chart-container.main-chart {
                min-height: 350px;
            }
            .chart-row {
                grid-template-columns: 1fr;
            }
            .chart-row.bottom-chart {
                grid-template-columns: 1fr;
            }
            .vertical-cards-container {
                height: auto;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            .header {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
                width: calc(100% - 20px);
            }
            .header h1 {
                font-size: 26px;
                margin-bottom: 10px;
            }
            .datetime-info {
                font-size: 14px;
                text-align: left;
            }
            hr {
                width: calc(100% - 20px);
                margin: 15px 0 25px 0;
            }
            .dashboard-grid, .card-container, .chart-row {
                gap: 15px;
                width: calc(100% - 20px);
            }
            .card .value {
                font-size: 30px;
            }
            .card .label {
                font-size: 15px;
            }
            .chart-container {
                min-height: 250px;
            }
            .chart-container h3 {
                font-size: 18px;
            }
            th, td {
                padding: 8px 10px;
                font-size: 13px;
            }
            .modal-content {
                width: 95%;
                padding: 20px;
            }
            .modal-content h3 {
                font-size: 20px;
            }
            .modal-body {
                height: 300px;
            }
            .modal-title-container {
                flex-direction: column; /* Stack title and button on small screens */
                align-items: flex-start;
            }
            .modal-title-container h3 {
                margin-bottom: 10px;
            }
            .copy-button {
                margin-left: 0;
                width: 100%; /* Full width button on small screens */
                justify-content: center;
            }
        }
    </style>
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
                <h3><i class="fas fa-car-crash chart-title-icon"></i>交强险与商业险数量趋势</h3>
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

    <!-- 图表模态框 -->
    <div id="chartModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 id="modal-title"></h3>
            <div id="modal-chart" class="modal-body"></div>
        </div>
    </div>

    <!-- 详情表格模态框 -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close-button detail-close-button">&times;</span>
            <div class="modal-title-container"> 
                <h3 id="detail-modal-title"></h3>
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
        // 添加一个全局的控制台日志，用于调试脚本是否开始执行
        console.log('Script started');

        let dashboardData = {}; // 声明一个全局变量来存储从API获取的数据

        // --- 背景颜色随机化函数 ---
        function setRandomDarkBackgroundColor() {
            const darkColors = [
                '#2C3E50', '#34495E', '#1A2B3C', '#4A4A4A', '#36454F', '#2F4F4F', '#2D3436'
            ];
            const randomIndex = Math.floor(Math.random() * darkColors.length);
            document.body.style.backgroundColor = darkColors[randomIndex];
            console.log('Background color set.'); // 调试信息
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

        setInterval(updateDateTime, 1000); // 每秒更新时间
        updateDateTime(); // 页面加载时立即更新时间

        // --- 更新数字看板数据 ---
        function updateNumberCards() {
            // 在这里添加一个检查，确保 dashboardData 已经加载
            if (!dashboardData || Object.keys(dashboardData).length === 0) {
                console.warn("Dashboard data is not loaded yet. Skipping number card update.");
                // 可以显示加载状态或默认值
                return;
            }

            document.getElementById('total-task-volume').textContent = dashboardData.totalTaskVolume.toLocaleString();
            document.getElementById('monthly-task-volume').textContent = dashboardData.monthlyTaskVolume.toLocaleString();
            document.getElementById('monthly-renewal-volume').textContent = dashboardData.monthlyRenewalVolume.toLocaleString();
            document.getElementById('daily-renewal-volume').textContent = dashboardData.dailyRenewalVolume.toLocaleString();
            document.getElementById('day-on-day-ratio').textContent = dashboardData.dayOnDayRatio;

            document.getElementById('quarterly-renewal-rate').textContent = dashboardData.quarterlyRenewalRate;
            document.getElementById('monthly-renewal-rate').textContent = dashboardData.monthlyRenewalRate;
            document.getElementById('weekly-renewal-rate').textContent = dashboardData.weeklyRenewalRate;

            document.getElementById('monthly-calculation-volume').textContent = dashboardData.monthlyCalculationVolume.toLocaleString();
            document.getElementById('renewal-calculation-volume').textContent = dashboardData.renewalCalculationVolume.toLocaleString();
        }

        // --- 渲染 ECharts 图表 ---
        function renderECharts() {
            // 在这里添加一个检查，确保 dashboardData 已经加载
            if (!dashboardData || Object.keys(dashboardData).length === 0) {
                console.warn("Dashboard data is not loaded yet. Skipping chart rendering.");
                return;
            }

            const chartTextColor = '#ecf0f1'; // 图表文字颜色
            const chartLineColor = 'rgba(255, 255, 255, 0.3)'; // 图表线条颜色
            const chartAreaColor = 'rgba(139, 233, 253, 0.1)'; // 图表区域填充色

            // 交强险与商业险数量趋势图
            const insuranceTrendChart = echarts.init(document.getElementById('insurance-trend-chart'));
            const insuranceTrendOption = {
                color: ['#8be9fd', '#bd93f9'], // 系列颜色
                tooltip: { trigger: 'axis' }, // 提示框
                legend: {
                    data: ['交强险', '商业险'],
                    textStyle: { color: chartTextColor }
                },
                xAxis: {
                    type: 'category',
                    data: dashboardData.insuranceTrend.dates, // X轴数据
                    axisLabel: {
                        color: chartTextColor,
                        rotate: 45, // 标签旋转
                        interval: 0 // 所有标签都显示
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
                    { name: '交强险', type: 'line', data: dashboardData.insuranceTrend.jiaopai, smooth: true, areaStyle: { color: chartAreaColor } },
                    { name: '商业险', type: 'line', data: dashboardData.insuranceTrend.shangye, smooth: true, areaStyle: { color: 'rgba(189, 147, 249, 0.1)' } }
                ]
            };
            insuranceTrendChart.setOption(insuranceTrendOption);

            // 录单员当月续保量饼图
            const recorderRenewalPieChart = echarts.init(document.getElementById('recorder-renewal-pie-chart'));
            const recorderRenewalPieOption = {
                color: ['#ff6b6b', '#feca57', '#48dbfb', '#1dd1a1', '#ff9ff3'],
                tooltip: { trigger: 'item', formatter: '{b}: {c} ({d}%)' },
                legend: {
                    orient: 'vertical',
                    left: 'left',
                    textStyle: { color: chartTextColor }
                },
                series: [
                    {
                        name: '续保量',
                        type: 'pie',
                        radius: ['40%', '70%'], // 修改为环形图
                        center: ['50%', '50%'],
                        avoidLabelOverlap: false,
                        label: {
                            show: false, // 默认不显示标签
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
                        labelLine: { show: false }, // 不显示标签线
                        data: dashboardData.recorderRenewal
                    }
                ]
            };
            recorderRenewalPieChart.setOption(recorderRenewalPieOption);

            // 饼图点击事件，显示录单员详单
            recorderRenewalPieChart.on('click', function (params) {
                if (params.componentType === 'series' && params.seriesType === 'pie') {
                    const recorderName = params.name;
                    const detailData = dashboardData.recorderRenewalDetails[recorderName];

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
                        // 使用模拟弹窗代替 alert
                        showCustomAlert(`没有找到 ${recorderName} 的详细续保数据。`);
                    }
                }
            });


            // 续保到期数量柱状图
            const renewalDueBarChart = echarts.init(document.getElementById('renewal-due-bar-chart'));
            const renewalDueOption = {
                tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
                xAxis: {
                    type: 'category',
                    data: dashboardData.renewalDue.map(item => item.name),
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
                    data: dashboardData.renewalDue.map(item => item.value),
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

            // 当前日期与上年同期数据对比图
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
                    data: dashboardData.yearOnYearComparison.dates,
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
                    { name: '本年', type: 'line', data: dashboardData.yearOnYearComparison.currentYear, smooth: true, areaStyle: { color: 'rgba(255, 234, 167, 0.1)' } },
                    { name: '上年同期', type: 'line', data: dashboardData.yearOnYearComparison.lastYear, smooth: true, lineStyle: { type: 'dashed', color: '#a29bfe' }, areaStyle: { color: 'rgba(162, 155, 254, 0.1)' } }
                ]
            };
            yearOnYearComparisonChart.setOption(yearOnYearOption);

            // 续保周期天数环形图
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
                        data: dashboardData.renewalCycle
                    }
                ]
            };
            renewalCycleDonutChart.setOption(renewalCycleOption);

            // 窗口大小改变时重绘图表
            window.addEventListener('resize', () => {
                insuranceTrendChart.resize();
                recorderRenewalPieChart.resize();
                renewalDueBarChart.resize();
                yearOnYearComparisonChart.resize();
                renewalCycleDonutChart.resize();
                if (modalChartInstance) { // 如果模态框图表实例存在，也需要重绘
                    modalChartInstance.resize();
                }
            });
        }

        // --- ECharts图表弹窗相关逻辑 ---
        const chartModal = document.getElementById('chartModal');
        const closeButton = document.querySelector('.close-button:not(.detail-close-button)');
        const modalTitle = document.getElementById('modal-title');
        const modalChartDiv = document.getElementById('modal-chart');
        let modalChartInstance = null; // 用于存储模态框中的 ECharts 实例

        closeButton.onclick = function() {
            chartModal.style.display = 'none';
            if (modalChartInstance) {
                modalChartInstance.dispose(); // 销毁图表实例以释放资源
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

        // 点击模态框外部关闭模态框
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

        /**
         * 显示通用图表模态框。
         * @param {string} title 模态框标题。
         * @param {'bar'|'line'} chartType 图表类型 ('bar' 或 'line')。
         * @param {Array<number>} data 图表数据。
         * @param {Array<string>} xData X轴标签数据。
         * @param {string} yAxisLabel Y轴名称。
         */
        function showChartModal(title, chartType, data, xData, yAxisLabel) {
            modalTitle.textContent = title;
            chartModal.style.display = 'flex'; // 显示模态框

            if (modalChartInstance) {
                modalChartInstance.dispose(); // 销毁之前的实例
            }
            modalChartInstance = echarts.init(modalChartDiv); // 初始化新的图表实例

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
            modalChartInstance.resize(); // 确保图表在模态框中正确渲染
        }

        // 函数：复制表格数据到剪贴板
        async function copyTableToClipboard(tableElement, columnsConfig) {
            let copiedText = '';
            const rows = tableElement.querySelectorAll('thead tr, tbody tr');
            const columnWidths = {}; // 用于存储每列的最大宽度

            // 第一遍遍历：计算每列的最大宽度并收集数据
            const allRowData = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowValues = [];
                cells.forEach((cell, colIndex) => {
                    let textContent = cell.textContent.trim();
                    const columnField = columnsConfig[colIndex] ? columnsConfig[colIndex].field : null;
                    const columnLabel = columnsConfig[colIndex] ? columnsConfig[colIndex].label : null;


                    // 如果是 '投保人名称' 列，并且内容过长，则截断
                    if ((columnField === 'applicantName' || columnLabel === '投保人名称') && textContent.length > 5) {
                        textContent = textContent.substring(0, 5) + '...';
                    }
                    // 对于过期表格中的复选框列，只使用占位符
                    if (cell.querySelector('input[type="checkbox"]')) {
                        textContent = ''; // 不复制复选框的文本内容
                    }

                    rowValues.push(textContent);
                    columnWidths[colIndex] = Math.max(columnWidths[colIndex] || 0, textContent.length);
                });
                allRowData.push(rowValues);
            });

            // 第二遍遍历：用空格填充文本以对齐列
            allRowData.forEach(rowValues => {
                rowValues.forEach((text, colIndex) => {
                    copiedText += text.padEnd(columnWidths[colIndex] + 2); // 额外添加2个空格作为间距
                });
                copiedText += '\n'; // 每行结束后换行
            });

            try {
                // 使用 document.execCommand('copy') 因为 navigator.clipboard.writeText() 在 iframe 中可能受限
                const textarea = document.createElement('textarea');
                textarea.value = copiedText;
                textarea.style.position = 'fixed'; // 避免滚动
                textarea.style.left = '-9999px'; // 移出屏幕
                document.body.appendChild(textarea);
                textarea.select();
                const successful = document.execCommand('copy');
                document.body.removeChild(textarea);

                if (successful) {
                    showCustomAlert('表格数据已复制到剪贴板！');
                } else {
                    showCustomAlert('复制失败，请手动复制。');
                }
            } catch (err) {
                console.error('复制失败:', err);
                showCustomAlert('复制失败，请手动复制。');
            }
        }

        // 自定义弹窗函数 (替代 alert)
        function showCustomAlert(message) {
            const alertModal = document.createElement('div');
            alertModal.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: rgba(44, 62, 80, 0.95);
                padding: 30px;
                border-radius: 15px;
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
                color: #fff;
                font-size: 18px;
                text-align: center;
                z-index: 1001;
                border: 1px solid rgba(255, 255, 255, 0.3);
                backdrop-filter: blur(5px);
                -webkit-backdrop-filter: blur(5px);
                display: flex;
                flex-direction: column;
                align-items: center;
            `;
            alertModal.innerHTML = `
                <p>${message}</p>
                <button style="
                    background-color: #007bff;
                    color: white;
                    border: none;
                    border-radius: 8px;
                    padding: 10px 25px;
                    font-size: 16px;
                    cursor: pointer;
                    margin-top: 20px;
                    transition: background-color 0.3s ease;
                " onmouseover="this.style.backgroundColor='#0056b3'" onmouseout="this.style.backgroundColor='#007bff'">确定</button>
            `;
            document.body.appendChild(alertModal);

            alertModal.querySelector('button').onclick = () => {
                document.body.removeChild(alertModal);
            };
        }


        // 函数：显示详细表格弹窗
        function showDetailModal(title, columns, data, isExpiredTable = false) {
            detailModalTitle.textContent = title;
            detailModal.style.display = 'flex';

            const tableHeaderRow = detailModalTable.querySelector('thead tr');
            const tableBody = detailModalTable.querySelector('tbody');

            tableHeaderRow.innerHTML = '';
            tableBody.innerHTML = '';

            // 检查是否需要显示复制按钮
            if (!isExpiredTable) { // 只要不是脱保表格，就显示复制按钮
                copyTableButton.style.display = 'inline-flex';
                copyTableButton.onclick = null; // 清除旧的事件监听器
                copyTableButton.onclick = () => copyTableToClipboard(detailModalTable, columns); // 绑定新的事件监听器
            } else {
                copyTableButton.style.display = 'none'; // 脱保表格隐藏复制按钮
            }


            columns.forEach(col => {
                const th = document.createElement('th');
                if (isExpiredTable && col.field === 'markOption') {
                    // 对于“标注选项”列，添加全选复选框
                    const labelDiv = document.createElement('div');
                    labelDiv.textContent = col.label; // "标注选项"
                    th.appendChild(labelDiv);

                    const selectAllCheckbox = document.createElement('input');
                    selectAllCheckbox.type = 'checkbox';
                    selectAllCheckbox.id = 'selectAllExpired'; // 给全选复选框一个ID
                    selectAllCheckbox.style.marginLeft = '10px';
                    
                    const labelForSelectAll = document.createElement('label');
                    labelForSelectAll.setAttribute('for', 'selectAllExpired');
                    labelForSelectAll.textContent = '全选';
                    labelForSelectAll.style.cursor = 'pointer';
                    labelForSelectAll.style.color = '#a8dadc'; // 匹配表头文字颜色

                    const headerCheckboxContainer = document.createElement('div');
                    headerCheckboxContainer.style.display = 'flex';
                    headerCheckboxContainer.style.alignItems = 'center';
                    headerCheckboxContainer.style.justifyContent = 'center'; // 居中整个表头内容
                    headerCheckboxContainer.appendChild(selectAllCheckbox);
                    headerCheckboxContainer.appendChild(labelForSelectAll);

                    th.appendChild(headerCheckboxContainer);

                    // 为全选复选框添加事件监听器
                    selectAllCheckbox.addEventListener('change', function() {
                        const checkboxes = tableBody.querySelectorAll('input[type="checkbox'][data-license-plate]');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked; // 设置所有复选框与全选框状态一致
                            // 触发每个复选框的change事件，以更新它们的样式
                            const event = new Event('change');
                            checkbox.dispatchEvent(event);
                        });
                    });

                } else {
                    th.textContent = col.label;
                }
                tableHeaderRow.appendChild(th);
            });

            // 填充表格数据
            data.forEach(rowData => {
                const tr = tableBody.insertRow();
                columns.forEach((col, colIndex) => {
                    const td = tr.insertCell();
                    if (isExpiredTable && col.field === 'markOption') {
                        // 对于“标注选项”列，添加单个复选框
                        const checkboxContainer = document.createElement('div');
                        checkboxContainer.className = 'checkbox-container';
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.dataset.licensePlate = rowData.licensePlate; // 存储车牌号以便后续使用
                        checkbox.addEventListener('change', function() {
                            if (this.checked) {
                                // 模拟从计算中移除（视觉效果）
                                tr.style.opacity = '0.5'; // 视觉上淡化
                                tr.style.textDecoration = 'line-through'; // 文字添加删除线
                                // 在真实应用中，这里会发送数据到后端，将此车辆标记为“不再计数”
                                console.log(`车辆 ${this.dataset.licensePlate} 已被标记为“不再计数”。`);
                            } else {
                                tr.style.opacity = '1';
                                tr.style.textDecoration = 'none';
                                // 在真实应用中，这里会发送数据到后端，取消标记
                                console.log(`车辆 ${this.dataset.licensePlate} 已取消标记“不再计数”。`);
                            }
                            // 根据单个复选框的状态更新“全选”复选框
                            const allCheckboxes = tableBody.querySelectorAll('input[type="checkbox'][data-license-plate]');
                            const checkedCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]:checked');
                            const selectAllCheckbox = document.getElementById('selectAllExpired');
                            if (selectAllCheckbox) {
                                selectAllCheckbox.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCheckboxes.length;
                                selectAllCheckbox.indeterminate = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
                            }
                        });
                        checkboxContainer.appendChild(checkbox);
                        td.appendChild(checkboxContainer);
                    } else if (col.field === 'applicantName' || (col.label === '投保人名称' && !isExpiredTable)) { // 对“投保人名称”列进行截断处理
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

            // 表格填充完成后，如果这是过期表格，设置全选复选框的初始不确定状态
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
        function makeSortable(tableId, initialData) {
            const table = document.getElementById(tableId);
            if (!table) return;

            const headers = table.querySelectorAll('.sortable-header');
            const tableBody = table.querySelector('tbody');
            let currentData = [...initialData]; // 处理数据的副本
            const localStorageKey = `sortState_${tableId}`;

            // 辅助函数：获取可排序的值（例如将“100,000”转换为数字）
            function getSortableValue(value) {
                if (typeof value === 'string') {
                    // 检查是否为数字字符串（可能包含逗号或百分号）
                    const cleanedValue = value.replace(/,/g, '').replace(/%/g, '');
                    if (!isNaN(cleanedValue) && cleanedValue.trim() !== '') {
                        return parseFloat(cleanedValue);
                    }
                }
                return value; // 对于纯字符串（如名称）或实际数字，按原样返回
            }

            // 函数：根据当前数据渲染表格行
            function renderTableRows(dataToRender) {
                tableBody.innerHTML = ''; // 清空现有行
                dataToRender.forEach((rowDataArray, rowIndex) => { // rowDataArray 类似于 ['张三', '100,000', ...]
                    const row = tableBody.insertRow(); 
                    rowDataArray.forEach((cellData, colIndex) => {
                        const cell = row.insertCell(); // 插入新单元格
                        cell.textContent = cellData;

                        // 重新绑定业务员详情表格中可点击单元格的事件监听器
                        if (tableId === 'salesperson-detail-table' && colIndex > 0) {
                             cell.classList.add('clickable-policy-count'); // 添加可点击样式
                             const salespersonName = rowDataArray[0]; // 假设业务员名称始终是第一列
                             // 将列索引映射到数据类型（可续保、已续保、脱保）
                             const dataTypeMap = {1: 'renewable', 2: 'renewed', 3: 'expired'};
                             const dataType = dataTypeMap[colIndex];
                             if (dataType) {
                                cell.dataset.salesperson = salespersonName; // 存储业务员姓名
                                cell.dataset.type = dataType; // 存储数据类型
                                // 重要：移除之前的监听器以防止重复绑定
                                cell.removeEventListener('click', handleSalespersonCellClick);
                                cell.addEventListener('click', handleSalespersonCellClick); // 绑定新的监听器
                             }
                        }
                    });
                });
            }

            // 业务员详情单元格点击事件处理函数（在 renderTableRows 外部定义，确保只定义一次）
            const handleSalespersonCellClick = function() {
                const salespersonName = this.dataset.salesperson;
                const dataType = this.dataset.type;

                // 从 dashboardData 中获取详细数据
                const details = dashboardData.salespersonPolicyDetails[salespersonName][dataType];
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
                            { label: '标注选项', field: 'markOption' } // 添加标注选项列
                        ];
                        isExpired = true; // 标记为过期表格
                        break;
                    default:
                        title += '未知明细';
                        columns = [];
                }
                showDetailModal(title, columns, details, isExpired);
            };


            // 为每个表头添加点击事件监听器
            headers.forEach(header => {
                let sortIcon = header.querySelector('.sort-icon');
                if (!sortIcon) { // 确保图标存在，如果不存在则添加
                    sortIcon = document.createElement('span');
                    sortIcon.className = 'sort-icon fas';
                    header.appendChild(sortIcon);
                }

                header.addEventListener('click', function() {
                    const columnIndex = parseInt(this.dataset.sortColumn);
                    // 切换排序顺序 (升序 -> 降序 -> 升序)
                    let sortOrder = this.dataset.sortOrder === 'asc' ? 'desc' : 'asc';

                    // 重置其他表头的样式和排序状态
                    headers.forEach(h => {
                        h.classList.remove('asc', 'desc');
                        h.querySelector('.sort-icon').className = 'sort-icon fas';
                        h.querySelector('.sort-icon').classList.remove('fa-sort-up', 'fa-sort-down');
                    });

                    // 设置当前点击表头的样式和排序状态
                    this.classList.add(sortOrder);
                    this.querySelector('.sort-icon').classList.add(sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    this.dataset.sortOrder = sortOrder;

                    // 排序数据
                    currentData.sort((a, b) => {
                        const valA = getSortableValue(a[columnIndex]);
                        const valB = getSortableValue(b[columnIndex]);

                        // 对于字符串列（如“录单员”或“业务员”），使用 localeCompare 进行按字母排序
                        if (typeof valA === 'string' && typeof valB === 'string') {
                            // 修复点：确保 localeCompare 的第二个参数是正确的比较对象 valA
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

            // 初始渲染或从 localStorage 恢复排序状态
            const savedSortState = JSON.parse(localStorage.getItem(localStorageKey));
            if (savedSortState) {
                const { columnIndex, sortOrder } = savedSortState;
                const headerToClick = table.querySelector(`.sortable-header[data-sort-column="${columnIndex}"]`);
                if (headerToClick) {
                    // 临时设置 data-sort-order 为相反的值，以确保点击时能够正确切换
                    headerToClick.dataset.sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                    headerToClick.click(); // 触发点击事件以应用排序并更新 UI
                } else {
                    renderTableRows(currentData); // 如果保存的状态无效，则渲染默认数据
                }
            } else {
                renderTableRows(currentData); // 如果没有保存的状态，则渲染默认数据
            }
        }


        // --- 从 API 获取数据并初始化页面 ---
        async function fetchDataAndInitialize() {
            setRandomDarkBackgroundColor(); // 无论数据加载成功与否，都先设置背景色

            try {
                // 修复点：将相对路径 'api.php' 改为根相对路径 '/api.php'，确保路径解析正确
                const response = await fetch('/api.php'); 
                if (!response.ok) {
                    // 如果 HTTP 状态码不是 2xx，则抛出错误
                    throw new Error(`HTTP 错误! 状态码: ${response.status}`);
                }
                dashboardData = await response.json(); // 将获取到的数据赋值给 dashboardData 变量

                // 数据加载成功后，更新页面元素
                updateNumberCards();
                renderECharts();
                
                // 初始化录单员详情表格的排序功能
                makeSortable('recorder-detail-table', dashboardData.recorderDetails);

                // 初始化业务员详情表格的排序功能
                makeSortable('salesperson-detail-table', dashboardData.salespersonDetails);


                // 为卡片添加事件监听器 (现在使用 dashboardData 变量)
                document.getElementById('quarterly-renewal-card').addEventListener('click', () => {
                    showChartModal('近4个季度续保率', 'bar', dashboardData.quarterlyRenewalData.rates, dashboardData.quarterlyRenewalData.quarters, '续保率');
                });

                document.getElementById('monthly-renewal-card').addEventListener('click', () => {
                    showChartModal('近12个月续保率', 'line', dashboardData.monthlyRenewalData.rates, dashboardData.monthlyRenewalData.months, '续保率');
                });

                document.getElementById('weekly-renewal-card').addEventListener('click', () => {
                    showChartModal('近24周续保率', 'line', dashboardData.weeklyRenewalData.rates, dashboardData.weeklyRenewalData.weeks, '续保率');
                });

                document.getElementById('monthly-calculation-card').addEventListener('click', () => {
                    showChartModal('近30天每日算单量', 'bar', dashboardData.dailyCalculationVolume.data, dashboardData.dailyCalculationVolume.dates, '算单量');
                });

                document.getElementById('renewal-calculation-card').addEventListener('click', () => {
                    showChartModal('近30天每日续保算单量', 'bar', dashboardData.dailyRenewalCalculationVolume.data, dashboardData.dailyRenewalCalculationVolume.dates, '续保算单量');
                });

                document.getElementById('total-task-card').addEventListener('click', () => {
                    showDetailModal('总任务量详单', [
                        { label: '车牌号', field: 'licensePlate' },
                        { label: '投保人名称', field: 'applicantName' },
                        { label: '电话', field: 'phone' },
                        { label: '保险止期', field: 'insuranceEndDate' }
                    ], dashboardData.totalTaskDetails);
                });

                document.getElementById('monthly-task-card').addEventListener('click', () => {
                    showDetailModal('月任务量详单', [
                        { label: '车牌号', field: 'licensePlate' },
                        { label: '投保人名称', field: 'applicantName' },
                        { label: '电话', field: 'phone' },
                        { label: '保险止期', field: 'insuranceEndDate' }
                    ], dashboardData.monthlyTaskDetails);
                });

                document.getElementById('monthly-renewal-card-summary').addEventListener('click', () => {
                    showDetailModal('月续保量详单', [
                        { label: '业务员名称', field: 'salespersonName' },
                        { label: '出单台次', field: 'policiesIssued' },
                        { label: '续保台次', field: 'policiesRenewed' },
                        { label: '净保费', field: 'netPremium' },
                        { label: '续保保费', field: 'renewalPremium' },
                        { label: '续保占比', field: 'renewalRatio' }
                    ], dashboardData.monthlyRenewalDetails);
                });

                document.getElementById('daily-renewal-card-summary').addEventListener('click', () => {
                    showDetailModal('当日续保量详单', [
                        { label: '录单员', field: 'recorder' },
                        { label: '业务员名称', field: 'salespersonName' },
                        { label: '净保费', field: 'netPremium' },
                        { label: '续保保费', field: 'renewalPremium' }
                    ], dashboardData.dailyRenewalDetails);
                });

            } catch (error) {
                console.error('获取数据失败:', error);
                // 即使数据加载失败，也要设置背景色（已提前调用）
                // 并且显示自定义的错误提示
                showCustomAlert('无法加载数据。请检查网络或联系管理员。');
            }
        }

        // --- 页面加载完成时执行 ---
        document.addEventListener('DOMContentLoaded', () => {
            fetchDataAndInitialize();
        });

    </script>
</body>
</html>
