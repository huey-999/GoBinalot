<?php
/**
 * System settings helper.
 * Provides getSystemSetting() with per-request static caching.
 */

function getSystemSetting($key, $default = '')
{
    static $cache = null;

    global $conn;

    if ($cache === null) {
        $cache = [];
        if ($conn instanceof PDO) {
            try {
                $stmt = $conn->query("SELECT setting_key, setting_value FROM system_settings");
                if ($stmt) {
                    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        $cache[$row['setting_key']] = $row['setting_value'];
                    }
                }
            } catch (\PDOException $e) {
                // Table may not exist yet; silently fall back to defaults
            }
        }
    }

    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}
