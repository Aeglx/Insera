<?php
// functions.php

/**
 * 获取在特定日期条件下，符合续保范围的已续保车辆台次。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param array $whereClauses 包含要添加到WHERE子句的SQL片段数组。例如 ['t1.支付日期 = :dateVal']
 * @param array $bindParams 所有命名参数的键值对数组。例如 [':dateVal' => $date]
 * @return int 已续保车辆台次。
 */
function getRenewedCount($pdo, $xubaoTable, $yuanshujuTable, $whereClauses = [], $bindParams = []) {
    $allWhereClauses = ["t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期"];
    if (!empty($whereClauses)) {
        $allWhereClauses = array_merge($allWhereClauses, $whereClauses);
    }

    $finalWhereSql = implode(" AND ", $allWhereClauses);

    $sql = "
        SELECT COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号))
        FROM {$xubaoTable} t1
        JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    ";
    
    if (!empty($finalWhereSql)) {
        $sql .= " WHERE {$finalWhereSql}"; // 移除外层括号，简化WHERE子句
    }
    $sql .= ";"; // 确保SQL语句以分号结束

    $stmt = $pdo->prepare($sql);

    // --- 严格的参数验证和过滤 ---
    // 使用数组来存储唯一的参数名
    $expectedParamsInSql = [];
    preg_match_all('/(:[a-zA-Z0-9_]+)/', $sql, $matches);
    if (!empty($matches[0])) {
        // 使用 array_unique 确保每个参数名只记录一次
        $uniqueParams = array_unique($matches[0]);
        foreach ($uniqueParams as $paramName) {
            $expectedParamsInSql[$paramName] = true;
        }
    }

    $finalParams = [];
    // 1. 确保所有提供的参数都在SQL中被需要
    foreach ($bindParams as $paramKey => $paramValue) {
        if (isset($expectedParamsInSql[$paramKey])) {
            $finalParams[$paramKey] = $paramValue;
        } else {
            error_log("Warning in getRenewedCount: Provided parameter '{$paramKey}' is not found in SQL. SQL: '{$sql}'");
        }
    }

    // 2. 确保所有SQL中需要的参数都被提供了
    foreach ($expectedParamsInSql as $paramName => $bool) {
        if (!array_key_exists($paramName, $finalParams)) {
            error_log("Error in getRenewedCount: Required SQL parameter '{$paramName}' is missing. SQL: '{$sql}'");
            // 在生产环境中，这里可以抛出异常来阻止错误的执行
            // throw new InvalidArgumentException("Missing required SQL parameter: {$paramName}");
        }
    }
    // --- 结束参数验证和过滤 ---

    error_log('Debug SQL (getRenewedCount): ' . $sql);
    error_log('Debug Params (getRenewedCount): ' . json_encode($finalParams));
    $stmt->execute($finalParams);
    return (int)$stmt->fetchColumn();
}

/**
 * 获取在特定日期条件下，符合续保范围的已续保保费总额。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 对原始数据表的引用。
 * @param array $whereClauses 包含要添加到WHERE子句的SQL片段数组。
 * @param array $bindParams 所有命名参数的键值对数组。
 * @return float 已续保保费总额。
 */
function getRenewedPremium($pdo, $xubaoTable, $yuanshujuTable, $whereClauses = [], $bindParams = []) {
    $allWhereClauses = ["t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期"];
    if (!empty($whereClauses)) {
        $allWhereClauses = array_merge($allWhereClauses, $whereClauses);
    }

    $finalWhereSql = implode(" AND ", $allWhereClauses);

    $sql = "
        SELECT IFNULL(SUM(t1.不含税保费), 0)
        FROM {$xubaoTable} t1
        JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    ";
    if (!empty($finalWhereSql)) {
        $sql .= " WHERE {$finalWhereSql}"; // 移除外层括号，简化WHERE子句
    }
    $sql .= ";";

    $stmt = $pdo->prepare($sql);

    // --- 严格的参数验证和过滤 ---
    $expectedParamsInSql = [];
    preg_match_all('/(:[a-zA-Z0-9_]+)/', $sql, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $paramName) {
            $expectedParamsInSql[$paramName] = true;
        }
    }

    $finalParams = [];
    foreach ($bindParams as $paramKey => $paramValue) {
        if (isset($expectedParamsInSql[$paramKey])) {
            $finalParams[$paramKey] = $paramValue;
        } else {
            error_log("Warning in getRenewedPremium: Provided parameter '{$paramKey}' is not found in SQL. SQL: '{$sql}'");
        }
    }

    foreach ($expectedParamsInSql as $paramName => $bool) {
        if (!array_key_exists($paramName, $finalParams)) {
            error_log("Error in getRenewedPremium: Required SQL parameter '{$paramName}' is missing. SQL: '{$sql}'");
        }
    }
    // --- 结束参数验证和过滤 ---

    error_log('Debug SQL (getRenewedPremium): ' . $sql);
    error_log('Debug Params (getRenewedPremium): ' . json_encode($finalParams));
    $stmt->execute($finalParams);
    return (float)$stmt->fetchColumn();
}

