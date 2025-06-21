<?php
// PHP 代码部分：模拟数据生成
// 在实际应用中，这些数据将从数据库查询、外部API获取等

// 获取当前日期，用于生成动态数据
$currentDate = new DateTime();

// 获取 ISO 周数 (PHP 8.0+ 支持 DateTime::format('W'))
// 对于旧版本PHP，可能需要自定义函数或第三方库
function getIsoWeek(DateTime $date) {
    $date->setTime(0, 0, 0);
    $date->modify('+3 days'); // Thursday in current week decides the year.
    $week1 = new DateTime($date->format('Y') . '-01-04');
    return 1 + floor(($date->getTimestamp() - $week1->getTimestamp()) / 604800);
}


// --- 生成近24周数据函数 (当前周至前24周) ---
function generateRecentWeeklyData(DateTime $currentDate) {
    $weeks = [];
    $rates = [];
    $tempDate = clone $currentDate; // Copy current date

    for ($i = 0; $i < 24; $i++) {
        $year = $tempDate->format('Y');
        $weekNum = getIsoWeek($tempDate);
        array_unshift($weeks, "{$year}-W{$weekNum}"); // Add to the beginning to keep chronological order

        // Simulate random but somewhat trending rates
        $randomRate = rand(65, 95); // Random between 65% and 95%
        array_unshift($rates, $randomRate); // Add to the beginning

        // Move to the previous week
        $tempDate->modify('-7 days');
    }
    return ['weeks' => $weeks, 'rates' => $rates];
}

// --- 生成近12个月数据函数 ---
function generateRecentMonthlyData(DateTime $currentDate) {
    $months = [];
    $rates = [];
    $d = new DateTime($currentDate->format('Y-m-01')); // Start from beginning of current month

    for ($i = 0; $i < 12; $i++) {
        array_unshift($months, $d->format('Y-m'));
        // Simulate rates, keeping them distinct for each month
        array_unshift($rates, rand(70, 90)); // Random rates between 70% and 90%
        $d->modify('-1 month'); // Move to previous month
    }
    return ['months' => $months, 'rates' => $rates];
}

// --- 生成当前日期与上年同期数据对比 (当前日期至前30天) ---
function generateYearOnYearDailyComparisonData(DateTime $currentDate) {
    $dates = [];
    $currentYearData = [];
    $lastYearData = [];

    $tempDate = clone $currentDate; // Copy current date

    // Go back 30 days
    for ($i = 0; $i < 30; $i++) {
        array_unshift($dates, $tempDate->format('Y-m-d')); // Add to the beginning for chronological order

        // Simulate data for current year
        array_unshift($currentYearData, rand(500, 700));

        // Simulate data for last year (random between 400-600)
        array_unshift($lastYearData, rand(400, 600));

        // Move to the previous day
        $tempDate->modify('-1 day');
    }

    return ['dates' => $dates, 'currentYear' => $currentYearData, 'lastYear' => $lastYearData];
}

// --- 生成近30天每日算单量数据函数 ---
function generateDailyCalculationVolumeData(DateTime $currentDate) {
    $dates = [];
    $data = [];
    $tempDate = clone $currentDate;
    $tempDate->modify('-1 day'); // 从当前日期的前一天开始倒推

    for ($i = 0; $i < 30; $i++) {
        array_unshift($dates, $tempDate->format('Y-m-d')); // 插入到数组开头以保持时间顺序
        // 模拟每日算单量数据，例如50到150之间
        array_unshift($data, rand(50, 150));
        $tempDate->modify('-1 day'); // 移动到前一天
    }
    return ['dates' => $dates, 'data' => $data];
}

// --- 生成近30天每日续保算单量数据函数 ---
function generateDailyRenewalCalculationVolumeData(DateTime $currentDate) {
    $dates = [];
    $data = [];
    $tempDate = clone $currentDate;
    $tempDate->modify('-1 day'); // 从当前日期的前一天开始倒推

    for ($i = 0; $i < 30; $i++) {
        array_unshift($dates, $tempDate->format('Y-m-d')); // 插入到数组开头以保持时间顺序
        // 模拟每日续保算单量数据，例如30到100之间
        array_unshift($data, rand(30, 100));
        $tempDate->modify('-1 day'); // 移动到前一天
    }
    return ['dates' => $dates, 'data' => $data];
}

