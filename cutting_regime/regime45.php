<?php
include '../include/header.php';
// Include the database connection file
require_once '../db_connect.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize species groups with their corresponding numeric labels
$speciesGroups = [
    1 => "Mersawa",
    2 => "Keruing",
    3 => "Dip Marketable",
    4 => "Dip Non Market",
    5 => "Non Dip Market",
    6 => "Non Dip Non Market",
    7 => "Others"
];

// Get the filter value if set
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'production0';

// Initialize the table title
$tableTitle = 'Production 0 Stand Table';

// Construct SQL query based on the selected filter
switch ($filter) {
    case 'production0':
        $table = 'year0_regime45';
        $diameterColumn = 'Diameter';
        $volumeColumn = 'Volume';
        $statusCondition = "AND t.Status = 'Cut'";
        $tableTitle = 'Production 0 Stand Table';
        break;
    case 'damage':
        $table = 'year0_regime45';
        $diameterColumn = 'Diameter';
        $volumeColumn = 'Volume';
        $statusCondition = "AND t.Status = 'victim'";
        $tableTitle = 'Damage Stand Table';
        break;
    case 'growth30':
        $table = 'year30_regime45';
        $diameterColumn = 'Diameter30';
        $volumeColumn = 'Volume30';
        $statusCondition = '';
        $tableTitle = 'Growth 30 Stand Table';
        break;
    case 'production30regime45':
        $table = 'year30_regime45';
        $diameterColumn = 'Diameter30';
        $volumeColumn = 'Volume30';
        $statusCondition = "AND t.Status45 = 'Cut'";
        $tableTitle = 'Production 30 Regime 45 Stand Table';
        break;
    case 'production30regime50':
        $table = 'year30_regime45';
        $diameterColumn = 'Diameter30';
        $volumeColumn = 'Volume30';
        $statusCondition = "AND t.Status50 = 'Cut'";
        $tableTitle = 'Production 30 Regime 50 Stand Table';
        break;
    case 'production30regime55':
        $table = 'year30_regime45';
        $diameterColumn = 'Diameter30';
        $volumeColumn = 'Volume30';
        $statusCondition = "AND t.Status55 = 'Cut'";
        $tableTitle = 'Production 30 Regime 55 Stand Table';
        break;
    case 'production30regime60':
        $table = 'year30_regime45';
        $diameterColumn = 'Diameter30';
        $volumeColumn = 'Volume30';
        $statusCondition = "AND t.Status60 = 'Cut'";
        $tableTitle = 'Production 30 Regime 60 Stand Table';
        break;
    default:
        $table = 'year0_regime45';
        $diameterColumn = 'Diameter';
        $volumeColumn = 'Volume';
        $statusCondition = "AND t.Status = 'Cut'";
        $tableTitle = 'Production 0 Stand Table';
        break;
}

// Construct SQL query
$sql = "
SELECT 
    CASE 
        WHEN yf.SpeciesGroup = 1 THEN 'Mersawa'
        WHEN yf.SpeciesGroup = 2 THEN 'Keruing'
        WHEN yf.SpeciesGroup = 3 THEN 'Dip-Com'
        WHEN yf.SpeciesGroup = 4 THEN 'Dip-Non-Com'
        WHEN yf.SpeciesGroup = 5 THEN 'Non-Dip-Com'
        WHEN yf.SpeciesGroup = 6 THEN 'Non-Dip-Non-Com'
        ELSE 'Others'
    END AS SpeciesGroupName,

    ROUND(SUM(CASE 
        WHEN $diameterColumn >= 5 AND $diameterColumn < 15 THEN $volumeColumn / 100 
        ELSE 0 
        END), 2) AS '5cm-15cm_Volume',

    ROUND(SUM(CASE 
        WHEN $diameterColumn >= 15 AND $diameterColumn < 30 THEN $volumeColumn / 100 
        ELSE 0 
        END), 2) AS '15cm-30cm_Volume',

    ROUND(SUM(CASE 
        WHEN $diameterColumn >= 30 AND $diameterColumn < 45 THEN $volumeColumn / 100 
        ELSE 0 
        END), 2) AS '30cm-45cm_Volume',

    ROUND(SUM(CASE 
        WHEN $diameterColumn >= 45 AND $diameterColumn < 60 THEN $volumeColumn / 100 
        ELSE 0 
        END), 2) AS '45cm-60cm_Volume',

    ROUND(SUM(CASE 
        WHEN $diameterColumn >= 60 THEN $volumeColumn / 100 
        ELSE 0 
        END), 2) AS '60cm+_Volume',

    ROUND(SUM($volumeColumn / 100), 2) AS 'Total_Volume'
FROM 
    $table t
JOIN 
    year0_forest yf ON t.TreeNum = yf.TreeNum