/**
 * 获取在特定日期条件下，可续保的车辆台次。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $yuanshujuTable 原始数据表名。
 * @param array $whereClauses 包含要添加到WHERE子句的SQL片段数组。
 * @param array $bindParams 所有命名参数的键值对数组。
 * @return int 可续保车辆台次。
 */
function getRenewableCount($pdo, $yuanshujuTable, $whereClauses = [], $bindParams = []) {
    // 确保 $whereClauses 中的每个元素都是字符串形式的SQL片段
    $cleanedWhereClauses = [];
    foreach ($whereClauses as $clause) {
        if (is_string($clause)) {
            $cleanedWhereClauses[] = $clause;
        } else {
            error_log("Warning in getRenewableCount: Non-string element found in \$whereClauses: " . var_export($clause, true));
        }
    }
    $finalWhereSql = implode(" AND ", $cleanedWhereClauses);

    $sql = "
        SELECT COUNT(DISTINCT CONCAT(`车架号/VIN码`, 发动机号))
        FROM {$yuanshujuTable}
    ";
    if (!empty($finalWhereSql)) {
        $sql .= " WHERE {$finalWhereSql}"; // 移除外层括号，简化WHERE子句
    }
    $sql .= ";"; // 确保SQL语句以分号结束
    
    $stmt = $pdo->prepare($sql);

    // --- 严格的参数验证和过滤 ---
    $expectedParamsInSql = [];
    preg_match_all('/(:[a-zA-Z0-9_]+)/', $sql, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $paramName) {
            $expectedParamsInSql[$paramName] = true;
        }
    }

    $finalParams = [];
    $missingParams = [];
    $unusedProvidedParams = [];

    // 1. 检查 SQL 中需要的每个参数是否都已提供值
    foreach ($expectedParamsInSql as $paramName => $dummyValue) {
        if (array_key_exists($paramName, $bindParams)) {
            $finalParams[$paramName] = $bindParams[$paramName];
        } else {
            $missingParams[] = $paramName;
        }
    }

    // 2. 检查提供的参数中是否有 SQL 语句中未使用的
    foreach ($bindParams as $paramKey => $paramValue) {
        if (!isset($expectedParamsInSql[$paramKey])) {
            $unusedProvidedParams[] = $paramKey;
        }
    }

    if (!empty($missingParams)) {
        error_log("CRITICAL ERROR in getRenewableCount (file: " . __FILE__ . ", line: " . __LINE__ . "): Missing required SQL parameters: " . implode(', ', $missingParams) . ". Full SQL: '{$sql}'. Provided Params: " . json_encode($bindParams));
        // 这一行是 233 行。抛出异常会阻止程序继续执行，并显示明确的错误信息。
        throw new InvalidArgumentException("Missing required SQL parameter(s) for getRenewableCount query. Check error log for details.");
    }
    if (!empty($unusedProvidedParams)) {
        error_log("WARNING in getRenewableCount (file: " . __FILE__ . ", line: " . __LINE__ . "): Provided parameters not used in SQL: " . implode(', ', $unusedProvidedParams) . ". Full SQL: '{$sql}'. Provided Params: " . json_encode($bindParams));
    }
    // --- 结束参数验证和过滤 ---

    error_log('DEBUG: getRenewableCount Final SQL: ' . $sql);
    error_log('DEBUG: getRenewableCount Final Params: ' . json_encode($finalParams));
    $stmt->execute($finalParams);
    return (int)$stmt->fetchColumn();
}


