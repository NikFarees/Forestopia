<?php
include '../db_connect.php';

// Get filter parameters
$blockX = isset($_GET['BlockX']) ? intval($_GET['BlockX']) : null;
$blockY = isset($_GET['BlockY']) ? intval($_GET['BlockY']) : null;
$speciesGroup = isset($_GET['SpeciesGroup']) ? intval($_GET['SpeciesGroup']) : 8;
$regime = isset($_GET['Regime']) ? intval($_GET['Regime']) : 55;

// Determine regime table
$tableName = "year0_regime" . $regime;

// Build SQL query
$query = "SELECT 
            yf.BlockX,
            yf.BlockY,
            yf.CoordX,
            yf.CoordY,
            yf.TreeNum,
            yf.Species,
            yf.SpeciesGroup,
            yf.Diameter,
            yf.DiameterClass,
            yf.Height,
            yf.Volume,
            yr.Status,
            yr.FallAngle,
            yr.FallQuarter
          FROM 
            year0_forest yf
          LEFT JOIN 
            $tableName yr 
          ON 
            yf.TreeNum = yr.TreeNum
          WHERE 1=1";

// Apply filters
if ($blockX !== null) {
    $query .= " AND yf.BlockX = $blockX";
}
if ($blockY !== null) {
    $query .= " AND yf.BlockY = $blockY";
}
if ($speciesGroup != 8) {
    $query .= " AND yf.SpeciesGroup = $speciesGroup";
}

// Execute query
$result = mysqli_query($conn, $query);

// Check for errors
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . mysqli_error($conn)]);
    mysqli_close($conn);
    exit;
}

// Prepare and return data
$trees = [];
while ($row = mysqli_fetch_assoc($result)) {
    $trees[] = $row;
}

header('Content-Type: application/json');
echo json_encode($trees);

mysqli_close($conn);
?>
