<?php

class Currencies_Controller
{
    public static function ctrGetConn()
    {
        $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
        if ($conn->connect_error) {
            return null;
        }
        $conn->set_charset('utf8mb4');
        self::ctrEnsureSchema($conn);
        return $conn;
    }

    public static function ctrEnsureSchema($conn = null)
    {
        $closeAfter = false;
        if (!$conn) {
            $conn = new mysqli('srv1013.hstgr.io', 'u961992735_plataforma', 'Peru+*963.', 'u961992735_plataforma', 3306);
            if ($conn->connect_error) {
                return false;
            }
            $conn->set_charset('utf8mb4');
            $closeAfter = true;
        }

        $conn->query("CREATE TABLE IF NOT EXISTS currencies (
            code VARCHAR(10) PRIMARY KEY,
            name VARCHAR(80) NOT NULL,
            symbol VARCHAR(10) NOT NULL,
            decimals INT NOT NULL DEFAULT 2,
            active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $conn->query("CREATE TABLE IF NOT EXISTS currency_exchange_rates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            currency_code VARCHAR(10) NOT NULL,
            usd_per_unit DECIMAL(18,8) NOT NULL,
            effective_at DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_currency_effective (currency_code, effective_at),
            CONSTRAINT fk_currency_rates_currency FOREIGN KEY (currency_code) REFERENCES currencies(code)
                ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        self::ctrSeedDefaults($conn);

        if ($closeAfter) {
            $conn->close();
        }

        return true;
    }

    private static function ctrSeedDefaults($conn)
    {
        $defaults = [
            ['USD', 'Dolar estadounidense', 'USD', 2, 1.00000000],
            ['PEN', 'Sol peruano', 'PEN', 2, 0.27000000],
            ['CLP', 'Peso chileno', 'CLP', 0, 0.00105000],
        ];

        foreach ($defaults as $row) {
            $code = $conn->real_escape_string($row[0]);
            $name = $conn->real_escape_string($row[1]);
            $symbol = $conn->real_escape_string($row[2]);
            $decimals = intval($row[3]);
            $rate = floatval($row[4]);

            $conn->query("INSERT INTO currencies (code, name, symbol, decimals, active)
                VALUES ('$code', '$name', '$symbol', $decimals, 1)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    symbol = VALUES(symbol),
                    decimals = VALUES(decimals)");

            $chk = $conn->query("SELECT id FROM currency_exchange_rates WHERE currency_code = '$code' LIMIT 1");
            if ($chk && $chk->num_rows === 0) {
                $conn->query("INSERT INTO currency_exchange_rates (currency_code, usd_per_unit, effective_at)
                    VALUES ('$code', $rate, NOW())");
            }
        }
    }

    public static function ctrGetCurrencies($onlyActive = false)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return [];
        }

        $where = $onlyActive ? 'WHERE c.active = 1' : '';
        $sql = "SELECT c.code, c.name, c.symbol, c.decimals, c.active,
                    (
                        SELECT cer.usd_per_unit
                        FROM currency_exchange_rates cer
                        WHERE cer.currency_code = c.code
                        ORDER BY cer.effective_at DESC, cer.id DESC
                        LIMIT 1
                    ) AS current_usd_per_unit,
                    (
                        SELECT cer.effective_at
                        FROM currency_exchange_rates cer
                        WHERE cer.currency_code = c.code
                        ORDER BY cer.effective_at DESC, cer.id DESC
                        LIMIT 1
                    ) AS current_effective_at
                FROM currencies c
                $where
                ORDER BY c.code ASC";

        $res = $conn->query($sql);
        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = [
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'symbol' => $row['symbol'],
                    'decimals' => intval($row['decimals']),
                    'active' => intval($row['active']),
                    'current_usd_per_unit' => $row['current_usd_per_unit'] === null ? null : floatval($row['current_usd_per_unit']),
                    'current_effective_at' => $row['current_effective_at'],
                ];
            }
        }

        $conn->close();
        return $rows;
    }