/**
 * 生成近24周的周续保率数据。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含周标签和续保率的数组。
 */
function generateRecentWeeklyDataFromDb(PDO $pdo, $xubaoTable, $yuanshujuTable, DateTime $currentDateObj) {
    $weeks = [];
    $rates = [];
    $tempDate = clone $currentDateObj;

    for ($i = 0; $i < 24; $i++) {
        $startOfWeek = (clone $tempDate)->modify('monday this week')->format('Y-m-d');
        $endOfWeek = (clone $tempDate)->modify('sunday this week')->format('Y-m-d');

        $year = $tempDate->format('Y');
        $weekNum = $tempDate->format('W');

        array_unshift($weeks, "{$year}-W{$weekNum}");

        // 可续保数量：保险止期在该周内
        $renewableCount = getRenewableCount($pdo, $yuanshujuTable,
            ["保险止期 BETWEEN :startOfWeek AND :endOfWeek"],
            [':startOfWeek' => $startOfWeek, ':endOfWeek' => $endOfWeek]
        );

        // 已续保数量：支付日期在该周内，且在续保范围内
        $renewedCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
            ["t1.支付日期 BETWEEN :startOfWeek AND :endOfWeek"],
            [':startOfWeek' => $startOfWeek, ':endOfWeek' => $endOfWeek]
        );

        $rate = ($renewableCount > 0) ? round(($renewedCount / $renewableCount) * 100, 2) : 0;
        array_unshift($rates, $rate);

        $tempDate->modify('-7 days');
    }
    return ['weeks' => $weeks, 'rates' => $rates];
}

/**
 * 生成近12个月的月续保率数据。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含月标签和续保率的数组。
 */
function generateRecentMonthlyDataFromDb(PDO $pdo, $xubaoTable, $yuanshujuTable, DateTime $currentDateObj) {
    $months = [];
    $rates = [];
    $tempDate = new DateTime($currentDateObj->format('Y-m-01')); // 从当前月的第一天开始

    for ($i = 0; $i < 12; $i++) {
        $monthStr = $tempDate->format('Y-m');
        $startOfMonth = (clone $tempDate)->format('Y-m-01');
        $endOfMonth = (clone $tempDate)->modify('last day of this month')->format('Y-m-d');

        array_unshift($months, $monthStr);

        // 可续保数量：保险止期在该月内
        $renewableCount = getRenewableCount($pdo, $yuanshujuTable,
            ["DATE_FORMAT(保险止期, '%Y-%m') = :monthStr"],
            [':monthStr' => $monthStr]
        );

        // 已续保数量：支付日期在该月内，且在续保范围内
        $renewedCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
            ["DATE_FORMAT(t1.支付日期, '%Y-%m') = :monthStr"],
            [':monthStr' => $monthStr]
        );

        $rate = ($renewableCount > 0) ? round(($renewedCount / $renewableCount) * 100, 2) : 0;
        array_unshift($rates, $rate);

        $tempDate->modify('-1 month'); // 移动到上一个月
    }
    return ['months' => $months, 'rates' => $rates];
}

/**
 * 生成当前日期与上年同期数据对比（近30天）。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含日期、本年数据和上年数据的数组。
 */
function generateYearOnYearDailyComparisonDataFromDb(PDO $pdo, $yuanshujuTable, DateTime $currentDateObj) {
    $dates = [];
    $currentYearData = [];
    $lastYearData = [];

    $tempDate = clone $currentDateObj;

    $stmtCurrentYear = $pdo->prepare("SELECT COUNT(*) FROM {$yuanshujuTable} WHERE 保险止期 = :dateStr;");
    $stmtLastYear = $pdo->prepare("SELECT COUNT(*) FROM {$yuanshujuTable} WHERE 保险止期 = :lastYearDateStr;");

    for ($i = 0; $i < 30; $i++) {
        $dateStr = $tempDate->format('Y-m-d');
        $lastYearDateStr = (clone $tempDate)->modify('-1 year')->format('Y-m-d');

        array_unshift($dates, $dateStr);

        $stmtCurrentYear->execute([':dateStr' => $dateStr]);
        array_unshift($currentYearData, (int)$stmtCurrentYear->fetchColumn());

        $stmtLastYear->execute([':lastYearDateStr' => $lastYearDateStr]);
        array_unshift($lastYearData, (int)$stmtLastYear->fetchColumn());

        $tempDate->modify('-1 day');
    }

    return ['dates' => $dates, 'currentYear' => $currentYearData, 'lastYear' => $lastYearData];
}

