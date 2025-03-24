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

                    // Disable foreign key checks
                    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");

                    // Clear the `year0_forest` table before inserting new data
                    $truncateForestSql = "TRUNCATE TABLE year0_forest";

                    if (!mysqli_query($conn, $truncateForestSql)) {
                        die("Failed to clear the year0_forest table: " . mysqli_error($conn));
                    }

                    // Re-enable foreign key checks
                    mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

                    // Configuration variables for the forest data generation
                    $NoBlockX = 10;
                    $NoBlockY = 10;
                    $NoGroupSpecies = 7;
                    $NumDclass = 5;

                    // Define the species group names
                    $speciesGroupNames = [
                        1 => 'Mersawa',
                        2 => 'Keruing',
                        3 => 'Dip Commercial',
                        4 => 'Dip Non Commercial',
                        5 => 'Non Dip Commercial',
                        6 => 'Non Dip Non Commercial',
                        7 => 'Other'
                    ];

                    // Define the number of trees per hectare for each species group and diameter class
                    $TreePerha = [
                        [15, 21, 21, 30, 30, 39, 44],
                        [12, 18, 18, 27, 27, 36, 42],
                        [4, 6, 6, 9, 9, 12, 14],
                        [2, 4, 4, 5, 4, 7, 9],
                        [2, 4, 4, 3, 4, 4, 4]
                    ];

                    // Fetch species data from the database
                    $ListSpecies = [];
                    $speciesGroupMapping = [];
                    $sql = "SELECT NO, SPECODE, SPEC_GR FROM speciesname";
                    $result = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $ListSpecies[$row['NO']] = $row['SPECODE'];
                            $speciesGroupMapping[$row['SPECODE']] = $row['SPEC_GR'];
                        }
                    } else {
                        die("No species found in the database.");
                    }

                    // Function to calculate the volume
                    function calculateVolume($diameter, $height, $spGroup)
                    {
                        // Convert to meters
                        $D = number_format(($diameter / 100), 2);
                        $H = number_format(($height), 2);

                        if ($spGroup <= 4) { // Dipterocarp groups (Group 1, 2, 3, 4)
                            if ($D < 15) {
                                return 0.022 + (3.4 * pow($D, 2));
                            } else {
                                return 0.015 + (2.137 * pow($D, 2)) + (0.513 * pow($D, 2) * $H);
                            }
                        } else { // Non-Dipterocarp groups (Group 5, 6, 7)
                            if ($D < 15) {
                                return 0.03 + (2.8 * pow($D, 2));
                            } else {
                                return -0.0023 + (2.942 * pow($D, 2)) + (0.262 * pow($D, 2) * $H);
                            }
                        }
                    }

                    // Function to assign species group
                    function assignSpeciesGroup($speciesGroup, $ListSpecies, $speciesGroupMapping)
                    {
                        if ($speciesGroup == 2) { // Special case for Group 2
                            $speciesIndex = (rand(0, 1) == 0) ? rand(2, 5) : rand(317, 318);
                        } else {
                            switch ($speciesGroup) {
                                case 1:
                                    $speciesIndex = rand(1, 1);
                                    break;
                                case 3:
                                    $speciesIndex = rand(6, 12);
                                    break;
                                case 4:
                                    $speciesIndex = rand(13, 19);
                                    break;
                                case 5:
                                    $speciesIndex = rand(20, 59);
                                    break;
                                case 6:
                                    $speciesIndex = rand(60, 155);
                                    break;
                                case 7:
                                    $speciesIndex = rand(156, 316);
                                    break;
                            }
                        }
                        $species = $ListSpecies[$speciesIndex] ?? "Unknown";
                        $spGroup = $speciesGroupMapping[$species] ?? $speciesGroup;
                        return [$species, $spGroup];
                    }

                    // Function to assign diameter
                    function assignDiameter($diameterClass)
                    {
                        switch ($diameterClass) {
                            case 1:
                                return rand(500, 1500) / 100;  // 5m to 15m
                            case 2:
                                return rand(1500, 3000) / 100;  // 15m to 30m
                            case 3:
                                return rand(3000, 4500) / 100;  // 30m to 45m
                            case 4:
                                return rand(4500, 6000) / 100;  // 45m to 60m
                            case 5:
                                return rand(6000, 12000) / 100;  // 60m to 120m
                        }
                    }

                    // Function to assign height
                    function assignHeight($diameterClass)
                    {
                        switch ($diameterClass) {
                            case 1:
                                return rand(250, 550) / 100;  // 2.5m to 5.5m
                            case 2:
                                return rand(550, 1000) / 100;  // 5.5m to 10m
                            case 3:
                                return rand(1000, 2000) / 100;  // 10m to 20m
                            case 4:
                                return rand(2000, 4000) / 100;  // 20m to 40m
                            case 5:
                                return rand(1500, 4000) / 100;  // 15m to 40m
                        }
                    }

                    // Function to randomly assign tree coordinates within the block
                    function assignTreeCoordinates($blockX, $blockY, &$generatedCoordinates)
                    {
                        do {
                            $CoordX = ($blockX - 1) * 100 + rand(1, 100);
                            $CoordY = ($blockY - 1) * 100 + rand(1, 100);
                            $coordKey = $CoordX . ',' . $CoordY;
                        } while (isset($generatedCoordinates[$coordKey]));

                        // Store the generated coordinates to avoid duplicates
                        $generatedCoordinates[$coordKey] = true;

                        return [$CoordX, $CoordY];
                    }

                    // Generate and insert data into the year0_forest table
                    $generatedCoordinates = [];
                    $insertValues = [];
                    $insertQuery = "INSERT INTO year0_forest (BlockX, BlockY, CoordX, CoordY, TreeNum, Species, SpeciesGroup, Diameter, DiameterClass, Height, Volume) VALUES ";

                    for ($blockX = 1; $blockX <= $NoBlockX; $blockX++) {
                        for ($blockY = 1; $blockY <= $NoBlockY; $blockY++) {
                            for ($diameterClass = 1; $diameterClass <= $NumDclass; $diameterClass++) {
                                for ($speciesGroup = 1; $speciesGroup <= $NoGroupSpecies; $speciesGroup++) {
                                    $NumTrees = $TreePerha[$diameterClass - 1][$speciesGroup - 1];

                                    for ($tree = 1; $tree <= $NumTrees; $tree++) {
                                        // Assign species and other tree attributes
                                        list($species, $spGroup) = assignSpeciesGroup($speciesGroup, $ListSpecies, $speciesGroupMapping);

                                        // Assign diameter and height
                                        $diameter = assignDiameter($diameterClass);
                                        $height = assignHeight($diameterClass);

                                        // Calculate volume
                                        $volume = calculateVolume($diameter, $height, $spGroup);

                                        // Randomly assign tree coordinates (x, y) within the block
                                        list($CoordX, $CoordY) = assignTreeCoordinates($blockX, $blockY, $generatedCoordinates);

                                        // Generate TreeNum in the format TBxByCxCy
                                        $TreeNum = sprintf("T%02d%02d%03d%03d", $blockX, $blockY, $CoordX, $CoordY);

                                        // Prepare the insert values
                                        $insertValues[] = "($blockX, $blockY, $CoordX, $CoordY, '$TreeNum', '$species', $spGroup, $diameter, $diameterClass, $height, $volume)";

                                        // Batch insert every 1000 records
                                        if (count($insertValues) >= 1000) {
                                            $insertQuery .= implode(", ", $insertValues);
                                            mysqli_query($conn, $insertQuery);
                                            $insertValues = [];
                                            $insertQuery = "INSERT INTO year0_forest (BlockX, BlockY, CoordX, CoordY, TreeNum, Species, SpeciesGroup, Diameter, DiameterClass, Height, Volume) VALUES ";
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Insert remaining records
                    if (count($insertValues) > 0) {
                        $insertQuery .= implode(", ", $insertValues);
                        mysqli_query($conn, $insertQuery);
                    }

                    // Close the database connection
                    mysqli_close($conn);
                    ?>

                    <div class="d-flex flex-column align-items-center mt-5">
                        <h1 class="display-4 text-center mb-4">Forest Data Generation Completed!</h1>
                        <p class="lead text-center mb-4">The forest data has been successfully inserted into the database.</p>

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
                        <a class="btn custom-button btn-lg mt-4" href="year0_create_regime.php" role="button">Next Step</a>
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
    updateMilestones(1); // Change the number to update to your desired step dynamically
</script>