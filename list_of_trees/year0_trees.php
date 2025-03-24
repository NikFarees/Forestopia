<?php
// Include the database connection file
require_once '../db_connect.php';

// Include the header file
include '../include/header.php';

// Define pagination parameters
$limit = 100; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Calculate total pages for the list of trees
$sqlCountTrees = "SELECT COUNT(*) AS total FROM year0_forest";
$resultCountTrees = mysqli_query($conn, $sqlCountTrees);
$totalRecordsTrees = mysqli_fetch_assoc($resultCountTrees)['total'];
$totalPagesTrees = ceil($totalRecordsTrees / $limit);

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
        WHEN Diameter >= 5 AND Diameter < 15 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '5cm-15cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter >= 5 AND Diameter < 15 THEN Volume / 100 
        ELSE 0 
        END), 2) AS '5cm-15cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter >= 15 AND Diameter < 30 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '15cm-30cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter >= 15 AND Diameter < 30 THEN Volume / 100 
        ELSE 0 
        END), 2) AS '15cm-30cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter >= 30 AND Diameter < 45 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '30cm-45cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter >= 30 AND Diameter < 45 THEN Volume / 100 
        ELSE 0 
        END), 2) AS '30cm-45cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter >= 45 AND Diameter < 60 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '45cm-60cm_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter >= 45 AND Diameter < 60 THEN Volume / 100 
        ELSE 0 
        END), 2) AS '45cm-60cm_Volume',

    ROUND(SUM(CASE 
        WHEN Diameter >= 60 THEN 1 
        ELSE 0 
        END) / 100, 2) AS '60cm+_Trees',
    ROUND(SUM(CASE 
        WHEN Diameter >= 60 THEN Volume / 100 
        ELSE 0 
        END), 2) AS '60cm+_Volume'
FROM 
    year0_forest
GROUP BY SpeciesGroup";

$resultStandTable = mysqli_query($conn, $sqlStandTable);

if (!$resultStandTable) {
    die("Failed to fetch data from the year0_forest table: " . mysqli_error($conn));
}

// Base SQL query for list of trees with pagination
$sqlTrees = "SELECT * FROM year0_forest LIMIT $limit OFFSET $offset";

$resultTrees = mysqli_query($conn, $sqlTrees);

if (!$resultTrees) {
    die("Failed to fetch data from the year0_forest table: " . mysqli_error($conn));
}
?>

<main class="main">

    <!-- About Section -->
    <section id="about" class="about section">
        
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Year 0</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
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

                    <!-- Pagination Controls for List of Trees -->
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
                            $endPage = min($totalPagesTrees, $startPage + $maxPagesToShow - 1);
                            $startPage = max(1, $endPage - $maxPagesToShow + 1);

                            if ($page > 1) {
                                echo "<li class='page-item'><a class='page-link' href='?page=1'>&laquo;&laquo;</a></li>";
                                echo "<li class='page-item'><a class='page-link' href='?page=" . ($page - 1) . "'>&laquo;</a></li>";
                            }

                            for ($i = $startPage; $i <= $endPage; $i++) {
                                $active = ($i == $page) ? 'active' : '';
                                echo "<li class='page-item $active'><a class='page-link' href='?page=$i'>$i</a></li>";
                            }

                            if ($page < $totalPagesTrees) {
                                echo "<li class='page-item'><a class='page-link' href='?page=" . ($page + 1) . "'>&raquo;</a></li>";
                                echo "<li class='page-item'><a class='page-link' href='?page=$totalPagesTrees'>&raquo;&raquo;</a></li>";
                            }
                            ?>
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