/**
 * 生成近30天每日算单量数据 (暂时返回 0)。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含日期和算单量数据的数组 (当前设置为0)。
 */
function generateDailyCalculationVolumeDataFromDb(PDO $pdo, $yuanshujuTable, DateTime $currentDateObj) {
    $dates = [];
    $data = [];
    $tempDate = clone $currentDateObj;
    $tempDate->modify('-1 day'); // 从当前日期的前一天开始倒推

    for ($i = 0; $i < 30; $i++) {
        $dateStr = $tempDate->format('Y-m-d');
        array_unshift($dates, $dateStr);
        // 暂时返回 0，因为算单量数据在另一个表中，此处不处理
        array_unshift($data, 0);
        $tempDate->modify('-1 day');
    }
    return ['dates' => $dates, 'data' => $data];
}

/**
 * 生成近30天每日续保算单量数据。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含日期和续保算单量数据的数组。
 */
function generateDailyRenewalCalculationVolumeDataFromDb(PDO $pdo, $xubaoTable, $yuanshujuTable, DateTime $currentDateObj) {
    $dates = [];
    $data = [];
    $tempDate = clone $currentDateObj;
    $tempDate->modify('-1 day'); // 从当前日期的前一天开始倒推

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号))
        FROM {$xubaoTable} t1
        JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
        WHERE t1.支付日期 = :dateStr
        AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
    ");

    for ($i = 0; $i < 30; $i++) {
        $dateStr = $tempDate->format('Y-m-d');
        array_unshift($dates, $dateStr);
        $stmt->execute([':dateStr' => $dateStr]);
        array_unshift($data, (int)$stmt->fetchColumn());
        $tempDate->modify('-1 day');
    }
    return ['dates' => $dates, 'data' => $data];
}

/**
 * 生成近30天每日已续保数量趋势数据（替代原交强险与商业险趋势）。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含日期和已续保数量的数组。
 */
function generateDailyRenewedTrendDataFromDb(PDO $pdo, $xubaoTable, $yuanshujuTable, DateTime $currentDateObj) {
    $dates = [];
    $renewedData = [];

    $tempDate = clone $currentDateObj;

    $stmtRenewed = $pdo->prepare("
        SELECT COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号))
        FROM {$xubaoTable} t1
        JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
        WHERE t1.支付日期 = :dateStr
        AND t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期;
    ");

    for ($i = 0; $i < 30; $i++) {
        $dateStr = $tempDate->format('Y-m-d');
        array_unshift($dates, $dateStr);

        $stmtRenewed->execute([':dateStr' => $dateStr]);
        array_unshift($renewedData, (int)$stmtRenewed->fetchColumn());

        $tempDate->modify('-1 day');
    }
    return ['dates' => $dates, 'renewed' => $renewedData];
}


/**
 * 生成近4个季度的续保率数据。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param DateTime $currentDateObj 当前日期对象。
 * @return array 包含季度标签和续保率的数组。
 */
/**
 * 计算续保周期天数，即保险止期与支付日期之间的天数差。
 *
 * @param PDO $pdo PDO数据库连接对象。
 * @param string $xubaoTable 续保表名。
 * @param string $yuanshujuTable 原始数据表名。
 * @param array $whereClauses 包含要添加到WHERE子句的SQL片段数组。
 * @param array $bindParams 所有命名参数的键值对数组。
 * @return array 包含续保周期天数分类及其对应数量的数组。
 */
