<?php
// Include the database connection file
require_once '../db_connect.php';

// Include the header file
include '../include/header.php';

// Check the database connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Define regime table mapping for year 0
$regimeTablesYear0 = [
    '45' => 'year0_regime45',
    '50' => 'year0_regime50',
    '55' => 'year0_regime55',
    '60' => 'year0_regime60',
];

// Define regime table mapping for year 30
$regimeTablesYear30 = [
    '45' => 'year30_regime45',
    '50' => 'year30_regime50',
    '55' => 'year30_regime55',
    '60' => 'year30_regime60',
];

// Define statuses for Year 30
$statusesYear30 = [
    '45' => 'Status45',
    '50' => 'Status50',
    '55' => 'Status55',
    '60' => 'Status60',
];

// Retrieve the selected filters from GET parameters, defaulting to Year 0 Regime 45 and Year 30 Status45
$cuttingRegimeYear0 = $_GET['year0Regime'] ?? '45';
$statusFilterYear30 = $_GET['year30Status'] ?? '45';

// Validate the selected regimes and statuses
$selectedTableYear0 = $regimeTablesYear0[$cuttingRegimeYear0] ?? 'year0_regime45';
$selectedTableYear30 = $regimeTablesYear30[$cuttingRegimeYear0] ?? 'year30_regime45';
$selectedStatusYear30 = $statusesYear30[$statusFilterYear30] ?? 'Status45';

// Pagination parameters
$limit = 100; // Number of records per page
$pageYear0 = intval($_GET['pageYear0'] ?? 1);
$offsetYear0 = ($pageYear0 - 1) * $limit;
$pageYear30 = intval($_GET['pageYear30'] ?? 1);
$offsetYear30 = ($pageYear30 - 1) * $limit;

function renderPagination($currentPage, $totalPages, $urlParam, $additionalParams = '')
{
    $maxPagesToShow = 10; // Maximum number of pages to display
    $pagination = "<nav><ul class='pagination justify-content-center'>";

    // First and Previous Page Links
    if ($currentPage > 1) {
        $pagination .= "<li class='page-item'><a class='page-link' href='?$urlParam=1$additionalParams'>&raquo;&raquo;</a></li>";
        $pagination .= "<li class='page-item'><a class='page-link' href='?$urlParam=" . ($currentPage - 1) . "$additionalParams'>&raquo;</a></li>";
    }

    // Calculate start and end page
    $startPage = max(1, $currentPage - floor($maxPagesToShow / 2));
    $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);

    // Adjust if near the end of pages
    if ($endPage - $startPage + 1 < $maxPagesToShow) {
        $startPage = max(1, $endPage - $maxPagesToShow + 1);
    }

    // Page Number Links
    for ($i = $startPage; $i <= $endPage; $i++) {
        $activeClass = ($i == $currentPage) ? 'active' : '';
        $pagination .= "<li class='page-item $activeClass'><a class='page-link' href='?$urlParam=$i$additionalParams'>$i</a></li>";
    }

    // Next and Last Page Links
    if ($currentPage < $totalPages) {
        $pagination .= "<li class='page-item'><a class='page-link' href='?$urlParam=" . ($currentPage + 1) . "$additionalParams'>&raquo;</a></li>";
        $pagination .= "<li class='page-item'><a class='page-link' href='?$urlParam=$totalPages$additionalParams'>&raquo;&raquo;</a></li>";
    }

    $pagination .= "</ul></nav>";
    return $pagination;
}

?>

