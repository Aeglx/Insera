<?php
// data_logic.php - 数据生成和模拟逻辑

// 引入数据库配置和连接
require_once 'config.php';

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

// 主数据获取函数，从这里调用上述生成函数或数据库查询
function getDashboardData() {
    global $pdo; // 使用全局 PDO 对象

    $currentDate = new DateTime();

    // 模拟数据 (部分数据可替换为从数据库获取)
    $data = [
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
        ],
        // 其他可能需要的详单数据
        'totalTaskDetails' => [
            ['licensePlate' => '京A12345', 'applicantName' => '李明', 'phone' => '13800138000', 'insuranceEndDate' => '2025-08-01'],
            ['licensePlate' => '沪B54321', 'applicantName' => '王芳', 'phone' => '13911112222', 'insuranceEndDate' => '2025-08-05'],
            ['licensePlate' => '粤C98765', 'applicantName' => '赵强', 'phone' => '13033334444', 'insuranceEndDate' => '2025-08-10'],
            ['licensePlate' => '苏D11223', 'applicantName' => '钱丽', 'phone' => '13155556666', 'insuranceEndDate' => '2025-08-15'],
        ],
        'monthlyTaskDetails' => [
            ['licensePlate' => '鲁E77889', 'applicantName' => '孙飞', 'phone' => '13277778888', 'insuranceEndDate' => '2025-07-01'],
            ['licensePlate' => '浙F00112', 'applicantName' => '周敏', 'phone' => '13399990000', 'insuranceEndDate' => '2025-07-03'],
            ['licensePlate' => '闽G33445', 'applicantName' => '吴刚', 'phone' => '13422223333', 'insuranceEndDate' => '2025-07-07'],
            ['licensePlate' => '湘H55667', 'applicantName' => '郑霞', 'phone' => '13544445555', 'insuranceEndDate' => '2025-07-09'],
            ['licensePlate' => '豫J88990', 'applicantName' => '郭鹏', 'phone' => '13666667777', 'insuranceEndDate' => '2025-07-12'],
            ['licensePlate' => '鄂K12345', 'applicantName' => '何文', 'phone' => '13788889999', 'insuranceEndDate' => '2025-07-14'],
        ],
        'monthlyRenewalDetails' => [
            ['salespersonName' => '赵六', 'policiesIssued' => 100, 'policiesRenewed' => 80, 'netPremium' => '100000.00', 'renewalPremium' => '80000.00', 'renewalRatio' => '80%'],
            ['salespersonName' => '钱七', 'policiesIssued' => 90, 'policiesRenewed' => 70, 'netPremium' => '90000.00', 'renewalPremium' => '70000.00', 'renewalRatio' => '77.78%'],
            ['salespersonName' => '孙八', 'policiesIssued' => 120, 'policiesRenewed' => 95, 'netPremium' => '120000.00', 'renewalPremium' => '95000.00', 'renewalRatio' => '79.17%'],
        ],
        'dailyRenewalDetails' => [
            ['recorder' => '张三', 'salespersonName' => '业务员A', 'netPremium' => '5000.00', 'renewalPremium' => '4500.00'],
            ['recorder' => '李四', 'salespersonName' => '业务员C', 'netPremium' => '3000.00', 'renewalPremium' => '2800.00'],
            ['recorder' => '王五', 'salespersonName' => '业务员E', 'netPremium' => '7000.00', 'renewalPremium' => '6500.00'],
        ]
    ];

    // 动态生成弹窗图表数据
    $data['insuranceTrend'] = generateDailyInsuranceTrendData($currentDate);
    $data['quarterlyRenewalData'] = [
        'quarters' => ['2024Q3', '2024Q4', '2025Q1', '2025Q2'],
        'rates' => [75, 80, 82, 85]
    ];
    $data['monthlyRenewalData'] = generateRecentMonthlyData($currentDate);
    $data['weeklyRenewalData'] = generateRecentWeeklyData($currentDate);
    $data['yearOnYearComparison'] = generateYearOnYearDailyComparisonData($currentDate);
    $data['dailyCalculationVolume'] = generateDailyCalculationVolumeData($currentDate);
    $data['dailyRenewalCalculationVolume'] = generateDailyRenewalCalculationVolumeData($currentDate);

    // 在这里可以添加从数据库查询数据的逻辑，并合并到 $data 数组中
    // 示例：从数据库获取录单员列表 (如果 'recorders' 表存在)
    /*
    try {
        $stmt = $pdo->query("SELECT id, name FROM recorders");
        $recordersFromDb = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // 可以根据需要将数据库数据格式化并添加到 $data 数组中
        // $data['recordersList'] = $recordersFromDb;
    } catch (PDOException $e) {
        error_log("Error fetching recorders: " . $e->getMessage());
        // 处理错误，例如返回空数组或特定错误信息
    }
    */

    return $data;
}
?>