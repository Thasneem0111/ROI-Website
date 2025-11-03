<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'email';
// load admin contact info for reply-from
$adminEmail = '';
$adminName = '';
try {
    $r = $conn->query("SELECT firstName, lastName, email FROM `admin` LIMIT 1");
    if ($r) {
        $row = $r->fetch_assoc();
        $adminEmail = $row['email'] ?? '';
        $adminName = trim(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''));
        if (method_exists($r,'free')) $r->free();
    }
} catch (Throwable $e) { }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>Email - Admin</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-backdrop.show { z-index: 2050 !important; }
        .modal { z-index: 2100 !important; }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>ROI - Marketing & Technology Agency</title>
    <link rel="icon" type="image/png" href="../images/logo.png">
    <link rel="stylesheet" href="./dashboard.css">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0" style="justify-content:center;">
                    <h5 class="modal-title w-100 text-center" id="logoutModalLabel" style="font-weight:700;">Logout Confirmation</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-sign-out-alt" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer border-0" style="justify-content:center; gap:18px; padding-bottom:24px;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" id="confirmLogout" style="border-radius:8px; background:#218a8a; border:none;">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0" style="justify-content:center;">
                    <h5 class="modal-title w-100 text-center" id="deleteModalLabel" style="font-weight:700;">Delete Email</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" id="deleteModalBody" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-trash" style="font-size:2.2rem; color:#d9534f; margin-bottom:12px;"></i><br>
                    Do you want to delete?
                </div>
                <div class="modal-footer border-0" style="justify-content:center; gap:18px; padding-bottom:24px;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius:8px;">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDelete" style="border-radius:8px; background:#d9534f; border:none;">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <main class="main-panel">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1>Email</h1>
                <div class="top-bar-right">
                    <div class="search-bar">
                        <input type="text" placeholder="Search mail">
                        <button><i class="fa fa-search"></i></button>
                    </div>
                    <div class="admin-profile">
                        <img src="../images/man.png" alt="Admin" class="profile-pic">
                        <span>Mr. Kaleel</span>
                    </div>
                </div>
            </div>

            <!-- Email View Panel -->
            <div class="email-panel" style="background:#fff; border-radius:20px; margin-top:20px; margin-left:0; margin-right:0; width:100%; max-width:none; box-shadow:0 2px 12px rgba(44,166,164,0.06); padding:32px 0 0 0;">
                <div style="padding:0 32px 16px 32px;">
                    <div style="display:flex; align-items:center; justify-content:space-between;">
                        <div class="search-bar" style="flex:1; max-width:340px;">
                                <input type="text" id="emailSearch" placeholder="Search mail" style="width:100%;">
                                <button onclick="filterEmails()"><i class="fa fa-search"></i></button>
                        </div>
                        <div style="display:flex; gap:16px;">
                            <button style="background:none; border:none; font-size:1.2rem; cursor:pointer;"><i class="fa fa-star"></i></button>
                            <button id="deleteAllBtn" style="background:none; border:none; font-size:1.2rem; cursor:pointer;"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
                <table style="width:100%; border-collapse:collapse;">
                    <tbody id="emailsTableBody">
                    </tbody>
                </table>
                    <!-- Reply Modal -->
                    <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reply to message</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="replyForm">
                                        <div class="mb-3">
                                            <label class="form-label">To</label>
                                            <input type="email" id="replyTo" name="to" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Subject</label>
                                            <input type="text" id="replySubject" name="subject" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Message</label>
                                            <textarea id="replyBody" name="body" class="form-control" rows="6" required></textarea>
                                        </div>
                                    </form>
                                    <div id="replyStatus" style="display:none; margin-top:8px;"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" id="sendReplyBtn" class="btn btn-primary">Send</button>
                                </div>
                            </div>
                        </div>
                    </div>
<!-- Only keep one script block below -->
                <!-- Only one chevron button, right for next page -->
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:8px; padding:16px 32px;">
                    <button style="background:none; border:none; font-size:1.2rem; cursor:pointer;"><i class="fa fa-chevron-left"></i></button>
                    <button style="background:none; border:none; font-size:1.2rem; cursor:pointer;"><i class="fa fa-chevron-right"></i></button>
                </div>
            </div>
        </main>
    </div>
