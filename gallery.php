<?php
// JSON file to store gallery data
$json_file = "rooms.json";
$gallery = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Handle POST actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // Upload
    if ($action === "upload" && isset($_FILES["image"])) {
        $title = trim($_POST["title"]);
        if ($_FILES["image"]["error"] === UPLOAD_ERR_OK) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = $upload_dir . time() . "_" . basename($_FILES["image"]["name"]);
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $filename)) {
                $gallery[] = [
                    "id" => uniqid(),
                    "title" => $title,
                    "path" => $filename
                ];
                file_put_contents($json_file, json_encode($gallery, JSON_PRETTY_PRINT), LOCK_EX);
            }
        }
    }

    // Delete
    if ($action === "delete" && isset($_POST["id"])) {
        $id = $_POST["id"];
        foreach ($gallery as $index => $img) {
            if ($img["id"] === $id) {
                if (file_exists($img["path"])) unlink($img["path"]);
                array_splice($gallery, $index, 1);
                file_put_contents($json_file, json_encode($gallery, JSON_PRETTY_PRINT), LOCK_EX);
                break;
            }
        }
    }

    // Edit
    if ($action === "edit" && isset($_POST["id"])) {
        $id = $_POST["id"];
        $newTitle = trim($_POST["title"]);
        foreach ($gallery as &$img) {
            if ($img["id"] === $id) {
                $img["title"] = $newTitle;
                file_put_contents($json_file, json_encode($gallery, JSON_PRETTY_PRINT), LOCK_EX);
                break;
            }
        }
    }

    // Redirect to avoid form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gallery | Haven Resort</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #fdf6e3; }
.gallery-img { width: 100%; height: 250px; object-fit: cover; border-radius: 8px; transition: transform 0.3s ease; }
.gallery-img:hover { transform: scale(1.05); }
.card { border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.html">
      <img src="H.png" alt="Logo" width="60" height="60" class="me-2">
      <span style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 600;">HAVEN RESORT</span>
    </a>
  </div>
</nav>

<div class="container text-center py-4">
  <h2 class="fw-bold text-success" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #0A400C" >Add a Photo</h2>
  <form class="row g-2 justify-content-center" method="POST" enctype="multipart/form-data">
    <div class="col-md-3">
      <input type="text" name="title" class="form-control" placeholder="Image title" required>
    </div>
    <div class="col-md-3">
      <input type="file" name="image" class="form-control" required>
    </div>
    <div class="col-md-2">
      <input type="hidden" name="action" value="upload">
      <button type="submit" class="btn btn-dark w-100">Upload</button>
    </div>
  </form>
</div>

<div class="container py-4">
  <h2 class="text-center fw-bold text-success" style="font-family: 'Playfair Display', serif; font-weight: 700; color: #0A400C">Our Gallery</h2>
  <div class="row g-4 mt-3 justify-content-center">
    <?php if (empty($gallery)): ?>
      <p class="text-center">No images uploaded yet.</p>
    <?php else: ?>
      <?php foreach ($gallery as $img): ?>
        <div class="col-sm-6 col-md-4">
          <div class="card">
            <img src="<?= htmlspecialchars($img['path']) ?>" class="gallery-img" alt="<?= htmlspecialchars($img['title']) ?>">
            <div class="card-body text-center">
              <h5><?= htmlspecialchars($img['title']) ?></h5>
              <!-- Edit Form -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $img['id'] ?>">
                <input type="text" name="title" class="form-control form-control-sm d-inline w-50" placeholder="New title" required>
                <button type="submit" class="btn btn-sm btn-warning">Edit</button>
              </form>
              <!-- Delete Form -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $img['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer class="bg-dark text-white text-center py-3">
  &copy; 2025 Haven Resort | All Rights Reserved
</footer>

</body>
</html>
