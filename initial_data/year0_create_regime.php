<?php
include '../include/header.php';

// Set max execution time
ini_set('max_execution_time', 900);
?>

<style>
    /* Ensure the body and html take up the full height */
    html,
    body {
        height: 100%;
        margin: 0;
        display: flex;
        flex-direction: column;
    }

    /* Flex container to wrap the entire content */
    .flex-container {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
    }

    /* Main content area should take up the remaining space */
    .main-content {
        flex-grow: 1;
    }

    /* Footer should stay at the bottom */
    footer {
        flex-shrink: 0;
    }
</style>

<div class="flex-container">
    <main class="main main-content">
        <!-- About Section -->
        <section id="about" class="about section">
            <div class="container" data-aos="fade-up" data-aos-delay="100">
                <div class="container mt-5">
                    <?php
                    // Include the database connection file
                    require_once '../db_connect.php';

                    // Function to determine cut status and angles
                    function determineCutStatus($spGroup, $diameter, $threshold, $cutAngle = null)
                    {
                        if (in_array($spGroup, [1, 2, 3, 5]) && $diameter >= $threshold) {
                            $status = "Cut";

                            // Generate a random cut angle between 1 and 359 if not already set
                            if ($cutAngle === null) {
                                $cutAngle = rand(1, 359);
                            }
                            
                            // Adjust the falling angle based on the cut angle
                            if ($cutAngle >= 1 && $cutAngle <= 60) {
                                $fallAngle = $cutAngle;
                                $fallQuarter = 'Q1';
                            } elseif ($cutAngle > 60 && $cutAngle <= 180) {
                                $fallAngle = 180 - $cutAngle;
                                $fallQuarter = 'Q2';
                            } elseif ($cutAngle > 180 && $cutAngle <= 270) {
                                $fallAngle = $cutAngle - 180;
                                $fallQuarter = 'Q3';
                            } else {
                                $fallAngle = 360 - $cutAngle;
                                $fallQuarter = 'Q4';
                            }
                        } else {
                            $status = "Not Cut";
                            $cutAngle = NULL;
                            $fallAngle = NULL;
                            $fallQuarter = NULL;
                        }

                        return [$status, $cutAngle, $fallAngle, $fallQuarter];
                    }

                    // Array of regime thresholds and table names
                    $regimes = [
                        45 => "year0_regime45",
                        50 => "year0_regime50",
                        55 => "year0_regime55",
                        60 => "year0_regime60"
                    ];

                    // Clear the regime tables before inserting new data
                    foreach ($regimes as $regimeTable) {
                        $truncateSql = "TRUNCATE TABLE $regimeTable";
                        if (!mysqli_query($conn, $truncateSql)) {
                            die("Failed to clear the $regimeTable table: " . mysqli_error($conn));
                        }
                    }

                    // Fetch all trees from the year0_forest table
                    $fetchSql = "SELECT TreeNum, SpeciesGroup, Diameter FROM year0_forest";
                    $result = mysqli_query($conn, $fetchSql);

                    if (mysqli_num_rows($result) > 0) {
                        $insertValues = [];
                        $insertQuery = [];

                        foreach ($regimes as $threshold => $regimeTable) {
                            $insertQuery[$regimeTable] = "INSERT INTO $regimeTable (TreeNum, CutAngle, FallAngle, FallQuarter, Status) VALUES ";
                            $insertValues[$regimeTable] = [];
                        }

                        while ($row = mysqli_fetch_assoc($result)) {
                            $TreeNum = $row['TreeNum'];
                            $spGroup = $row['SpeciesGroup'];
                            $diameter = $row['Diameter'];

                            // Generate cut status, cut angle, fall angle, and fall quarter once
                            list($status, $cutAngle, $fallAngle, $fallQuarter) = determineCutStatus($spGroup, $diameter, min(array_keys($regimes)));

                            foreach ($regimes as $threshold => $regimeTable) {
                                // Determine cut status, cut angle, fall angle, and fall quarter for each regime
                                list($status, $cutAngle, $fallAngle, $fallQuarter) = determineCutStatus($spGroup, $diameter, $threshold, $cutAngle);

                                // Prepare the insert values
                                $insertValues[$regimeTable][] = "('$TreeNum', " . ($cutAngle ?? 'NULL') . ", " . ($fallAngle ?? 'NULL') . ", " . ($fallQuarter ? "'$fallQuarter'" : 'NULL') . ", '$status')";

                                // Batch insert every 1000 records
                                if (count($insertValues[$regimeTable]) >= 1000) {
                                    $insertQuery[$regimeTable] .= implode(", ", $insertValues[$regimeTable]);
                                    mysqli_query($conn, $insertQuery[$regimeTable]);
                                    $insertValues[$regimeTable] = [];
                                    $insertQuery[$regimeTable] = "INSERT INTO $regimeTable (TreeNum, CutAngle, FallAngle, FallQuarter, Status) VALUES ";
                                }
                            }
                        }

                        // Insert remaining records
                        foreach ($regimes as $regimeTable) {
                            if (count($insertValues[$regimeTable]) > 0) {
                                $insertQuery[$regimeTable] .= implode(", ", $insertValues[$regimeTable]);
                                mysqli_query($conn, $insertQuery[$regimeTable]);
                            }
                        }
                    }

                    // Close the database connection
                    mysqli_close($conn);
                    ?>

                    <div class="d-flex flex-column align-items-center mt-5">
                        <h1 class="display-4 text-center mb-4">Regime Data Generation Completed!</h1>
                        <p class="lead text-center mb-4">The data for regimes 45, 50, 55, and 60 has been successfully generated and saved.</p>

                        <div class="d-flex justify-content-center align-items-center milestone-container">
                            <!-- Step 1 -->
                            <div class="milestone">
                                <div class="circle">1</div>
                                <p class="step-label">Step 1</p>
                                <p class="step-description">Create Forest</p>
                            </div>
                            <div class="connector"></div>
                            <!-- Step 2 -->
                            <div class="milestone">
                                <div class="circle">2</div>
                                <p class="step-label">Step 2</p>
                                <p class="step-description">Generate Regime</p>
                            </div>
                            <div class="connector"></div>
                            <!-- Step 3 -->
                            <div class="milestone">
                                <div class="circle">3</div>
                                <p class="step-label">Step 3</p>
                                <p class="step-description">Identify Victim</p>
                            </div>
                            <div class="connector"></div>
                            <!-- Step 4 -->
                            <div class="milestone">
                                <div class="circle">4</div>
                                <p class="step-label">Step 4</p>
                                <p class="step-description">Simulate Forest 30</p>
                            </div>
                        </div>
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
                        <a class="btn custom-button btn-lg mt-4" href="year0_identify_victim.php" role="button">Next Step</a>
                    </div>
                </div>
            </div>
        </section><!-- /About Section -->
    </main>

    <?php include '../include/footer.php'; ?>
