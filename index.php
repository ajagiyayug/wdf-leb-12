<?php
include 'db.php';

$msg = "";
$msg_type = "";

// Delete Operation
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Fetch image to delete file from folder
    $res = mysqli_query($conn, "SELECT poster FROM events WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        if (file_exists($row['poster'])) {
            unlink($row['poster']);
        }
    }

    $query = "DELETE FROM events WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $msg = "Event deleted successfully!";
        $msg_type = "success";
    } else {
        $msg = "Error deleting event: " . mysqli_error($conn);
        $msg_type = "error";
    }
}

// Redirect Status Messages
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'added') {
        $msg = "New event added with poster successfully!";
        $msg_type = "success";
    } elseif ($_GET['status'] == 'updated') {
        $msg = "Event updated successfully!";
        $msg_type = "success";
    }
}

// Filter Logic
$filter_cat = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';
$sql = "SELECT * FROM events WHERE 1=1";
if (!empty($filter_cat)) {
    $sql .= " AND category = '$filter_cat'";
}
$sql .= " ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Practical 12: Event Management CRUD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <div class="header-flex">
        <h2>Event Management Dashboard</h2>
        <a href="add_edit.php" class="btn btn-success">+ Add New Event</a>
    </div>

    <!-- Success/Failure Feedback Message -->
    <?php if(!empty($msg)): ?>
        <div class="msg msg-<?php echo $msg_type; ?>"><?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <form method="GET" action="index.php" class="filter-bar">
        <select name="category">
            <option value="">Filter by Category</option>
            <option value="Technical" <?php if($filter_cat == 'Technical') echo 'selected'; ?>>Technical</option>
            <option value="Cultural" <?php if($filter_cat == 'Cultural') echo 'selected'; ?>>Cultural</option>
            <option value="Sports" <?php if($filter_cat == 'Sports') echo 'selected'; ?>>Sports</option>
        </select>
        <button type="submit" class="btn">Filter</button>
        <a href="index.php" class="btn" style="background-color: #6c757d; text-align:center; line-height:20px;">Reset</a>
    </form>

    <!-- Events Table -->
    <table>
        <thead>
            <tr>
                <th>Poster</th>
                <th>Title</th>
                <th>Date</th>
                <th>Category</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><img src="<?php echo $row['poster']; ?>" alt="Poster" class="poster-img"></td>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo $row['event_date']; ?></td>
                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td class="actions">
                            <a href="add_edit.php?id=<?php echo $row['id']; ?>" class="btn-warning">Edit</a>
                            <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this event?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; color: #777;">No events found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>