// --- 生成交强险与商业险近30天数据函数 ---
function generateDailyInsuranceTrendData(DateTime $currentDate) {
    $dates = [];
    $jiaopaiData = [];
    $shangyeData = [];

    $tempDate = clone $currentDate; // Copy current date

    // Go back 30 days
    for ($i = 0; $i < 30; $i++) {
        array_unshift($dates, $tempDate->format('Y-m-d')); // Add to the beginning for chronological order

        // Simulate data for jiaopai
        array_unshift($jiaopaiData, rand(80, 200));

        // Simulate data for shangye
        array_unshift($shangyeData, rand(150, 300));

        // Move to the previous day
        $tempDate->modify('-1 day');
    }
    return ['dates' => $dates, 'jiaopai' => $jiaopaiData, 'shangye' => $shangyeData];
}

// PHP 模拟数据
$mockData = [
    // 数字看板数据
    'totalTaskVolume' => 4,
    'monthlyTaskVolume' => 6,
    'monthlyRenewalVolume' => 3,
    'dailyRenewalVolume' => 50,
    'dayOnDayRatio' => '+2.5%',
    'quarterlyRenewalRate' => '85%',
    'monthlyRenewalRate' => '50.00%',
    'weeklyRenewalRate' => '90%',

    // 新增的算单量数据
    'monthlyCalculationVolume' => 1850, // 示例值：本月算单量
    'renewalCalculationVolume' => 920,  // 示例值：续保算单量

    // 录单员当月续保量饼图数据
    'recorderRenewal' => [
        ['value' => 300, 'name' => '张三'],
        ['value' => 250, 'name' => '李四'],
        ['value' => 200, 'name' => '王五'],
        ['value' => 150, 'name' => '赵六'],
        ['value' => 100, 'name' => '钱七']
    ],

    // 为录单员详单弹窗添加新的模拟数据
    'recorderRenewalDetails' => [
        '张三' => [
            ['salespersonName' => '业务员A', 'licensePlate' => '京K12345', 'applicantName' => '张客户1', 'netPremium' => '1200.00', 'profit' => '120.00'],
            ['salespersonName' => '业务员B', 'licensePlate' => '沪H67890', 'applicantName' => '张客户2', 'netPremium' => '800.00', 'profit' => '80.00'],
            ['salespersonName' => '业务员A', 'licensePlate' => '粤C98765', 'applicantName' => '张客户3', 'netPremium' => '1500.00', 'profit' => '150.00']
        ],
        '李四' => [
            ['salespersonName' => '业务员C', 'licensePlate' => '浙J11223', 'applicantName' => '李客户1', 'netPremium' => '900.00', 'profit' => '90.00'],
            ['salespersonName' => '业务员D', 'licensePlate' => '苏G44556', 'applicantName' => '李客户2', 'netPremium' => '1100.00', 'profit' => '110.00']
        ],
        '王五' => [
            ['salespersonName' => '业务员E', 'licensePlate' => '鲁L78901', 'applicantName' => '王客户1', 'netPremium' => '1300.00', 'profit' => '130.00'],
            ['salespersonName' => '业务员F', 'licensePlate' => '闽F23456', 'applicantName' => '王客户2', 'netPremium' => '750.00', 'profit' => '75.00']
        ],
        '赵六' => [
            ['salespersonName' => '业务员G', 'licensePlate' => '冀A54321', 'applicantName' => '赵客户1', 'netPremium' => '1000.00', 'profit' => '100.00']
        ],
        '钱七' => [
            ['salespersonName' => '业务员H', 'licensePlate' => '晋B87654', 'applicantName' => '钱客户1', 'netPremium' => '600.00', 'profit' => '60.00']
        ]
    ],

    // 续保到期数量矩形图数据
    'renewalDue' => [
        ['name' => '7日内', 'value' => 80],
        ['name' => '15日内', 'value' => 120],
        ['name' => '23日内', 'value' => 90],
        ['name' => '30日内', 'value' => 150],
        ['value' => 100, 'name' => '45日内'],
        ['value' => 70, 'name' => '60日内']
    ],

    // 续保周期天数环形图数据
    'renewalCycle' => [
        ['value' => 200, 'name' => '7日内续保'],
        ['value' => 300, 'name' => '15日内续保'],
        ['value' => 250, 'name' => '23日内续保'],
        ['value' => 400, 'name' => '30日内续保'],
        ['value' => 150, 'name' => '45日内续保'],
        ['value' => 100, 'name' => '60日内续保']
    ],

    // 录单员详情表格数据 (新增 '当日续保量' 和 '同期续保量' 列)
    'recorderDetails' => [
        ['张三', '100,000', '120,000', '120%', '100', '120', '15', '12', '80%', 'A+'],
        ['李四', '80,000', '95,000', '118.75%', '80', '95', '10', '8', '75%', 'A'],
        ['王五', '150,000', '140,000', '93.33%', '130', '110', '18', '15', '78%', 'B+'],
        ['赵六', '70,000', '80,000', '114.28%', '70', '80', '8', '7', '70%', 'B'],
        ['钱七', '90,000', '100,000', '111.11%', '90', '100', '12', '10', '82%', 'A']
    ],

    // 业务员详情表格数据
    // 移除了“未续台次”列
    'salespersonDetails' => [
        ['赵六', 200, 150, 10], // 原 '未续台次' 50 已删除
        ['钱七', 180, 120, 8],  // 原 '未续台次' 60 已删除
        ['孙八', 250, 200, 12], // 原 '未续台次' 50 已删除
        ['周九', 190, 130, 7],  // 原 '未续台次' 60 已删除
        ['吴十', 220, 180, 9]   // 原 '未续台次' 40 已删除
    ],

    // 业务员详情弹窗明细数据 (模拟数据)
    'salespersonPolicyDetails' => [
        '赵六' => [
            'renewable' => [
                ['applicantName' => '王女士', 'licensePlate' => '苏B12345', 'insuranceEndDate' => '2025-07-10'],
                ['applicantName' => '李先生', 'licensePlate' => '京A67890', 'insuranceEndDate' => '2025-07-15'],
                ['applicantName' => '张小姐一个很长很长的名字', 'licensePlate' => '沪C11223', 'insuranceEndDate' => '2025-07-20'], // 增加一个长名字测试
                ['applicantName' => '陈', 'licensePlate' => '粤D45678', 'insuranceEndDate' => '2025-07-25'] // 短名字测试
            ],
            'renewed' => [
                ['applicantName' => '刘先生', 'licensePlate' => '浙D44556', 'insuranceEndDate' => '2025-06-01'],
                ['applicantName' => '陈女士', 'licensePlate' => '粤E77889', 'insuranceEndDate' => '2025-06-05']
            ],
            // 'unrenewed' 键已移除
            'expired' => [
                ['applicantName' => '林先生', 'licensePlate' => '冀H55667', 'daysExpired' => 5],
                ['applicantName' => '郑女士一个很长很长的名字', 'licensePlate' => '辽J88990', 'daysExpired' => 10], // 增加一个长名字测试
                ['applicantName' => '赵', 'licensePlate' => '吉K12345', 'daysExpired' => 20] // 短名字测试
            ]
        ],
        '钱七' => [
            'renewable' => [
                ['applicantName' => '孙女士', 'licensePlate' => '吉K00011', 'insuranceEndDate' => '2025-07-08'],
                ['applicantName' => '周先生', 'licensePlate' => '黑L22334', 'insuranceEndDate' => '2025-07-18']
            ],
            'renewed' => [
                ['applicantName' => '吴小姐', 'licensePlate' => '皖M55667', 'insuranceEndDate' => '2025-06-02']
            ],
            // 'unrenewed' 键已移除
            'expired' => [
                ['applicantName' => '蒋先生', 'licensePlate' => '湘Q44556', 'daysExpired' => 7]
            ]
        ],
        '孙八' => [
            'renewable' => [
                ['applicantName' => '沈先生', 'licensePlate' => '鄂R77889', 'insuranceEndDate' => '2025-07-09'],
                ['applicantName' => '韩女士', 'licensePlate' => '晋S00112', 'insuranceEndDate' => '2025-07-16']
            ],
            'renewed' => [
                ['applicantName' => '杨小姐', 'licensePlate' => '陕T33445', 'insuranceEndDate' => '2025-06-03'],
                ['applicantName' => '朱先生', 'licensePlate' => '蒙U55667', 'insuranceEndDate' => '2025-06-07']
            ],
            // 'unrenewed' 键已移除
            'expired' => [
                ['applicantName' => '许先生', 'licensePlate' => '青W11223', 'daysExpired' => 6]
            ]
        ],
        '周九' => [
            'renewable' => [
                ['applicantName' => '何女士', 'licensePlate' => '新X44556', 'insuranceEndDate' => '2025-07-11']
            ],
            'renewed' => [
                ['applicantName' => '吕先生', 'licensePlate' => '藏Y77889', 'insuranceEndDate' => '2025-06-04']
            ],
            // 'unrenewed' 键已移除
            'expired' => [
                ['applicantName' => '孔先生', 'licensePlate' => '琼A33445', 'daysExpired' => 9]
            ]
        ],
        '吴十' => [
            'renewable' => [
                ['applicantName' => '曹先生', 'licensePlate' => '沪F98765', 'insuranceEndDate' => '2025-07-12'],
                ['applicantName' => '魏女士', 'licensePlate' => '京B54321', 'insuranceEndDate' => '2025-07-19']
            ],
            'renewed' => [
                ['applicantName' => '马先生', 'licensePlate' => '冀C12398', 'insuranceEndDate' => '2025-06-06']
            ],
            // 'unrenewed' 键已移除
            'expired' => [
                ['applicantName' => '金先生', 'licensePlate' => '黑E78901', 'daysExpired' => 8]
            ]
        ]
    ]
];


