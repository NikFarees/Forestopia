<?php
include '../include/header.php';
require_once 'sql.php';
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
        width: auto;
        /* Set width to auto */
        min-width: 100px;
        /* Set a minimum width */
    }

    .btn:hover {
        background-color: #0056b3;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<main class="main">

    <!-- About Section -->
    <section id="about" class="about section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Bar Chart</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="zoom-out" data-aos-delay="100">

            <!-- Filter Form -->
            <div class="row justify-content-center mb-4">
                <div class="col-md-8">
                    <form method="get" action="" class="mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header" style="background-color:rgb(155, 238, 230);">
                                <h5 class="card-title mb-0">Filter by Year 30 Regime</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Year 30 Regime</label>
                                        <select id="status" name="status" class="form-control">
                                            <option value="Status45" <?= ($selectedStatus == 'Status45') ? 'selected' : '' ?>>Regime 45</option>
                                            <option value="Status50" <?= ($selectedStatus == 'Status50') ? 'selected' : '' ?>>Regime 50</option>
                                            <option value="Status55" <?= ($selectedStatus == 'Status55') ? 'selected' : '' ?>>Regime 55</option>
                                            <option value="Status60" <?= ($selectedStatus == 'Status60') ? 'selected' : '' ?>>Regime 60</option>
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
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <br>
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-center">Volume Base</h5>
                    <canvas id="barChartVolume"></canvas>
                </div>
                <div class="col-md-6">
                    <h5 class="text-center">Count Base</h5>
                    <canvas id="barChartCount"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Analysis Section -->
    <section id="analysis" class="analysis section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Analysis and Best Regime to Cut</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="zoom-out" data-aos-delay="100">
            <div class="row justify-content-center">
                <!-- Card for Analysis -->
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <!-- Card Header with Custom Background -->
                        <div class="card-header text-white" style="background-color:rgb(155, 238, 230);">
                            <h5 class="mb-0">Best Regime Analysis</h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text">
                                Based on the provided data and calculations, the best regime for Year 0 and Year 30 is determined by analyzing production, damage, and growth scores. Below are the results:
                            </p>
                            <ul class="list-group">
                                <li class="list-group-item">
                                    <strong>Year 0 Regime:</strong> <?= $bestCombination['Year 0 Regime'] ?? 'N/A' ?>
                                </li>
                                <li class="list-group-item">
                                    <strong>Year 30 Regime:</strong> <?= $bestCombination['Year 30 Regime'] ?? 'N/A' ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


</main>

<script>
    const dataVolume = <?= json_encode($dataVolume) ?>;
    const labelsVolume = dataVolume.map(item => item.Regime);
    const prod0Volume = dataVolume.map(item => parseFloat(item.prod0_volume));
    const damageVolume = dataVolume.map(item => parseFloat(item.damage_volume));
    const growth30Volume = dataVolume.map(item => parseFloat(item.growth30_volume));
    const prod30Volume = dataVolume.map(item => parseFloat(item.prod30_volume));

    const ctxVolume = document.getElementById('barChartVolume').getContext('2d');
    const barChartVolume = new Chart(ctxVolume, {
        type: 'bar',
        data: {
            labels: labelsVolume,
            datasets: [{
                    label: 'Production 0',
                    data: prod0Volume,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Damage',
                    data: damageVolume,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Growth 30',
                    data: growth30Volume,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Production 30',
                    data: prod30Volume,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const dataCount = <?= json_encode($dataCount) ?>;
    const labelsCount = dataCount.map(item => item.Regime);
    const prod0Count = dataCount.map(item => parseFloat(item.prod0_count));
    const damageCount = dataCount.map(item => parseFloat(item.damage_count));
    const growth30Count = dataCount.map(item => parseFloat(item.growth30_count));
    const prod30Count = dataCount.map(item => parseFloat(item.prod30_count));

    const ctxCount = document.getElementById('barChartCount').getContext('2d');
    const barChartCount = new Chart(ctxCount, {
        type: 'bar',
        data: {
            labels: labelsCount,
            datasets: [{
                    label: 'Production 0',
                    data: prod0Count,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Damage',
                    data: damageCount,
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Growth 30',
                    data: growth30Count,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Production 30',
                    data: prod30Count,
                    backgroundColor: 'rgba(153, 102, 255, 0.2)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

<?php include '../include/footer.php'; ?>