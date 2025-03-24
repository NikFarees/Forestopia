<?php
require_once '../db_connect.php';

// Get the selected regime status filter
$selectedStatus = isset($_GET['status']) ? $_GET['status'] : 'Status45';

// Define the SQL query for volume base
$sqlVolume = "
SELECT 
    'Regime 45' AS Regime,
    FORMAT(COALESCE(SUM(CASE WHEN y0r45.Status = 'cut' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS prod0_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y0r45.Status = 'victim' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS damage_volume,
    FORMAT(COALESCE(SUM(CASE 
        WHEN y0r45.Status = 'not cut' AND y30r45.TreeNum IS NOT NULL 
        THEN y30r45.Volume30 - f.Volume 
        ELSE 0 
    END), 0) / 100.0, 2) AS growth30_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y30r45.$selectedStatus = 'cut' THEN y30r45.Volume30 ELSE 0 END), 0) / 100.0, 2) AS prod30_volume
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime45 y0r45 ON f.TreeNum = y0r45.TreeNum
LEFT JOIN 
    year30_regime45 y30r45 ON f.TreeNum = y30r45.TreeNum

UNION ALL

SELECT 
    'Regime 50' AS Regime,
    FORMAT(COALESCE(SUM(CASE WHEN y0r50.Status = 'cut' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS prod0_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y0r50.Status = 'victim' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS damage_volume,
    FORMAT(COALESCE(SUM(CASE 
        WHEN y0r50.Status = 'not cut' AND y30r50.TreeNum IS NOT NULL 
        THEN y30r50.Volume30 - f.Volume 
        ELSE 0 
    END), 0) / 100.0, 2) AS growth30_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y30r50.$selectedStatus = 'cut' THEN y30r50.Volume30 ELSE 0 END), 0) / 100.0, 2) AS prod30_volume
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime50 y0r50 ON f.TreeNum = y0r50.TreeNum
LEFT JOIN 
    year30_regime50 y30r50 ON f.TreeNum = y30r50.TreeNum

UNION ALL

SELECT 
    'Regime 55' AS Regime,
    FORMAT(COALESCE(SUM(CASE WHEN y0r55.Status = 'cut' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS prod0_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y0r55.Status = 'victim' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS damage_volume,
    FORMAT(COALESCE(SUM(CASE 
        WHEN y0r55.Status = 'not cut' AND y30r55.TreeNum IS NOT NULL 
        THEN y30r55.Volume30 - f.Volume 
        ELSE 0 
    END), 0) / 100.0, 2) AS growth30_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y30r55.$selectedStatus = 'cut' THEN y30r55.Volume30 ELSE 0 END), 0) / 100.0, 2) AS prod30_volume
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime55 y0r55 ON f.TreeNum = y0r55.TreeNum
LEFT JOIN 
    year30_regime55 y30r55 ON f.TreeNum = y30r55.TreeNum

UNION ALL

SELECT 
    'Regime 60' AS Regime,
    FORMAT(COALESCE(SUM(CASE WHEN y0r60.Status = 'cut' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS prod0_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y0r60.Status = 'victim' THEN f.Volume ELSE 0 END), 0) / 100.0, 2) AS damage_volume,
    FORMAT(COALESCE(SUM(CASE 
        WHEN y0r60.Status = 'not cut' AND y30r60.TreeNum IS NOT NULL 
        THEN y30r60.Volume30 - f.Volume 
        ELSE 0 
    END), 0) / 100.0, 2) AS growth30_volume,
    FORMAT(COALESCE(SUM(CASE WHEN y30r60.$selectedStatus = 'cut' THEN y30r60.Volume30 ELSE 0 END), 0) / 100.0, 2) AS prod30_volume
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime60 y0r60 ON f.TreeNum = y0r60.TreeNum
LEFT JOIN 
    year30_regime60 y30r60 ON f.TreeNum = y30r60.TreeNum;
";

