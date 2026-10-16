<?php
include 'db.php';

$title = "";
$description = "";
$event_date = "";
$category = "";
$status = "";
$id = "";
$is_update = false;
$error = "";

// Folder for uploads
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Fetch for Edit
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $is_update = true;
    $res = mysqli_query($conn, "SELECT * FROM events WHERE id = $id");
    if ($row = mysqli_fetch_assoc($res)) {
        $title = $row['title'];
        $description = $row['description'];
        $event_date = $row['event_date'];
        $category = $row['category'];
        $status = $row['status'];
        $existing_poster = $row['poster'];
    }
}

// Handle Form Submit
if (isset($_POST['submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    $event_id = $_POST['id'];

    $poster_path = $is_update ? $_POST['old_poster'] : "";

    // File Upload & Validation Logic
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == 0) {
        $file_name = $_FILES['poster']['name'];
        $file_size = $_FILES['poster']['size'];
        $file_tmp = $_FILES['poster']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_extensions = array("jpg", "jpeg", "png");
        $max_size = 2 * 1024 * 1024; // 2MB max size

        if (!in_array($file_ext, $allowed_extensions)) {
            $error = "Invalid file type! Only JPG, JPEG, and PNG files are allowed.";
        } elseif ($file_size > $max_size) {
            $error = "File size is too large! Maximum allowed size is 2MB.";
        } else {
            // Unique file naming to prevent overwriting
            $new_file_name = uniqid("poster_", true) . '.' . $file_ext;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Delete old poster if updating
                if ($is_update && !empty($_POST['old_poster']) && file_exists($_POST['old_poster'])) {
                    unlink($_POST['old_poster']);
                }
                $poster_path = $destination;
            } else {
                $error = "Failed to upload the image file.";
            }
        }
    } elseif (!$is_update) {
        $error = "Event poster image is required!";
    }

    // If no validation errors, proceed with DB operations
    if (empty($error)) {
        if (!empty($event_id)) {
            // UPDATE Query
            $query = "UPDATE events SET title='$title', description='$description', event_date='$event_date', category='$category', status='$status', poster='$poster_path' WHERE id=$event_id";
            if (mysqli_query($conn, $query)) {
                header("Location: index.php?status=updated");
                exit();
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        } else {
            // INSERT Query
            $query = "INSERT INTO events (title, description, event_date, category, status, poster) VALUES ('$title', '$description', '$event_date', '$category', '$status', '$poster_path')";
            if (mysqli_query($conn, $query)) {
                header("Location: index.php?status=added");
                exit();
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_update ? 'Edit Event' : 'Add Event'; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container" style="max-width: 600px;">
    <div class="header-flex">
        <h3><?php echo $is_update ? 'Edit Event Record' : 'Add New Event'; ?></h3>
        <a href="index.php" class="btn" style="background-color: #6c757d;">Back</a>
    </div>

    <!-- Error Validation Feedback Message -->
    <?php if(!empty($error)): ?>
        <div class="msg msg-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <input type="hidden" name="old_poster" value="<?php echo isset($existing_poster) ? $existing_poster : ''; ?>">

        <div class="form-group">
            <label>Event Title:</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
        </div>

        <div class="form-group">
            <label>Description:</label>
            <textarea name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <div class="form-group">
            <label>Event Date:</label>
            <input type="date" name="event_date" value="<?php echo htmlspecialchars($event_date); ?>" required>
        </div>

        <div class="form-group">
            <label>Category:</label>
            <select name="category" required>
                <option value="">Select Category</option>
                <option value="Technical" <?php if($category == 'Technical') echo 'selected'; ?>>Technical</option>
                <option value="Cultural" <?php if($category == 'Cultural') echo 'selected'; ?>>Cultural</option>
                <option value="Sports" <?php if($category == 'Sports') echo 'selected'; ?>>Sports</option>
            </select>
        </div>

        <div class="form-group">
            <label>Status:</label>
            <select name="status" required>
                <option value="">Select Status</option>
                <option value="Upcoming" <?php if($status == 'Upcoming') echo 'selected'; ?>>Upcoming</option>
                <option value="Closed" <?php if($status == 'Closed') echo 'selected'; ?>>Closed</option>
            </select>
        </div>

        <div class="form-group">
            <label>Event Poster (JPG, PNG - Max 2MB):</label>
            <input type="file" name="poster" accept=".jpg, .jpeg, .png">
            <?php if(!empty($existing_poster)): ?>
                <p style="margin-top: 5px; font-size: 13px;">Current Poster: <a href="<?php echo $existing_poster; ?>" target="_blank">View</a></p>
            <?php endif; ?>
        </div>

        <button type="submit" name="submit" class="btn btn-success" style="width: 100%; padding: 12px;"><?php echo $is_update ? 'Update Event' : 'Save Event'; ?></button>
    </form>
</div>

</body>
</html>