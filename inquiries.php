<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

// Handle Status Updates to 'Contacted'
if (isset($_GET['contacted'])) {
    $id = (int)$_GET['contacted'];
    $pdo->prepare("UPDATE inquiries SET status = 'Contacted' WHERE id = ?")->execute([$id]);
    redirect('inquiries.php');
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM inquiries WHERE id = ?")->execute([$id]);
    redirect('inquiries.php');
}

// FIXED QUERY: Using 'inquiry_date' and 'customer_name' from your SQL dump
$sql = "SELECT i.*, h.house_number 
        FROM inquiries i 
        JOIN houses h ON i.house_id = h.id 
        ORDER BY i.inquiry_date DESC";
$inquiries = $pdo->query($sql)->fetchAll();

include '../includes/header.php';
?>

<div class="container-fluid py-4">
    <h2 class="fw-bold mb-4">Guest Inquiries</h2>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr class="small text-uppercase fw-bold">
                        <th class="ps-4">Date</th>
                        <th>House</th>
                        <th>Guest Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inquiries as $inq): ?>
                    <tr>
                        <td class="ps-4"><?php echo date('d M, Y', strtotime($inq['inquiry_date'])); ?></td>
                        <td><span class="badge bg-primary-subtle text-primary">House <?php echo $inq['house_number']; ?></span></td>
                        <td class="fw-bold"><?php echo $inq['customer_name']; ?></td>
                        <td><a href="tel:<?php echo $inq['customer_phone']; ?>"><?php echo $inq['customer_phone']; ?></a></td>
                        <td>
                            <?php 
                                $class = $inq['status'] == 'New' ? 'bg-danger' : ($inq['status'] == 'Contacted' ? 'bg-warning text-dark' : 'bg-success');
                            ?>
                            <span class="badge rounded-pill <?php echo $class; ?>">
                                <?php echo $inq['status']; ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <?php if ($inq['status'] == 'New'): ?>
                                <a href="?contacted=<?php echo $inq['id']; ?>" class="btn btn-sm btn-outline-warning" title="Mark Contacted"><i class="fas fa-phone"></i></a>
                            <?php endif; ?>
                            <a href="?delete=<?php echo $inq['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>