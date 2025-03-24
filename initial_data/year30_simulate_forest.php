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
                    require_once '../db_connect.php';

                    function calculateVolume($diameter, $height, $spGroup)
                    {
                        $D = number_format(($diameter / 100), 2);
                        $H = number_format(($height), 2);

                        if ($spGroup <= 4) {
                            return ($D < 15) ? (0.022 + (3.4 * pow($D, 2))) : (0.015 + (2.137 * pow($D, 2)) + (0.513 * pow($D, 2) * $H));
                        } else {
                            return ($D < 15) ? (0.03 + (2.8 * pow($D, 2))) : (-0.0023 + (2.942 * pow($D, 2)) + (0.262 * pow($D, 2) * $H));
                        }
                    }

                    function getDiameterClass($diameter)
                    {
                        if ($diameter >= 5 && $diameter < 15) return 1;
                        if ($diameter >= 15 && $diameter < 30) return 2;
                        if ($diameter >= 30 && $diameter < 45) return 3;
                        if ($diameter >= 45 && $diameter < 60) return 4;
                        return 5;
                    }

                    function calculateGrowth($diameter)
                    {
                        if ($diameter >= 5 && $diameter < 15) return $diameter + 0.4;
                        if ($diameter >= 15 && $diameter < 30) return $diameter + 0.6;
                        if ($diameter >= 30 && $diameter < 45) return $diameter + 0.5;
                        if ($diameter >= 45 && $diameter < 60) return $diameter + 0.5;
                        return $diameter + 0.7;
                    }

                    function determineStatus($spGroup, $diameter)
                    {
                        $status45 = (in_array($spGroup, [1, 2, 3, 5]) && $diameter >= 45) ? "Cut" : "Not Cut";
                        $status50 = (in_array($spGroup, [1, 2, 3, 5]) && $diameter >= 50) ? "Cut" : "Not Cut";
                        $status55 = (in_array($spGroup, [1, 2, 3, 5]) && $diameter >= 55) ? "Cut" : "Not Cut";
                        $status60 = (in_array($spGroup, [1, 2, 3, 5]) && $diameter >= 60) ? "Cut" : "Not Cut";

                        return [$status45, $status50, $status55, $status60];
                    }

                    $regimes = [
                        45 => "year30_regime45",
                        50 => "year30_regime50",
                        55 => "year30_regime55",
                        60 => "year30_regime60"
                    ];

                    foreach ($regimes as $threshold => $tableName) {
                        mysqli_query($conn, "TRUNCATE TABLE $tableName");

                        $year0RegimeTable = "year0_regime" . $threshold;

                        $fetchSql = "SELECT yf.TreeNum, yf.SpeciesGroup, yf.Diameter, yf.Height
                                    FROM year0_forest yf
                                    JOIN $year0RegimeTable yr ON yf.TreeNum = yr.TreeNum
                                    WHERE yr.Status = 'Not Cut';";

                        $result = mysqli_query($conn, $fetchSql);

                        if (mysqli_num_rows($result) > 0) {
                            $insertValues = [];
                            $insertQuery = "INSERT INTO $tableName (TreeNum, Diameter30, DiameterClass30, Volume30, Status45, Status50, Status55, Status60) VALUES ";

                            while ($row = mysqli_fetch_assoc($result)) {
                                $TreeNum = $row['TreeNum'];
                                $spGroup = $row['SpeciesGroup'];
                                $diameter = $row['Diameter'];
                                $height = $row['Height'];

                                for ($year = 1; $year <= 30; $year++) {
                                    $diameter = calculateGrowth($diameter);
                                }

                                $diameterClass = getDiameterClass($diameter);
                                $volume = calculateVolume($diameter, $height, $spGroup);
                                list($status45, $status50, $status55, $status60) = determineStatus($spGroup, $diameter);

                                $insertValues[] = "('$TreeNum', $diameter, $diameterClass, $volume, '$status45', '$status50', '$status55', '$status60')";

                                if (count($insertValues) >= 1000) {
                                    mysqli_query($conn, $insertQuery . implode(", ", $insertValues));
                                    $insertValues = [];
                                }
                            }

                            if (count($insertValues) > 0) {
                                mysqli_query($conn, $insertQuery . implode(", ", $insertValues));
                            }
                        }
                    }

                    mysqli_close($conn);
                    ?>

                    <div class="d-flex flex-column align-items-center mt-5">
                        <h1 class="display-4 text-center mb-4">Year 30 Simulation Completed!</h1>
                        <p class="lead text-center mb-4">Growth and cutting regime for year 30 have been successfully simulated and saved.</p>

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
                        <a class="btn custom-button btn-lg mt-4" href="../index.php" role="button">Complete</a>
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
    updateMilestones(4); // Change the number to update to your desired step dynamically
</script>