// Execute the volume query
$resultVolume = mysqli_query($conn, $sqlVolume);

if (!$resultVolume) {
    die("Failed to fetch volume data: " . mysqli_error($conn));
}

// Fetch the volume data
$dataVolume = [];
while ($row = mysqli_fetch_assoc($resultVolume)) {
    $dataVolume[] = $row;
}

// Define the SQL query for count base
$sqlCount = "
SELECT 
    'Regime 45' AS Regime,
    COUNT(CASE WHEN y0r45.Status = 'cut' THEN 1 END) / 100.0 AS prod0_count,
    COUNT(CASE WHEN y0r45.Status = 'victim' THEN 1 END) / 100.0 AS damage_count,
    COUNT(CASE 
        WHEN y0r45.Status = 'not cut' AND y30r45.TreeNum IS NOT NULL 
        THEN 1 
    END) / 100.0 AS growth30_count,
    COUNT(CASE WHEN y30r45.$selectedStatus = 'cut' THEN 1 END) / 100.0 AS prod30_count
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime45 y0r45 ON f.TreeNum = y0r45.TreeNum
LEFT JOIN 
    year30_regime45 y30r45 ON f.TreeNum = y30r45.TreeNum

UNION ALL

SELECT 
    'Regime 50' AS Regime,
    COUNT(CASE WHEN y0r50.Status = 'cut' THEN 1 END) / 100.0 AS prod0_count,
    COUNT(CASE WHEN y0r50.Status = 'victim' THEN 1 END) / 100.0 AS damage_count,
    COUNT(CASE 
        WHEN y0r50.Status = 'not cut' AND y30r50.TreeNum IS NOT NULL 
        THEN 1 
    END) / 100.0 AS growth30_count,
    COUNT(CASE WHEN y30r50.$selectedStatus = 'cut' THEN 1 END) / 100.0 AS prod30_count
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime50 y0r50 ON f.TreeNum = y0r50.TreeNum
LEFT JOIN 
    year30_regime50 y30r50 ON f.TreeNum = y30r50.TreeNum

UNION ALL

SELECT 
    'Regime 55' AS Regime,
    COUNT(CASE WHEN y0r55.Status = 'cut' THEN 1 END) / 100.0 AS prod0_count,
    COUNT(CASE WHEN y0r55.Status = 'victim' THEN 1 END) / 100.0 AS damage_count,
    COUNT(CASE 
        WHEN y0r55.Status = 'not cut' AND y30r55.TreeNum IS NOT NULL 
        THEN 1 
    END) / 100.0 AS growth30_count,
    COUNT(CASE WHEN y30r55.$selectedStatus = 'cut' THEN 1 END) / 100.0 AS prod30_count
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime55 y0r55 ON f.TreeNum = y0r55.TreeNum
LEFT JOIN 
    year30_regime55 y30r55 ON f.TreeNum = y30r55.TreeNum

UNION ALL

SELECT 
    'Regime 60' AS Regime,
    COUNT(CASE WHEN y0r60.Status = 'cut' THEN 1 END) / 100.0 AS prod0_count,
    COUNT(CASE WHEN y0r60.Status = 'victim' THEN 1 END) / 100.0 AS damage_count,
    COUNT(CASE 
        WHEN y0r60.Status = 'not cut' AND y30r60.TreeNum IS NOT NULL 
        THEN 1 
    END) / 100.0 AS growth30_count,
    COUNT(CASE WHEN y30r60.$selectedStatus = 'cut' THEN 1 END) / 100.0 AS prod30_count
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime60 y0r60 ON f.TreeNum = y0r60.TreeNum
LEFT JOIN 
    year30_regime60 y30r60 ON f.TreeNum = y30r60.TreeNum;
";

// Execute the count query
$resultCount = mysqli_query($conn, $sqlCount);

if (!$resultCount) {
    die("Failed to fetch count data: " . mysqli_error($conn));
}

