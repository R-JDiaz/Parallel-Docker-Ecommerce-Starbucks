<?php
require_once(__DIR__ . '/../function.php');

$addresses = [];

// Insert Philippines if not exists
$countryId = insertDataAndGetId(
    $con,
    'country',
    ['name'],
    [['Philippines']],
    ['name']
);

// List of [Province, City, Postal Code]
$cityData = [
    ['Pampanga', 'Angeles City', '2009'],
    ['Rizal', 'Antipolo', '1870'],
    ['Negros Occidental', 'Bacolod', '6100'],
    ['Benguet', 'Baguio', '2600'],
    ['Batangas', 'Batangas City', '4200'],
    ['Agusan del Norte', 'Butuan', '8600'],
    ['Cebu', 'Cebu City', '6000'],
    ['Misamis Oriental', 'Cagayan de Oro', '9000'],
    ['Metro Manila', 'Caloocan', '1400'],
    ['Maguindanao del Norte', 'Cotabato City', '9600'],
    ['Davao del Sur', 'Davao City', '8000'],
    ['Pangasinan', 'Dagupan', '2400'],
    ['Cavite', 'Dasmariñas', '4114'],
    ['Negros Occidental', 'Escalante City', '6124'],
    ['South Cotabato', 'General Santos', '9500'],
    ['Misamis Oriental', 'Gingoog City', '9014'],
    ['Iloilo', 'Iloilo City', '5000'],
    ['Cavite', 'Imus', '4103'],
    ['Metro Manila', 'Las Piñas', '1740'],
    ['Batangas', 'Lipa City', '4217'],
    ['Quezon', 'Lucena', '4301'],
    ['Metro Manila', 'Manila', '1000'],
    ['Metro Manila', 'Makati', '1226'],
    ['Metro Manila', 'Malabon', '1470'],
    ['Metro Manila', 'Mandaluyong', '1550'],
    ['Metro Manila', 'Marikina', '1800'],
    ['Metro Manila', 'Muntinlupa', '1770'],
    ['Metro Manila', 'Navotas', '1485'],
    ['Camarines Sur', 'Naga City', '4400'],
    ['Zambales', 'Olongapo City', '2200'],
    ['Metro Manila', 'Pasig', '1600'],
    ['Metro Manila', 'Pasay', '1300'],
    ['Palawan', 'Puerto Princesa', '5300'],
    ['Metro Manila', 'Quezon City', '1100'],
    ['Rizal', 'Rizal', '1900'],
    ['Pangasinan', 'San Carlos City', '2420'],
    ['Pampanga', 'San Fernando (Pampanga)', '2000'],
    ['Laguna', 'Santa Rosa', '4026'],
    ['Isabela', 'Santiago City', '3311'],
    ['Metro Manila', 'Taguig', '1630'],
    ['Leyte', 'Tacloban', '6500'],
    ['Tarlac', 'Tarlac City', '2300'],
    ['Pangasinan', 'Urdaneta City', '2428'],
    ['Metro Manila', 'Valenzuela', '1440'],
    ['Zamboanga del Sur', 'Zamboanga City', '7000']
];

// Insert provinces and cities
$cityIds = [];
foreach ($cityData as $data) {
    [$provinceName, $cityName, $postalCode] = $data;

    // Insert province if not exists
    $provinceId = insertDataAndGetId(
        $con,
        'province',
        ['country_id', 'name'],
        [[$countryId, $provinceName]],
        ['country_id', 'name']
    );

    // Insert city
    $cityId = insertDataAndGetId(
        $con,
        'city',
        ['province_id', 'name', 'postal_code'],
        [[$provinceId, $cityName, $postalCode]],
        ['province_id', 'name']
    );

    // Store ID for reference (optional)
    $cityIds[$cityName] = [$provinceId, $cityId];
}

// Example user address (Juan Cruz → Makati)
$userId = getIdByFullName($con, 'user', 'Juan', 'Cruz');
if ($userId && isset($cityIds['Makati'])) {
    $addresses[] = ['user', $userId, '123 Mango St', $countryId, $cityIds['Makati'][0], $cityIds['Makati'][1]];
}

// Example admin address (Maria Santos → Pasig)
$adminId = getIdByFullName($con, 'admin', 'Maria', 'Santos');
if ($adminId && isset($cityIds['Pasig'])) {
    $addresses[] = ['admin', $adminId, '456 Admin Ave', $countryId, $cityIds['Pasig'][0], $cityIds['Pasig'][1]];
}

// Insert all addresses
insertData(
    $con,
    'address',
    ['addressable_type', 'addressable_id', 'street', 'country_id', 'province_id', 'city_id'],
    $addresses,
    ['addressable_type', 'addressable_id']
);

echo "✅ Address seeding completed with all major PH cities.<br>";
?>
