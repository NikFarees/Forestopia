<?php
// Include the database connection file
require_once '../db_connect.php';

// Include the header file
include '../include/header.php';

// Initialize filter variables
$regimeFilter = isset($_GET['regime']) ? $_GET['regime'] : 45; // Default to regime 45

// Select the relevant regime table based on the regimeFilter
switch ($regimeFilter) {
    case 50:
        $regimeTable = 'year0_regime50';
        break;
    case 55:
        $regimeTable = 'year0_regime55';
        break;
    case 60:
        $regimeTable = 'year0_regime60';
        break;
    default:
        $regimeTable = 'year0_regime45';
        break;
}

// Pagination parameters
$limit = 100; // Number of records per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Base SQL query, considering the regime filter and Status = 'Victim'
$sql = "
    SELECT f.BlockX, f.BlockY, f.CoordX, f.CoordY, f.TreeNum, f.Species, f.SpeciesGroup, 
           f.Diameter, f.DiameterClass, f.Volume, r.Status
    FROM year0_forest f
    LEFT JOIN $regimeTable r ON f.TreeNum = r.TreeNum
    WHERE r.Status = 'Victim'
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Failed to fetch data from the regime table: " . mysqli_error($conn));
}

// Get total number of records
$sqlCount = "
    SELECT COUNT(*) AS total
    FROM year0_forest f
    LEFT JOIN $regimeTable r ON f.TreeNum = r.TreeNum
    WHERE r.Status = 'Victim'
";
$resultCount = mysqli_query($conn, $sqlCount);
$totalRecords = mysqli_fetch_assoc($resultCount)['total'];
$totalPages = ceil($totalRecords / $limit);
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
            <h2>Damage</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">
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
                                            <option value="45" <?php echo ($regimeFilter == 45) ? 'selected' : ''; ?>>Regime 45</option>
                                            <option value="50" <?php echo ($regimeFilter == 50) ? 'selected' : ''; ?>>Regime 50</option>
                                            <option value="55" <?php echo ($regimeFilter == 55) ? 'selected' : ''; ?>>Regime 55</option>
                                            <option value="60" <?php echo ($regimeFilter == 60) ? 'selected' : ''; ?>>Regime 60</option>
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
                                        <button type="submit" class="btn custom-button w-100">Apply Filters</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Check if there are records in the table
            if (mysqli_num_rows($result) > 0) {
                echo "<div class='table-responsive'>
                    <table class='table table-striped table-bordered mt-4 shadow'>
                        <thead class='thead-dark'>
                            <tr>
                                <th>#</th>
                                <th>BlockX</th>
                                <th>BlockY</th>
                                <th>X</th>
                                <th>Y</th>
                                <th>TreeNum</th>
                                <th>Species</th>
                                <th>Species Group</th>
                                <th>Diameter (cm)</th>
                                <th>Diameter Class</th>
                                <th>Volume (m³)</th>
                            </tr>
                        </thead>
                        <tbody>";

                // Initialize a counter for the increment number
                $counter = $offset + 1;

                // Loop through each record and display it
                while ($row = mysqli_fetch_assoc($result)) {
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
                        <td>" . number_format($row['Volume'], 2) . "</td>
                    </tr>";

                    // Increment the counter after each row
                    $counter++;
                }

                echo "</tbody>
                    </table>
                </div>";

                // Pagination Controls
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
                echo "<nav aria-label='Page navigation'>
                        <ul class='pagination justify-content-center'>";
                $maxPagesToShow = 10;
                $startPage = max(1, $page - floor($maxPagesToShow / 2));
                $endPage = min($totalPages, $startPage + $maxPagesToShow - 1);
                $startPage = max(1, $endPage - $maxPagesToShow + 1);

                if ($page > 1) {
                    echo "<li class='page-item'><a class='page-link' href='?regime=$regimeFilter&page=1'>&laquo;&laquo;</a></li>";
                    echo "<li class='page-item'><a class='page-link' href='?regime=$regimeFilter&page=" . ($page - 1) . "'>&laquo;</a></li>";
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    $active = ($i == $page) ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='?regime=$regimeFilter&page=$i'>$i</a></li>";
                }

                if ($page < $totalPages) {
                    echo "<li class='page-item'><a class='page-link' href='?regime=$regimeFilter&page=" . ($page + 1) . "'>&raquo;</a></li>";
                    echo "<li class='page-item'><a class='page-link' href='?regime=$regimeFilter&page=$totalPages'>&raquo;&raquo;</a></li>";
                }

                echo "</ul></nav>";
            } else {
                echo "<p class='text-center'>No victim trees found for the selected filters.</p>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </div>
    </section><!-- /About Section -->
</main>

<?php include '../include/footer.php'; ?>

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