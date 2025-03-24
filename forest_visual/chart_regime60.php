<?php
require_once '../db_connect.php';

// Define species groups
$speciesGroups = [
    1 => "Mersawa",
    2 => "Keruing",
    3 => "Dip Marketable",
    4 => "Dip Non Market",
    5 => "Non Dip Market",
    6 => "Non Dip Non Market",
    7 => "Others"
];

// Fetch data for the graph
$sql = "
SELECT 
    f.SpeciesGroup AS speciesgroup,
    SUM(CASE WHEN r60.Status = 'cut' THEN f.Volume ELSE 0 END) / 100 AS production_0, 
    SUM(CASE WHEN r60.Status = 'victim' THEN f.Volume ELSE 0 END) / 100 AS damage_0, 
    SUM(y30_60.Volume30 - f.Volume) / 100 AS growth_30,
    SUM(CASE WHEN y30_60.Status45 = 'cut' THEN y30_60.Volume30 ELSE 0 END) / 100 AS production_30_regime_45, 
    SUM(CASE WHEN y30_60.Status50 = 'cut' THEN y30_60.Volume30 ELSE 0 END) / 100 AS production_30_regime_50,
    SUM(CASE WHEN y30_60.Status55 = 'cut' THEN y30_60.Volume30 ELSE 0 END) / 100 AS production_30_regime_55,
    SUM(CASE WHEN y30_60.Status60 = 'cut' THEN y30_60.Volume30 ELSE 0 END) / 100 AS production_30_regime_60
FROM 
    year0_forest f
LEFT JOIN 
    year0_regime60 r60 ON f.TreeNum = r60.TreeNum
LEFT JOIN 
    year30_regime60 y30_60 ON f.TreeNum = y30_60.TreeNum
GROUP BY 
    f.SpeciesGroup
ORDER BY 
    f.SpeciesGroup;
";

$result = mysqli_query($conn, $sql);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}
?>

<style>
    .chart-container {
        width: 100%;
        height: 100%;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<main class="main">
    <section id="chart" class="chart section">
        <div class="container section-title" data-aos="fade-up">
            <h2>Regime 60 Chart</h2>
        </div>
        <div class="container" data-aos="zoom-out" data-aos-delay="100">
            <div class="chart-container">
                <canvas id="regime60Chart"></canvas>
            </div>
        </div>
    </section>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const data = <?= json_encode($data) ?>;
        const speciesGroups = <?= json_encode($speciesGroups) ?>;

        const labels = data.map(item => speciesGroups[item.speciesgroup]);
        const production0 = data.map(item => parseFloat(item.production_0));
        const damage0 = data.map(item => parseFloat(item.damage_0));
        const growth30 = data.map(item => parseFloat(item.growth_30));
        const production30Regime45 = data.map(item => parseFloat(item.production_30_regime_45));
        const production30Regime50 = data.map(item => parseFloat(item.production_30_regime_50));
        const production30Regime55 = data.map(item => parseFloat(item.production_30_regime_55));
        const production30Regime60 = data.map(item => parseFloat(item.production_30_regime_60));

        const ctx = document.getElementById('regime60Chart').getContext('2d');
        const regime60Chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Production 0',
                        data: production0,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Damage 0',
                        data: damage0,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Growth 30',
                        data: growth30,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Production 30 Regime 45',
                        data: production30Regime45,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Production 30 Regime 50',
                        data: production30Regime50,
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Production 30 Regime 55',
                        data: production30Regime55,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Production 30 Regime 60',
                        data: production30Regime60,
                        backgroundColor: 'rgba(77, 77, 77, 0.2)',
                        borderColor: 'rgb(51, 51, 51)',
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
    });
</script>