WHERE 
    1=1 $statusCondition
GROUP BY 
    yf.SpeciesGroup";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Failed to fetch data from the $table table: " . mysqli_error($conn));
}

// Check if any rows are returned
if (mysqli_num_rows($result) == 0) {
    echo "<p>No data found for the selected filter.</p>";
}

// Calculate the total volume for each diameter range
$totalVolumes = [
    '5cm-15cm_Volume' => 0,
    '15cm-30cm_Volume' => 0,
    '30cm-45cm_Volume' => 0,
    '45cm-60cm_Volume' => 0,
    '60cm+_Volume' => 0,
    'Total_Volume' => 0
];

while ($row = mysqli_fetch_assoc($result)) {
    $totalVolumes['5cm-15cm_Volume'] += $row['5cm-15cm_Volume'];
    $totalVolumes['15cm-30cm_Volume'] += $row['15cm-30cm_Volume'];
    $totalVolumes['30cm-45cm_Volume'] += $row['30cm-45cm_Volume'];
    $totalVolumes['45cm-60cm_Volume'] += $row['45cm-60cm_Volume'];
    $totalVolumes['60cm+_Volume'] += $row['60cm+_Volume'];
    $totalVolumes['Total_Volume'] += $row['Total_Volume'];
}
?>
<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 1rem;
        align-items: end;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 1rem;
    }

    .btn {
        padding: 0.5rem;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        width: auto; /* Set width to auto */
        min-width: 100px; /* Set a minimum width */
    }

    .btn:hover {
        background-color: #0056b3;
    }
</style>

