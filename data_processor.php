<?php
// data_processor.php
// 增加 PHP 执行时间限制，例如设置为 300 秒（5分钟）。
// 在生产环境中，更推荐通过服务器配置（php.ini 或 web server config）来管理。
set_time_limit(300);

require_once 'config.php';
require_once 'functions.php';

$pdo = getDbConnection();

$currentDateTime = new DateTime();
$currentDate = $currentDateTime->format('Y-m-d');
$currentMonth = $currentDateTime->format('Y-m');
$currentYear = $currentDateTime->format('Y');

// 获取当前日期前1天和前2天的日期，用于环比计算
$yesterdayDate = (clone $currentDateTime)->modify('-1 day')->format('Y-m-d');
$dayBeforeYesterdayDate = (clone $currentDateTime)->modify('-2 day')->format('Y-m-d');


$xubaoTable = TABLE_XUBAO;
$yuanshujuTable = TABLE_YUANSHUJU;

$mockData = [];

// 1. 数字看板数据

// 总任务量: yuanshuju表中保险止期为当前日期至后60天内可续保数据
// 预先计算日期，避免在SQL中使用DATE_ADD函数
$endDate = (clone $currentDateTime)->modify('+60 days')->format('Y-m-d');
$mockData['totalTaskVolume'] = getRenewableCount($pdo, $yuanshujuTable,
    ["保险止期 BETWEEN :currentDate AND :endDate"], // $whereClauses
    [':currentDate' => $currentDate, ':endDate' => $endDate] // $bindParams
);


// 月任务量: yuanshuju表中保险止期为当前日期当前月份内可续保数据
$firstDayOfCurrentMonth = (clone $currentDateTime)->modify('first day of this month')->format('Y-m-d');
$lastDayOfCurrentMonth = (clone $currentDateTime)->modify('last day of this month')->format('Y-m-d');
$mockData['monthlyTaskVolume'] = getRenewableCount($pdo, $yuanshujuTable,
    ["保险止期 BETWEEN :firstDay AND :lastDay"], // $whereClauses
    [':firstDay' => $firstDayOfCurrentMonth, ':lastDay' => $lastDayOfCurrentMonth] // $bindParams
);

// 月续保量: xubao表中支付日期为当前月份的已续保数据
$mockData['monthlyRenewalVolume'] = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["DATE_FORMAT(t1.支付日期, '%Y-%m') = :currentMonth"], // $whereClauses
    [':currentMonth' => $currentMonth] // $bindParams
);

// 当日续保量: xubao表中支付日期为当前日期前一天的已续保数据
$mockData['dailyRenewalVolume'] = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 = :yesterdayDate"], // $whereClauses
    [':yesterdayDate' => $yesterdayDate] // $bindParams
);

// 与上日环比: (当前日期前一天续保量 - 当前日期前2天续保量) / 当前日期前2天续保量
$yesterdayRenewalCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 = :yesterdayDate"], // $whereClauses
    [':yesterdayDate' => $yesterdayDate] // $bindParams
);
$dayBeforeYesterdayRenewalCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 = :dayBeforeYesterdayDate"], // $whereClauses
    [':dayBeforeYesterdayDate' => $dayBeforeYesterdayDate] // $bindParams
);

if ($dayBeforeYesterdayRenewalCount > 0) {
    $ratio = (($yesterdayRenewalCount - $dayBeforeYesterdayRenewalCount) / $dayBeforeYesterdayRenewalCount) * 100;
    $mockData['dayOnDayRatio'] = sprintf('%+-.1f%%', $ratio);
} else {
    // 如果前天数据为0，且昨天数据不为0，显示“+INF%”
    if ($yesterdayRenewalCount > 0) {
        $mockData['dayOnDayRatio'] = '+INF%';
    } else {
        $mockData['dayOnDayRatio'] = '0%'; // 都为0，则环比为0
    }
}


// 季度续保率 (以当前季度为例，计算当前季度已续保量/当前季度可续保量)
$month = (int)$currentDateTime->format('m');
$quarter = floor(($month - 1) / 3) + 1;
$year = $currentDateTime->format('Y');

