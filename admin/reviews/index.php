<?php
include_once("../includes/db_config.php");
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../index.php");
    exit();
}

// Handle approve/reject/delete actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] == 'approve') {
        $db->query("UPDATE reviews SET status = 1 WHERE id = $id");
    } elseif ($_GET['action'] == 'reject') {
        $db->query("UPDATE reviews SET status = 0 WHERE id = $id");
    } elseif ($_GET['action'] == 'delete') {
        $db->query("DELETE FROM reviews WHERE id = $id");
    }
    header("Location: index.php");
    exit();
}

// Fetch all reviews
$sql = "SELECT r.id, r.rating, r.comment, r.created_at, r.status, 
               p.name AS product_name, u.firstname, u.lastname 
        FROM reviews r
        JOIN products p ON r.product_id = p.id
        JOIN users u ON r.user_id = u.id
        ORDER BY r.created_at DESC";
$result = $db->query($sql);
?>

<h2>Product Reviews</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Product</th>
        <th>User</th>
        <th>Rating</th>
        <th>Comment</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['product_name']) ?></td>
            <td><?= htmlspecialchars($row['firstname'].' '.$row['lastname']) ?></td>
            <td><?= $row['rating'] ?>/5</td>
            <td><?= htmlspecialchars($row['comment']) ?></td>
            <td><?= $row['created_at'] ?></td>
            <td><?= $row['status'] ? 'Approved' : 'Pending/Rejected' ?></td>
            <td>
                <?php if(!$row['status']): ?>
                    <a href="?action=approve&id=<?= $row['id'] ?>">Approve</a> |
                <?php else: ?>
                    <a href="?action=reject&id=<?= $row['id'] ?>">Reject</a> |
                <?php endif; ?>
                <a href="?action=delete&id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>