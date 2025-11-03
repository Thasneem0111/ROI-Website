<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'setting';
// fetch current admin settings (single-row)
$admin_row = null;
try {
    $res = $conn->query("SELECT * FROM `admin` LIMIT 1");
    if ($res) {
        $admin_row = $res->fetch_assoc();
        if (method_exists($res,'free')) $res->free();
    }
} catch (Exception $e) {
    // ignore, $admin_row stays null
}

// handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect posted values
    $firstName = isset($_POST['firstName']) ? trim($_POST['firstName']) : '';
    $lastName = isset($_POST['lastName']) ? trim($_POST['lastName']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $contact = isset($_POST['contact']) ? trim($_POST['contact']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // choose identifier to update: username if available else update all rows
    $identifierCol = null;
    $identifierVal = null;
    if ($admin_row && isset($admin_row['username'])) {
        $identifierCol = 'username';
        $identifierVal = $admin_row['username'];
    }

    // prepare update SQL
    if ($identifierCol) {
        $stmt = $conn->prepare("UPDATE `admin` SET `firstName` = ?, `lastName` = ?, `email` = ?, `phoneNumber` = ?, `description` = ? WHERE `".$identifierCol."` = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('ssssss', $firstName, $lastName, $email, $contact, $description, $identifierVal);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            $ok = false;
        }
    } else {
        // fallback: update every row (not ideal) — use safe prepared statement
        $stmt = $conn->prepare("UPDATE `admin` SET `firstName` = ?, `lastName` = ?, `email` = ?, `phoneNumber` = ?, `description` = ?");
        if ($stmt) {
            $stmt->bind_param('sssss', $firstName, $lastName, $email, $contact, $description);
            $ok = $stmt->execute();
            $stmt->close();
        } else {
            $ok = false;
        }
    }

    // reload admin row for display
    try {
        $res = $conn->query("SELECT * FROM `admin` LIMIT 1");
        if ($res) { $admin_row = $res->fetch_assoc(); if (method_exists($res,'free')) $res->free(); }
    } catch (Exception $e) {}

    $saved = $ok ? true : false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Settings - Admin</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="./dashboard.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-panel">
            <div class="top-bar">
                <h1>General Settings</h1>
                <div class="top-bar-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search">
                        <button><i class="fa fa-search"></i></button>
                    </div>
                    <div class="admin-profile">
                        <img src="../images/man.png" alt="Admin" class="profile-pic">
                        <span>Mr. Kaleel</span>
                    </div>
                </div>
            </div>

            <div class="settings-panel" style="background:#ffffff; border-radius:24px; margin:40px auto 0 auto; max-width:700px; box-shadow:0 4px 32px rgba(44,166,164,0.10); padding:40px 36px 32px 36px;">
                <form method="post" action="setting" style="display:flex; flex-direction:column; align-items:center; gap:32px; width:100%;">
                    <div style="display:flex; flex-direction:column; align-items:center; margin-bottom:16px;">
                        <div style="width:80px; height:80px; background:#2ca6a4; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:12px; box-shadow:0 2px 12px rgba(44,166,164,0.12);">
                            <i class="fa fa-camera" style="color:#fff; font-size:2.2rem;"></i>
                        </div>
                        <span style="font-weight:600; color:#222; font-size:1.08rem;">Edit your profile</span>
                    </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:28px 32px; width:100%; max-width:600px; margin-bottom:16px;">
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <label for="firstName" style="font-weight:500; color:#222;">First Name</label>
                                <input type="text" name="firstName" id="firstName" value="<?php echo htmlspecialchars($admin_row['firstName'] ?? ''); ?>" style="padding:12px 16px; border-radius:10px; border:1.5px solid #e3e6ea; background:#f7f8fa; font-size:1rem;">
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <label for="email" style="font-weight:500; color:#222;">Email</label>
                                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($admin_row['email'] ?? ''); ?>" style="padding:12px 16px; border-radius:10px; border:1.5px solid #e3e6ea; background:#f7f8fa; font-size:1rem;">
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <label for="lastName" style="font-weight:500; color:#222;">Last Name</label>
                                <input type="text" name="lastName" id="lastName" value="<?php echo htmlspecialchars($admin_row['lastName'] ?? ''); ?>" style="padding:12px 16px; border-radius:10px; border:1.5px solid #e3e6ea; background:#f7f8fa; font-size:1rem;">
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px; grid-column:2/3; grid-row:2/4;">
                                <label for="description" style="font-weight:500; color:#222;">Description</label>
                                <textarea name="description" id="description" style="padding:12px 16px; border-radius:10px; border:1.5px solid #e3e6ea; background:#f7f8fa; min-height:70px; font-size:1rem;"><?php echo htmlspecialchars($admin_row['description'] ?? ''); ?></textarea>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:8px;">
                                <label for="contact" style="font-weight:500; color:#222;">Contact No</label>
                                <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($admin_row['phoneNumber'] ?? ''); ?>" style="padding:12px 16px; border-radius:10px; border:1.5px solid #e3e6ea; background:#f7f8fa; font-size:1rem;">
                            </div>
                        </div>
                    <button type="submit" class="save-btn" style="align-self:center; width:180px;">Save</button>
                    <?php if (isset($saved) && $saved): ?>
                        <div id="saveStatus" style="margin-top:12px; color:green; font-weight:600;">Settings saved successfully.</div>
                        <script>
                            // Hide the success message after 5 seconds
                            (function(){
                                try {
                                    var el = document.getElementById('saveStatus');
                                    if (el) {
                                        setTimeout(function(){
                                            // fade out
                                            el.style.transition = 'opacity 300ms ease';
                                            el.style.opacity = '0';
                                            setTimeout(function(){ el.style.display = 'none'; }, 450);
                                        }, 5000);
                                    }
                                } catch(e) { /* ignore */ }
                            })();
                        </script>
                    <?php elseif (isset($saved) && $saved === false): ?>
                        <div style="margin-top:12px; color:#c00; font-weight:600;">Unable to save settings. Check server logs.</div>
                    <?php endif; ?>
                </form>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var logoutBtn = document.getElementById('logoutBtn');
            if (logoutBtn) logoutBtn.addEventListener('click', function(){ window.location.href = 'logout'; });
        });
    </script>
</body>
</html>
