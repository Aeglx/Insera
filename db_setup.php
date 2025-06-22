<?php
/**
 * 数据库索引和计算逻辑设置
 * 
 * 此文件负责检查和添加必要的MySQL索引和计算逻辑
 * 在index.php运行时自动执行，不显示任何提示
 */

require_once 'config.php';

/**
 * 检查并创建必要的索引
 * 
 * @param PDO $pdo 数据库连接
 * @param string $xubaoTable 续保表名
 * @param string $yuanshujuTable 原始数据表名
 * @return void
 */
function setupDatabaseIndexes(PDO $pdo, string $xubaoTable, string $yuanshujuTable): void {
    // 错误处理设置 - 静默模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    try {
        // 开始事务
        $pdo->beginTransaction();
        
        // 检查并创建xubao表的索引
        setupXubaoIndexes($pdo, $xubaoTable);
        
        // 检查并创建yuanshuju表的索引
        setupYuanshujuIndexes($pdo, $yuanshujuTable);
        
        // 提交事务
        $pdo->commit();
    } catch (Exception $e) {
        // 回滚事务
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        
        // 记录错误但不显示
        error_log("数据库索引设置错误: " . $e->getMessage());
    }
}

/**
 * 检查并创建续保表的索引
 * 
 * @param PDO $pdo 数据库连接
 * @param string $xubaoTable 续保表名
 * @return void
 */
function setupXubaoIndexes(PDO $pdo, string $xubaoTable): void {
    // 获取表中已存在的索引
    $existingIndexes = getExistingIndexes($pdo, $xubaoTable);
    
    // 需要创建的索引列表
    $indexesToCreate = [
        // 车架号和发动机号的复合索引，用于JOIN操作
        ['name' => 'idx_xubao_vin_engine', 'columns' => ['`车架号/VIN码`', '发动机号']],
        // 支付日期索引，用于日期范围查询
        ['name' => 'idx_xubao_payment_date', 'columns' => ['支付日期']],
        // 不含税保费索引，用于SUM聚合
        ['name' => 'idx_xubao_premium', 'columns' => ['不含税保费']]
    ];
    
    // 创建缺失的索引
    createMissingIndexes($pdo, $xubaoTable, $existingIndexes, $indexesToCreate);
}

/**
 * 检查并创建原始数据表的索引
 * 
 * @param PDO $pdo 数据库连接
 * @param string $yuanshujuTable 原始数据表名
 * @return void
 */
function setupYuanshujuIndexes(PDO $pdo, string $yuanshujuTable): void {
    // 获取表中已存在的索引
    $existingIndexes = getExistingIndexes($pdo, $yuanshujuTable);
    
    // 需要创建的索引列表
    $indexesToCreate = [
        // 车架号和发动机号的复合索引，用于JOIN操作
        ['name' => 'idx_yuanshuju_vin_engine', 'columns' => ['`车架号/VIN码`', '发动机号']],
        // 保险止期索引，用于日期范围查询
        ['name' => 'idx_yuanshuju_insurance_end', 'columns' => ['保险止期']]
    ];
    
    // 创建缺失的索引
    createMissingIndexes($pdo, $yuanshujuTable, $existingIndexes, $indexesToCreate);
}

/**
 * 获取表中已存在的索引
 * 
 * @param PDO $pdo 数据库连接
 * @param string $tableName 表名
 * @return array 已存在的索引名称数组
 */
function getExistingIndexes(PDO $pdo, string $tableName): array {
    $stmt = $pdo->prepare("SHOW INDEX FROM {$tableName}");
    $stmt->execute();
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $existingIndexes = [];
    foreach ($indexes as $index) {
        $existingIndexes[] = $index['Key_name'];
    }
    
    return array_unique($existingIndexes);
}

/**
 * 创建缺失的索引
 * 
 * @param PDO $pdo 数据库连接
 * @param string $tableName 表名
 * @param array $existingIndexes 已存在的索引名称数组
 * @param array $indexesToCreate 需要创建的索引配置数组
 * @return void
 */
function createMissingIndexes(PDO $pdo, string $tableName, array $existingIndexes, array $indexesToCreate): void {
    foreach ($indexesToCreate as $indexConfig) {
        $indexName = $indexConfig['name'];
        
        // 如果索引不存在，则创建
        if (!in_array($indexName, $existingIndexes)) {
            $columns = implode(', ', $indexConfig['columns']);
            $sql = "CREATE INDEX {$indexName} ON {$tableName} ({$columns})";
            $pdo->exec($sql);
        }
    }
}

