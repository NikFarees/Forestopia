<?php
// Include the database connection file
require_once '../db_connect.php';

// Include the header file
include '../include/header.php';

// Set max execution time
ini_set('max_execution_time', 1300);

// Function to identify and update victims
function identify_and_update_victims($regime_table, $diameter_limit)
{
    global $conn;

    // Query to fetch cut trees from the specified regime table
    $cut_trees_query = "SELECT c.TreeNum, c.CutAngle, c.FallAngle, c.FallQuarter, 
                            f.BlockX, f.BlockY, f.CoordX, f.CoordY, f.Diameter, f.Height
                    FROM $regime_table AS c
                    JOIN year0_forest AS f ON c.TreeNum = f.TreeNum
                    WHERE c.Status = 'Cut' AND f.Diameter >= ?";
    $stmt = $conn->prepare($cut_trees_query);
    $stmt->bind_param("d", $diameter_limit);
    $stmt->execute();
    $cut_trees_result = $stmt->get_result();

    if (!$cut_trees_result) {
        die('Error fetching cut trees: ' . mysqli_error($conn));
    }

    $update_queries = [];

    // Iterate through each cut tree
    while ($cut_tree = $cut_trees_result->fetch_assoc()) {
        $blockX = $cut_tree['BlockX'];
        $blockY = $cut_tree['BlockY'];
        $cutX = $cut_tree['CoordX'];
        $cutY = $cut_tree['CoordY'];
        $fallingAngle = $cut_tree['FallAngle'];
        $fallQuarter = $cut_tree['FallQuarter'];
        $stemHeight = $cut_tree['Height'];

        // Radius of potential impact zone (distance)
        $impactRadius = $stemHeight + 5; // Adding a crown buffer of 5 meters to the height

        // Calculate the bounds of the fall zone based on the fallQuarter and fallingAngle
        $fallStartAngle = 0;
        $fallEndAngle = 0;

        // Apply the correct rotation based on the fall quarter
        if ($fallQuarter === 'Q1') {
            $fallStartAngle = $fallingAngle;
            $fallEndAngle = $fallingAngle + 90;
        } elseif ($fallQuarter === 'Q2') {
            $fallStartAngle = 180 - $fallingAngle;
            $fallEndAngle = 180 - $fallingAngle + 90;
        } elseif ($fallQuarter === 'Q3') {
            $fallStartAngle = $fallingAngle - 180;
            $fallEndAngle = $fallingAngle - 180 + 90;
        } elseif ($fallQuarter === 'Q4') {
            $fallStartAngle = 360 - $fallingAngle;
            $fallEndAngle = 360 - $fallingAngle + 90;
        }

        // Normalize the angles to ensure they stay between 0-360 degrees
        $fallStartAngle = fmod($fallStartAngle, 360);
        $fallEndAngle = fmod($fallEndAngle, 360);

        // Calculate the min and max fall area for x and y
        $minX = $cutX - $impactRadius;
        $maxX = $cutX + $impactRadius;
        $minY = $cutY - $impactRadius;
        $maxY = $cutY + $impactRadius;

        // Query all trees within the same block and within the impact radius
        $all_trees_query = "SELECT f.TreeNum, f.CoordX, f.CoordY, c.Status
                            FROM year0_forest AS f
                            JOIN $regime_table AS c ON f.TreeNum = c.TreeNum
                            WHERE f.BlockX = ? AND f.BlockY = ? AND f.CoordX BETWEEN ? AND ? AND f.CoordY BETWEEN ? AND ?";
        $stmt_all_trees = $conn->prepare($all_trees_query);
        $stmt_all_trees->bind_param("iiiiii", $blockX, $blockY, $minX, $maxX, $minY, $maxY);
        $stmt_all_trees->execute();
        $all_trees_result = $stmt_all_trees->get_result();

        if (!$all_trees_result) {
            die('Error fetching all trees: ' . mysqli_error($conn));
        }

        $potentialVictims = [];

        // Check each tree in the block
        while ($tree = $all_trees_result->fetch_assoc()) {
            $treeX = $tree['CoordX'];
            $treeY = $tree['CoordY'];

            // Calculate the Euclidean distance between the cut tree and the potential victim
            $distance = sqrt(pow(($cutX - $treeX), 2) + pow(($cutY - $treeY), 2));

            // If the distance is within the impact radius, consider it a potential victim
            if ($distance <= $impactRadius && $tree['Status'] !== 'Cut') { // Check if the tree is not already cut
                // Calculate the angle between the cut tree and the victim relative to the cut tree's location
                $victimAngle = rad2deg(atan2($treeY - $cutY, $treeX - $cutX));

                // Normalize the victim angle to be within 0 to 360 degrees
                if ($victimAngle < 0) {
                    $victimAngle += 360;
                }

                // Check if the victim's angle is within the fall zone
                if ($fallStartAngle > $fallEndAngle) {
                    // This handles cases where the quarter spans the 360 degree boundary
                    if ($victimAngle >= $fallStartAngle || $victimAngle <= $fallEndAngle) {
                        $potentialVictims[] = [
                            'TreeNum' => $tree['TreeNum'],
                            'Distance' => $distance,
                        ];
                    }
                } else {
                    // Normal case where the fall zone doesn't span 360 degrees
                    if ($victimAngle >= $fallStartAngle && $victimAngle <= $fallEndAngle) {
                        $potentialVictims[] = [
                            'TreeNum' => $tree['TreeNum'],
                            'Distance' => $distance,
                        ];
                    }
                }
            }
        }

        // Sort the potential victims by distance (ascending)
        usort($potentialVictims, function ($a, $b) {
            return $a['Distance'] <=> $b['Distance'];
        });

        // Limit the number of victims to a maximum of 3 per cut tree
        $potentialVictims = array_slice($potentialVictims, 0, 2);

        // If there are victims, update their status in the corresponding regime table
        if (count($potentialVictims) > 0) {
            foreach ($potentialVictims as $victim) {
                $update_queries[] = "UPDATE $regime_table SET Status = 'Victim' WHERE TreeNum = '{$victim['TreeNum']}'";
            }
        }
    }

    // Execute batch updates
    if (count($update_queries) > 0) {
        foreach (array_chunk($update_queries, 1000) as $batch) {
            $batch_query = implode(";", $batch);
            if (!mysqli_multi_query($conn, $batch_query)) {
                die('Error updating victim status: ' . mysqli_error($conn));
            }
            // Clear the results of the multi-query
            while (mysqli_more_results($conn) && mysqli_next_result($conn)) {;
            }
        }
    }
}

// Identify and update victims for all regime tables
identify_and_update_victims('year0_regime45', 45);
identify_and_update_victims('year0_regime50', 50);
identify_and_update_victims('year0_regime55', 55);
identify_and_update_victims('year0_regime60', 60);

// Count the number of victims in each regime table
function count_victims($regime_table)
{
    global $conn;
    $count_query = "SELECT COUNT(*) AS victim_count FROM $regime_table WHERE Status = 'Victim'";
    $count_result = mysqli_query($conn, $count_query);
    if (!$count_result) {
        die('Error counting victims: ' . mysqli_error($conn));
    }
    $count_data = mysqli_fetch_assoc($count_result);
    return $count_data['victim_count'];
}

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
                    <div class="d-flex flex-column align-items-center mt-5">
                        <h1 class="display-4 text-center mb-4">Victim Identification Completed!</h1>
                        <p class="lead text-center mb-4">The victim identification and status update have been successfully completed.</p>

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
                        <a class="btn custom-button btn-lg mt-4" href="year30_simulate_forest.php" role="button">Next Step</a>
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
    updateMilestones(3); // Change the number to update to your desired step dynamically
</script>