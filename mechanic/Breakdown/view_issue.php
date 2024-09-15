<?php
require '../navbar/nav.php';
require '../../connection.php';

// Get the issue ID from the URL
$issue_id = intval($_GET['id']);

// Fetch issue details along with the vehicle owner's city from the database
$sql = "SELECT vi.*, vo.city 
from vehicleissues vi JOIN vehicle v ON vi.v_id = v.v_id
    JOIN vehicle_owner vo ON v.email = vo.email
    WHERE vi.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $issue_id);
$stmt->execute();
$result = $stmt->get_result();
$issue = $result->fetch_assoc();

$location = explode(',', $issue['location']);
$latitude = trim($location[0]);
$longitude = trim($location[1]);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Details</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="../../vehicle_owner/profile/style.css">

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB4WKu5raw64v4-CB8bYSq7SMtFikfu5lg"></script>
</head>
<body>

<div class="issue_container">
    <div class="issue-details">
        <h1>Issue Details</h1>
        <p><strong>Email:</strong> <?= htmlspecialchars($issue['email']); ?></p>
        <p><strong>Vehicle Model:</strong> <?= htmlspecialchars($issue['vehicle_model']); ?></p>
        <p><strong>Year:</strong> <?= htmlspecialchars($issue['year']); ?></p>
        <p><strong>Mobile Number:</strong> <?= htmlspecialchars($issue['mobile_number']); ?></p>
        <p><strong>Vehicle Issue:</strong> <?= htmlspecialchars($issue['vehicle_issue']); ?></p>
        <p><strong>City:</strong> <?= htmlspecialchars($issue['city']); ?></p>

        <button class="btn accept-btn">Accept</button>
        <button class="btn decline-btn">Decline</button>
    </div>

    <div class="map-container">
        <br>
        <div id="map" style="width:100%; height:400px;"></div>
    </div>
</div>

<!-- Modal Structure -->
<div id="confirmModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h4>Confirmation</h4>
        <p>Are you sure you want to accept this issue?</p>
        <button id="confirmYes" class="btn">Yes</button>
        <button id="confirmNo" class="btn">No</button>
    </div>
</div>

<script>
// Initialize Google Maps
function initMap() {
    // Destination location (database location)
    var destination = { lat: <?= $latitude ?>, lng: <?= $longitude ?> };

    // Initialize the map centered at the destination
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: destination
    });

    // Add a marker at the destination location
    var marker = new google.maps.Marker({
        position: destination,
        map: map
    });

    // Directions service and renderer to display the route
    var directionsService = new google.maps.DirectionsService();
    var directionsRenderer = new google.maps.DirectionsRenderer({
        map: map
    });

    // Get the user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var currentLocation = {
                lat: position.coords.latitude,
                lng: position.coords.longitude
            };

            // Set up the directions request
            var request = {
                origin: currentLocation, // Start point (user's current location)
                destination: destination, // End point (stored location)
                travelMode: 'DRIVING' // You can change this to 'WALKING', 'BICYCLING', etc.
            };

            // Get and display the route
            directionsService.route(request, function(result, status) {
                if (status == 'OK') {
                    directionsRenderer.setDirections(result);
                } else {
                    alert('Could not calculate route: ' + status);
                }
            });
        }, function() {
            alert('Geolocation failed. Unable to retrieve your location.');
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Initialize the map when the page loads
window.onload = initMap;

// Modal Logic
document.querySelector('.accept-btn').addEventListener('click', function() {
    // Show the modal when Accept button is clicked
    document.getElementById('confirmModal').style.display = 'flex';
});

document.getElementById('confirmNo').addEventListener('click', function() {
    // Hide the modal when No is clicked
    document.getElementById('confirmModal').style.display = 'none';
});

document.getElementById('confirmYes').addEventListener('click', function() {
    // Get the issue ID
    var issueId = <?= $issue_id ?>;

    // Make an AJAX request to update the database
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'accept_issue.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            alert('Issue accepted successfully!');
            document.getElementById('confirmModal').style.display = 'none';
            // Optionally, you can redirect the user after success
        } else {
            alert('Error: Could not accept the issue.');
        }
    };
    xhr.send('issue_id=' + issueId); // Only sending the issue ID
});
</script>

</body>
</html>
