<?php
// Edit form for an existing industry
include 'db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    echo 'Invalid id'; exit;
}

$stmt = $conn->prepare("SELECT id, `name`, `description`, `image` FROM industry WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
if (!$row) { echo 'Industry not found'; exit; }
$stmt->close();
$conn->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Industry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card shadow-sm">
            <div class="card-body">
              <h3 class="card-title mb-3">Edit Industry</h3>
              <?php if(isset($_GET['status'])): ?>
                <?php $s = $_GET['status']; $msg = isset($_GET['msg']) ? urldecode($_GET['msg']) : ''; ?>
                <?php if($s === 'success'): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($msg ?: 'Updated successfully.'); ?></div>
                <?php else: ?>
                  <div class="alert alert-danger"><?php echo htmlspecialchars($msg ?: 'An error occurred.'); ?></div>
                <?php endif; ?>
              <?php endif; ?>

              <form action="update_upload.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                <div class="mb-3">
                  <label class="form-label">Industry Name</label>
                  <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="description" rows="4" class="form-control" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Current Image</label><br>
                  <?php if(!empty($row['image'])): ?>
                    <img src="/images/<?php echo htmlspecialchars($row['image']); ?>" alt="" style="max-width:200px; border-radius:8px; display:block; margin-bottom:8px;">
                  <?php else: ?>
                    <div class="text-muted">No image uploaded</div>
                  <?php endif; ?>
                </div>
                <div class="mb-3">
                  <label class="form-label">Replace Image (optional)</label>
                  <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-success">Update Industry</button>
                  <a href="../components/industries.html" class="btn btn-outline-secondary">Back to Industries</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>