$qStart = '';
$qEnd = '';
if ($quarter == 1) {
    $qStart = "{$year}-01-01";
    $qEnd = "{$year}-03-31";
} elseif ($quarter == 2) {
    $qStart = "{$year}-04-01";
    $qEnd = "{$year}-06-30";
} elseif ($quarter == 3) {
    $qStart = "{$year}-07-01";
    $qEnd = "{$year}-09-30";
} else { // Quarter 4
    $qStart = "{$year}-10-01";
    $qEnd = "{$year}-12-31";
}

$renewableCountQuarter = getRenewableCount($pdo, $yuanshujuTable,
    ["保险止期 BETWEEN :qStart AND :qEnd"], // $whereClauses
    [':qStart' => $qStart, ':qEnd' => $qEnd] // $bindParams
);

$renewedCountQuarter = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 BETWEEN :qStart AND :qEnd"], // $whereClauses
    [':qStart' => $qStart, ':qEnd' => $qEnd] // $bindParams
);
$mockData['quarterlyRenewalRate'] = ($renewableCountQuarter > 0) ? round(($renewedCountQuarter / $renewableCountQuarter) * 100, 2) . '%' : '0%';


// 月度续保率: (本月已续保量 / 本月可续保量)
$renewableCountMonth = getRenewableCount($pdo, $yuanshujuTable,
    ["DATE_FORMAT(保险止期, '%Y-%m') = :currentMonth"], // $whereClauses
    [':currentMonth' => $currentMonth] // $bindParams
);
$mockData['monthlyRenewalRate'] = ($renewableCountMonth > 0) ? round(($mockData['monthlyRenewalVolume'] / $renewableCountMonth) * 100, 2) . '%' : '0%';

// 周续保率: (本周已续保量 / 本周可续保量)
$startOfWeek = (clone $currentDateTime)->modify('monday this week')->format('Y-m-d');
$endOfWeek = (clone $currentDateTime)->modify('sunday this week')->format('Y-m-d');

$renewableCountWeek = getRenewableCount($pdo, $yuanshujuTable,
    ["保险止期 BETWEEN :startOfWeek AND :endOfWeek"], // $whereClauses
    [':startOfWeek' => $startOfWeek, ':endOfWeek' => $endOfWeek] // $bindParams
);

$renewedCountWeek = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 BETWEEN :startOfWeek AND :endOfWeek"], // $whereClauses
    [':startOfWeek' => $startOfWeek, ':endOfWeek' => $endOfWeek] // $bindParams
);
$mockData['weeklyRenewalRate'] = ($renewableCountWeek > 0) ? round(($renewedCountWeek / $renewableCountWeek) * 100, 2) . '%' : '0%';


// 本月算单量: 调整为固定为 0，因为算单量模块暂时不处理
$mockData['monthlyCalculationVolume'] = 0;


// 续保算单量: 假设为 xubao 表本月新增的记录，且在续保范围内的
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号))
    FROM {$xubaoTable} t1
    JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    WHERE DATE_FORMAT(t1.支付日期, '%Y-%m') = :currentMonth
    AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
");
$stmt->execute([':currentMonth' => $currentMonth]);
$mockData['renewalCalculationVolume'] = (int)$stmt->fetchColumn();