<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-grid .form-group:nth-child(3) {
        grid-column: span 2;
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
        display: block;
        width: 100%;
        padding: 0.5rem;
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        width: auto;
        min-width: 150px;
    }

    .btn:hover {
        background-color: #0056b3;
    }

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

    .table {
        border: 1px solid #dee2e6;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
</style>

<main class="main">
    <section id="about" class="about section">
        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Production</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Filter Form in a Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header" style="background-color:rgb(155, 238, 230);">
                            <h5 class="card-title mb-0">Filter by Cutting Regime</h5>
                        </div>
                        <div class="card-body">
                            <form method="get" action="">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="year0Regime" class="form-label">Year 0 Regime</label>
                                        <select id="year0Regime" name="year0Regime" class="form-control">
                                            <?php foreach ($regimeTablesYear0 as $key => $tableName): ?>
                                                <option value="<?= $key ?>" <?= ($cuttingRegimeYear0 == $key) ? 'selected' : '' ?>>
                                                    Regime <?= $key ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="year30Status" class="form-label">Year 30 Regime</label>
                                        <select id="year30Status" name="year30Status" class="form-control">
                                            <?php foreach ($statusesYear30 as $key => $status): ?>
                                                <option value="<?= $key ?>" <?= ($statusFilterYear30 == $key) ? 'selected' : '' ?>>
                                                    Regime <?= $key ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
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

                <div class="col-md-6">
                    <h1 class="text-center">Year 0</h1>
                    <?php
                    // Calculate total rows for Year 0
                    $totalRowsYear0Query = "SELECT COUNT(*) AS total FROM $selectedTableYear0 WHERE status = 'Cut'";
                    $totalRowsYear0Result = mysqli_query($conn, $totalRowsYear0Query);
                    $totalRowsYear0 = mysqli_fetch_assoc($totalRowsYear0Result)['total'];

                    // Calculate total pages for Year 0
                    $totalPagesYear0 = ceil($totalRowsYear0 / $limit);

                    // Fetch Year 0 Data
                    $sqlYear0 = "SELECT 
                    $selectedTableYear0.TreeNum, 
                    year0_forest.SpeciesGroup, 
                    year0_forest.Diameter, 
                    year0_forest.Volume 
                FROM $selectedTableYear0
                INNER JOIN year0_forest ON $selectedTableYear0.TreeNum = year0_forest.TreeNum
                WHERE $selectedTableYear0.status = 'Cut'
                LIMIT ? OFFSET ?";

                    $stmtYear0 = $conn->prepare($sqlYear0);
                    $stmtYear0->bind_param('ii', $limit, $offsetYear0);
                    $stmtYear0->execute();
                    $resultYear0 = $stmtYear0->get_result();

                    if ($resultYear0->num_rows > 0) {
                        echo "<div class='table-responsive'>
                <table class='table table-striped table-bordered mt-4 shadow-sm'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>No.</th>
                            <th>TreeNum</th>
                            <th>Species Group</th>
                            <th>Diameter (cm)</th>
                            <th>Volume (m³)</th>
                        </tr>
                    </thead>
                    <tbody>";

                        $counter = $offsetYear0 + 1;
                        while ($row = $resultYear0->fetch_assoc()) {
                            echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row['TreeNum']}</td>
                    <td>{$row['SpeciesGroup']}</td>
                    <td>{$row['Diameter']}</td>
                    <td>" . number_format($row['Volume'], 2) . "</td>
                </tr>";
                            $counter++;
                        }

                        echo "</tbody></table></div>";
                    } else {
                        echo "<p class='text-center'>No tree data available for the selected regime.</p>";
                    }

                    // Display Year 0 Pagination
                    if ($totalPagesYear0 > 1) {
                        echo "<style>
                        .pagination .page-item .page-link {
                            background-color: #f8f9fa; /* Change this to your desired color */
                            color: rgb(42, 82, 78); /* Change this to your desired text color */
                        }
                        .pagination .page-item.active .page-link {
                            background-color: rgb(72, 172, 162); /* Change this to your desired active color */
                            color: #fff; /* Change this to your desired active text color */
                        }
                        .pagination .page-item .page-link:hover {
                            background-color: rgb(72, 172, 162); /* Change this to your desired hover color */
                            color: #fff; /* Change this to your desired hover text color */
                        }
                        </style>";
                        echo renderPagination($pageYear0, $totalPagesYear0, 'pageYear0', "&year0Regime=$cuttingRegimeYear0&year30Status=$statusFilterYear30");
                    }

                    ?>
                </div>

                <div class="col-md-6">
                    <h1 class="text-center">Year 30</h1>
                    <?php
                    // Calculate total rows for Year 30
                    $totalRowsYear30Query = "SELECT COUNT(*) AS total FROM $selectedTableYear30 WHERE $selectedStatusYear30 = 'Cut'";
                    $totalRowsYear30Result = mysqli_query($conn, $totalRowsYear30Query);
                    $totalRowsYear30 = mysqli_fetch_assoc($totalRowsYear30Result)['total'];

                    // Calculate total pages for Year 30
                    $totalPagesYear30 = ceil($totalRowsYear30 / $limit);

                    // Fetch Year 30 Data
                    $sqlYear30 = "SELECT 
                    $selectedTableYear30.TreeNum, 
                    year0_forest.SpeciesGroup, 
                    $selectedTableYear30.Diameter30 AS Diameter, 
                    $selectedTableYear30.Volume30 AS Volume 
                FROM $selectedTableYear30
                INNER JOIN year0_forest ON $selectedTableYear30.TreeNum = year0_forest.TreeNum
                WHERE $selectedTableYear30.$selectedStatusYear30 = 'Cut'
                LIMIT ? OFFSET ?";

                    $stmtYear30 = $conn->prepare($sqlYear30);
                    $stmtYear30->bind_param('ii', $limit, $offsetYear30);
                    $stmtYear30->execute();
                    $resultYear30 = $stmtYear30->get_result();

                    if ($resultYear30->num_rows > 0) {
                        echo "<div class='table-responsive'>
                <table class='table table-striped table-bordered mt-4 shadow-sm'>
                    <thead class='thead-dark'>
                        <tr>
                            <th>No.</th>
                            <th>TreeNum</th>
                            <th>Species Group</th>
                            <th>Diameter (cm)</th>
                            <th>Volume (m³)</th>
                        </tr>
                    </thead>
                    <tbody>";

                        $counter = $offsetYear30 + 1;
                        while ($row = $resultYear30->fetch_assoc()) {
                            echo "<tr>
                    <td>{$counter}</td>
                    <td>{$row['TreeNum']}</td>
                    <td>{$row['SpeciesGroup']}</td>
                    <td>{$row['Diameter']}</td>
                    <td>" . number_format($row['Volume'], 2) . "</td>
                </tr>";
                            $counter++;
                        }

                        echo "</tbody></table></div>";
                    } else {
                        echo "<p class='text-center'>No tree data available for the selected regime.</p>";
                    }

                    // Display Year 30 Pagination
                    if ($totalPagesYear30 > 1) {
                        echo renderPagination($pageYear30, $totalPagesYear30, 'pageYear30', "&year0Regime=$cuttingRegimeYear0&year30Status=$statusFilterYear30");
                    }

                    ?>
                </div>

            </div>
        </div>
    </section>
</main>

<?php include '../include/footer.php'; ?>

<?php
// Close the database connection
$conn->close();
?>