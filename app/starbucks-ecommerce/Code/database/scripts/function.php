<?php

//Create table (Creating only onced, skip if exists) Update changes such as ADDED COLLUMN AND REMOVED COLLUMN
//Insertdata (Only once insertion just for Pre define fields not for object)
function createTable($con, $name, $sql) {
    try {
        mysqli_query($con, $sql);
        echo "✅ TABLE '$name' created successfully.<br>";
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "⚠️ TABLE '$name' already exists. Checking for updates...<br>";

            // Step 1: Get current columns from DB
            $result = mysqli_query($con, "DESCRIBE `$name`");
            $existingColumns = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $existingColumns[] = $row['Field'];
            }

            // Step 2: Extract columns from the SQL definition
            $lines = explode("\n", $sql);
            $definedColumns = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (
                    $line === '' ||
                    stripos($line, 'PRIMARY KEY') !== false ||
                    stripos($line, 'FOREIGN KEY') !== false ||
                    stripos($line, 'UNIQUE') === 0 ||
                    stripos($line, 'KEY') === 0 ||
                    stripos($line, 'INDEX') === 0 ||
                    stripos($line, 'CONSTRAINT') === 0 ||
                    stripos($line, 'CREATE TABLE') === 0 ||
                    $line[0] === ')'
                    ) {
                        continue;
                    }


                $parts = preg_split('/\s+/', $line, 2);
                if (count($parts) < 2) continue;

                $column = trim($parts[0], '` ,');
                $definition = rtrim(trim($parts[1]), ',');
                $definedColumns[$column] = $definition;

                // Add missing columns
                if (!in_array($column, $existingColumns)) {
                    $alterSql = "ALTER TABLE `$name` ADD COLUMN `$column` $definition";
                    if (mysqli_query($con, $alterSql)) {
                        echo "✅ Added column '$column' to '$name'.<br>";
                    } else {
                        echo "❌ Failed to add column '$column': " . mysqli_error($con) . "<br>";
                    }
                }
            }

            // Step 3: Remove extra columns not in SQL
            foreach ($existingColumns as $existingColumn) {
                if (!array_key_exists($existingColumn, $definedColumns)) {
                    // Don't remove 'id' or foreign keys
                    if ($existingColumn === 'id') continue;

                    $alterSql = "ALTER TABLE `$name` DROP COLUMN `$existingColumn`";
                    if (mysqli_query($con, $alterSql)) {
                        echo "🗑️ Removed column '$existingColumn' from '$name'.<br>";
                    } else {
                        echo "❌ Failed to remove column '$existingColumn': " . mysqli_error($con) . "<br>";
                    }
                }
            }

        } else {
            echo "❌ Error creating table '$name': " . $e->getMessage() . "<br>";
        }
    }
}



function insertData($con, $table, $columns, $values, $uniqueColumns = null) {
    $cols = implode(',', $columns);
    $placeholders = rtrim(str_repeat('?,', count($columns)), ',');

    $inserted = 0;

    foreach ($values as $row) {
        // Determine param types
        $types = implode('', array_map(function ($val) {
            if (is_int($val)) return 'i';
            if (is_float($val) || is_double($val)) return 'd';
            return 's';
        }, $row));

        // If uniqueColumns are specified, use them to check for duplicates
        $check = $row;
        $checkCols = $columns;

        if ($uniqueColumns) {
            $check = [];
            $checkCols = [];

            foreach ($uniqueColumns as $uc) {
                $index = array_search($uc, $columns);
                if ($index !== false) {
                    $check[] = $row[$index];
                    $checkCols[] = $uc;
                }
            }

            // If none of the unique columns were found, skip check
            if (empty($check)) {
                $checkCols = $columns;
                $check = $row;
            }
        }

        $checkTypes = implode('', array_map(function ($val) {
            if (is_int($val)) return 'i';
            if (is_float($val) || is_double($val)) return 'd';
            return 's';
        }, $check));

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", $checkCols));
        $checkSql = "SELECT COUNT(*) FROM $table WHERE $whereClause";

        $checkStmt = $con->prepare($checkSql);
        $checkStmt->bind_param($checkTypes, ...$check);
        $checkStmt->execute();
        $count = 0;
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count == 0) {
            $stmt = $con->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
            $stmt->bind_param($types, ...$row);
            $stmt->execute();
            $stmt->close();
            $inserted++;
        }
    }

    echo "Inserted $inserted new rows into '$table'.<br>";
}


