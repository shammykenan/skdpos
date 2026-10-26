<?php
// app/models/app_settings.php

/**
 * Get a setting value by key
 */
function getSetting(PDO $pdo, string $key): string {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM app_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['setting_value'] : '';
    } catch (PDOException $e) {
        error_log("Error in getSetting: " . $e->getMessage());
        return '';
    }
}

/**
 * Set a setting value by key
 */
function setSetting(PDO $pdo, string $key, string $value): bool {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO app_settings (setting_key, setting_value)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP
        ");
        return $stmt->execute([$key, $value, $value]);
    } catch (PDOException $e) {
        error_log("Error in setSetting: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all settings as key-value array
 */
function getAllSettings(PDO $pdo): array {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM app_settings");
        $settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        error_log("Error in getAllSettings: " . $e->getMessage());
        return [];
    }
}
