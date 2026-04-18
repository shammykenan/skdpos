<?php
/**
 * BusinessInfo Model
 * Handles CRUD for the business_info table.
 * There is always exactly one row (info_id = 1).
 */

/**
 * Fetch the single business_info row.
 * Returns an associative array, or a safe default array if the table is empty.
 */
function getBusinessInfo(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM business_info WHERE info_id = 1 LIMIT 1");
    $row  = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [
            'info_id'              => 1,
            'business_name'        => '',
            'address'              => '',
            'tin'                  => '',
            'vat_registered'       => 0,
            'vat_rate'             => 12.00,
            'machine_serial_no'    => '',
            'ptu_number'           => '',
            'accreditation_no'     => '',
            'min_pwd_discount'     => 20.00,
            'min_senior_discount'  => 20.00,
        ];
    }

    return $row;
}

/**
 * Save (upsert) the business_info row.
 * Always targets info_id = 1.
 *
 * @param PDO   $pdo
 * @param array $data  Keys must match column names.
 * @return bool
 */
function saveBusinessInfo(PDO $pdo, array $data): bool
{
    $stmt = $pdo->prepare("
        INSERT INTO business_info (
            info_id,
            business_name,
            address,
            tin,
            vat_registered,
            vat_rate,
            machine_serial_no,
            ptu_number,
            accreditation_no,
            min_pwd_discount,
            min_senior_discount
        ) VALUES (
            1,
            :business_name,
            :address,
            :tin,
            :vat_registered,
            :vat_rate,
            :machine_serial_no,
            :ptu_number,
            :accreditation_no,
            :min_pwd_discount,
            :min_senior_discount
        )
        ON DUPLICATE KEY UPDATE
            business_name       = VALUES(business_name),
            address             = VALUES(address),
            tin                 = VALUES(tin),
            vat_registered      = VALUES(vat_registered),
            vat_rate            = VALUES(vat_rate),
            machine_serial_no   = VALUES(machine_serial_no),
            ptu_number          = VALUES(ptu_number),
            accreditation_no    = VALUES(accreditation_no),
            min_pwd_discount    = VALUES(min_pwd_discount),
            min_senior_discount = VALUES(min_senior_discount)
    ");

    return $stmt->execute([
        ':business_name'       => trim($data['business_name']       ?? ''),
        ':address'             => trim($data['address']             ?? ''),
        ':tin'                 => trim($data['tin']                 ?? ''),
        ':vat_registered'      => isset($data['vat_registered']) ? (int)$data['vat_registered'] : 0,
        ':vat_rate'            => isset($data['vat_rate']) ? (float)$data['vat_rate'] : 12.00,
        ':machine_serial_no'   => trim($data['machine_serial_no']   ?? ''),
        ':ptu_number'          => trim($data['ptu_number']          ?? ''),
        ':accreditation_no'    => trim($data['accreditation_no']    ?? ''),
        ':min_pwd_discount'    => isset($data['min_pwd_discount'])  ? (float)$data['min_pwd_discount']  : 20.00,
        ':min_senior_discount' => isset($data['min_senior_discount']) ? (float)$data['min_senior_discount'] : 20.00,
    ]);
}