<main class="main">

    <!-- About Section -->
    <section id="about" class="about section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Regime 45</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <!-- Filter Form -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-header" style="background-color:rgb(155, 238, 230);">
                            <h5 class="card-title mb-0">Filter by Category</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" action="">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="filter" class="form-label">Category</label>
                                        <select id="filter" name="filter" class="form-control">
                                            <option value="production0" <?= ($filter == 'production0') ? 'selected' : '' ?>>Production 0</option>
                                            <option value="damage" <?= ($filter == 'damage') ? 'selected' : '' ?>>Damage</option>
                                            <option value="growth30" <?= ($filter == 'growth30') ? 'selected' : '' ?>>Growth 30</option>
                                            <option value="production30regime45" <?= ($filter == 'production30regime45') ? 'selected' : '' ?>>Production 30 Regime 45</option>
                                            <option value="production30regime50" <?= ($filter == 'production30regime50') ? 'selected' : '' ?>>Production 30 Regime 50</option>
                                            <option value="production30regime55" <?= ($filter == 'production30regime55') ? 'selected' : '' ?>>Production 30 Regime 55</option>
                                            <option value="production30regime60" <?= ($filter == 'production30regime60') ? 'selected' : '' ?>>Production 30 Regime 60</option>
                                        </select>
                                        <style>
                                            .custom-button {
                                                background-color: rgb(72, 172, 162);
                                                /* Change this to your desired color */
                                                color: #fff;
                                                /* Change this to your desired text color */
                                            }

                                            .custom-button:hover {
                                                background-color: rgb(42, 82, 78);
                                                /* Change this to your desired hover color */
                                                color: #fff;
                                                /* Change this to your desired hover text color */
                                            }
                                        </style>
                                        <button type="submit" class="btn custom-button w-100">Apply Filter</button>
                                    </div>
                                        
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <br>

            <div class="row">
                <div class="col-md-6">
                    <h1 class="text-center"><?= $tableTitle ?></h1>

                    <!-- Stand Table Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark text-center">
                                <tr>
                                    <th rowspan="2">Species Group</th>
                                    <th colspan="1">5cm-15cm</th>
                                    <th colspan="1">15cm-30cm</th>
                                    <th colspan="1">30cm-45cm</th>
                                    <th colspan="1">45cm-60cm</th>
                                    <th colspan="1">60cm+</th>
                                    <th rowspan="2">Total</th>
                                </tr>
                                <tr>
                                    <th>Volume</th>
                                    <th>Volume</th>
                                    <th>Volume</th>
                                    <th>Volume</th>
                                    <th>Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Reset the result pointer and fetch the data again
                                mysqli_data_seek($result, 0);

                                // Display the fetched data in the table
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr class='text-center'>
                                                <td>{$row['SpeciesGroupName']}</td>
                                                <td>{$row['5cm-15cm_Volume']}</td>
                                                <td>{$row['15cm-30cm_Volume']}</td>
                                                <td>{$row['30cm-45cm_Volume']}</td>
                                                <td>{$row['45cm-60cm_Volume']}</td>
                                                <td>{$row['60cm+_Volume']}</td>
                                                <td class='font-weight-bold'>{$row['Total_Volume']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center'>No data found</td></tr>";
                                }

                                // Display the total volumes row
                                echo "<tr class='text-center font-weight-bold'>
                                        <td>Total</td>
                                        <td>{$totalVolumes['5cm-15cm_Volume']}</td>
                                        <td>{$totalVolumes['15cm-30cm_Volume']}</td>
                                        <td>{$totalVolumes['30cm-45cm_Volume']}</td>
                                        <td>{$totalVolumes['45cm-60cm_Volume']}</td>
                                        <td>{$totalVolumes['60cm+_Volume']}</td>
                                        <td>{$totalVolumes['Total_Volume']}</td>
                                      </tr>";
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h1 class="text-center">Final Output Data</h1>
                    <div class="table-responsive">
                        <table class='table table-striped table-bordered shadow-lg' style="width: 150%;"">
                        <thead class=' thead-dark text-center'>
                            <tr>
                                <th>Species Groups</th>
                                <th>Total Volume 0</th>
                                <th>Total Number 0</th>
                                <th>Prod 0</th>
                                <th>Damage 0</th>
                                <th>Remain 0</th>
                                <th>Total Growth 30</th>
                                <th>Total Prod 30 Regime 45</th>
                                <th>Total Prod 30 Regime 50</th>
                                <th>Total Prod 30 Regime 55</th>
                                <th>Total Prod 30 Regime 60</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Loop through species groups and fetch data from the database
                                foreach ($speciesGroups as $spgroup => $groupName) {
                                    // Query to fetch data for the current species group from the year0_forest table
                                    $sql = "
                                        SELECT 
                                            SUM(yf.Volume) AS total_volume, 
                                            COUNT(*) AS total_number,
                                            SUM(CASE WHEN t.Status = 'Cut' THEN yf.Volume ELSE 0 END) AS prod_volume,
                                            SUM(CASE WHEN yr.Status = 'victim' THEN yf.Volume ELSE 0 END) AS damage_volume,
                                            SUM(CASE WHEN t.Status = 'Not Cut' THEN yf.Volume ELSE 0 END) AS remain_volume
                                        FROM year0_forest yf
                                        LEFT JOIN year0_regime45 t ON yf.TreeNum = t.TreeNum
                                        LEFT JOIN year0_regime45 yr ON yf.TreeNum = yr.TreeNum
                                        WHERE yf.SpeciesGroup = $spgroup
                                    ";

                                    $result = mysqli_query($conn, $sql);

                                    if (!$result) {
                                        echo "<tr><td colspan='11'>SQL Error: " . mysqli_error($conn) . "</td></tr>";
                                        continue;
                                    }

                                    $data = mysqli_fetch_assoc($result);

                                    if (!$data || $data['total_volume'] === null) {
                                        // Display zeroes for species with no data
                                        echo "<tr class='text-center'>
                                                <td>$groupName</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                                <td class='center'>0.000</td>
                                              </tr>";
                                        continue;
                                    }

                                    // Calculate Total Volume 0 (divide by 100 hectares)
                                    $totalVolume = $data['total_volume'] / 100;

                                    // Total Number 0
                                    $totalNumber = $data['total_number'] / 100;

                                    // Prod 0 (total volume of trees with status 'Cut')
                                    $prodVolume = $data['prod_volume'] / 100;

                                    // Damage 0 (total volume of trees with status 'victim' from year0_regime45)
                                    $damageVolume = $data['damage_volume'] / 100;

                                    // Remain 0 (total volume of trees with status 'Not Cut')
                                    $remainVolume = $data['remain_volume'] / 100;

                                    // Query to fetch total volume 30 and calculate growth for the current species group from the year30_regime45 table
                                    $sqlGrowth = "
                                        SELECT 
                                            (SUM(t.Volume30) - (
                                                SELECT SUM(yf.Volume)
                                                FROM year0_forest yf
                                                JOIN year0_regime45 t0 ON yf.TreeNum = t0.TreeNum
                                                WHERE yf.SpeciesGroup = $spgroup AND t0.Status != 'Cut'
                                            )) AS total_growth30,
                                            SUM(t.Volume30) AS total_volume30
                                        FROM year30_regime45 t
                                        JOIN year0_forest yf ON t.TreeNum = yf.TreeNum
                                        WHERE yf.SpeciesGroup = $spgroup
                                    ";

                                    $resultGrowth = mysqli_query($conn, $sqlGrowth);

                                    if (!$resultGrowth) {
                                        echo "<tr><td colspan='11'>SQL Error: " . mysqli_error($conn) . "</td></tr>";
                                        continue;
                                    }

                                    $dataGrowth = mysqli_fetch_assoc($resultGrowth);

                                    if (!$dataGrowth || $dataGrowth['total_volume30'] === null) {
                                        $totalGrowth30 = 0;
                                        $totalProd30Regime45 = 0;
                                        $totalProd30Regime50 = 0;
                                        $totalProd30Regime55 = 0;
                                        $totalProd30Regime60 = 0;
                                    } else {
                                        // Calculate Total Growth 30 (total growth divided by 100 hectares)
                                        $totalGrowth30 = $dataGrowth['total_growth30'] / 100;

                                        // Calculate Total Prod 30 Regime 45
                                        $sqlProd30Regime45 = "
                                        SELECT SUM(r.Volume30) AS total_prod30_regime45
                                        FROM year30_regime45 r
                                        JOIN year0_forest f ON r.TreeNum = f.TreeNum
                                        WHERE f.SpeciesGroup = $spgroup
                                          AND r.Status45 = 'Cut'
                                    ";
                                        $resultProd30Regime45 = mysqli_query($conn, $sqlProd30Regime45);
                                        $dataProd30Regime45 = mysqli_fetch_assoc($resultProd30Regime45);
                                        $totalProd30Regime45 = $dataProd30Regime45['total_prod30_regime45'] / 100;

                                        // Calculate Total Prod 30 Regime 50
                                        $sqlProd30Regime50 = "
                                        SELECT SUM(r.Volume30) AS total_prod30_regime50
                                        FROM year30_regime50 r
                                        JOIN year0_forest f ON r.TreeNum = f.TreeNum
                                        WHERE f.SpeciesGroup = $spgroup
                                          AND r.Status50 = 'Cut'
                                    ";
                                        $resultProd30Regime50 = mysqli_query($conn, $sqlProd30Regime50);
                                        $dataProd30Regime50 = mysqli_fetch_assoc($resultProd30Regime50);
                                        $totalProd30Regime50 = $dataProd30Regime50['total_prod30_regime50'] / 100;

                                        // Calculate Total Prod 30 Regime 55
                                        $sqlProd30Regime55 = "
                                        SELECT SUM(r.Volume30) AS total_prod30_regime55
                                        FROM year30_regime55 r
                                        JOIN year0_forest f ON r.TreeNum = f.TreeNum
                                        WHERE f.SpeciesGroup = $spgroup
                                          AND r.Status55 = 'Cut'
                                    ";
                                        $resultProd30Regime55 = mysqli_query($conn, $sqlProd30Regime55);
                                        $dataProd30Regime55 = mysqli_fetch_assoc($resultProd30Regime55);
                                        $totalProd30Regime55 = $dataProd30Regime55['total_prod30_regime55'] / 100;

                                        // Calculate Total Prod 30 Regime 60
                                        $sqlProd30Regime60 = "
                                        SELECT SUM(r.Volume30) AS total_prod30_regime60
                                        FROM year30_regime60 r
                                        JOIN year0_forest f ON r.TreeNum = f.TreeNum
                                        WHERE f.SpeciesGroup = $spgroup
                                          AND r.Status60 = 'Cut'
                                    ";
                                        $resultProd30Regime60 = mysqli_query($conn, $sqlProd30Regime60);
                                        $dataProd30Regime60 = mysqli_fetch_assoc($resultProd30Regime60);
                                        $totalProd30Regime60 = $dataProd30Regime60['total_prod30_regime60'] / 100;
                                    }

                                    // Display the row with calculated data
                                    echo "<tr class='text-center'>
                                            <td>$groupName</td>
                                            <td class='center'>" . number_format($totalVolume, 2) . "</td>
                                            <td class='center'>" . number_format($totalNumber, 2) . "</td>
                                            <td class='center'>" . number_format($prodVolume, 2) . "</td>
                                            <td class='center'>" . number_format($damageVolume, 2) . "</td>
                                            <td class='center'>" . number_format($remainVolume, 2) . "</td>
                                            <td class='center'>" . number_format($totalGrowth30, 2) . "</td>
                                            <td class='center'>" . number_format($totalProd30Regime45, 2) . "</td>
                                            <td class='center'>" . number_format($totalProd30Regime50, 2) . "</td>
                                            <td class='center'>" . number_format($totalProd30Regime55, 2) . "</td>
                                            <td class='center'>" . number_format($totalProd30Regime60, 2) . "</td>
                                          </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class=" container mt-5" data-aos="fade-up">
                            <?php
                            // Include the chart from chart_regime45.php
                            include '../forest_visual/chart_regime45.php';
                            ?>
                    </div>
                </div>
    </section><!-- /About Section -->
</main>

<?php include '../include/footer.php'; ?>

<?php
// Close the database connection
mysqli_close($conn);
?>

<style>
    .table-responsive {
        position: relative;
        height: 500px;
        overflow: auto;
    }

    .table-responsive thead {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #fff;
    }
</style>