    public static function ctrGetCurrencyByCode($code)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return null;
        }

        $code = $conn->real_escape_string(strtoupper(trim((string)$code)));
        $res = $conn->query("SELECT code, name, symbol, decimals, active FROM currencies WHERE code = '$code' LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;
        if (!$row) {
            $conn->close();
            return null;
        }

        $rate = self::ctrGetLatestRateByCode($conn, $code);
        $conn->close();

        return [
            'code' => $row['code'],
            'name' => $row['name'],
            'symbol' => $row['symbol'],
            'decimals' => intval($row['decimals']),
            'active' => intval($row['active']),
            'current_usd_per_unit' => $rate ? floatval($rate['usd_per_unit']) : null,
            'current_effective_at' => $rate ? $rate['effective_at'] : null,
        ];
    }

    private static function ctrGetLatestRateByCode($conn, $code, $asOf = null)
    {
        $code = $conn->real_escape_string(strtoupper(trim((string)$code)));
        $whereAsOf = '';
        if ($asOf) {
            $asOfEsc = $conn->real_escape_string($asOf);
            $whereAsOf = " AND effective_at <= '$asOfEsc'";
        }

        $sql = "SELECT usd_per_unit, effective_at
                FROM currency_exchange_rates
                WHERE currency_code = '$code' $whereAsOf
                ORDER BY effective_at DESC, id DESC
                LIMIT 1";
        $res = $conn->query($sql);
        return ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
    }

    public static function ctrGetLatestRate($code, $asOf = null)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return null;
        }

        $row = self::ctrGetLatestRateByCode($conn, $code, $asOf);
        $conn->close();

        if (!$row) {
            return null;
        }
        return [
            'usd_per_unit' => floatval($row['usd_per_unit']),
            'effective_at' => $row['effective_at'],
        ];
    }

    public static function ctrGetRateHistory($code, $limit = 50)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return [];
        }

        $code = $conn->real_escape_string(strtoupper(trim((string)$code)));
        $limit = max(1, min(500, intval($limit)));

        $res = $conn->query("SELECT id, currency_code, usd_per_unit, effective_at, created_at
            FROM currency_exchange_rates
            WHERE currency_code = '$code'
            ORDER BY effective_at DESC, id DESC
            LIMIT $limit");

        $rows = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $rows[] = [
                    'id' => intval($row['id']),
                    'currency_code' => $row['currency_code'],
                    'usd_per_unit' => floatval($row['usd_per_unit']),
                    'effective_at' => $row['effective_at'],
                    'created_at' => $row['created_at'],
                ];
            }
        }

        $conn->close();
        return $rows;
    }

    public static function ctrSaveCurrency($payload)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return ['success' => false, 'message' => 'DB connection failed'];
        }

        $code = strtoupper(trim((string)($payload['code'] ?? '')));
        $name = trim((string)($payload['name'] ?? ''));
        $symbol = trim((string)($payload['symbol'] ?? ''));
        $decimals = intval($payload['decimals'] ?? 2);
        $active = isset($payload['active']) ? intval($payload['active']) : 1;
        $usdPerUnit = floatval($payload['usd_per_unit'] ?? 0);
        $effectiveAt = trim((string)($payload['effective_at'] ?? ''));

        if (!preg_match('/^[A-Z]{3,10}$/', $code)) {
            $conn->close();
            return ['success' => false, 'message' => 'Código de moneda inválido'];
        }
        if ($name === '' || $symbol === '') {
            $conn->close();
            return ['success' => false, 'message' => 'Nombre y símbolo son obligatorios'];
        }
        if ($decimals < 0 || $decimals > 8) {
            $conn->close();
            return ['success' => false, 'message' => 'Decimales inválidos'];
        }
        if ($usdPerUnit <= 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Tipo de cambio inválido'];
        }

        if ($effectiveAt === '') {
            $effectiveAt = date('Y-m-d H:i:s');
        }

        $escCode = $conn->real_escape_string($code);
        $escName = $conn->real_escape_string($name);
        $escSymbol = $conn->real_escape_string($symbol);
        $escEffectiveAt = $conn->real_escape_string($effectiveAt);

        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO currencies (code, name, symbol, decimals, active)
                VALUES ('$escCode', '$escName', '$escSymbol', $decimals, $active)
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    symbol = VALUES(symbol),
                    decimals = VALUES(decimals),
                    active = VALUES(active)");

            $conn->query("INSERT INTO currency_exchange_rates (currency_code, usd_per_unit, effective_at)
                VALUES ('$escCode', $usdPerUnit, '$escEffectiveAt')");

            $conn->commit();
            $conn->close();
            return ['success' => true];
        } catch (Exception $e) {
            $conn->rollback();
            $msg = $e->getMessage();
            $conn->close();
            return ['success' => false, 'message' => $msg];
        }
    }

    public static function ctrAddRate($code, $usdPerUnit, $effectiveAt = null)
    {
        $conn = self::ctrGetConn();
        if (!$conn) {
            return ['success' => false, 'message' => 'DB connection failed'];
        }

        $code = $conn->real_escape_string(strtoupper(trim((string)$code)));
        $usdPerUnit = floatval($usdPerUnit);
        if ($usdPerUnit <= 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Tipo de cambio inválido'];
        }

        if (!$effectiveAt) {
            $effectiveAt = date('Y-m-d H:i:s');
        }
        $effectiveAt = $conn->real_escape_string($effectiveAt);

        $conn->query("INSERT INTO currency_exchange_rates (currency_code, usd_per_unit, effective_at)
            VALUES ('$code', $usdPerUnit, '$effectiveAt')");

        if ($conn->errno) {
            $msg = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => $msg];
        }

        $conn->close();
        return ['success' => true];
    }

    public static function ctrConvertAmount($amount, $fromCode, $toCode, $asOf = null)
    {
        $amount = floatval($amount);
        if (strtoupper($fromCode) === strtoupper($toCode)) {
            return [
                'success' => true,
                'amount' => $amount,
                'from_usd_per_unit' => 1.0,
                'to_usd_per_unit' => 1.0,
            ];
        }

        $fromRate = self::ctrGetLatestRate($fromCode, $asOf);
        $toRate = self::ctrGetLatestRate($toCode, $asOf);

        if (!$fromRate || !$toRate || $fromRate['usd_per_unit'] <= 0 || $toRate['usd_per_unit'] <= 0) {
            return ['success' => false, 'message' => 'No se encontró tipo de cambio para conversión'];
        }

        $amountUsd = $amount * floatval($fromRate['usd_per_unit']);
        $amountTo = $amountUsd / floatval($toRate['usd_per_unit']);

        return [
            'success' => true,
            'amount' => $amountTo,
            'from_usd_per_unit' => floatval($fromRate['usd_per_unit']),
            'to_usd_per_unit' => floatval($toRate['usd_per_unit']),
            'as_of' => $asOf,
        ];
    }

    public static function ctrFormatAmount($amount, $currencyCode)
    {
        $currency = self::ctrGetCurrencyByCode($currencyCode);
        $decimals = $currency ? intval($currency['decimals']) : 2;
        return number_format(floatval($amount), $decimals);
    }
}
