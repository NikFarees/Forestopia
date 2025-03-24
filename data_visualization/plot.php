<?php include '../include/header.php'; ?>

<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        #map {
            width: 90%;
            max-width: 90%;
            height: 700px;
            margin: 20px auto;
            /* Center the map */
        }

        .legend {
            background: white;
            line-height: 1.5;
            padding: 10px;
            font: 14px Arial, Helvetica, sans-serif;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        .legend div {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .legend i {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            display: inline-block;
            background-color: transparent;
            border-radius: 50%;
        }

        .btn-blue {
            padding: 0.5rem;
            background-color: #007bff;
            /* Blue color */
            color: white;
            border: none;
            cursor: pointer;
            width: auto;
            min-width: 100px;
        }

        .btn-blue:hover {
            background-color: #0056b3;
            /* Darker blue on hover */
        }
    </style>
</head>

<main class="main">

    <!-- About Section -->
    <section id="about" class="about section">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
            <h2>Trees Plot</h2>
        </div><!-- End Section Title -->

        <div class="container text-center" data-aos="zoom-out" data-aos-delay="100">
            <div class="row justify-content-center">
                <div class="card shadow">
                    <div class="card-body">
                        <form id="filterForm">
                            <label for="blockX">Block X:</label>
                            <select id="blockX" name="blockX" required>
                                <?php
                                // Fetch distinct BlockX values from the database
                                include '../db_connect.php';
                                $query = "SELECT DISTINCT BlockX FROM year0_forest ORDER BY BlockX";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$row['BlockX']}'>{$row['BlockX']}</option>";
                                }
                                ?>
                            </select>

                            <label for="blockY">Block Y:</label>
                            <select id="blockY" name="blockY" required>
                                <?php
                                // Fetch distinct BlockY values from the database
                                $query = "SELECT DISTINCT BlockY FROM year0_forest ORDER BY BlockY";
                                $result = mysqli_query($conn, $query);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='{$row['BlockY']}'>{$row['BlockY']}</option>";
                                }
                                mysqli_close($conn);
                                ?>
                            </select>

                            <label for="speciesGroup">Species Group:</label>
                            <select id="speciesGroup" name="speciesGroup">
                                <option value="8" selected>All Group</option>
                                <option value="1">Mersawa</option>
                                <option value="2">Keruing</option>
                                <option value="3">Dip-Com</option>
                                <option value="4">Dip-Non-Com</option>
                                <option value="5">Non-Dip-Com</option>
                                <option value="6">Non-Dip-Non-Com</option>
                                <option value="7">Others</option>
                            </select>

                            <label for="regime">Regime:</label>
                            <select id="regime" name="regime">
                                <option value="45" selected>Regime 45</option>
                                <option value="50">Regime 50</option>
                                <option value="55">Regime 55</option>
                                <option value="60">Regime 60</option>
                            </select>

                            <label for="latitude">Latitude:</label>
                            <input type="number" id="latitude" name="latitude" value="5.417965" step="0.000001" required>

                            <label for="longitude">Longitude:</label>
                            <input type="number" id="longitude" name="longitude" value="101.812081" step="0.000001" required>

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
                            <button type="submit" class="btn custom-button">Apply Filter</button>
                        </form>
                    </div>
                </div>

                <div id="map"></div>

            </div>
        </div>
    </section>
</main>

<?php include '../include/footer.php'; ?>

