<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'services';
?>
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
                    <h5 class="modal-title w-100 text-center" id="deleteModalLabel" style="font-weight:700;">Delete Service</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-trash" style="font-size:2.2rem; color:#d9534f; margin-bottom:12px;"></i><br>
                    Do you want to delete this service?
                </div>
                <div class="modal-footer border-0" style="justify-content:center; gap:18px; padding-bottom:24px;">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal" style="border-radius:8px;">No</button>
                    <button type="button" class="btn btn-danger px-4" id="confirmDelete" style="border-radius:8px; background:#d9534f; border:none;">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-container">
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-panel" style="display:flex; flex-direction:column; height:100vh;">
            <!-- Top Bar -->
            <div class="top-bar" style="position:sticky; top:0; z-index:2; background:#fff;">
                <h1>Services</h1>
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

            <!-- Services View Panel -->
            <div class="services-panel" style="background:#fff; border-radius:16px; margin-top:32px; width:100%; max-width:none; box-shadow:0 2px 12px rgba(44,166,164,0.06); padding:32px 0 0 0; flex:1;">
                <div style="display:flex; gap:32px; padding:0 32px 24px 32px;">
                    <div style="display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px; font-size:1rem; font-weight:500;">
                        Total Services <span style="margin-left:16px; font-size:1rem; font-weight:normal;">21</span>
                    </div>
                    <div style="flex:1; display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px;">
                        <input type="text" id="serviceSearch" placeholder="Search" style="border:none; background:transparent; font-size:1rem; width:100%; outline:none;">
                        <button style="background:none; border:none; color:#222; font-size:1.3rem; cursor:pointer;" onclick="filterServices()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div style="padding:0 32px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody id="servicesTableBody">
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:24px; padding:32px 32px;">
                       <button class="action-btn update-btn">Update Services</button>
                       <button class="action-btn add-btn" id="addServiceBtn" data-bs-toggle="modal" data-bs-target="#addServiceModal">
                           <span class="add-icon-circle"><i class="fa fa-plus"></i></span>
                           Add new service
                       </button>
       <!-- Add New Service Modal -->
       <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
           <div class="modal-dialog modal-lg modal-dialog-centered">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title" id="addServiceModalLabel">New Services</h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <div class="modal-body">
                       <form id="serviceForm">
                           <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Total Services</label>
                                <input type="text" class="form-control" value="21" readonly>
                            </div>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Service Name <span class="text-danger">*</span></label>
                               <input type="text" class="form-control required-field" id="serviceName" placeholder="Type your service name here...." required>
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Upload Image <span class="text-danger">*</span></label>
                               <div class="input-group">
                                   <input type="file" class="form-control" id="serviceUploadImage" accept="image/*">
                                   <button class="btn btn-outline-secondary" type="button" id="serviceUploadBtn"><i class="fa fa-upload"></i></button>
                               </div>
                               <img id="servicePreviewImg" src="#" alt="Preview" style="max-width:120px; margin-top:10px; display:none;" />
                           </div>
                           <div class="mb-3">
                               <label class="form-label">Service Description <span class="text-danger">*</span></label>
                               <textarea class="form-control required-field" id="serviceDesc" rows="3" placeholder="Write About Service...." required></textarea>
                           </div>
                       </form>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                       <button type="button" class="btn btn-success" id="updateServiceBtn">Update Services</button>
                       <button type="button" class="btn btn-primary" id="addNewServiceBtn">Add new service</button>
                   </div>
               </div>
           </div>
       </div>

       <!-- Confirmation Modal -->
       <div class="modal fade" id="confirmAddServiceModal" tabindex="-1" aria-labelledby="confirmAddServiceModalLabel" aria-hidden="true">
           <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title w-100 text-center" id="confirmAddServiceModalLabel">Confirm Add Service</h5>
                       <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <div class="modal-body text-center" style="border-bottom:1px solid #eee;">
                       <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                       <span style="font-size:1.15rem; color:#222;">Please confirm:<br>Do you want to add this service to the system?</span>
                   </div>
                   <div class="modal-footer justify-content-center" style="gap:16px;">
                       <button type="button" class="btn btn-secondary px-4" id="noAddServiceBtn">No</button>
                       <button type="button" class="btn btn-success px-4" id="yesAddServiceBtn">Yes</button>
                   </div>
               </div>
           </div>
       </div>

       <!-- Success Modal (generic for Add/Update/Delete) -->
       <div class="modal fade" id="successServiceModal" tabindex="-1" aria-labelledby="successServiceModalLabel" aria-hidden="true">
           <div class="modal-dialog modal-dialog-centered">
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title" id="successServiceModalTitle">Success</h5>
                       <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                   </div>
                   <div class="modal-body text-center">
                       <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                       <div id="successServiceModalBody" style="font-size:1.15rem; color:#222;"></div>
                   </div>
                   <div class="modal-footer justify-content-center" style="gap:16px;">
                       <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" id="successServiceCloseBtn">Close</button>
                       <button type="button" class="btn btn-primary px-4" id="addAnotherServiceBtn">Add Another Service</button>
                   </div>
               </div>
           </div>
       </div>
                </div>
                <style>
                .action-btn {
                    background: #2ca6a4;
                    color: #fff;
                    border: none;
                    border-radius: 20px;
                    padding: 8px 24px;
                    font-size: 1rem;
                    min-width: 170px;
                    min-height: 44px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 8px;
                    cursor: pointer;
                    transition: background 0.2s, color 0.2s;
                }
                .action-btn:hover {
                    background: #00796b;
                    color: #fff;
                }
                .add-icon-circle {
                    color: #ffffff;
                    border-radius: 50%;
                    width: 28px;
                    height: 28px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.2rem;
                    transition: background 0.2s, color 0.2s;
                }
                .add-btn:hover .add-icon-circle {
                    background: #00796b;
                    color: #fff;
                }
                /* Increase gap between service rows */
                #servicesTableBody tr {
                    border-bottom: 1px solid #eee;
                    height: 56px;
                }
                </style>
            </div>
            <script>
            // Dynamic services list and CRUD wiring (mirrors industries behavior)
            let services = [];
            const apiList = 'get_services.php';
            const apiUpload = 'api_upload_service.php';

            function escapeHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            function renderServices(list) {
                const tbody = document.getElementById('servicesTableBody');
                tbody.innerHTML = '';
                list.forEach((it, i) => {
                    const dateText = it.createdAt ? new Date(it.createdAt).toLocaleDateString() : '';
                    tbody.innerHTML += `<tr>
                        <td style='padding:12px 0; width:40px;'><input type='checkbox' name='serviceSelect' data-id='${it.id}'></td>
                        <td style='font-weight:600;'>${escapeHtml(it.name || '')}</td>
                        <td style='color:#888; text-align:right;'>${escapeHtml(dateText)}</td>
                        <td style='text-align:right; width:60px;'><i class='fa fa-trash' style='color:#222; cursor:pointer;' data-index='${i}' data-id='${it.id}'></i></td>
                    </tr>`;
                });

                // attach delete handlers
                Array.from(document.querySelectorAll('.fa-trash[data-index]')).forEach(icon => {
                    icon.addEventListener('click', function() {
                        var idx = parseInt(this.getAttribute('data-index'));
                        var id = this.getAttribute('data-id');
                        document.getElementById('confirmDelete').setAttribute('data-id', id);
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        deleteModal.show();
                    });
                });

                // checkbox single-select behavior and update button enabling
                const checkboxes = Array.from(document.querySelectorAll('input[name="serviceSelect"]'));
                checkboxes.forEach(cb => cb.addEventListener('change', function(){
                    if (this.checked) {
                        checkboxes.forEach(other => { if (other !== this) other.checked = false; });
                    }
                    const selected = checkboxes.filter(c => c.checked);
                    document.querySelectorAll('.update-btn').forEach(b=>b.disabled = (selected.length !== 1));
                }));
            }

            async function loadServices(){
                try {
                    const res = await fetch(apiList);
                    if (!res.ok) throw new Error('Network');
                    services = await res.json();
                    renderServices(services);
                } catch (e) { console.error('Failed to load services', e); }
            }

            function filterServices() {
              const search = document.getElementById('serviceSearch').value.toLowerCase();
              renderServices(services.filter(s => (s.name||'').toLowerCase().includes(search)));
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadServices();
                // hide update button by default (modal used for add)
                var usBtn = document.getElementById('updateServiceBtn'); if (usBtn) usBtn.style.display = 'none';
                // Wire upload input preview
                document.getElementById('serviceUploadBtn').addEventListener('click', function(){ document.getElementById('serviceUploadImage').click(); });
                document.getElementById('serviceUploadImage').addEventListener('change', function(e){ var f=e.target.files[0]; var p=document.getElementById('servicePreviewImg'); if(f){var r=new FileReader(); r.onload=function(ev){p.src=ev.target.result;p.style.display='block';}; r.readAsDataURL(f);} else p.style.display='none';});

                document.getElementById('serviceSearch').addEventListener('input', filterServices);
            });
            </script>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add style for invalid fields
            if (!document.getElementById('service-invalid-style')) {
                var styleEl = document.createElement('style');
                styleEl.id = 'service-invalid-style';
                styleEl.innerHTML = `.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25) !important; }`;
                document.head.appendChild(styleEl);
            }

            // Upload image button & preview already wired in earlier script; ensure fallback
            var uploadBtn = document.getElementById('serviceUploadBtn');
            if (uploadBtn) uploadBtn.addEventListener('click', function() { document.getElementById('serviceUploadImage').click(); });
            var uploadInput = document.getElementById('serviceUploadImage');
            if (uploadInput) uploadInput.addEventListener('change', function(e) {
                var file = e.target.files[0];
                var preview = document.getElementById('servicePreviewImg');
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(ev) { preview.src = ev.target.result; preview.style.display = 'block'; };
                    reader.readAsDataURL(file);
                } else { if (preview) preview.style.display = 'none'; }
            });

            // Add new service -> validate and show confirm
            document.getElementById('addNewServiceBtn').addEventListener('click', function() {
                var valid = true;
                var requiredFields = document.querySelectorAll('#serviceName, #serviceDesc');
                requiredFields.forEach(function(field) {
                    if (!field.value || field.value.trim() === '') { field.classList.add('is-invalid'); valid = false; } else { field.classList.remove('is-invalid'); }
                });
                var imageInput = document.getElementById('serviceUploadImage');
                if (!imageInput.files || imageInput.files.length === 0) { imageInput.classList.add('is-invalid'); valid = false; } else { imageInput.classList.remove('is-invalid'); }
                if (!valid) { var firstInvalid = document.querySelector('.is-invalid'); if (firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth', block:'center'}); return; }
                var confirmModal = new bootstrap.Modal(document.getElementById('confirmAddServiceModal'));
                confirmModal.show();
            });

            // When user confirms add, perform AJAX upload to api_upload_service.php
            document.getElementById('yesAddServiceBtn').addEventListener('click', async function(){
                var confirmModalEl=document.getElementById('confirmAddServiceModal'); var confirmModal=bootstrap.Modal.getInstance(confirmModalEl); confirmModal.hide();
                var addModalEl=document.getElementById('addServiceModal'); var addModal=bootstrap.Modal.getInstance(addModalEl); addModal.hide();

                const fd = new FormData();
                fd.append('name', document.getElementById('serviceName').value.trim());
                fd.append('description', document.getElementById('serviceDesc').value.trim());
                const f = document.getElementById('serviceUploadImage').files[0]; if (f) fd.append('image', f);

                this.disabled = true;
                try {
                    const resp = await fetch(apiUpload, { method: 'POST', body: fd });
                    const json = await resp.json();
                    if (json.success) {
                        const newItem = { id: json.id, name: json.name, description: json.description, image: json.image, createdAt: json.createdAt };
                        services.unshift(newItem);
                        document.getElementById('serviceForm').reset();
                        document.getElementById('servicePreviewImg').style.display='none';
                        try {
                            document.getElementById('successServiceModalTitle').innerText = 'Service Successfully Added';
                            document.getElementById('successServiceModalBody').innerHTML = 'Thank you! The new service has been added to the system.<br>Would you like to add another service or return to the dashboard?';
                            document.getElementById('addAnotherServiceBtn').style.display = 'inline-block';
                            var successModal = new bootstrap.Modal(document.getElementById('successServiceModal'));
                            successModal.show();
                        } catch (e) { alert('Added'); }
                        renderServices(services);
                    } else {
                        alert(json.message || 'Upload failed');
                    }
                } catch (err) { console.error(err); alert('Upload failed'); }
                finally { this.disabled = false; }
            });

            document.getElementById('addAnotherServiceBtn').addEventListener('click', function() {
                var successModalEl = document.getElementById('successServiceModal');
                var successModal = bootstrap.Modal.getInstance(successModalEl);
                successModal.hide();
                var addServiceModalEl = document.getElementById('addServiceModal');
                var addServiceModal = new bootstrap.Modal(addServiceModalEl);
                addServiceModal.show();
            });

            document.getElementById('noAddServiceBtn').addEventListener('click', function() {
                var confirmModalEl = document.getElementById('confirmAddServiceModal');
                var confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
                confirmModal.hide();
                if (confirm('Do you want to close the form and clear all provided details?')) {
                    var form = document.getElementById('serviceForm'); if (form) form.reset();
                    var preview = document.getElementById('servicePreviewImg'); if (preview) preview.style.display = 'none';
                    var addServiceModalEl = document.getElementById('addServiceModal');
                    var addServiceModal = bootstrap.Modal.getInstance(addServiceModalEl);
                    if (addServiceModal) addServiceModal.hide();
                }
            });

            // Delete confirm: call api_delete_service.php
            document.getElementById('confirmDelete').addEventListener('click', async function(){
                const id = this.getAttribute('data-id');
                if (!id) return;
                try {
                    const fd = new FormData(); fd.append('id', id);
                    const r = await fetch('api_delete_service.php', { method: 'POST', body: fd });
                    const j = await r.json();
                    if (j.success) {
                        services = services.filter(it => String(it.id) !== String(id));
                        renderServices(services);
                        var delModalEl = document.getElementById('deleteModal'); var delModal = bootstrap.Modal.getInstance(delModalEl); if(delModal) delModal.hide();
                        // show generic success modal for delete
                        try {
                            document.getElementById('successServiceModalTitle').innerText = 'Service Deleted';
                            document.getElementById('successServiceModalBody').innerText = 'The service has been deleted successfully.';
                            document.getElementById('addAnotherServiceBtn').style.display = 'none';
                            var successModal = new bootstrap.Modal(document.getElementById('successServiceModal'));
                            successModal.show();
                        } catch (e) { /* ignore UI error */ }
                    } else {
                        alert(j.message || 'Delete failed');
                    }
                } catch (e) { console.error(e); alert('Delete failed'); }
            });

            // Update flow using same modal: enable update button only when exactly one is selected
            const updateButtons = document.querySelectorAll('.update-btn');
            updateButtons.forEach(b => b.disabled = true);
            document.querySelectorAll('.update-btn').forEach(btn => btn.addEventListener('click', function(){
                const sel = Array.from(document.querySelectorAll('input[name="serviceSelect"]')).find(c => c.checked);
                if (!sel) { alert('Please select one service to update.'); return; }
                const id = sel.getAttribute('data-id');
                const item = services.find(it => String(it.id) === String(id));
                if (!item) { alert('Selected item not found'); return; }

                document.getElementById('serviceName').value = item.name || '';
                document.getElementById('serviceDesc').value = item.description || '';
                document.getElementById('servicePreviewImg').style.display = item.image ? 'block' : 'none';
                if (item.image) document.getElementById('servicePreviewImg').src = item.image;
                document.getElementById('serviceUploadImage').value = '';

                var addModalEl = document.getElementById('addServiceModal');
                var addModal = new bootstrap.Modal(addModalEl);
                document.getElementById('updateServiceBtn').style.display = 'inline-block';
                document.getElementById('addNewServiceBtn').style.display = 'none';
                document.getElementById('updateServiceBtn').setAttribute('data-id', id);
                addModal.show();
            }));

            // Handle update submission
            document.getElementById('updateServiceBtn').addEventListener('click', async function(){
                const id = this.getAttribute('data-id');
                if (!id) { alert('Missing id'); return; }
                const name = document.getElementById('serviceName').value.trim();
                if (!name) { alert('Name is required'); return; }
                const desc = document.getElementById('serviceDesc').value.trim();
                const file = document.getElementById('serviceUploadImage').files[0];

                const fd = new FormData(); fd.append('id', id); fd.append('name', name); fd.append('description', desc); if (file) fd.append('image', file);
                this.disabled = true;
                try {
                    const resp = await fetch('api_update_service.php', { method: 'POST', body: fd });
                    const json = await resp.json();
                    if (json.success) {
                        services = services.map(it => {
                            if (String(it.id) === String(id)) {
                                return Object.assign({}, it, { name: json.name || it.name, description: json.description || it.description, image: json.image || it.image });
                            }
                            return it;
                        });
                        renderServices(services);
                        var addModalEl = document.getElementById('addServiceModal'); var addModal = bootstrap.Modal.getInstance(addModalEl); if(addModal) addModal.hide();
                        // show generic success modal for update
                        try {
                            document.getElementById('successServiceModalTitle').innerText = 'Service Updated';
                            document.getElementById('successServiceModalBody').innerText = 'The service has been updated successfully.';
                            document.getElementById('addAnotherServiceBtn').style.display = 'none';
                            var successModal = new bootstrap.Modal(document.getElementById('successServiceModal'));
                            successModal.show();
                        } catch (e) { alert('Service updated'); }
                    } else {
                        alert(json.message || 'Update failed');
                    }
                } catch (e) { console.error(e); alert('Update failed'); }
                finally { this.disabled = false; }
            });

            // Reset modal on hide
            document.getElementById('addServiceModal').addEventListener('hidden.bs.modal', function(){
                document.getElementById('updateServiceBtn').style.display = 'none';
                document.getElementById('addNewServiceBtn').style.display = 'inline-block';
                document.getElementById('updateServiceBtn').removeAttribute('data-id');
                document.getElementById('serviceForm').reset();
                document.getElementById('servicePreviewImg').style.display='none';
            });

            // Search wiring
            document.getElementById('serviceSearch').addEventListener('input', filterServices);

            // Logout handling
            var logoutBtn = document.getElementById('logoutBtn');
            var logoutModalEl = document.getElementById('logoutModal');
            var confirmLogout = document.getElementById('confirmLogout');
            var logoutModal = new bootstrap.Modal(logoutModalEl);
            if (logoutBtn) logoutBtn.addEventListener('click', function() { logoutModal.show(); });
            if (confirmLogout) confirmLogout.addEventListener('click', function() { window.location.href = 'logout.php'; });
        });
    </script>
</body>
</html>
