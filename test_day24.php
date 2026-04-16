<?php
// test_day24.php
require_once 'config/db.php';
require_once 'includes/functions.php';
include 'includes/header.php'; 

// Fetch the available houses from our database
$available = getAvailableHouses($pdo);
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">Available Houses for Rent</h2>
        <span class="badge bg-secondary p-2"><?php echo count($available); ?> Houses Found</span>
    </div>

    <div class="row g-4">
        <?php if(empty($available)): ?>
            <div class="col-12 text-center py-5">
                <p class="text-muted">No houses are currently available for rent. Check back later!</p>
            </div>
        <?php else: ?>
            <?php foreach ($available as $house): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="assets/images/default_house.jpg" class="card-img-top" alt="House Image" style="height: 200px; object-fit: cover;">
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title fw-bold"><?php echo $house['house_number']; ?></h5>
                            <span class="text-primary fw-bold"><?php echo formatMoney($house['rent_amount']); ?></span>
                        </div>
                        <p class="card-text text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i> <?php echo $house['location']; ?>
                        </p>
                        <p class="small text-secondary">
                            <i class="fas fa-info-circle me-2"></i> <?php echo $house['category']; ?>
                        </p>
                        <hr>
                        <p class="card-text small text-dark"><?php echo $house['features']; ?></p>
                    </div>
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="inquiry.php?id=<?php echo $house['id']; ?>" class="btn btn-primary w-100">
                            Inquire Now
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php // Footer will be created tomorrow! ?>
</body>
</html>