<script>
    // Initialize the map
    const map = L.map('map').setView([5.417965, 101.812081], 20);

    // Add MapTiler tiles
    const apiKey = 'y6v3bkIMzetlflu80CNu'; // Replace with your MapTiler API key
    L.tileLayer(`https://api.maptiler.com/maps/basic-v2/{z}/{x}/{y}.png?key=${apiKey}`, {
        attribution: '&copy; <a href="https://www.maptiler.com/">MapTiler</a>',
        maxZoom: 22
    }).addTo(map);

    let markers = []; // Store markers to clear them when applying a new filter
    let gridLines = []; // Store grid lines to clear them when the map view changes
    let fallLines = []; // Store fall direction lines to clear them dynamically

    // Add grid lines to the map
    function addGridLines() {
        // Clear existing grid lines
        gridLines.forEach(line => map.removeLayer(line));
        gridLines = [];

        const bounds = map.getBounds();
        const startLat = bounds.getSouth();
        const endLat = bounds.getNorth();
        const startLng = bounds.getWest();
        const endLng = bounds.getEast();
        const gridInterval = 0.0001; // Adjust grid interval as needed

        // Draw vertical grid lines
        for (let lng = startLng; lng <= endLng; lng += gridInterval) {
            const line = L.polyline([
                [startLat, lng],
                [endLat, lng]
            ], {
                color: 'black',
                weight: 1,
                dashArray: '5, 5' // Dashed line
            }).addTo(map);
            gridLines.push(line);
        }

        // Draw horizontal grid lines
        for (let lat = startLat; lat <= endLat; lat += gridInterval) {
            const line = L.polyline([
                [lat, startLng],
                [lat, endLng]
            ], {
                color: 'black',
                weight: 1,
                dashArray: '5, 5' // Dashed line
            }).addTo(map);
            gridLines.push(line);
        }
    }

    // Fetch tree data and plot on the map
    async function fetchTreeData(blockX, blockY, speciesGroup, regime, latitude, longitude) {
        let apiUrl = `fetchdata.php?SpeciesGroup=${speciesGroup}&Regime=${regime}`;
        if (blockX) apiUrl += `&BlockX=${blockX}`;
        if (blockY) apiUrl += `&BlockY=${blockY}`;
        if (latitude) apiUrl += `&Latitude=${latitude}`;
        if (longitude) apiUrl += `&Longitude=${longitude}`;

        try {
            const response = await fetch(apiUrl);
            if (!response.ok) {
                throw new Error(`Error fetching data: ${response.statusText}`);
            }

            const data = await response.json();
            plotTreeData(data);
            showSummary(data);
        } catch (error) {
            console.error('Error:', error);
        }
    }

    let victimMarkers = []; // Store victim markers to clear them when applying a new filter

    // Function to plot tree data on the map
    function plotTreeData(data) {
        if (!data || data.length === 0) {
            console.warn("No data received or data is empty");
            return;
        }

        // Clear existing markers, fall lines, and victim markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];
        fallLines.forEach(line => map.removeLayer(line));
        fallLines = [];
        victimMarkers.forEach(marker => map.removeLayer(marker));
        victimMarkers = [];

        data.forEach(tree => {
            const lat = 5.417965 + (tree.CoordY * 0.000009);
            const lng = 101.812081 + (tree.CoordX * 0.000009);

            // Determine icon path based on status or species group
            let iconPath = '../assets/images/others.png'; // Default icon
            if (tree.Status === 'Cut') {
                iconPath = '../assets/images/cut_tree.png';
            } else {
                const icons = {
                    1: '../assets/images/mersawa.png',
                    2: '../assets/images/keruing.png',
                    3: '../assets/images/dip_com.png',
                    4: '../assets/images/dip_non_com.png',
                    5: '../assets/images/non_dip_com.png',
                    6: '../assets/images/non_dip_non_com.png',
                };
                iconPath = icons[tree.SpeciesGroup] || iconPath;
            }

            // Create a custom icon
            const icon = L.icon({
                iconUrl: iconPath,
                iconSize: [30, 30],
                iconAnchor: [15, 15],
            });

            // Add marker with popup
            const marker = L.marker([lat, lng], {
                icon
            }).addTo(map);
            marker.bindPopup(`
            <b>Tree Number:</b> ${tree.TreeNum}<br>
            <b>Diameter:</b> ${tree.Diameter}m<br>
            <b>Height:</b> ${tree.Height}m
        `);
            markers.push(marker);

            // Draw the red line indicating the fall direction if applicable
            if (tree.Status === 'Cut' && tree.FallAngle && tree.FallQuarter) {
                const fallLength = tree.Height * 0.000009;
                const angleRad = (tree.FallAngle * Math.PI) / 180;
                let endLat = lat,
                    endLng = lng;

                // Calculate fall endpoint based on FallQuarter
                switch (tree.FallQuarter) {
                    case 'Q1':
                        endLat -= fallLength * Math.cos(angleRad);
                        endLng += fallLength * Math.sin(angleRad);
                        break;
                    case 'Q2':
                        endLat -= fallLength * Math.cos(angleRad);
                        endLng -= fallLength * Math.sin(angleRad);
                        break;
                    case 'Q3':
                        endLat += fallLength * Math.cos(angleRad);
                        endLng -= fallLength * Math.sin(angleRad);
                        break;
                    case 'Q4':
                        endLat += fallLength * Math.cos(angleRad);
                        endLng += fallLength * Math.sin(angleRad);
                        break;
                    default:
                        console.warn(`Unknown FallQuarter: ${tree.FallQuarter}`);
                }

                // Calculate offsets for left and right lines
                const offset = 0.00001;
                const offsetLat1 = endLat + offset * Math.sin(angleRad);
                const offsetLng1 = endLng - offset * Math.cos(angleRad);
                const offsetLat2 = endLat - offset * Math.sin(angleRad);
                const offsetLng2 = endLng + offset * Math.cos(angleRad);

                // Draw the red line
                const fallLine = L.polyline(
                    [
                        [lat, lng],
                        [endLat, endLng],
                    ], {
                        color: 'red',
                        weight: 2
                    }
                ).addTo(map);
                fallLines.push(fallLine);

                // Draw the left red line
                const fallLineLeft = L.polyline(
                    [
                        [lat, lng],
                        [offsetLat1, offsetLng1],
                    ], {
                        color: 'red',
                        weight: 2
                    }
                ).addTo(map);
                fallLines.push(fallLineLeft);

                // Draw the right red line
                const fallLineRight = L.polyline(
                    [
                        [lat, lng],
                        [offsetLat2, offsetLng2],
                    ], {
                        color: 'red',
                        weight: 2
                    }
                ).addTo(map);
                fallLines.push(fallLineRight);

                // Add a red circle to indicate the crown damage
                const crownRadius = 4;
                const crownCircle = L.circle([endLat, endLng], {
                    color: 'red',
                    fillColor: 'transparent',
                    fillOpacity: 0,
                    radius: crownRadius
                }).addTo(map);
                fallLines.push(crownCircle);

                // Check for victim trees
                data.forEach(otherTree => {
                    if (otherTree.Status === 'Cut') return;
                    const otherLat = 5.417965 + (otherTree.CoordY * 0.000009);
                    const otherLng = 101.812081 + (otherTree.CoordX * 0.000009);

                    // Check if tree is within crown (circle) area
                    const distanceToCrown = Math.sqrt((otherLat - endLat) ** 2 + (otherLng - endLng) ** 2);
                    if (distanceToCrown <= 0.000036) { // Adjust 0.000036 based on crown size in lat/lng units
                        const victimCircle = L.circle([otherLat, otherLng], {
                            color: 'blue',
                            fillColor: 'blue',
                            fillOpacity: 0.5,
                            radius: 1
                        }).addTo(map);
                        victimMarkers.push(victimCircle);
                    }

                    // Check if tree is within fall range (bounded by left and right lines)
                    const isWithinFallRange = (
                        ((otherLat - lat) * (offsetLng1 - lng) - (otherLng - lng) * (offsetLat1 - lat)) >= 0 &&
                        ((otherLat - lat) * (offsetLng2 - lng) - (otherLng - lng) * (offsetLat2 - lat)) <= 0 &&
                        Math.abs((otherLat - lat) * Math.cos(angleRad) - (otherLng - lng) * Math.sin(angleRad)) <= fallLength
                    );

                    if (isWithinFallRange) {
                        const victimCircle = L.circle([otherLat, otherLng], {
                            color: 'yellow',
                            fillColor: 'yellow',
                            fillOpacity: 0.5,
                            radius: 1
                        }).addTo(map);
                        victimMarkers.push(victimCircle);
                    }
                });
            }
        });

        console.log(`Plotted ${data.length} trees and updated fall lines.`);
    }

    // Fetch initial data for BlockX=1, BlockY=1, SpeciesGroup=8 (All), Regime=45, default latitude and longitude
    fetchTreeData(1, 1, 8, 45, 5.417965, 101.812081);

    // Handle filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const blockX = document.getElementById('blockX').value || null;
        const blockY = document.getElementById('blockY').value || null;
        const speciesGroup = document.getElementById('speciesGroup').value;
        const regime = document.getElementById('regime').value;
        const latitude = document.getElementById('latitude').value || null;
        const longitude = document.getElementById('longitude').value || null;

        fetchTreeData(blockX, blockY, speciesGroup, regime, latitude, longitude);
    });

    // Add grid lines to the map initially
    addGridLines();

    // Redraw grid lines when the map view changes
    map.on('moveend', addGridLines);
    map.on('zoomend', addGridLines);

    // Add legend to the map
    const legend = L.control({
        position: 'bottomright'
    });

    legend.onAdd = function(map) {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML += `
    <div><i style="background: url(../assets/images/mersawa.png) no-repeat center center; background-size: contain;"></i>Mersawa</div>
    <div><i style="background: url(../assets/images/keruing.png) no-repeat center center; background-size: contain;"></i>Keruing</div>
    <div><i style="background: url(../assets/images/dip_com.png) no-repeat center center; background-size: contain;"></i>Dip-Com</div>
    <div><i style="background: url(../assets/images/dip_non_com.png) no-repeat center center; background-size: contain;"></i>Dip-Non-Com</div>
    <div><i style="background: url(../assets/images/non_dip_com.png) no-repeat center center; background-size: contain;"></i>Non-Dip-Com</div>
    <div><i style="background: url(../assets/images/non_dip_non_com.png) no-repeat center center; background-size: contain;"></i>Non-Dip-Non-Com</div>
    <div><i style="background: url(../assets/images/others.png) no-repeat center center; background-size: contain;"></i>Others</div>
    <div><i style="background: url(../assets/images/cut_tree.png) no-repeat center center; background-size: contain;"></i>Cut Tree</div>
    <div><i style="background: yellow; width: 20px; height: 20px; border-radius: 50%;"></i>Stem Damage</div>
    <div><i style="background: blue; width: 20px; height: 20px; border-radius: 50%;"></i>Crown Damage</div>
  `;
        return div;
    };

    legend.addTo(map);
</script>