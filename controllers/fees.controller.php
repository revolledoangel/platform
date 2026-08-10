<?php
require_once __DIR__ . '/currencies.controller.php';

class Fees_Controller
{
    private static function runOrThrow($conn, $sql)
    {
        $ok = $conn->query($sql);
        if ($ok === false) {
            throw new Exception($conn->error ?: 'SQL execution failed');
        }
        return $ok;
    }

    private static function getConn()
    {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) {
            return null;
        }
        $conn->set_charset('utf8mb4');
        Currencies_Controller::ctrEnsureSchema($conn);
        self::ensureTables($conn);
        return $conn;
    }

    private static function ensureColumnExists($conn, $table, $column, $definition)
    {
        $tableEsc = $conn->real_escape_string($table);
        $columnEsc = $conn->real_escape_string($column);
        $sql = "SELECT COUNT(*) AS cnt
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = '$tableEsc'
                  AND COLUMN_NAME = '$columnEsc'";
        $res = $conn->query($sql);
        $exists = ($res && ($row = $res->fetch_assoc()) && intval($row['cnt']) > 0);
        if (!$exists) {
            $conn->query("ALTER TABLE $table ADD COLUMN $definition");
        }
    }

    private static function ensureTables($conn)
    {
        $conn->query("CREATE TABLE IF NOT EXISTS fee_concepts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uq_fee_concepts_name (name)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS client_fee_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            min_investment DECIMAL(12,2) NOT NULL DEFAULT 0,
            max_investment DECIMAL(12,2) NULL,
            fee_mode ENUM('percentage','fixed','percentage_plus_fixed') NOT NULL DEFAULT 'percentage',
            fee_label VARCHAR(120) NULL,
            percentage_value DECIMAL(10,4) NOT NULL DEFAULT 0,
            fixed_value DECIMAL(12,2) NOT NULL DEFAULT 0,
            fixed_currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
            priority INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_client_fee_rules_client (client_id, is_active, priority)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS client_fee_charges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            client_id INT NOT NULL,
            concept_id INT NULL,
            concept_name VARCHAR(150) NOT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_client_fee_charges_client (client_id, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS mmre_fee_rules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mediamixrealestate_id INT NOT NULL,
            min_investment DECIMAL(12,2) NOT NULL DEFAULT 0,
            max_investment DECIMAL(12,2) NULL,
            fee_mode ENUM('percentage','fixed','percentage_plus_fixed') NOT NULL DEFAULT 'percentage',
            fee_label VARCHAR(120) NULL,
            percentage_value DECIMAL(10,4) NOT NULL DEFAULT 0,
            fixed_value DECIMAL(12,2) NOT NULL DEFAULT 0,
            fixed_currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
            fixed_usd_per_unit_snapshot DECIMAL(18,8) NOT NULL DEFAULT 1,
            priority INT NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_mmre_fee_rules_mmre (mediamixrealestate_id, is_active, priority)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS mmre_fee_charges (
            id INT AUTO_INCREMENT PRIMARY KEY,
            mediamixrealestate_id INT NOT NULL,
            concept_id INT NULL,
            concept_name VARCHAR(150) NOT NULL,
            amount DECIMAL(12,2) NOT NULL DEFAULT 0,
            currency_code VARCHAR(10) NOT NULL DEFAULT 'USD',
            usd_per_unit_snapshot DECIMAL(18,8) NOT NULL DEFAULT 1,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_mmre_fee_charges_mmre (mediamixrealestate_id, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::ensureColumnExists($conn, 'mediamixrealestates', 'currency_usd_per_unit_snapshot', 'currency_usd_per_unit_snapshot DECIMAL(18,8) NULL');
        self::ensureColumnExists($conn, 'client_fee_rules', 'fixed_currency_code', "fixed_currency_code VARCHAR(10) NOT NULL DEFAULT 'USD'");
        self::ensureColumnExists($conn, 'client_fee_rules', 'fee_label', 'fee_label VARCHAR(120) NULL');
        self::ensureColumnExists($conn, 'client_fee_charges', 'currency_code', "currency_code VARCHAR(10) NOT NULL DEFAULT 'USD'");
        self::ensureColumnExists($conn, 'mmre_fee_rules', 'fixed_currency_code', "fixed_currency_code VARCHAR(10) NOT NULL DEFAULT 'USD'");
        self::ensureColumnExists($conn, 'mmre_fee_rules', 'fee_label', 'fee_label VARCHAR(120) NULL');
        self::ensureColumnExists($conn, 'mmre_fee_rules', 'fixed_usd_per_unit_snapshot', 'fixed_usd_per_unit_snapshot DECIMAL(18,8) NOT NULL DEFAULT 1');
        self::ensureColumnExists($conn, 'mmre_fee_charges', 'currency_code', "currency_code VARCHAR(10) NOT NULL DEFAULT 'USD'");
        self::ensureColumnExists($conn, 'mmre_fee_charges', 'usd_per_unit_snapshot', 'usd_per_unit_snapshot DECIMAL(18,8) NOT NULL DEFAULT 1');
    }

    public static function ctrIsAdminSession()
    {
        $perfil = $_SESSION['perfil'] ?? '';
        return in_array($perfil, ['Super', 'Administrador'], true);
    }

    private static function sanitizeFeeMode($mode)
    {
        return in_array($mode, ['percentage', 'fixed', 'percentage_plus_fixed'], true)
            ? $mode
            : 'percentage';
    }

    private static function normalizeRules($rules, $defaultCurrency = 'USD')
    {
        $normalized = [];
        if (!is_array($rules)) {
            return $normalized;
        }

        foreach ($rules as $index => $rule) {
            if (!is_array($rule)) {
                continue;
            }
            $min = isset($rule['min_investment']) ? floatval($rule['min_investment']) : 0;
            $maxRaw = isset($rule['max_investment']) ? trim((string)$rule['max_investment']) : '';
            $max = ($maxRaw === '' || strtolower($maxRaw) === 'null') ? null : floatval($maxRaw);
            $mode = self::sanitizeFeeMode($rule['fee_mode'] ?? 'percentage');
            $percentage = isset($rule['percentage_value']) ? floatval($rule['percentage_value']) : 0;
            $fixed = isset($rule['fixed_value']) ? floatval($rule['fixed_value']) : 0;
            $feeLabel = trim((string)($rule['fee_label'] ?? ($rule['label'] ?? '')));
            $feeLabel = $feeLabel === '' ? null : mb_substr($feeLabel, 0, 120);
            $fixedCurrencyCode = strtoupper(trim((string)($rule['fixed_currency_code'] ?? $defaultCurrency)));
            if (!preg_match('/^[A-Z]{3,10}$/', $fixedCurrencyCode)) {
                $fixedCurrencyCode = strtoupper($defaultCurrency);
            }

            if ($min < 0) {
                $min = 0;
            }
            if ($max !== null && $max < $min) {
                continue;
            }

            $baseRule = [
                'min_investment' => round($min, 2),
                'max_investment' => $max === null ? null : round($max, 2),
                'fee_label' => $feeLabel,
                'percentage_value' => round($percentage, 4),
                'fixed_value' => round($fixed, 2),
                'fixed_currency_code' => $fixedCurrencyCode,
                'priority' => (int)$index,
            ];

            // Compatibilidad legacy: si existe "% + fijo", se descompone en dos reglas aplicables.
            if ($mode === 'percentage_plus_fixed') {
                if (abs($baseRule['percentage_value']) > 0) {
                    $pctRule = $baseRule;
                    $pctRule['fee_mode'] = 'percentage';
                    $pctRule['fixed_value'] = 0.0;
                    $normalized[] = $pctRule;
                }
                if (abs($baseRule['fixed_value']) > 0) {
                    $fixRule = $baseRule;
                    $fixRule['fee_mode'] = 'fixed';
                    $fixRule['percentage_value'] = 0.0;
                    $normalized[] = $fixRule;
                }
            } else {
                $baseRule['fee_mode'] = $mode;
                $normalized[] = $baseRule;
            }
        }

        usort($normalized, function ($a, $b) {
            if ($a['min_investment'] == $b['min_investment']) {
                return $a['priority'] <=> $b['priority'];
            }
            return $a['min_investment'] <=> $b['min_investment'];
        });

        foreach ($normalized as $i => &$row) {
            $row['priority'] = $i;
        }

        return $normalized;
    }

    private static function normalizeCharges($charges, $defaultCurrency = 'USD')
    {
        $normalized = [];
        if (!is_array($charges)) {
            return $normalized;
        }

        foreach ($charges as $charge) {
            if (!is_array($charge)) {
                continue;
            }
            $name = trim((string)($charge['concept_name'] ?? ''));
            $amount = isset($charge['amount']) ? floatval($charge['amount']) : 0;
            $conceptId = isset($charge['concept_id']) ? intval($charge['concept_id']) : null;
            $currencyCode = strtoupper(trim((string)($charge['currency_code'] ?? $defaultCurrency)));
            if (!preg_match('/^[A-Z]{3,10}$/', $currencyCode)) {
                $currencyCode = strtoupper($defaultCurrency);
            }

            if ($name === '' || $amount == 0) {
                continue;
            }

            $normalized[] = [
                'concept_id' => $conceptId > 0 ? $conceptId : null,
                'concept_name' => mb_substr($name, 0, 150),
                'amount' => round($amount, 2),
                'currency_code' => $currencyCode,
            ];
        }

        return $normalized;
    }

    public static function ctrGetFeeConcepts()
    {
        $conn = self::getConn();
        if (!$conn) {
            return [];
        }

        $res = $conn->query("SELECT id, name FROM fee_concepts WHERE active = 1 ORDER BY name ASC");
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = [
                    'id' => (int)$row['id'],
                    'name' => $row['name'],
                ];
            }
        }
        $conn->close();
        return $rows;
    }

    public static function ctrCreateFeeConcept($name)
    {
        $conn = self::getConn();
        if (!$conn) {
            return ['success' => false, 'message' => 'DB connection failed'];
        }

        $name = mb_substr(trim((string)$name), 0, 150);
        if ($name === '') {
            $conn->close();
            return ['success' => false, 'message' => 'El concepto no puede estar vacío'];
        }

        $escName = $conn->real_escape_string($name);
        $conn->query("INSERT INTO fee_concepts (name, active) VALUES ('$escName', 1)");

        if ($conn->errno && $conn->errno != 1062) {
            $msg = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => $msg];
        }

        $res = $conn->query("SELECT id, name FROM fee_concepts WHERE name = '$escName' LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;
        $conn->close();

        if (!$row) {
            return ['success' => false, 'message' => 'No se pudo crear el concepto'];
        }

        return [
            'success' => true,
            'concept' => [
                'id' => (int)$row['id'],
                'name' => $row['name'],
            ],
        ];
    }

    public static function ctrGetClientFeeConfig($clientId)
    {
        $conn = self::getConn();
        if (!$conn) {
            return ['rules' => [], 'charges' => []];
        }

        $clientId = intval($clientId);

        $rules = [];
        $resRules = $conn->query("SELECT id, min_investment, max_investment, fee_mode, fee_label, percentage_value, fixed_value, fixed_currency_code, priority
            FROM client_fee_rules
            WHERE client_id = $clientId AND is_active = 1
            ORDER BY priority ASC, id ASC");
        if ($resRules) {
            while ($row = $resRules->fetch_assoc()) {
                $rules[] = [
                    'id' => (int)$row['id'],
                    'min_investment' => (float)$row['min_investment'],
                    'max_investment' => $row['max_investment'] === null ? null : (float)$row['max_investment'],
                    'fee_mode' => $row['fee_mode'],
                    'fee_label' => $row['fee_label'],
                    'percentage_value' => (float)$row['percentage_value'],
                    'fixed_value' => (float)$row['fixed_value'],
                    'fixed_currency_code' => $row['fixed_currency_code'] ?: 'USD',
                    'priority' => (int)$row['priority'],
                ];
            }
        }

        $charges = [];
        $resCharges = $conn->query("SELECT id, concept_id, concept_name, amount, currency_code
            FROM client_fee_charges
            WHERE client_id = $clientId AND is_active = 1
            ORDER BY id ASC");
        if ($resCharges) {
            while ($row = $resCharges->fetch_assoc()) {
                $charges[] = [
                    'id' => (int)$row['id'],
                    'concept_id' => $row['concept_id'] === null ? null : (int)$row['concept_id'],
                    'concept_name' => $row['concept_name'],
                    'amount' => (float)$row['amount'],
                    'currency_code' => $row['currency_code'] ?: 'USD',
                ];
            }
        }

        $conn->close();
        return ['rules' => $rules, 'charges' => $charges];
    }

    public static function ctrSaveClientFeeConfig($clientId, $rules, $charges)
    {
        $conn = self::getConn();
        if (!$conn) {
            return ['success' => false, 'message' => 'DB connection failed'];
        }

        $clientId = intval($clientId);
        if ($clientId <= 0) {
            $conn->close();
            return ['success' => false, 'message' => 'client_id inválido'];
        }

        $rules = self::normalizeRules($rules, 'USD');
        $charges = self::normalizeCharges($charges, 'USD');

        $conn->begin_transaction();
        try {
            self::runOrThrow($conn, "UPDATE client_fee_rules SET is_active = 0 WHERE client_id = $clientId");
            self::runOrThrow($conn, "UPDATE client_fee_charges SET is_active = 0 WHERE client_id = $clientId");

            foreach ($rules as $row) {
                $min = $row['min_investment'];
                $max = $row['max_investment'] === null ? 'NULL' : $row['max_investment'];
                $mode = $conn->real_escape_string($row['fee_mode']);
                $feeLabel = isset($row['fee_label']) && $row['fee_label'] !== null
                    ? "'" . $conn->real_escape_string($row['fee_label']) . "'"
                    : 'NULL';
                $percentage = $row['percentage_value'];
                $fixed = $row['fixed_value'];
                $fixedCurrencyCode = $conn->real_escape_string($row['fixed_currency_code']);
                $priority = (int)$row['priority'];

                self::runOrThrow($conn, "INSERT INTO client_fee_rules
                    (client_id, min_investment, max_investment, fee_mode, fee_label, percentage_value, fixed_value, fixed_currency_code, priority, is_active)
                    VALUES
                    ($clientId, $min, $max, '$mode', $feeLabel, $percentage, $fixed, '$fixedCurrencyCode', $priority, 1)");
            }

            foreach ($charges as $charge) {
                $conceptId = $charge['concept_id'] === null ? 'NULL' : (int)$charge['concept_id'];
                $conceptName = $conn->real_escape_string($charge['concept_name']);
                $amount = $charge['amount'];
                $currencyCode = $conn->real_escape_string($charge['currency_code']);

                self::runOrThrow($conn, "INSERT INTO client_fee_charges
                    (client_id, concept_id, concept_name, amount, currency_code, is_active)
                    VALUES
                    ($clientId, $conceptId, '$conceptName', $amount, '$currencyCode', 1)");
            }

            $conn->commit();
            $conn->close();
            return ['success' => true];
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $conn->close();
            return ['success' => false, 'message' => $message];
        }
    }

    public static function ctrGetMixFeeConfig($mixId)
    {
        $conn = self::getConn();
        if (!$conn) {
            return ['rules' => [], 'charges' => []];
        }

        $mixId = intval($mixId);

        $rules = [];
        $resRules = $conn->query("SELECT id, min_investment, max_investment, fee_mode, fee_label, percentage_value, fixed_value,
            fixed_currency_code, fixed_usd_per_unit_snapshot, priority
            FROM mmre_fee_rules
            WHERE mediamixrealestate_id = $mixId AND is_active = 1
            ORDER BY priority ASC, id ASC");
        if ($resRules) {
            while ($row = $resRules->fetch_assoc()) {
                $rules[] = [
                    'id' => (int)$row['id'],
                    'min_investment' => (float)$row['min_investment'],
                    'max_investment' => $row['max_investment'] === null ? null : (float)$row['max_investment'],
                    'fee_mode' => $row['fee_mode'],
                    'fee_label' => $row['fee_label'],
                    'percentage_value' => (float)$row['percentage_value'],
                    'fixed_value' => (float)$row['fixed_value'],
                    'fixed_currency_code' => $row['fixed_currency_code'] ?: 'USD',
                    'fixed_usd_per_unit_snapshot' => isset($row['fixed_usd_per_unit_snapshot']) ? (float)$row['fixed_usd_per_unit_snapshot'] : 1.0,
                    'priority' => (int)$row['priority'],
                ];
            }
        }

        $charges = [];
        $resCharges = $conn->query("SELECT id, concept_id, concept_name, amount, currency_code, usd_per_unit_snapshot
            FROM mmre_fee_charges
            WHERE mediamixrealestate_id = $mixId AND is_active = 1
            ORDER BY id ASC");
        if ($resCharges) {
            while ($row = $resCharges->fetch_assoc()) {
                $charges[] = [
                    'id' => (int)$row['id'],
                    'concept_id' => $row['concept_id'] === null ? null : (int)$row['concept_id'],
                    'concept_name' => $row['concept_name'],
                    'amount' => (float)$row['amount'],
                    'currency_code' => $row['currency_code'] ?: 'USD',
                    'usd_per_unit_snapshot' => isset($row['usd_per_unit_snapshot']) ? (float)$row['usd_per_unit_snapshot'] : 1.0,
                ];
            }
        }

        $mixMeta = [
            'currency_code' => 'USD',
            'currency_usd_per_unit_snapshot' => 1.0,
        ];
        $mixRes = $conn->query("SELECT currency, currency_usd_per_unit_snapshot FROM mediamixrealestates WHERE id = $mixId LIMIT 1");
        if ($mixRes && $mixRes->num_rows > 0) {
            $mixRow = $mixRes->fetch_assoc();
            $mixMeta['currency_code'] = $mixRow['currency'] ?: 'USD';
            $mixMeta['currency_usd_per_unit_snapshot'] = $mixRow['currency_usd_per_unit_snapshot'] !== null
                ? (float)$mixRow['currency_usd_per_unit_snapshot']
                : 1.0;
        }

        $conn->close();
        return ['rules' => $rules, 'charges' => $charges, 'mix_meta' => $mixMeta];
    }

    public static function ctrSaveMixFeeConfig($mixId, $rules, $charges)
    {
        $conn = self::getConn();
        if (!$conn) {
            return ['success' => false, 'message' => 'DB connection failed'];
        }

        $mixId = intval($mixId);
        if ($mixId <= 0) {
            $conn->close();
            return ['success' => false, 'message' => 'mix_id inválido'];
        }

        $mixRes = $conn->query("SELECT currency FROM mediamixrealestates WHERE id = $mixId LIMIT 1");
        $mixCurrencyCode = 'USD';
        if ($mixRes && $mixRes->num_rows > 0) {
            $mixRow = $mixRes->fetch_assoc();
            $mixCurrencyCode = strtoupper(trim((string)($mixRow['currency'] ?? 'USD')));
        }
        $mixCurrencyRate = Currencies_Controller::ctrGetLatestRate($mixCurrencyCode);
        $mixCurrencyUsdSnapshot = $mixCurrencyRate ? (float)$mixCurrencyRate['usd_per_unit'] : 1.0;

        $rules = self::normalizeRules($rules, $mixCurrencyCode);
        $charges = self::normalizeCharges($charges, $mixCurrencyCode);

        $conn->begin_transaction();
        try {
            self::runOrThrow($conn, "UPDATE mmre_fee_rules SET is_active = 0 WHERE mediamixrealestate_id = $mixId");
            self::runOrThrow($conn, "UPDATE mmre_fee_charges SET is_active = 0 WHERE mediamixrealestate_id = $mixId");
            self::runOrThrow($conn, "UPDATE mediamixrealestates SET currency_usd_per_unit_snapshot = $mixCurrencyUsdSnapshot WHERE id = $mixId");

            foreach ($rules as $row) {
                $min = $row['min_investment'];
                $max = $row['max_investment'] === null ? 'NULL' : $row['max_investment'];
                $mode = $conn->real_escape_string($row['fee_mode']);
                $feeLabel = isset($row['fee_label']) && $row['fee_label'] !== null
                    ? "'" . $conn->real_escape_string($row['fee_label']) . "'"
                    : 'NULL';
                $percentage = $row['percentage_value'];
                $fixed = $row['fixed_value'];
                $fixedCurrencyCode = strtoupper(trim((string)$row['fixed_currency_code']));
                $fixedCurrencyCodeEsc = $conn->real_escape_string($fixedCurrencyCode);
                $fixedRate = Currencies_Controller::ctrGetLatestRate($fixedCurrencyCode);
                $fixedUsdSnapshot = $fixedRate ? (float)$fixedRate['usd_per_unit'] : 1.0;
                $priority = (int)$row['priority'];

                 self::runOrThrow($conn, "INSERT INTO mmre_fee_rules
                          (mediamixrealestate_id, min_investment, max_investment, fee_mode, fee_label, percentage_value, fixed_value,
                     fixed_currency_code, fixed_usd_per_unit_snapshot, priority, is_active)
                    VALUES
                          ($mixId, $min, $max, '$mode', $feeLabel, $percentage, $fixed,
                     '$fixedCurrencyCodeEsc', $fixedUsdSnapshot, $priority, 1)");
            }

            foreach ($charges as $charge) {
                $conceptId = $charge['concept_id'] === null ? 'NULL' : (int)$charge['concept_id'];
                $conceptName = $conn->real_escape_string($charge['concept_name']);
                $amount = $charge['amount'];
                $currencyCode = strtoupper(trim((string)$charge['currency_code']));
                $currencyCodeEsc = $conn->real_escape_string($currencyCode);
                $rate = Currencies_Controller::ctrGetLatestRate($currencyCode);
                $usdSnapshot = $rate ? (float)$rate['usd_per_unit'] : 1.0;

                self::runOrThrow($conn, "INSERT INTO mmre_fee_charges
                    (mediamixrealestate_id, concept_id, concept_name, amount, currency_code, usd_per_unit_snapshot, is_active)
                    VALUES
                    ($mixId, $conceptId, '$conceptName', $amount, '$currencyCodeEsc', $usdSnapshot, 1)");
            }

            $conn->commit();
            $conn->close();
            return ['success' => true];
        } catch (Exception $e) {
            $conn->rollback();
            $message = $e->getMessage();
            $conn->close();
            return ['success' => false, 'message' => $message];
        }
    }

    public static function ctrSyncMixFromClient($mixId, $clientId)
    {
        $clientConfig = self::ctrGetClientFeeConfig($clientId);
        return self::ctrSaveMixFeeConfig($mixId, $clientConfig['rules'], $clientConfig['charges']);
    }

    public static function ctrCalculateAgencyFee(
        $investmentTotal,
        $rules,
        $charges,
        $legacyFee = 0,
        $legacyFeeType = 'percentage',
        $targetCurrencyCode = 'USD',
        $targetUsdPerUnitSnapshot = 1.0
    )
    {
        $investmentTotal = floatval($investmentTotal);
        $targetCurrencyCode = strtoupper(trim((string)$targetCurrencyCode));
        $targetUsdPerUnitSnapshot = floatval($targetUsdPerUnitSnapshot);
        if ($targetUsdPerUnitSnapshot <= 0) {
            $targetRate = Currencies_Controller::ctrGetLatestRate($targetCurrencyCode);
            $targetUsdPerUnitSnapshot = $targetRate ? (float)$targetRate['usd_per_unit'] : 1.0;
        }

        $rules = self::normalizeRules($rules, $targetCurrencyCode);
        $charges = self::normalizeCharges($charges, $targetCurrencyCode);

        $matchingRules = [];
        foreach ($rules as $rule) {
            $maxOk = $rule['max_investment'] === null || $investmentTotal <= $rule['max_investment'];
            if ($investmentTotal >= $rule['min_investment'] && $maxOk) {
                $matchingRules[] = $rule;
            }
        }

        if (count($matchingRules) === 0 && count($rules) > 0) {
            $matchingRules[] = $rules[count($rules) - 1];
        }

        $baseFee = 0.0;
        $baseLabel = 'Sin regla';
        $fixedComponentConverted = 0.0;
        $fixedComponentOriginal = null;
        $ruleComponents = [];

        if (count($matchingRules) > 0) {
            $baseLabel = count($matchingRules) > 1 ? 'Reglas combinadas' : 'Regla aplicada';
            foreach ($matchingRules as $idx => $rule) {
                $mode = $rule['fee_mode'] ?? 'percentage';
                $componentAmount = 0.0;
                $component = [
                    'index' => $idx + 1,
                    'fee_mode' => $mode,
                    'fee_label' => $rule['fee_label'] ?? null,
                    'converted_amount' => 0.0,
                    'percentage_value' => (float)($rule['percentage_value'] ?? 0),
                    'fixed_value' => (float)($rule['fixed_value'] ?? 0),
                    'fixed_currency_code' => strtoupper($rule['fixed_currency_code'] ?? $targetCurrencyCode),
                ];

                if ($mode === 'fixed') {
                    $fixedValue = (float)$rule['fixed_value'];
                    $fixedCurrency = strtoupper($rule['fixed_currency_code'] ?? $targetCurrencyCode);
                    $fixedRateSnapshot = isset($rule['fixed_usd_per_unit_snapshot'])
                        ? floatval($rule['fixed_usd_per_unit_snapshot'])
                        : 0.0;
                    if ($fixedRateSnapshot <= 0) {
                        $fixedRate = Currencies_Controller::ctrGetLatestRate($fixedCurrency);
                        $fixedRateSnapshot = $fixedRate ? (float)$fixedRate['usd_per_unit'] : 1.0;
                    }
                    $componentAmount = ($fixedValue * $fixedRateSnapshot) / $targetUsdPerUnitSnapshot;
                    $fixedComponentConverted += $componentAmount;
                    $component['fixed_usd_per_unit_snapshot'] = $fixedRateSnapshot;
                    $component['original'] = [
                        'amount' => $fixedValue,
                        'currency_code' => $fixedCurrency,
                        'usd_per_unit_snapshot' => $fixedRateSnapshot,
                    ];

                    if ($fixedComponentOriginal === null) {
                        $fixedComponentOriginal = $component['original'];
                    }
                } else {
                    // Cualquier modo no-fijo se trata como porcentual para robustez.
                    $pct = (float)$rule['percentage_value'];
                    $componentAmount = $investmentTotal * ($pct / 100);
                }

                $component['converted_amount'] = round($componentAmount, 2);
                $baseFee += $componentAmount;
                $ruleComponents[] = $component;
            }
        } else {
            $legacyFee = floatval($legacyFee);
            if ($legacyFeeType === 'fixed') {
                $baseFee = $legacyFee;
                $baseLabel = 'Fee histórico fijo';
                $ruleComponents[] = [
                    'index' => 1,
                    'fee_mode' => 'fixed',
                    'fee_label' => null,
                    'converted_amount' => round($legacyFee, 2),
                    'percentage_value' => 0,
                    'fixed_value' => $legacyFee,
                    'fixed_currency_code' => $targetCurrencyCode,
                ];
            } else {
                $baseFee = $investmentTotal * ($legacyFee / 100);
                $baseLabel = 'Fee histórico porcentual';
                $ruleComponents[] = [
                    'index' => 1,
                    'fee_mode' => 'percentage',
                    'fee_label' => null,
                    'converted_amount' => round($baseFee, 2),
                    'percentage_value' => $legacyFee,
                    'fixed_value' => 0,
                    'fixed_currency_code' => $targetCurrencyCode,
                ];
            }
        }

        $chargesTotal = 0.0;
        $chargesConverted = [];
        foreach ($charges as $charge) {
            $amount = floatval($charge['amount']);
            $currencyCode = strtoupper($charge['currency_code'] ?? $targetCurrencyCode);
            $usdSnapshot = isset($charge['usd_per_unit_snapshot']) ? floatval($charge['usd_per_unit_snapshot']) : 0.0;
            if ($usdSnapshot <= 0) {
                $rate = Currencies_Controller::ctrGetLatestRate($currencyCode);
                $usdSnapshot = $rate ? (float)$rate['usd_per_unit'] : 1.0;
            }

            $converted = ($amount * $usdSnapshot) / $targetUsdPerUnitSnapshot;
            $chargesTotal += $converted;
            $charge['converted_amount'] = $converted;
            $charge['usd_per_unit_snapshot'] = $usdSnapshot;
            $chargesConverted[] = $charge;
        }

        return [
            'selected_rule' => count($matchingRules) > 0 ? $matchingRules[0] : null,
            'applied_rules' => $matchingRules,
            'rule_components' => $ruleComponents,
            'base_fee' => round($baseFee, 2),
            'fixed_component_converted' => round($fixedComponentConverted, 2),
            'fixed_component_original' => $fixedComponentOriginal,
            'base_label' => $baseLabel,
            'charges' => $chargesConverted,
            'charges_total' => round($chargesTotal, 2),
            'total_fee' => round($baseFee + $chargesTotal, 2),
            'target_currency_code' => $targetCurrencyCode,
            'target_usd_per_unit_snapshot' => $targetUsdPerUnitSnapshot,
        ];
    }
}