// Fetch the count data
$dataCount = [];
while ($row = mysqli_fetch_assoc($resultCount)) {
    $dataCount[] = $row;
}


 // Step 1: Calculate averages
function calculateAverage($data, $fields)
{
    $averages = [];
    foreach ($fields as $field) {
        $sum = 0;
        $count = 0; // To track valid rows with the field
        foreach ($data as $row) {
            if (isset($row[$field])) { // Ensure the field exists in the row
               $sum += $row[$field];
                $count++;
            }
        }
        $averages[$field] = $count > 0 ? $sum / $count : 0; // Avoid division by zero
    }
    return $averages;
}

// Define fields for count and volume
$countFields = ['prod0_count', 'damage_count', 'growth30_count'];
$volumeFields = ['prod0_volume', 'damage_volume', 'growth30_volume'];
$prod30Fields = ['prod30_count_r45', 'prod30_count_r50', 'prod30_count_r55', 'prod30_count_r60'];

// Calculate averages
$avgCount = calculateAverage($dataCount, array_merge($countFields, $prod30Fields));
$avgVolume = calculateAverage($dataVolume, array_merge($volumeFields, $prod30Fields));

// Step 2: Define range
$range = 0.05;

// Step 3: Calculate scores
function calculateScore($value, $avg, $range)
{
    if ($value > $avg * (1 + $range)) {
        return 0; // Above average
    } elseif ($value >= $avg * (1 - $range) && $value <= $avg * (1 + $range)) {
        return 1; // Within average
    } else {
        return -1; // Below average
    }
}

// Step 4: Evaluate all combinations
$bestCombination = null;
$maxScore = PHP_INT_MIN;

foreach ($dataVolume as $year0Index => $volumeRowYear0) {
    $countRowYear0 = $dataCount[$year0Index];
    $regimeYear0 = $volumeRowYear0['Regime'];

    // Calculate scores for Year 0 regime
    $year0Score = [
        'prod0' => calculateScore($volumeRowYear0['prod0_volume'] ?? 0, $avgVolume['prod0_volume'], $range) +
            calculateScore($countRowYear0['prod0_count'] ?? 0, $avgCount['prod0_count'], $range),
        'damage' => calculateScore($volumeRowYear0['damage_volume'] ?? 0, $avgVolume['damage_volume'], $range) +
            calculateScore($countRowYear0['damage_count'] ?? 0, $avgCount['damage_count'], $range),
        'growth30' => calculateScore($volumeRowYear0['growth30_volume'] ?? 0, $avgVolume['growth30_volume'], $range) +
            calculateScore($countRowYear0['growth30_count'] ?? 0, $avgCount['growth30_count'], $range)
    ];

    // Combine with Year 30 regimes (prod30_r45, prod30_r50, prod30_r55, prod30_r60)
    foreach ($dataVolume as $year30Index => $volumeRowYear30) {
        $countRowYear30 = $dataCount[$year30Index];
        $regimeYear30 = $volumeRowYear30['Regime'];

        // Calculate scores for each prod30 field
        $prod30Scores = [];
        foreach ($prod30Fields as $field) {
            $prod30Scores[$field] = calculateScore($volumeRowYear30[$field] ?? 0, $avgVolume[$field] ?? 0, $range) +
                calculateScore($countRowYear30[$field] ?? 0, $avgCount[$field] ?? 0, $range);
        }

        // Calculate total score for this combination
        foreach ($prod30Scores as $field => $prod30Score) {
            $totalScore = array_sum($year0Score) + $prod30Score;

            // Update best combination
            if ($totalScore > $maxScore) {
                $maxScore = $totalScore;
                $bestCombination = [
                    'Year 0 Regime' => $regimeYear0,
                    'Year 30 Regime' => $regimeYear30,
                    'Prod30 Field' => $field,
                    'Total Score' => $totalScore
                ];
            }
        }
    }
}

// Close the database connection
mysqli_close($conn);