</div>

<style>
    .milestone-container {
        display: flex;
        align-items: center;
        margin-top: 20px;
    }

    .milestone {
        text-align: center;
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Center-aligns the contents */
    }

    .circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .green-border {
        border: 3px solid #28a745;
        color: #28a745;
        background: #e9f7ef;
    }

    .gray-border {
        border: 3px solid #6c757d;
        color: #6c757d;
        background: #f8f9fa;
    }

    .green-text {
        color: #28a745;
    }

    .connector {
        width: 50px;
        height: 3px;
        margin: 0 10px;
    }

    .green-line {
        background: #28a745;
    }

    .gray-line {
        background: #6c757d;
    }

    .step-label {
        font-size: 0.9rem;
        font-weight: bold;
    }

    .step-description {
        font-size: 0.9rem;
    }

    .btn {
        margin-top: 20px;
    }
</style>

<script>
    function updateMilestones(currentStep) {
        const steps = document.querySelectorAll('.milestone .circle');
        const connectors = document.querySelectorAll('.connector');

        steps.forEach((step, index) => {
            if (index < currentStep) {
                step.classList.remove('gray-border');
                step.classList.add('green-border');
            } else {
                step.classList.remove('green-border');
                step.classList.add('gray-border');
            }
        });

        const labels = document.querySelectorAll('.milestone .step-label');
        const descriptions = document.querySelectorAll('.milestone .step-description');

        labels.forEach((label, index) => {
            if (index < currentStep) {
                label.classList.add('green-text');
            } else {
                label.classList.remove('green-text');
            }
        });

        descriptions.forEach((desc, index) => {
            if (index < currentStep) {
                desc.classList.add('green-text');
            } else {
                desc.classList.remove('green-text');
            }
        });

        connectors.forEach((connector, index) => {
            if (index < currentStep - 1) {
                connector.classList.remove('gray-line');
                connector.classList.add('green-line');
            } else {
                connector.classList.remove('green-line');
                connector.classList.add('gray-line');
            }
        });
    }

    // Example: Update to Step 1
    updateMilestones(2); // Change the number to update to your desired step dynamically
</script>