<script>
// Loaded messages from the server (api/messages.json)
let messages = [];
let filtered = [];
let deleteType = null; // 'selected' or 'all' (UI only)
let selectedIndexes = [];
function attachTrashListener() {
    const trashBtn = document.getElementById('deleteAllBtn');
    if (trashBtn) {
        trashBtn.onclick = function() {
            selectedIndexes = Array.from(document.querySelectorAll('#emailsTableBody input[type="checkbox"]:checked')).map(cb => parseInt(cb.getAttribute('data-index')));
            var deleteModalEl = document.getElementById('deleteModal');
            var deleteModalBody = document.getElementById('deleteModalBody');
            var deleteModal = new bootstrap.Modal(deleteModalEl);
            if (selectedIndexes.length > 0) {
                deleteType = 'selected';
                deleteModalBody.innerHTML = `<i class='fa fa-trash' style='font-size:2.2rem; color:#d9534f; margin-bottom:12px;'></i><br>Do you want to delete the selected email(s)?`;
            } else {
                deleteType = 'all';
                deleteModalBody.innerHTML = `<i class='fa fa-trash' style='font-size:2.2rem; color:#d9534f; margin-bottom:12px;'></i><br>Do you want to delete all?`;
            }
            deleteModal.show();
        };
    }
}

function fmtTime(ts){
    try{
        const d = typeof ts === 'number' ? new Date(ts) : new Date(ts || Date.now());
        const now = Date.now();
        const diff = Math.max(0, now - d.getTime());
        const mins = Math.floor(diff/60000);
        if(mins < 60) return `${mins} min ago`;
        const hrs = Math.floor(mins/60);
        if(hrs < 24) return `${hrs} hr${hrs>1?'s':''} ago`;
        return d.toLocaleString();
    }catch(_){
        return '';
    }
}

function renderEmails(list) {
    const tbody = document.getElementById('emailsTableBody');
    tbody.innerHTML = '';
    list.forEach((e, i) => {
        const subject = e.businessName && e.businessName.trim() ? `Consultation from ${e.businessName}` : 'Consultation request';
        const when = fmtTime(e.createdAt);
            tbody.innerHTML += `<tr style='border-bottom:1px solid #eee;'>
            <td style='padding:12px 32px;'><input type='checkbox' data-index='${i}'></td>
            <td style='padding:12px 0;'><i class='fa fa-star' style='color:#ccc;'></i></td>
            <td style='font-weight:600;'>${e.name || '-'}</td>
            <td>
                <div style='display:flex;flex-direction:column;'>
                    <span style='color:#222'>${subject}</span>
                    <small style='color:#666'>${e.email || ''} ${e.phone? ' • ' + e.phone : ''}</small>
                </div>
            </td>
            <td style='text-align:right; padding-right:32px; color:#888;'>${when}</td>
            <td style='padding-right:32px;'><button class='btn btn-sm btn-outline-primary replyBtn' data-index='${i}'>Reply</button></td>
        </tr>`;
    });
    attachTrashListener();
    attachReplyListeners();
}

function attachReplyListeners(){
    document.querySelectorAll('.replyBtn').forEach(btn=>{
        btn.onclick = function(){
            const idx = parseInt(this.getAttribute('data-index'));
            const msg = filtered[idx] || messages[idx];
            if(!msg) return;
            // prefill modal fields
            document.getElementById('replyTo').value = msg.email || '';
            document.getElementById('replySubject').value = `Re: Consultation from ${msg.businessName || msg.name || ''}`;
            document.getElementById('replyBody').value = `\n\n---\nOriginal message from ${msg.name || ''} (${msg.email || ''}) on ${new Date(msg.createdAt||Date.now()).toLocaleString()}\n`;
            // show modal
            var replyModalEl = document.getElementById('replyModal');
            var replyModal = new bootstrap.Modal(replyModalEl);
            replyModal.show();
        };
    });
}

function filterEmails() {
    const search = document.getElementById('emailSearch').value.toLowerCase();
    filtered = messages.filter(e => {
        const hay = [e.name, e.email, e.phone, e.businessName].filter(Boolean).join(' ').toLowerCase();
        return hay.includes(search);
    });
    renderEmails(filtered);
}