function getRenewalCycleDays($pdo, $xubaoTable, $yuanshujuTable, $whereClauses = [], $bindParams = []) {
    $allWhereClauses = ["t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期"];
    if (!empty($whereClauses)) {
        $allWhereClauses = array_merge($allWhereClauses, $whereClauses);
    }

    $finalWhereSql = implode(" AND ", $allWhereClauses);

    $sql = "
        SELECT
            CASE
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 0 AND 7 THEN '7日内'
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 8 AND 15 THEN '15日内'
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 16 AND 23 THEN '23日内'
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 24 AND 30 THEN '30日内'
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 31 AND 45 THEN '45日内'
                WHEN DATEDIFF(t2.保险止期, t1.支付日期) BETWEEN 46 AND 60 THEN '60日内'
                ELSE '超出范围'
            END AS cycle_category,
            COUNT(*) AS count
        FROM
            {$xubaoTable} t1
        JOIN
            {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
    ";
    
    if (!empty($finalWhereSql)) {
        $sql .= " WHERE {$finalWhereSql}";
    }
    
    $sql .= "
        GROUP BY
            cycle_category
        ORDER BY
            FIELD(cycle_category, '7日内', '15日内', '23日内', '30日内', '45日内', '60日内', '超出范围');
    ";

    $stmt = $pdo->prepare($sql);

    // --- 严格的参数验证和过滤 ---
    $expectedParamsInSql = [];
    preg_match_all('/(:[a-zA-Z0-9_]+)/', $sql, $matches);
    if (!empty($matches[0])) {
        foreach ($matches[0] as $paramName) {
            $expectedParamsInSql[$paramName] = true;
        }
    }

    $finalParams = [];
    foreach ($bindParams as $paramKey => $paramValue) {
        if (isset($expectedParamsInSql[$paramKey])) {
            $finalParams[$paramKey] = $paramValue;
        } else {
            error_log("Warning in getRenewalCycleDays: Provided parameter '{$paramKey}' is not found in SQL. SQL: '{$sql}'");
        }
    }

    foreach ($expectedParamsInSql as $paramName => $bool) {
        if (!array_key_exists($paramName, $finalParams)) {
            error_log("Error in getRenewalCycleDays: Required SQL parameter '{$paramName}' is missing. SQL: '{$sql}'");
        }
    }
    // --- 结束参数验证和过滤 ---

    error_log('Debug SQL (getRenewalCycleDays): ' . $sql);
    error_log('Debug Params (getRenewalCycleDays): ' . json_encode($finalParams));
    $stmt->execute($finalParams);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateRecentQuarterlyDataFromDb(PDO $pdo, $xubaoTable, $yuanshujuTable, DateTime $currentDateObj) {
    $quarters = [];
    $rates = [];
    $tempDate = clone $currentDateObj;

    for ($q = 0; $q < 4; $q++) {
        $year = $tempDate->format('Y');
        $month = (int)$tempDate->format('m');
        $quarterNum = floor(($month - 1) / 3) + 1;
        $quarterLabel = "{$year}Q{$quarterNum}";

        $qStart = '';
        $qEnd = '';
        if ($quarterNum == 1) {
            $qStart = "{$year}-01-01";
            $qEnd = "{$year}-03-31";
        } elseif ($quarterNum == 2) {
            $qStart = "{$year}-04-01";
            $qEnd = "{$year}-06-30";
        } elseif ($quarterNum == 3) {
            $qStart = "{$year}-07-01";
            $qEnd = "{$year}-09-30";
        } else { // Quarter 4
            $qStart = "{$year}-10-01";
            $qEnd = "{$year}-12-31";
        }

        // 可续保数量：保险止期在该季度内
        $renewableCount = getRenewableCount($pdo, $yuanshujuTable,
            ['保险止期 BETWEEN :qStart AND :qEnd'],
            [':qStart' => $qStart, ':qEnd' => $qEnd]
        );

        // 已续保数量：支付日期在该季度内，且在续保范围内
        $renewedCount = getRenewedCount($pdo, $xubaoTable, $yuanshujuTable,
            ['t1.支付日期 BETWEEN :qStart AND :qEnd'],
            [':qStart' => $qStart, ':qEnd' => $qEnd]
        );

        $qRate = ($renewableCount > 0) ? round(($renewedCount / $renewableCount) * 100, 2) : 0;

        array_unshift($quarters, $quarterLabel);
        array_unshift($rates, $qRate);

        $tempDate->modify('-3 months'); // 移动到前一个季度
    }
    return ['quarters' => $quarters, 'rates' => $rates];
}