/**
 * 检查并创建计算逻辑（视图、存储过程等）
 * 
 * @param PDO $pdo 数据库连接
 * @param string $xubaoTable 续保表名
 * @param string $yuanshujuTable 原始数据表名
 * @return void
 */
function setupCalculationLogic(PDO $pdo, string $xubaoTable, string $yuanshujuTable): void {
    try {
        // 创建或替换视图：每日续保量视图
        $dailyRenewalViewSql = "
        CREATE OR REPLACE VIEW daily_renewal_view AS
        SELECT 
            t1.支付日期 AS renewal_date,
            COUNT(DISTINCT CONCAT(t1.`车架号/VIN码`, t1.发动机号)) AS renewal_count,
            SUM(t1.不含税保费) AS renewal_premium
        FROM {$xubaoTable} t1
        JOIN {$yuanshujuTable} t2 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
        WHERE t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期
        GROUP BY t1.支付日期
        ";
        $pdo->exec($dailyRenewalViewSql);
        
        // 创建或替换视图：月度续保率视图
        $monthlyRenewalRateViewSql = "
        CREATE OR REPLACE VIEW monthly_renewal_rate_view AS
        SELECT 
            DATE_FORMAT(t2.保险止期, '%Y-%m') AS month,
            COUNT(DISTINCT CONCAT(t2.`车架号/VIN码`, t2.发动机号)) AS renewable_count,
            COUNT(DISTINCT CASE 
                WHEN t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期 
                THEN CONCAT(t1.`车架号/VIN码`, t1.发动机号) 
                ELSE NULL 
            END) AS renewed_count,
            ROUND(
                COUNT(DISTINCT CASE 
                    WHEN t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期 
                    THEN CONCAT(t1.`车架号/VIN码`, t1.发动机号) 
                    ELSE NULL 
                END) / COUNT(DISTINCT CONCAT(t2.`车架号/VIN码`, t2.发动机号)) * 100, 
                2
            ) AS renewal_rate
        FROM {$yuanshujuTable} t2
        LEFT JOIN {$xubaoTable} t1 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
        GROUP BY DATE_FORMAT(t2.保险止期, '%Y-%m')
        ";
        $pdo->exec($monthlyRenewalRateViewSql);
        
        // 创建或替换存储过程：计算指定日期范围的续保率
        $calculateRenewalRateProcSql = "
        DROP PROCEDURE IF EXISTS calculate_renewal_rate;
        CREATE PROCEDURE calculate_renewal_rate(
            IN start_date DATE,
            IN end_date DATE
        )
        BEGIN
            SELECT 
                COUNT(DISTINCT CONCAT(t2.`车架号/VIN码`, t2.发动机号)) AS renewable_count,
                COUNT(DISTINCT CASE 
                    WHEN t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期 
                    THEN CONCAT(t1.`车架号/VIN码`, t1.发动机号) 
                    ELSE NULL 
                END) AS renewed_count,
                ROUND(
                    COUNT(DISTINCT CASE 
                        WHEN t1.支付日期 BETWEEN DATE_SUB(t2.保险止期, INTERVAL 60 DAY) AND t2.保险止期 
                        THEN CONCAT(t1.`车架号/VIN码`, t1.发动机号) 
                        ELSE NULL 
                    END) / COUNT(DISTINCT CONCAT(t2.`车架号/VIN码`, t2.发动机号)) * 100, 
                    2
                ) AS renewal_rate
            FROM {$yuanshujuTable} t2
            LEFT JOIN {$xubaoTable} t1 ON t1.`车架号/VIN码` = t2.`车架号/VIN码` AND t1.发动机号 = t2.发动机号
            WHERE t2.保险止期 BETWEEN start_date AND end_date;
        END
        ";
        $pdo->exec($calculateRenewalRateProcSql);
        
    } catch (Exception $e) {
        // 记录错误但不显示
        error_log("数据库计算逻辑设置错误: " . $e->getMessage());
    }
}

/**
 * 主函数：检查并设置数据库索引和计算逻辑
 */
function setupDatabase(): void {
    try {
        // 获取数据库连接
        $pdo = getDbConnection();
        
        // 从config.php获取表名
        $xubaoTable = 'xubao';
        $yuanshujuTable = 'yuanshuju';
        
        // 设置索引
        setupDatabaseIndexes($pdo, $xubaoTable, $yuanshujuTable);
        
        // 设置计算逻辑
        setupCalculationLogic($pdo, $xubaoTable, $yuanshujuTable);
        
    } catch (Exception $e) {
        // 记录错误但不显示
        error_log("数据库设置错误: " . $e->getMessage());
    }
}

// 执行数据库设置
setupDatabase();