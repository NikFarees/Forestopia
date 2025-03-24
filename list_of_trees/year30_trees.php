<?php
// Include the database connection file
require_once '../db_connect.php';

// Include the header file
include '../include/header.php';

// Define regime table mapping for year 30
$regimeTablesYear30 = [
    '45' => 'year30_regime45',
    '50' => 'year30_regime50',
    '55' => 'year30_regime55',
    '60' => 'year30_regime60',
];

// Retrieve the selected regime from the GET parameters, defaulting to Regime 45
$regimeFilter = isset($_GET['regime']) && $_GET['regime'] !== '' ? $_GET['regime'] : '45';

// Validate the selected regime and set the table for year 30
$selectedTableYear30 = isset($regimeTablesYear30[$regimeFilter]) ? $regimeTablesYear30[$regimeFilter] : 'year30_regime45';

// Pagination parameters
$limit = 100; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Construct SQL query for the stand table
$sqlStandTable = "
SELECT 
    CASE 
        WHEN SpeciesGroup = 1 THEN 'Mersawa'
        WHEN SpeciesGroup = 2 THEN 'Keruing'
        WHEN SpeciesGroup = 3 THEN 'Dip-Com'
        WHEN SpeciesGroup = 4 THEN 'Dip-Non-Com'
        WHEN SpeciesGroup = 5 THEN 'Non-Dip-Com'
        WHEN SpeciesGroup = 6 THEN 'Non-Dip-Non-Com'
        ELSE 'Others'
    END AS SpeciesGroupName,

    ROUND(SUM(CASE 
        WHEN Diameter30 >= 5 AND Diameter30 < 15 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '5cm-15cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter30 >= 5 AND Diameter30 < 15 THEN Volume30 / 100 
        ELSE 0 
        END), 2) AS '5cm-15cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter30 >= 15 AND Diameter30 < 30 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '15cm-30cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter30 >= 15 AND Diameter30 < 30 THEN Volume30 / 100 
        ELSE 0 
        END), 2) AS '15cm-30cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter30 >= 30 AND Diameter30 < 45 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '30cm-45cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter30 >= 30 AND Diameter30 < 45 THEN Volume30 / 100 
        ELSE 0 
        END), 2) AS '30cm-45cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter30 >= 45 AND Diameter30 < 60 THEN 1 
        ELSE 0 
        END) / 100, 3) AS '45cm-60cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter30 >= 45 AND Diameter30 < 60 THEN Volume30 / 100 
        ELSE 0 
        END), 2) AS '45cm-60cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter30 >= 60 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '60cm+_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter30 >= 60 THEN Volume30 / 100 
        ELSE 0 
        END), 2) AS '60cm+_Volume'
FROM 
    $selectedTableYear30
INNER JOIN year0_forest ON $selectedTableYear30.TreeNum = year0_forest.TreeNum
GROUP BY SpeciesGroup";

$resultStandTable = mysqli_query($conn, $sqlStandTable);

if (!$resultStandTable) {
    die("Failed to fetch data from the $selectedTableYear30 table: " . mysqli_error($conn));
}

// Get total number of records
$sqlCount = "SELECT COUNT(*) AS total FROM $selectedTableYear30";
$resultCount = mysqli_query($conn, $sqlCount);
$totalRecords = mysqli_fetch_assoc($resultCount)['total'];
$totalPages = ceil($totalRecords / $limit);

// Base SQL query for list of trees with pagination
$sqlTrees = "
SELECT 
    year0_forest.BlockX, 
    year0_forest.BlockY, 
    year0_forest.CoordX, 
    year0_forest.CoordY, 
    year0_forest.TreeNum, 
    year0_forest.Species, 
    year0_forest.SpeciesGroup, 
    $selectedTableYear30.Diameter30 AS Diameter, 
    $selectedTableYear30.DiameterClass30 AS DiameterClass, 
    year0_forest.Height, 
    $selectedTableYear30.Volume30 AS Volume
FROM 
    $selectedTableYear30
INNER JOIN year0_forest ON $selectedTableYear30.TreeNum = year0_forest.TreeNum
LIMIT $limit OFFSET $offset";

$resultTrees = mysqli_query($conn, $sqlTrees);

