<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Industry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body class="bg-light">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card shadow-sm">
            <div class="card-body">
              <h3 class="card-title mb-3">Add New Industry</h3>

              <?php if(isset($_GET['status'])): ?>
                <?php $s = $_GET['status']; $msg = isset($_GET['msg']) ? urldecode($_GET['msg']) : ''; ?>
                <?php if($s === 'success'): ?>
                  <div class="alert alert-success"><?php echo htmlspecialchars($msg ?: 'Industry added successfully.'); ?></div>
                <?php else: ?>
                  <div class="alert alert-danger"><?php echo htmlspecialchars($msg ?: 'An error occurred.'); ?></div>
                <?php endif; ?>
              <?php endif; ?>

              <form action="upload.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Industry Name</label>
                  <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea name="description" rows="4" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Image (optional)</label>
                  <input type="file" name="image" accept="image/*" class="form-control">
                </div>
                <div class="d-flex gap-2">
                  <button type="submit" class="btn btn-primary">Add Industry</button>
                  <a href="../components/industries.html" class="btn btn-outline-secondary">View Industries Page</a>
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