//Function that can be use for Updating or changing the pre defined items by using its key 
function upsertData($con, $table, $columns, $values, $keys) {
    $cols = implode(',', $columns);
    $placeholders = rtrim(str_repeat('?,', count($columns)), ',');

    $updated = 0;
    $inserted = 0;

    foreach ($values as $row) {
        $keyClause = implode(' AND ', array_map(fn($k) => "$k = ?", $keys));
        $keyValues = [];
        foreach ($keys as $k) {
            $index = array_search($k, $columns);
            $keyValues[] = $row[$index];
        }

        $checkSql = "SELECT COUNT(*) FROM $table WHERE $keyClause";
        $checkStmt = $con->prepare($checkSql);
        $checkStmt->bind_param(str_repeat('s', count($keys)), ...$keyValues);
        $checkStmt->execute();

        $count = 0;
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ((int)$count > 0) {
            // UPDATE
            $setClause = implode(', ', array_map(fn($col) => "$col = ?", $columns));
            $sql = "UPDATE $table SET $setClause WHERE $keyClause";

            $stmt = $con->prepare($sql);
            $stmt->bind_param(str_repeat('s', count($row) + count($keyValues)), ...$row, ...$keyValues);
            $stmt->execute();
            $stmt->close();
            $updated++;
        } else {
            // INSERT
            $stmt = $con->prepare("INSERT INTO $table ($cols) VALUES ($placeholders)");
            $stmt->bind_param(str_repeat('s', count($row)), ...$row);
            $stmt->execute();
            $stmt->close();
            $inserted++;
        }
    }

    echo "Upserted into '$table': $inserted inserted, $updated updated.<br>";
}

// Reusable helper to get the ID of a Starbucks item by name
function getIdByName($con, $table, $name) {
    $stmt = $con->prepare("SELECT id FROM `$table` WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result || $result->num_rows === 0) {
        echo "❌ '$name' not found in '$table' table.<br>";
        return null;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return $row['id'];
}

// Get ID by full name from a table
function getIdByFullName($con, $table, $first, $last) {
    $stmt = $con->prepare("SELECT id FROM `$table` WHERE first_name = ? AND last_name = ?");
    $stmt->bind_param("ss", $first, $last);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        return $row['id'];
    }

    return null;
}

function insertDataAndGetId($con, $table, $columns, $values, $uniqueColumns = null) {
    $cols = implode(',', $columns);
    $placeholders = rtrim(str_repeat('?,', count($columns)), ',');

    foreach ($values as $row) {
        // Determine param types
        $types = implode('', array_map(function ($val) {
            if (is_int($val)) return 'i';
            if (is_float($val) || is_double($val)) return 'd';
            return 's';
        }, $row));

        // Prepare for duplicate checking
        $check = $row;
        $checkCols = $columns;

        if ($uniqueColumns) {
            $check = [];
            $checkCols = [];

            foreach ($uniqueColumns as $uc) {
                $index = array_search($uc, $columns);
                if ($index !== false) {
                    $check[] = $row[$index];
                    $checkCols[] = $uc;
                }
            }

            // If none of the unique columns were found, skip and use all
            if (empty($check)) {
                $checkCols = $columns;
                $check = $row;
            }
        }

        // Build WHERE clause for checking
        $checkTypes = implode('', array_map(function ($val) {
            if (is_int($val)) return 'i';
            if (is_float($val) || is_double($val)) return 'd';
            return 's';
        }, $check));

        $whereClause = implode(' AND ', array_map(fn($col) => "$col = ?", $checkCols));
        $checkSql = "SELECT id FROM `$table` WHERE $whereClause";

        $checkStmt = $con->prepare($checkSql);
        $checkStmt->bind_param($checkTypes, ...$check);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult && $rowData = $checkResult->fetch_assoc()) {
            $checkStmt->close();
            return $rowData['id']; // 🔁 Already exists, return existing ID
        }
        $checkStmt->close();

        // Proceed with INSERT if not found
        $stmt = $con->prepare("INSERT INTO `$table` ($cols) VALUES ($placeholders)");
        $stmt->bind_param($types, ...$row);
        $stmt->execute();
        $insertId = $stmt->insert_id;
        $stmt->close();

        return $insertId; // ✅ Inserted, return new ID
    }

    return null; // If no rows
}

?>