// 动态生成弹窗图表数据
$mockData['insuranceTrend'] = generateDailyInsuranceTrendData($currentDate);
$mockData['quarterlyRenewalData'] = [
    'quarters' => ['2024Q3', '2024Q4', '2025Q1', '2025Q2'],
    'rates' => [75, 80, 82, 85]
];
$mockData['monthlyRenewalData'] = generateRecentMonthlyData($currentDate);
$mockData['weeklyRenewalData'] = generateRecentWeeklyData($currentDate);
$mockData['yearOnYearComparison'] = generateYearOnYearDailyComparisonData($currentDate);
$mockData['dailyCalculationVolume'] = generateDailyCalculationVolumeData($currentDate);
$mockData['dailyRenewalCalculationVolume'] = generateDailyRenewalCalculationVolumeData($currentDate);

// 将 PHP 数组转换为 JSON 字符串，供 JavaScript 使用
$mockDataJson = json_encode($mockData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>数据看板</title>
    <script src="https://cdn.jsdelivr.net/npm/echarts@5.5.0/dist/echarts.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            /* background-color 将由 JavaScript 随机设置 */
            color: #ecf0f1;
            background-size: cover;
            background-attachment: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 1s ease-in-out;
        }

        /* 通用的磨砂玻璃效果和鼠标悬停特效 */
        .glass-card, .glass-chart-container, .glass-table-container {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        .glass-card:hover, .glass-chart-container:hover, .glass-table-container:hover {
            background-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.4);
        }

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

        /* 单个数字卡片的样式，应用磨砂玻璃效果 */
        .card {
            padding: 25px 20px;
            height: auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }

        .card .value {
            font-size: 38px;
            font-weight: 700;
            color: #8be9fd;
            text-shadow: 0 0 10px rgba(139, 233, 253, 0.5);
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

        /* Chart Row Layouts */
        .chart-row {
            display: grid;
            gap: 25px;
            margin-top: 25px;
            grid-template-columns: repeat(3, 1fr);
        }

        .chart-row.top-charts {
            grid-template-columns: 2fr 1fr;
        }

        .chart-row.middle-charts {
            grid-template-columns: 2fr 1fr;
        }

        .chart-row.bottom-chart {
            grid-template-columns: 2fr 1fr;
        }

        .glass-chart-container.main-chart {
            min-height: 400px;
        }

        /* Chart Container Styles */
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
        .chart-container > div[id$="-chart"] {
            width: 100%;
            height: 100%;
            flex-grow: 1;
        }

        /* Table Container Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 25px;
            padding: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            color: #f8f8f2;
            font-size: 15px;
        }
        th, td {
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 15px;
            text-align: left;
            white-space: nowrap;
        }
        th {
            background-color: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: #a8dadc;
        }
        tbody tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: scale(1.01);
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        /* Chart Title Icon */
        .chart-title-icon {
            margin-right: 10px;
            color: #a8dadc;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: rgba(44, 62, 80, 0.9);
            margin: auto;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            width: 80%;
            max-width: 900px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.3s ease-out;
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
            height: 400px; /* 适用于图表，表格可能会根据内容自动调整高度 */
            overflow-y: auto; /* 确保表格内容可滚动 */
        }

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
            transform: rotate(90deg);
        }

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

        /* Ensure tables in modals look good */
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
        #detail-modal-table tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }

        /* Checkbox styling */
        .checkbox-container {
            display: flex;
            align-items: center;
            justify-content: center; /* Center the checkbox */
            height: 100%; /* Ensure it takes full cell height */
        }
        .checkbox-container input[type="checkbox"] {
            transform: scale(1.2); /* Slightly larger checkbox */
            cursor: pointer;
            accent-color: #55efc4; /* Highlight color for checkbox */
        }

        /* Copy Button Style */
        .copy-button {
            background-color: #007bff; /* Primary blue color */
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-left: 20px; /* Space from title or other elements */
            display: flex;
            align-items: center;
            gap: 5px; /* Space between icon and text */
        }

        .copy-button:hover {
            background-color: #0056b3;
        }

        /* Styles for the modal title container to allow button beside title */
        .modal-title-container {
            display: flex;
            align-items: center;
            justify-content: center; /* Center title and button */
            width: 100%;
            margin-bottom: 25px;
        }
        .modal-title-container h3 {
            margin: 0; /* Remove default margin to align with button */
        }

        /* Sortable Header Styles */
        .sortable-header {
            cursor: pointer;
            position: relative;
            padding-right: 20px; /* Space for sort icon */
        }

        .sortable-header .sort-icon {
            position: absolute;
            right: 0px; /* Adjust as needed */
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8em;
            color: rgba(255, 255, 255, 0.5); /* Faded by default */
        }

        .sortable-header.asc .sort-icon {
            color: #8be9fd; /* Brighter when active */
        }

        .sortable-header.desc .sort-icon {
            color: #8be9fd; /* Brighter when active */
        }


        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .chart-row.top-charts,
            .chart-row.middle-charts {
                grid-template-columns: 1fr;
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
            document.getElementById('day-on-day-ratio').textContent = mockData.dayOnDayRatio;

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
                color: ['#8be9fd', '#bd93f9'],
                tooltip: { trigger: 'axis' },
                legend: {
                    data: ['交强险', '商业险'],
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
                    { name: '交强险', type: 'line', data: mockData.insuranceTrend.jiaopai, smooth: true, areaStyle: { color: chartAreaColor } },
                    { name: '商业险', type: 'line', data: mockData.insuranceTrend.shangye, smooth: true, areaStyle: { color: 'rgba(189, 147, 249, 0.1)' } }
                ]
            };
            insuranceTrendChart.setOption(insuranceTrendOption);

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
        function fillTable(tableId, data) {
            const tableBody = document.querySelector(`#${tableId} tbody`);
            tableBody.innerHTML = '';
            data.forEach(rowData => {
                const row = tableBody.insertRow();
                rowData.forEach(cellData => {
                    const cell = row.insertCell();
                    cell.textContent = cellData;
                });
            });
        }

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
            let copiedText = '';
            const rows = tableElement.querySelectorAll('thead tr, tbody tr');
            const columnWidths = {}; // To store max width for each column

            // First pass: Calculate max width for each column and collect data
            const allRowData = [];
            rows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                const rowValues = [];
                cells.forEach((cell, colIndex) => {
                    let textContent = cell.textContent.trim();
                    const columnField = columnsConfig[colIndex] ? columnsConfig[colIndex].field : null;
                    const columnLabel = columnsConfig[colIndex] ? columnsConfig[colIndex].label : null;


                    // Apply truncation for '投保人名称' if it's the specific column
                    if ((columnField === 'applicantName' || columnLabel === '投保人名称') && textContent.length > 5) {
                        textContent = textContent.substring(0, 5) + '...';
                    }
                    // For checkbox column in expired table, just use a placeholder
                    if (cell.querySelector('input[type="checkbox"]')) {
                        textContent = ''; // Don't copy checkbox text content
                    }

                    rowValues.push(textContent);
                    columnWidths[colIndex] = Math.max(columnWidths[colIndex] || 0, textContent.length);
                });
                allRowData.push(rowValues);
            });

            // Second pass: Format text with padding
            allRowData.forEach(rowValues => {
                rowValues.forEach((text, colIndex) => {
                    copiedText += text.padEnd(columnWidths[colIndex] + 2); // Add 2 for spacing
                });
                copiedText += '\n'; // New line for each row
            });

            try {
                await navigator.clipboard.writeText(copiedText);
                alert('表格数据已复制到剪贴板！');
            } catch (err) {
                console.error('复制失败:', err);
                alert('复制失败，请手动复制。');
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
                                // Simulate removal from calculations
                                tr.style.opacity = '0.5'; // Visually fade out
                                tr.style.textDecoration = 'line-through'; // Cross out text
                                // In a real app, send data to backend here to mark as "不再计数"
                                console.log(`车辆 ${this.dataset.licensePlate} 已被标记为“不再计数”。`);
                            } else {
                                tr.style.opacity = '1';
                                tr.style.textDecoration = 'none';
                                // In a real app, send data to backend here to unmark
                                console.log(`车辆 ${this.dataset.licensePlate} 已取消标记“不再计数”。`);
                            }
                            // Update "全选" checkbox state based on individual checkboxes
                            const allCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]');
                            const checkedCheckboxes = tableBody.querySelectorAll('input[type="checkbox"][data-license-plate]:checked');
                            const selectAllCheckbox = document.getElementById('selectAllExpired');
                            if (selectAllCheckbox) {
                                selectAllCheckbox.checked = allCheckboxes.length === checkedCheckboxes.length;
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
        function makeSortable(tableId, initialData) { // Removed columnMapping as it's not directly used for primary table data
            const table = document.getElementById(tableId);
            if (!table) return;

            const headers = table.querySelectorAll('.sortable-header');
            const tableBody = table.querySelector('tbody');
            let currentData = [...initialData]; // Work with a copy of initial data
            const localStorageKey = `sortState_${tableId}`;

            // Helper to get actual value for sorting (e.g., convert "100,000" to 100000)
            function getSortableValue(value) {
                if (typeof value === 'string') {
                    // Check if it's a number string (can contain commas or percentage)
                    const cleanedValue = value.replace(/,/g, '').replace(/%/g, '');
                    if (!isNaN(cleanedValue) && cleanedValue.trim() !== '') {
                        return parseFloat(cleanedValue);
                    }
                }
                return value; // For pure strings (like names) or actual numbers, return as is.
            }

            // Function to render table rows based on currentData
            function renderTableRows(dataToRender) {
                tableBody.innerHTML = '';
                dataToRender.forEach((rowDataArray, rowIndex) => { // rowDataArray is like ['张三', '100,000', ...]
                    const row = tableBody.insertRow();
                    rowDataArray.forEach((cellData, colIndex) => {
                        const cell = row.insertCell();
                        cell.textContent = cellData;

                        // Re-attach clickable class and event listeners for salesperson table cells
                        // This logic was originally in the PHP loop, now handled dynamically here
                        if (tableId === 'salesperson-detail-table' && colIndex > 0) {
                             cell.classList.add('clickable-policy-count');
                             const salespersonName = rowDataArray[0]; // Assuming salesperson name is always the first column
                             // Map column index to data type for salesperson details
                             const dataTypeMap = {1: 'renewable', 2: 'renewed', 3: 'expired'};
                             const dataType = dataTypeMap[colIndex];
                             if (dataType) {
                                cell.dataset.salesperson = salespersonName;
                                cell.dataset.type = dataType;
                                // Important: remove previous listener to prevent duplicates
                                cell.removeEventListener('click', handleSalespersonCellClick);
                                cell.addEventListener('click', handleSalespersonCellClick);
                             }
                        }
                    });
                });
            }

            // Event handler for salesperson detail cells (re-used for dynamic cells)
            // Moved this function outside renderTableRows to ensure it's defined once
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
                if (!sortIcon) { // Ensure icon exists, add if not (e.g., if PHP loop didn't add it)
                    sortIcon = document.createElement('span');
                    sortIcon.className = 'sort-icon fas';
                    header.appendChild(sortIcon);
                }

                header.addEventListener('click', function() {
                    const columnIndex = parseInt(this.dataset.sortColumn);
                    let sortOrder = this.dataset.sortOrder === 'asc' ? 'desc' : 'asc';

                    // Reset other headers
                    headers.forEach(h => {
                        h.classList.remove('asc', 'desc');
                        h.querySelector('.sort-icon').className = 'sort-icon fas';
                        h.querySelector('.sort-icon').classList.remove('fa-sort-up', 'fa-sort-down');
                    });

                    // Set current header
                    this.classList.add(sortOrder);
                    this.querySelector('.sort-icon').classList.add(sortOrder === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    this.dataset.sortOrder = sortOrder;

                    // Sort data
                    currentData.sort((a, b) => {
                        const valA = getSortableValue(a[columnIndex]);
                        const valB = getSortableValue(b[columnIndex]);

                        // For string columns (like '录单员' or '业务员'), use localeCompare for a-z sorting
                        if (typeof valA === 'string' && typeof valB === 'string') {
                            return sortOrder === 'asc' ? valA.localeCompare(valB, 'zh-CN') : valB.localeCompare(valA, 'zh-CN');
                        } else {
                            // For numeric or other types
                            if (valA < valB) {
                                return sortOrder === 'asc' ? -1 : 1;
                            }
                            if (valA > valB) {
                                return sortOrder === 'asc' ? 1 : -1;
                            }
                            return 0;
                        }
                    });

                    renderTableRows(currentData); // Re-render sorted data

                    // Save sort state to localStorage
                    localStorage.setItem(localStorageKey, JSON.stringify({ columnIndex, sortOrder }));
                });
            });

            // Initial render or restore from localStorage
            const savedSortState = JSON.parse(localStorage.getItem(localStorageKey));
            if (savedSortState) {
                const { columnIndex, sortOrder } = savedSortState;
                const headerToClick = table.querySelector(`.sortable-header[data-sort-column="${columnIndex}"]`);
                if (headerToClick) {
                    // Temporarily set the data-sort-order to the opposite to ensure the click toggles it correctly
                    headerToClick.dataset.sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
                    headerToClick.click(); // Trigger click to apply sort and update UI
                } else {
                    renderTableRows(currentData); // If saved state invalid, render default
                }
            } else {
                renderTableRows(currentData); // Render default if no saved state
            }
        }


        // --- 页面加载完成时执行 ---
        document.addEventListener('DOMContentLoaded', () => {
            setRandomDarkBackgroundColor(); // 确保随机背景色在 DOMContentLoaded 时被调用
            updateNumberCards();
            renderECharts();
            
            // 初始化录单员详情表格的排序功能
            // 使用 mockData.recorderDetails 的副本，因为 makeSortable 会修改它
            makeSortable('recorder-detail-table', mockData.recorderDetails);

            // 初始化业务员详情表格的排序功能
            // 使用 mockData.salespersonDetails 的副本
            makeSortable('salesperson-detail-table', mockData.salespersonDetails);


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

            document.getElementById('total-task-volume').closest('.card').addEventListener('click', () => {
                showDetailModal('总任务量详单', [
                    { label: '车牌号', field: 'licensePlate' },
                    { label: '投保人名称', field: 'applicantName' },
                    { label: '电话', field: 'phone' },
                    { label: '保险止期', field: 'insuranceEndDate' }
                ], mockData.totalTaskDetails);
            });

            document.getElementById('monthly-task-volume').closest('.card').addEventListener('click', () => {
                showDetailModal('月任务量详单', [
                    { label: '车牌号', field: 'licensePlate' },
                    { label: '投保人名称', field: 'applicantName' },
                    { label: '电话', field: 'phone' },
                    { label: '保险止期', field: 'insuranceEndDate' }
                ], mockData.monthlyTaskDetails);
            });

            document.getElementById('monthly-renewal-volume').closest('.card').addEventListener('click', () => {
                showDetailModal('月续保量详单', [
                    { label: '业务员名称', field: 'salespersonName' },
                    { label: '出单台次', field: 'policiesIssued' },
                    { label: '续保台次', field: 'policiesRenewed' },
                    { label: '净保费', field: 'netPremium' },
                    { label: '续保保费', field: 'renewalPremium' },
                    { label: '续保占比', field: 'renewalRatio' }
                ], mockData.monthlyRenewalDetails);
            });

            document.getElementById('daily-renewal-volume').closest('.card').addEventListener('click', () => {
                showDetailModal('当日续保量详单', [
                    { label: '录单员', field: 'recorder' },
                    { label: '业务员名称', field: 'salespersonName' },
                    { label: '净保费', field: 'netPremium' },
                    { label: '续保保费', field: 'renewalPremium' }
                ], mockData.dailyRenewalDetails);
            });
            // 业务员详情表格的可点击数据单元格事件委托，确保排序后依然有效。
            // 这个事件监听器现在直接在 makeSortable 的 renderTableRows 中添加和移除，
            // 以确保每次重新渲染表格行时，事件监听器都能正确绑定到新创建的 td 元素上。
            // 因此，这里不再需要全局的事件委托。
        });

    </script>
</body>
</html>