document.getElementById('emailSearch').addEventListener('input', filterEmails);
async function loadMessages(){
    try {
        // Use relative path so this works when the project is hosted in a subfolder (e.g. /ROIwebsite)
        const resp = await fetch('../api/messages.json?ts=' + Date.now(), { cache:'no-store' });
        if(!resp.ok) throw new Error('HTTP '+resp.status);
        const data = await resp.json();
        if(Array.isArray(data)){
            // newest first
            messages = data.sort((a,b)=> (b.createdAt||0) - (a.createdAt||0));
        } else {
            messages = [];
        }
    } catch(err){
        console.warn('Failed to load messages.json:', err.message);
        messages = [];
    }
    filtered = messages.slice();
    renderEmails(filtered);
}

window.onload = function(){ loadMessages(); };

// Delete modal logic
document.addEventListener('DOMContentLoaded', function() {
    var confirmDelete = document.getElementById('confirmDelete');
    confirmDelete.addEventListener('click', async function() {
        // gather createdAt targets to delete
        let targets = [];
        if (deleteType === 'selected' && selectedIndexes.length > 0) {
            selectedIndexes.forEach(idx => {
                const msg = filtered[idx];
                if (msg && msg.createdAt) targets.push(msg.createdAt);
            });
        } else if (deleteType === 'all') {
            // delete everything currently loaded
            targets = messages.map(m => m.createdAt).filter(Boolean);
        }

        // no targets => nothing to do
        if (!targets || targets.length === 0) {
            // hide modal
            var deleteModalEl = document.getElementById('deleteModal');
            var modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
            if (modalInstance) modalInstance.hide();
            selectedIndexes = [];
            deleteType = null;
            return;
        }

        // call server API to delete
        try {
            const resp = await fetch('api_delete_message', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ createdAt: targets })
            });
            const data = await resp.json();
            if (data && data.ok) {
                // remove deleted from local arrays
                const set = new Set(targets.map(t => Number(t)));
                messages = messages.filter(m => !set.has(Number(m.createdAt)));
                filtered = filtered.filter(m => !set.has(Number(m.createdAt)));
                renderEmails(filtered);
            } else {
                alert('Delete failed: ' + (data && data.message ? data.message : 'server error'));
            }
        } catch (err) {
            alert('Delete failed: ' + err.message);
        }

        // cleanup and hide modal
        selectedIndexes = [];
        deleteType = null;
        var deleteModalEl = document.getElementById('deleteModal');
        var modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            deleteModalEl.classList.remove('show');
            deleteModalEl.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            let backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
        }
    });
});
// Reply send logic
document.addEventListener('DOMContentLoaded', function() {
    const sendBtn = document.getElementById('sendReplyBtn');
    const statusEl = document.getElementById('replyStatus');
    sendBtn.addEventListener('click', async function(){
        const to = document.getElementById('replyTo').value.trim();
        const subject = document.getElementById('replySubject').value.trim();
        const body = document.getElementById('replyBody').value.trim();
        statusEl.style.display = 'none';
        if(!to || !subject || !body){
            statusEl.style.display = 'block'; statusEl.style.color = 'red'; statusEl.textContent = 'Please fill all fields.'; return;
        }
        sendBtn.disabled = true; sendBtn.textContent = 'Sending...';
        try {
            const resp = await fetch('api_send_reply', {
                method: 'POST', headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ to, subject, body })
            });
            const data = await resp.json();
            if(data && data.ok){
                statusEl.style.display = 'block'; statusEl.style.color = 'green'; statusEl.textContent = 'Reply sent successfully.';
                // auto-close modal after short delay
                setTimeout(()=>{ var m = bootstrap.Modal.getInstance(document.getElementById('replyModal')); if(m) m.hide(); }, 1200);
            } else {
                statusEl.style.display = 'block'; statusEl.style.color = 'red'; statusEl.textContent = data.message || 'Failed to send reply.';
            }
        } catch(err){
            statusEl.style.display = 'block'; statusEl.style.color = 'red'; statusEl.textContent = 'Error: '+err.message;
        } finally { sendBtn.disabled = false; sendBtn.textContent = 'Send'; }
    });
});
</script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logout modal logic
            var logoutBtn = document.getElementById('logoutBtn');
            var logoutModalEl = document.getElementById('logoutModal');
            var confirmLogout = document.getElementById('confirmLogout');
            var logoutModal = new bootstrap.Modal(logoutModalEl);
            if (logoutBtn) logoutBtn.addEventListener('click', function() { logoutModal.show(); });
            if (confirmLogout) confirmLogout.addEventListener('click', function() { window.location.href = 'logout'; });
        });
    </script>
</body>
</html>