if (!$resultTrees) {
    die("Failed to fetch data from the $selectedTableYear30 table: " . mysqli_error($conn));
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
    <section id="about" class="about section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Year 30</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <!-- Filter Form for Regime -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header" style="background-color:rgb(155, 238, 230);">
                            <h5 class="card-title mb-0">Filter by Regime</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" action="">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="regime" class="form-label">Regime</label>
                                        <select id="regime" name="regime" class="form-control">
                                            <?php foreach ($regimeTablesYear30 as $key => $tableName): ?>
                                                <option value="<?= $key ?>" <?= ($regimeFilter == $key) ? 'selected' : '' ?>>
                                                    Regime <?= $key ?>
                                                </option>
                                            <?php endforeach; ?>
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
                    <h1 class="text-center">List Of Trees</h1>
                    <br>

                    <!-- Display forest data -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>BlockX</th>
                                    <th>BlockY</th>
                                    <th>CoordX</th>
                                    <th>CoordY</th>
                                    <th>TreeNum</th>
                                    <th>Species</th>
                                    <th>SpeciesGroup</th>
                                    <th>Diameter</th>
                                    <th>DiameterClass</th>
                                    <th>Height</th>
                                    <th>Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($resultTrees) > 0) {
                                    $counter = $offset + 1; // Initialize a counter for row numbering
                                    while ($row = mysqli_fetch_assoc($resultTrees)) {
                                        echo "<tr>
                                                <td>{$counter}</td>
                                                <td>{$row['BlockX']}</td>
                                                <td>{$row['BlockY']}</td>
                                                <td>{$row['CoordX']}</td>
                                                <td>{$row['CoordY']}</td>
                                                <td>{$row['TreeNum']}</td>
                                                <td>{$row['Species']}</td>
                                                <td>{$row['SpeciesGroup']}</td>
                                                <td>{$row['Diameter']}</td>
                                                <td>{$row['DiameterClass']}</td>
                                                <td>" . number_format($row['Height'], 2) . "</td>
                                                <td>" . number_format($row['Volume'], 2) . "</td>
                                            </tr>";
                                        $counter++; // Increment the counter
                                    }
                                } else {
                                    echo "<tr>
                                            <td colspan='12' class='text-center'>No data available in the forest table.</td>
                                        </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <style>
                        .pagination .page-item .page-link {
                            background-color: #f8f9fa;
                            /* Change this to your desired color */
                            color:rgb(42, 82, 78);
                            /* Change this to your desired text color */
                        }

                        .pagination .page-item.active .page-link {
                            background-color:rgb(72, 172, 162);
                            /* Change this to your desired active color */
                            color: #fff;
                            /* Change this to your desired active text color */
                        }
                        .pagination .page-item .page-link:hover {
                            background-color: rgb(72, 172, 162); /* Change this to your desired hover color */
                            color: #fff; /* Change this to your desired hover text color */
                        }
                    </style>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php
                            $maxPagesToShow = 10;
                            $startPage = max(1, $page - floor($maxPagesToShow / 2));
                            $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);
                            ?>

                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?regime=<?= $regimeFilter ?>&page=1" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?regime=<?= $regimeFilter ?>&page=<?= $page - 1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                    <a class="page-link" href="?regime=<?= $regimeFilter ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?regime=<?= $regimeFilter ?>&page=<?= $page + 1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?regime=<?= $regimeFilter ?>&page=<?= $totalPages ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>

                <div class="col-md-6">
                    <h1 class="text-center">Stand Table</h1>

                    <!-- Display the data in a table format -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered shadow-lg mt-4">
                            <thead class="thead-dark">
                                <tr>
                                    <th rowspan="2">Species Group</th>
                                    <th colspan="2">5cm-15cm</th>
                                    <th colspan="2">15cm-30cm</th>
                                    <th colspan="2">30cm-45cm</th>
                                    <th colspan="2">45cm-60cm</th>
                                    <th colspan="2">60cm+</th>
                                    <th rowspan="2">Total Trees</th>
                                    <th rowspan="2">Total Volume</th>
                                </tr>
                                <tr>
                                    <th>Trees</th>
                                    <th>Volume</th>
                                    <th>Trees</th>
                                    <th>Volume</th>
                                    <th>Trees</th>
                                    <th>Volume</th>
                                    <th>Trees</th>
                                    <th>Volume</th>
                                    <th>Trees</th>
                                    <th>Volume</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Initialize total counters
                                $totalTrees = [
                                    '5cm-15cm' => 0,
                                    '15cm-30cm' => 0,
                                    '30cm-45cm' => 0,
                                    '45cm-60cm' => 0,
                                    '60cm+' => 0,
                                    'all' => 0
                                ];
                                $totalVolume = [
                                    '5cm-15cm' => 0,
                                    '15cm-30cm' => 0,
                                    '30cm-45cm' => 0,
                                    '45cm-60cm' => 0,
                                    '60cm+' => 0,
                                    'all' => 0
                                ];

                                // Display the fetched data in the table
                                if (mysqli_num_rows($resultStandTable) > 0) {
                                    while ($row = mysqli_fetch_assoc($resultStandTable)) {
                                        // Calculate total trees and volume for each row
                                        $totalRowTrees = $row['5cm-15cm_Trees'] + $row['15cm-30cm_Trees'] + $row['30cm-45cm_Trees'] + $row['45cm-60cm_Trees'] + $row['60cm+_Trees'];
                                        $totalRowVolume = $row['5cm-15cm_Volume'] + $row['15cm-30cm_Volume'] + $row['30cm-45cm_Volume'] + $row['45cm-60cm_Volume'] + $row['60cm+_Volume'];

                                        // Add to total counters
                                        $totalTrees['5cm-15cm'] += $row['5cm-15cm_Trees'];
                                        $totalTrees['15cm-30cm'] += $row['15cm-30cm_Trees'];
                                        $totalTrees['30cm-45cm'] += $row['30cm-45cm_Trees'];
                                        $totalTrees['45cm-60cm'] += $row['45cm-60cm_Trees'];
                                        $totalTrees['60cm+'] += $row['60cm+_Trees'];
                                        $totalTrees['all'] += $totalRowTrees;

                                        $totalVolume['5cm-15cm'] += $row['5cm-15cm_Volume'];
                                        $totalVolume['15cm-30cm'] += $row['15cm-30cm_Volume'];
                                        $totalVolume['30cm-45cm'] += $row['30cm-45cm_Volume'];
                                        $totalVolume['45cm-60cm'] += $row['45cm-60cm_Volume'];
                                        $totalVolume['60cm+'] += $row['60cm+_Volume'];
                                        $totalVolume['all'] += $totalRowVolume;

                                        echo "<tr>
                                                <td>{$row['SpeciesGroupName']}</td>
                                                <td>{$row['5cm-15cm_Trees']}</td>
                                                <td>{$row['5cm-15cm_Volume']}</td>
                                                <td>{$row['15cm-30cm_Trees']}</td>
                                                <td>{$row['15cm-30cm_Volume']}</td>
                                                <td>{$row['30cm-45cm_Trees']}</td>
                                                <td>{$row['30cm-45cm_Volume']}</td>
                                                <td>{$row['45cm-60cm_Trees']}</td>
                                                <td>{$row['45cm-60cm_Volume']}</td>
                                                <td>{$row['60cm+_Trees']}</td>
                                                <td>{$row['60cm+_Volume']}</td>
                                                <td>{$totalRowTrees}</td>
                                                <td>{$totalRowVolume}</td>
                                              </tr>";
                                    }

                                    // Display total row
                                    echo "<tr>
                                            <td><strong>Total</strong></td>
                                            <td><strong>{$totalTrees['5cm-15cm']}</strong></td>
                                            <td><strong>{$totalVolume['5cm-15cm']}</strong></td>
                                            <td><strong>{$totalTrees['15cm-30cm']}</strong></td>
                                            <td><strong>{$totalVolume['15cm-30cm']}</strong></td>
                                            <td><strong>{$totalTrees['30cm-45cm']}</strong></td>
                                            <td><strong>{$totalVolume['30cm-45cm']}</strong></td>
                                            <td><strong>{$totalTrees['45cm-60cm']}</strong></td>
                                            <td><strong>{$totalVolume['45cm-60cm']}</strong></td>
                                            <td><strong>{$totalTrees['60cm+']}</strong></td>
                                            <td><strong>{$totalVolume['60cm+']}</strong></td>
                                            <td><strong>{$totalTrees['all']}</strong></td>
                                            <td><strong>{$totalVolume['all']}</strong></td>
                                          </tr>";
                                } else {
                                    echo "<tr><td colspan='13' class='text-center'>No data found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section><!-- /About Section -->
</main>

<?php
// Close the database connection
mysqli_close($conn);

// Include the footer file
include '../include/footer.php';
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