// 2. 录单员当月续保量饼图数据
$stmt = $pdo->prepare("
    SELECT
        t1.录单员 AS name,
        COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号)) AS value
    FROM
        {$xubaoTable} t1
    JOIN
        {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    WHERE
        DATE_FORMAT(t1.支付日期, '%Y-%m') = :currentMonth
        AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期
    GROUP BY
        t1.录单员
    ORDER BY
        value DESC;
");
$stmt->execute([':currentMonth' => $currentMonth]);
$mockData['recorderRenewal'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. 录单员详单弹窗数据
$mockData['recorderRenewalDetails'] = [];
foreach ($mockData['recorderRenewal'] as $recorder) {
    $recorderName = $recorder['name'];
    $stmt = $pdo->prepare("
        SELECT
            t2.代理人名称 AS salespersonName,
            t1.车牌号 AS licensePlate,
            t2.投保人名称 AS applicantName,
            t1.不含税保费 AS netPremium,
            t1.`预估佣金总利润(元)` AS profit
        FROM
            {$xubaoTable} t1
        JOIN
            {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
        WHERE
            t1.录单员 = :recorderName
            AND DATE_FORMAT(t1.支付日期, '%Y-%m') = :currentMonth
            AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
    ");
    $stmt->execute([':recorderName' => $recorderName, ':currentMonth' => $currentMonth]);
    $mockData['recorderRenewalDetails'][$recorderName] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// 4. 续保到期数量矩形图数据
$mockData['renewalDue'] = [];
$intervals = [7, 15, 23, 30, 45, 60];
// 准备查询语句
$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM {$yuanshujuTable}
    WHERE 保险止期 BETWEEN :startDate AND :endDate;
");

foreach ($intervals as $i => $days) {
    $startDate = '';
    if ($i === 0) { // First interval is from current date
        $startDate = (clone $currentDateTime)->format('Y-m-d');
    } else { // Subsequent intervals start the day after the previous interval's end
        $prevEndDate = (clone $currentDateTime)->modify("+" . $intervals[$i-1] . " days")->format('Y-m-d');
        $startDate = (new DateTime($prevEndDate))->modify('+1 day')->format('Y-m-d');
    }
    $endDate = (clone $currentDateTime)->modify("+{$days} days")->format('Y-m-d');
    
    if (new DateTime($startDate) > new DateTime($endDate)) {
        continue;
    }

    $stmt->execute([':startDate' => $startDate, ':endDate' => $endDate]);
    $count = (int)$stmt->fetchColumn();
    $mockData['renewalDue'][] = ['name' => "{$days}日内", 'value' => $count];
}


// 5. 续保周期天数环形图数据
// 使用新的函数计算续保周期天数，即保险止期与支付日期之间的天数差
$twoMonthsAgo = (clone $currentDateTime)->modify('-2 months')->format('Y-m-d');
$renewalCycleData = getRenewalCycleDays($pdo, $xubaoTable, $yuanshujuTable,
    ["t1.支付日期 BETWEEN :twoMonthsAgo AND :currentDate"],
    [':twoMonthsAgo' => $twoMonthsAgo, ':currentDate' => $currentDate]
);

// 格式化数据以适应图表需求
$mockData['renewalCycle'] = [];
foreach ($renewalCycleData as $item) {
    $mockData['renewalCycle'][] = [
        'name' => $item['cycle_category'] . '续保',
        'value' => (int)$item['count']
    ];
}


// 6. 录单员详情表格数据
$stmt = $pdo->query("SELECT DISTINCT 录单员 FROM {$xubaoTable} UNION SELECT DISTINCT 录单员 FROM {$yuanshujuTable};");
$allRecorders = $stmt->fetchAll(PDO::FETCH_COLUMN); // 获取所有可能的录单员
$mockData['recorderDetails'] = [];

// 预计算一些日期字符串，避免在循环中重复计算
$currentMonthStr = $currentDateTime->format('Y-m');
$lastMonthDateObj = (clone $currentDateTime)->modify('-1 month');
$lastMonthStr = $lastMonthDateObj->format('Y-m');
$lastYearDateStr = (clone $currentDateTime)->modify('-1 year')->format('Y-m-d');

foreach ($allRecorders as $recorderName) {
    // 上月保费 - 统计xubao表中支付日期为上个月的不含税保费合计（全部保费）
    $lastMonthStart = (clone $currentDateTime)->modify('first day of last month')->format('Y-m-d');
    $lastMonthEnd = (clone $currentDateTime)->modify('last day of last month')->format('Y-m-d');
    $lastMonthPremium = getTotalPremium($pdo, $xubaoTable,
        ["支付日期 BETWEEN :lastMonthStart AND :lastMonthEnd", "录单员 = :recorderName_lp"], // $whereClauses
        [':lastMonthStart' => $lastMonthStart, ':lastMonthEnd' => $lastMonthEnd, ':recorderName_lp' => $recorderName] // $bindParams
    );
    
    // 当月保费 - 统计xubao表中支付日期为当月的不含税保费合计（全部保费）
    $currentMonthStart = (clone $currentDateTime)->modify('first day of this month')->format('Y-m-d');
    $currentMonthEnd = $currentDate;
    $currentMonthPremium = getTotalPremium($pdo, $xubaoTable,
        ["支付日期 BETWEEN :currentMonthStart AND :currentMonthEnd", "录单员 = :recorderName_cp"], // $whereClauses
        [':currentMonthStart' => $currentMonthStart, ':currentMonthEnd' => $currentMonthEnd, ':recorderName_cp' => $recorderName] // $bindParams
    );

    // 上月续保量
    $lastMonthRenewalCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
        ["DATE_FORMAT(t1.支付日期, '%Y-%m') = :lastMonthStr_c", "t1.录单员 = :recorderName_lc"], // $whereClauses
        [':lastMonthStr_c' => $lastMonthStr, ':recorderName_lc' => $recorderName] // $bindParams
    );
    // 当月续保量
    $currentMonthRenewalCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
        ["DATE_FORMAT(t1.支付日期, '%Y-%m') = :currentMonthStr_c", "t1.录单员 = :recorderName_cc"], // $whereClauses
        [':currentMonthStr_c' => $currentMonthStr, ':recorderName_cc' => $recorderName] // $bindParams
    );
    // 当日续保量 (昨日)
    $todayRenewalCountRecorder = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
        ["t1.支付日期 = :yesterdayDate_rc", "t1.录单员 = :recorderName_tr"], // $whereClauses
        [':yesterdayDate_rc' => $yesterdayDate, ':recorderName_tr' => $recorderName] // $bindParams
    );
    // 同期续保量 (去年今日)
    $lastYearTodayRenewalCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
        ["t1.支付日期 = :lastYearDateStr_ly", "t1.录单员 = :recorderName_ly"], // $whereClauses
        [':lastYearDateStr_ly' => $lastYearDateStr, ':recorderName_ly' => $recorderName] // $bindParams
    );

    // 续保占比 (当月已续保量 / 当月可续保量)
    $stmtRenewableForRecorder = $pdo->prepare("
        SELECT COUNT(DISTINCT CONCAT(`车架号/VIN码`, 发动机号))
        FROM {$yuanshujuTable}
        WHERE DATE_FORMAT(保险止期, '%Y-%m') = :currentMonth AND 录单员 = :recorderName;
    ");
    $stmtRenewableForRecorder->execute([':currentMonth' => $currentMonth, ':recorderName' => $recorderName]);
    $renewableCountForRecorder = (int)$stmtRenewableForRecorder->fetchColumn();
    
    $renewalRatio = ($renewableCountForRecorder > 0) ? round(($currentMonthRenewalCount / $renewableCountForRecorder) * 100, 2) : 0;
    $renewalRatioFormatted = sprintf('%.2f%%', $renewalRatio); // 确保两位小数和百分号

    // 贡献值 (保留模拟，因为没有明确的计算方式或数据库字段)
    $contribution = ['A+', 'A', 'B+', 'B'][array_rand(['A+', 'A', 'B+', 'B'])];

    // 只显示有数据用户
    if ($lastMonthPremium > 0 || $currentMonthPremium > 0 || $lastMonthRenewalCount > 0 || $currentMonthRenewalCount > 0 || $todayRenewalCountRecorder > 0 || $lastYearTodayRenewalCount > 0) {
        $mockData['recorderDetails'][] = [
            $recorderName,
            number_format($lastMonthPremium, 2, '.', ''), // 保留2位小数，不使用千位分隔符
            number_format($currentMonthPremium, 2, '.', ''), // 保留2位小数
            $premiumProgress = ($lastMonthPremium > 0) ? sprintf('%.2f%%', ($currentMonthPremium / $lastMonthPremium) * 100) : 'N/A', // 保留2位小数
            $lastMonthRenewalCount,
            $currentMonthRenewalCount,
            $todayRenewalCountRecorder,
            $lastYearTodayRenewalCount,
            $renewalRatioFormatted,
            $contribution
        ];
    }
}


// 7. 业务员详情表格数据
$stmt = $pdo->query("SELECT DISTINCT 代理人名称 FROM {$yuanshujuTable} UNION SELECT DISTINCT 代理人名称 FROM {$xubaoTable};");
$allSalespersons = $stmt->fetchAll(PDO::FETCH_COLUMN); // 获取所有可能的业务员
$mockData['salespersonDetails'] = [];

// 预准备查询语句
$stmtRenewableSalesperson = $pdo->prepare("
    SELECT COUNT(DISTINCT CONCAT(`车架号/VIN码`, 发动机号))
    FROM {$yuanshujuTable}
    WHERE 代理人名称 = :salespersonName
    AND 保险止期 BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY);
");
$stmtRenewedSalesperson = $pdo->prepare("
    SELECT COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号))
    FROM {$xubaoTable} t1
    JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    WHERE t2.代理人名称 = :salespersonName
    AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
");
// 修正脱保逻辑：保险止期在当前日期3个月前到6个月前
$stmtExpiredSalesperson = $pdo->prepare("
    SELECT COUNT(DISTINCT CONCAT(`车架号/VIN码`, 发动机号))
    FROM {$yuanshujuTable}
    WHERE 代理人名称 = :salespersonName
    AND 保险止期 < DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    AND 保险止期 >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
");


foreach ($allSalespersons as $salespersonName) {
    // 可续台次
    $stmtRenewableSalesperson->execute([':salespersonName' => $salespersonName]);
    $renewableCount = (int)$stmtRenewableSalesperson->fetchColumn();

    // 已续台次
    $stmtRenewedSalesperson->execute([':salespersonName' => $salespersonName]);
    $renewedCount = (int)$stmtRenewedSalesperson->fetchColumn();

    // 脱保台次
    $stmtExpiredSalesperson->execute([':salespersonName' => $salespersonName]);
    $expiredCount = (int)$stmtExpiredSalesperson->fetchColumn();

    // 只显示有数据用户
    if ($renewableCount > 0 || $renewedCount > 0 || $expiredCount > 0) {
        $mockData['salespersonDetails'][] = [
            $salespersonName,
            $renewableCount,
            $renewedCount,
            $expiredCount
        ];
    }
}


// 8. 业务员详情弹窗明细数据
$mockData['salespersonPolicyDetails'] = [];
$stmtRenewablePolicies = $pdo->prepare("
    SELECT
        投保人名称 AS applicantName,
        车牌号 AS licensePlate,
        保险止期 AS insuranceEndDate
    FROM
        {$yuanshujuTable}
    WHERE
        代理人名称 = :salespersonName
        AND 保险止期 BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY);
");
$stmtRenewedPolicies = $pdo->prepare("
    SELECT
        t2.投保人名称 AS applicantName,
        t2.车牌号 AS licensePlate,
        t2.保险止期 AS insuranceEndDate
    FROM
        {$xubaoTable} t1
    JOIN
        {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    WHERE
        t2.代理人名称 = :salespersonName
        AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
");
// 修正脱保查询，计算 DATEDIFF
$stmtExpiredPolicies = $pdo->prepare("
    SELECT
        投保人名称 AS applicantName,
        车牌号 AS licensePlate,
        DATEDIFF(CURDATE(), 保险止期) AS daysExpired
    FROM
        {$yuanshujuTable}
    WHERE
        代理人名称 = :salespersonName
        AND 保险止期 < DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
        AND 保险止期 >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH);
");

foreach ($allSalespersons as $salespersonName) {
    // 可续保
    $stmtRenewablePolicies->execute([':salespersonName' => $salespersonName]);
    $mockData['salespersonPolicyDetails'][$salespersonName]['renewable'] = $stmtRenewablePolicies->fetchAll(PDO::FETCH_ASSOC);

    // 已续保
    $stmtRenewedPolicies->execute([':salespersonName' => $salespersonName]);
    $mockData['salespersonPolicyDetails'][$salespersonName]['renewed'] = $stmtRenewedPolicies->fetchAll(PDO::FETCH_ASSOC);

    // 脱保
    $stmtExpiredPolicies->execute([':salespersonName' => $salespersonName]);
    $mockData['salespersonPolicyDetails'][$salespersonName]['expired'] = $stmtExpiredPolicies->fetchAll(PDO::FETCH_ASSOC);
}


// 动态生成弹窗图表数据
$mockData['insuranceTrend'] = generateDailyRenewedTrendDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime); // 现在是已续保趋势
$mockData['quarterlyRenewalData'] = generateRecentQuarterlyDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime);
$mockData['monthlyRenewalData'] = generateRecentMonthlyDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime);
$mockData['weeklyRenewalData'] = generateRecentWeeklyDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime);
$mockData['yearOnYearComparison'] = generateYearOnYearDailyComparisonDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime);
$mockData['dailyCalculationVolume'] = generateDailyCalculationVolumeDataFromDb($pdo, $yuanshujuTable, $currentDateTime); // 算单量现在返回0
$mockData['dailyRenewalCalculationVolume'] = generateDailyRenewalCalculationVolumeDataFromDb($pdo, $xubaoTable, $yuanshujuTable, $currentDateTime);

// 将 PHP 数组转换为 JSON 字符串，供 JavaScript 使用
$mockDataJson = json_encode($mockData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>