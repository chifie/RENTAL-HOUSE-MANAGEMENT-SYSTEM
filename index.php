<?php
// index.php - The Public Advertisement Page
require_once 'config/db.php';
require_once 'includes/functions.php';
include 'includes/header.php'; 

// Fetch only 'Available' houses for the public
$available_houses = getAvailableHouses($pdo);
?>

<div class="bg-primary text-white py-5 mb-5 shadow">
    <div class="container text-center">
        <h1 class="display-4 fw-bold">Find Your Next Home</h1>
        <p class="lead">Modern Rental Houses & Apartments in Tanzania</p>
    </div>
</div>

<div class="container mb-5">
    <div class="row g-4">
        <?php if(count($available_houses) > 0): ?>
            <?php foreach ($available_houses as $house): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm hover-shadow">
                    <div class="position-absolute p-2">
                        <span class="badge bg-success shadow">Newly Available</span>
                    </div>
                    
                    <img src="assets/images/default_house.jpg" class="card-img-top" alt="House Image" style="height: 220px; object-fit: cover;">
                    
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold mb-0"><?php echo $house['house_number']; ?></h5>
                            <span class="text-primary fw-bold fs-5"><?php echo formatMoney($house['rent_amount']); ?></span>
                        </div>
                        
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i> <?php echo $house['location']; ?>
                        </p>
                        
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-light text-dark border"><?php echo $house['category']; ?></span>
                        </div>
                        
                        <p class="small text-secondary mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i> <?php echo $house['features']; ?>
                        </p>
                    </div>
                    
                    <div class="card-footer bg-white border-0 pb-3">
                        <a href="contact_landlord.php?house_id=<?php echo $house['id']; ?>" class="btn btn-dark w-100 fw-bold">
                            Inquire & View Room
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-house-user fa-3x text-muted mb-3"></i>
                <h3>No Vacant Houses Right Now</h3>
                <p class="text-muted">All our properties are currently occupied. Please check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php // Footer will be added tomorrow (March 25) ?>
</body>
</html>