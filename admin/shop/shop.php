<?php
session_start();

require('../navbar/navbar.php');
include_once('../../connection.php');

if (!isset($_SESSION['email'])) {
    echo "User is not logged in.";
    exit();
}
$loggedInOwnerEmail = $_SESSION['email'];

$shop_data = [];
if (!empty($loggedInOwnerEmail)) {
    $stmt = $conn->prepare("SELECT * FROM shops WHERE ownerEmail = ?");
    $stmt->bind_param("s", $loggedInOwnerEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $shop_data[] = $row;
        }
    } else {
        echo "No results found for the given owner email.";
    }
    $stmt->close();
} else {
    echo "No logged-in owner email found.";
}

$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Manage</title>
    <link rel="stylesheet" href="../navbar/style.css">
    <link rel="stylesheet" href="view_shop.css">
    
    <style>
    .shop-cards {
        margin-top: 30px;
        margin-left: 150px;
        display: flex;
        flex-wrap: wrap; 
        justify-content: center; 
        gap: 20px;
    }

    .card {
        width: 400px; 
        border: 1px solid #ccc;
        background-color: white;
        padding: 15px;
        margin: 10px;
        border-radius: 20px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        line-height: 1.7;
        transition: transform 0.3s ease-in-out;
    }

    .card:hover {
        transform: scale(1.05);
    }

    .card h3 {
        margin-bottom: 10px;
        font-size: 1.5rem;
    }

    .card p {
        margin: 5px 0;
    }

    @media (max-width: 768px) {
        .shop-cards {
            margin-left: 0; 
            padding: 0 20px; 
        }

        .card {
            width: 100%; 
            margin: 10px 0; 
        }
    }

    @media (max-width: 480px) {
        .card {
            padding: 10px; 
        }

        .card h3 {
            font-size: 1.2rem; 
        }

        .card p {
            font-size: 0.9rem; 
        }
        .shop-cards{
            gap: 10px;
            margin-top: 50px;
        }
    }
</style>

</head>

<body>
<div class="main_container">

    <div class="shop-cards">
        <?php if (!empty($shop_data)): ?>
            <?php foreach ($shop_data as $shop): ?>
                <div class="card">
                    <h3><?= htmlspecialchars($shop['shop_name']) ?> - <?= htmlspecialchars($shop['branch']) ?> Branch</h3>
                    <p><strong>Email:</strong> <?= htmlspecialchars($shop['email']) ?></p>
                    <p><strong>Number:</strong> <?= htmlspecialchars($shop['number']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($shop['address']) ?></p>
                    <p><strong>Branch:</strong> <?= htmlspecialchars($shop['branch']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No shops found for the logged-in owner.</p>
        <?php endif; ?>
    </div>

</body>

<script>
    setTimeout(function() {
        var alertElement = document.getElementById('alertMessage');
        if (alertElement) {
            alertElement.style.display = 'none';
        }
    }, 10000);

    function viewProducts(shopId) {
        window.location.href = "products.php?shop_id=" + shopId;
    }
</script>

</html>
