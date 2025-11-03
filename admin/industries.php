<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'industries';
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
    <title>Industries - Admin</title>
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
                    <h5 class="modal-title w-100 text-center" id="deleteModalLabel" style="font-weight:700;">Delete Industry</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-trash" style="font-size:2.2rem; color:#d9534f; margin-bottom:12px;"></i><br>
                    Do you want to delete this industry?
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

        <main class="main-panel" style="display:flex; flex-direction:column; height:100vh;">
            <div class="top-bar" style="position:sticky; top:0; z-index:2; background:#fff;">
                <h1>Industries</h1>
                <div class="top-bar-right">
                    <div class="search-bar">
                        <input type="text" id="industrySearch" placeholder="Search">
                        <button onclick="filterIndustries()"><i class="fa fa-search"></i></button>
                    </div>
                    <div class="admin-profile">
                        <img src="../images/man.png" alt="Admin" class="profile-pic">
                        <span>Mr. Kaleel</span>
                    </div>
                </div>
            </div>

            <!-- Industries View Panel (mirrors clients layout) -->
            <div class="clients-panel" style="background:#fff; border-radius:16px; margin-top:32px; width:100%; box-shadow:0 2px 12px rgba(44,166,164,0.06); padding:32px 0 0 0; flex:1;">
                <div style="display:flex; gap:32px; padding:0 32px 24px 32px;">
                    <div style="display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px; font-size:1rem; font-weight:500;">
                        Total Industries <span style="margin-left:16px; font-size:1rem; font-weight:normal;">07</span>
                    </div>
                    <div style="flex:1; display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px;">
                        <input type="text" id="industrySearchInput" placeholder="Search" style="border:none; background:transparent; font-size:1rem; width:100%; outline:none;">
                        <button style="background:none; border:none; color:#222; font-size:1.3rem; cursor:pointer;" onclick="filterIndustries()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div style="padding:0 32px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody id="industriesTableBody">
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:24px; padding:32px 32px;">
                       <button class="action-btn update-btn">Update Industries</button>
                       <button class="action-btn add-btn" id="addIndustryBtn" data-bs-toggle="modal" data-bs-target="#addIndustryModal">
                           <span class="add-icon-circle"><i class="fa fa-plus"></i></span>
                           Add New Industry
                       </button>
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
                .action-btn:hover { background: #00796b; color: #fff; }
                .add-icon-circle { color: #ffffff; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; transition: background 0.2s, color 0.2s; }
                .add-btn:hover .add-icon-circle { background: #00796b; color: #fff; }
                #industriesTableBody tr { border-bottom: 1px solid #eee; height: 56px; }
                </style>

           <!-- Add New Industry Modal (mirrors clients modal but simplified) -->
           <div class="modal fade" id="addIndustryModal" tabindex="-1" aria-labelledby="addIndustryModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-lg modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title" id="addIndustryModalLabel">New Industry</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body">
                           <form id="industryForm">
                               <div class="row mb-3">
                                   <div class="col-md-4">
                                       <label class="form-label">Total Industries</label>
                                       <input type="text" class="form-control" value="07" readonly>
                                   </div>
                                   <!-- <div class="col-md-8">
                                       <label class="form-label">Category <span class="text-danger">*</span></label>
                                       <select class="form-select" id="industryCategorySelect" required>
                                           <option value="">Select Category</option>
                                       </select>
                                   </div> -->
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Industry Name <span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" id="industryName" placeholder="Type industry name here...." required>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Upload Image</label>
                                   <div class="input-group">
                                       <input type="file" class="form-control" id="industryUploadImage" accept="image/*">
                                       <button class="btn btn-outline-secondary" type="button" id="industryUploadBtn"><i class="fa fa-upload"></i></button>
                                   </div>
                                   <img id="industryPreviewImg" src="#" alt="Preview" style="max-width:120px; margin-top:10px; display:none;" />
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Description</label>
                                   <textarea class="form-control" id="industryDesc" rows="3" placeholder="Describe industry..."></textarea>
                               </div>
                           </form>
                       </div>
                       <div class="modal-footer">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                           <button type="button" class="btn btn-success" id="updateIndustryBtn">Update</button>
                           <button type="button" class="btn btn-primary" id="addNewIndustryBtn">Add new Industry</button>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Confirmation & Success Modals -->
           <div class="modal fade" id="confirmAddIndustryModal" tabindex="-1" aria-labelledby="confirmAddIndustryModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title w-100 text-center" id="confirmAddIndustryModalLabel">Confirm Add Industry</h5>
                           <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body text-center" style="border-bottom:1px solid #eee;">
                           <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                           <span style="font-size:1.15rem; color:#222;">Please confirm:<br>Do you want to add this industry to the system?</span>
                       </div>
                       <div class="modal-footer justify-content-center" style="gap:16px;">
                           <button type="button" class="btn btn-secondary px-4" id="noAddIndustryBtn">No</button>
                           <button type="button" class="btn btn-success px-4" id="yesAddIndustryBtn">Yes</button>
                       </div>
                   </div>
               </div>
           </div>

           <div class="modal fade" id="successIndustryModal" tabindex="-1" aria-labelledby="successIndustryModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title" id="successIndustryModalTitle">Success</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body text-center">
                           <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                           <div id="successIndustryModalBody" style="font-size:1.15rem; color:#222;"></div>
                       </div>
                       <div class="modal-footer justify-content-center" style="gap:16px;">
                           <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" id="successIndustryCloseBtn">Close</button>
                           <button type="button" class="btn btn-primary px-4" id="addAnotherIndustryBtn">Add Another</button>
                       </div>
                   </div>
               </div>
           </div>

            </div>
            <script>
            // Dynamic data: fetch current industries from server and use API for add
            let industries = [];
            const apiList = 'get_industries';
            const apiUpload = 'api_upload';

            function renderIndustries(list) {
                const tbody = document.getElementById('industriesTableBody');
                tbody.innerHTML = '';
                list.forEach((it, i) => {
                    const dateText = it.createdAt ? new Date(it.createdAt).toLocaleDateString() : '';
                    tbody.innerHTML += `<tr>
                        <td style='padding:12px 0; width:40px;'><input type='checkbox' name='industrySelect' data-id='${it.id}'></td>
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
                        // store id on confirm button
                        document.getElementById('confirmDelete').setAttribute('data-id', id);
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        deleteModal.show();
                    });
                });

                // attach checkbox behavior: single-select and enable update button
                const checkboxes = Array.from(document.querySelectorAll('input[name="industrySelect"]'));
                checkboxes.forEach(cb => cb.addEventListener('change', function(){
                    if (this.checked) {
                        // uncheck others (single selection)
                        checkboxes.forEach(other => { if (other !== this) other.checked = false; });
                    }
                    // enable update only when exactly one is selected
                    const selected = checkboxes.filter(c => c.checked);
                    document.querySelectorAll('.update-btn').forEach(b=>b.disabled = (selected.length !== 1));
                }));
            }

            function escapeHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            async function loadIndustries(){
                try {
                    const res = await fetch(apiList);
                    if (!res.ok) throw new Error('Network');
                    industries = await res.json();
                    renderIndustries(industries);
                } catch (e) { console.error('Failed to load industries', e); }
            }

            document.addEventListener('DOMContentLoaded', function() {
                loadIndustries();

                // hide update button in modal by default (we use same modal for add & edit)
                var updBtnEl = document.getElementById('updateIndustryBtn'); if (updBtnEl) updBtnEl.style.display = 'none';

                document.getElementById('industryUploadBtn').addEventListener('click', function(){ document.getElementById('industryUploadImage').click(); });
                document.getElementById('industryUploadImage').addEventListener('change', function(e){ var f=e.target.files[0]; var p=document.getElementById('industryPreviewImg'); if(f){var r=new FileReader(); r.onload=function(ev){p.src=ev.target.result;p.style.display='block';}; r.readAsDataURL(f);} else p.style.display='none';});

                document.getElementById('addNewIndustryBtn').addEventListener('click', function(){
                    var valid = true; var name = document.getElementById('industryName'); if(!name.value.trim()){ name.classList.add('is-invalid'); valid=false; } else name.classList.remove('is-invalid');
                    if(!valid) return; var confirmModal = new bootstrap.Modal(document.getElementById('confirmAddIndustryModal')); confirmModal.show();
                });

                // When user confirms add, perform AJAX upload
                document.getElementById('yesAddIndustryBtn').addEventListener('click', async function(){
                    var confirmModalEl=document.getElementById('confirmAddIndustryModal'); var confirmModal=bootstrap.Modal.getInstance(confirmModalEl); confirmModal.hide();
                    var addModalEl=document.getElementById('addIndustryModal'); var addModal=bootstrap.Modal.getInstance(addModalEl); addModal.hide();

                    // build FormData
                    const fd = new FormData();
                    fd.append('name', document.getElementById('industryName').value.trim());
                    fd.append('description', document.getElementById('industryDesc').value.trim());
                    const f = document.getElementById('industryUploadImage').files[0];
                    if (f) fd.append('image', f);

                    // show loading by disabling buttons
                    document.getElementById('yesAddIndustryBtn').disabled = true;
                    try {
                        const resp = await fetch(apiUpload, { method: 'POST', body: fd });
                        const json = await resp.json();
                        if (json.success) {
                            // insert returned item at top
                            const newItem = { id: json.id, name: json.name, description: json.description, image: json.image, createdAt: json.createdAt };
                            industries.unshift(newItem);
                            // reset form
                            document.getElementById('industryForm').reset();
                            document.getElementById('industryPreviewImg').style.display='none';
                            // show generic success modal for add
                            try {
                                document.getElementById('successIndustryModalTitle').innerText = 'Industry Successfully Added';
                                document.getElementById('successIndustryModalBody').innerHTML = 'Thank you! The new industry has been added to the system.<br>Would you like to add another industry or return?';
                                document.getElementById('addAnotherIndustryBtn').style.display = 'inline-block';
                                var successModal = new bootstrap.Modal(document.getElementById('successIndustryModal'));
                                successModal.show();
                            } catch (e) { console.warn('Could not show success modal', e); }
                            renderIndustries(industries);
                        } else {
                            alert(json.message || 'Upload failed');
                        }
                    } catch (err) {
                        console.error(err); alert('Upload failed');
                    } finally {
                        document.getElementById('yesAddIndustryBtn').disabled = false;
                    }
                });

                document.getElementById('addAnotherIndustryBtn').addEventListener('click', function(){ var successModalEl=document.getElementById('successIndustryModal'); var successModal=bootstrap.Modal.getInstance(successModalEl); successModal.hide(); var addModalEl=document.getElementById('addIndustryModal'); var addModal=new bootstrap.Modal(addModalEl); addModal.show(); });

                // Delete confirm: call api_delete.php
                document.getElementById('confirmDelete').addEventListener('click', async function(){
                    const id = this.getAttribute('data-id');
                    if (!id) return;
                    try {
                        const fd = new FormData(); fd.append('id', id);
                        const r = await fetch('api_delete', { method: 'POST', body: fd });
                        const j = await r.json();
                        if (j.success) {
                            // remove item locally and rerender
                                industries = industries.filter(it => String(it.id) !== String(id));
                                renderIndustries(industries);
                                var delModalEl = document.getElementById('deleteModal'); var delModal = bootstrap.Modal.getInstance(delModalEl); if(delModal) delModal.hide();
                                // show generic success modal for delete
                                try {
                                    document.getElementById('successIndustryModalTitle').innerText = 'Industry Deleted';
                                    document.getElementById('successIndustryModalBody').innerText = 'The industry has been deleted successfully.';
                                    document.getElementById('addAnotherIndustryBtn').style.display = 'none';
                                    var successModal = new bootstrap.Modal(document.getElementById('successIndustryModal'));
                                    successModal.show();
                                } catch (e) { console.warn('Could not show delete success modal', e); }
                            } else {
                                alert(j.message || 'Delete failed');
                            }
                    } catch (e) { console.error(e); alert('Delete failed'); }
                });

                // Update flow: single-select checkbox should enable Update button
                const updateButtons = document.querySelectorAll('.update-btn');
                updateButtons.forEach(b => b.disabled = true);

                // when Update is clicked, populate modal with selected industry data
                document.querySelectorAll('.update-btn').forEach(btn => btn.addEventListener('click', function(){
                    // find selected
                    const sel = Array.from(document.querySelectorAll('input[name="industrySelect"]')).find(c => c.checked);
                    if (!sel) { alert('Please select one industry to update.'); return; }
                    const id = sel.getAttribute('data-id');
                    const item = industries.find(it => String(it.id) === String(id));
                    if (!item) { alert('Selected item not found'); return; }

                    // populate form
                    document.getElementById('industryName').value = item.name || '';
                    document.getElementById('industryDesc').value = item.description || '';
                    document.getElementById('industryPreviewImg').style.display = item.image ? 'block' : 'none';
                    if (item.image) document.getElementById('industryPreviewImg').src = item.image;
                    // clear file input
                    document.getElementById('industryUploadImage').value = '';

                    // show modal and switch buttons
                    var addModalEl = document.getElementById('addIndustryModal');
                    var addModal = new bootstrap.Modal(addModalEl);
                    // show update button, hide add button
                    document.getElementById('updateIndustryBtn').style.display = 'inline-block';
                    document.getElementById('addNewIndustryBtn').style.display = 'none';
                    addModal.show();

                    // attach the id to the update button for later
                    document.getElementById('updateIndustryBtn').setAttribute('data-id', id);
                }));

                // Handle actual update submission
                document.getElementById('updateIndustryBtn').addEventListener('click', async function(){
                    const id = this.getAttribute('data-id');
                    if (!id) { alert('Missing id'); return; }
                    const name = document.getElementById('industryName').value.trim();
                    if (!name) { alert('Name is required'); return; }
                    const desc = document.getElementById('industryDesc').value.trim();
                    const file = document.getElementById('industryUploadImage').files[0];

                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('name', name);
                    fd.append('description', desc);
                    if (file) fd.append('image', file);

                    // disable to prevent double submit
                    this.disabled = true;
                    try {
                        const resp = await fetch('api_update', { method: 'POST', body: fd });
                        const json = await resp.json();
                        if (json.success) {
                            // update local array
                            industries = industries.map(it => {
                                if (String(it.id) === String(id)) {
                                    return Object.assign({}, it, {
                                        name: json.name || it.name,
                                        description: json.description || it.description,
                                        image: json.image || it.image
                                    });
                                }
                                return it;
                            });
                            renderIndustries(industries);
                            // hide modal
                            var addModalEl = document.getElementById('addIndustryModal'); var addModal = bootstrap.Modal.getInstance(addModalEl); if(addModal) addModal.hide();
                            // show generic success modal for update
                            try {
                                document.getElementById('successIndustryModalTitle').innerText = 'Industry Updated';
                                document.getElementById('successIndustryModalBody').innerText = 'The industry has been updated successfully.';
                                document.getElementById('addAnotherIndustryBtn').style.display = 'none';
                                var successModal = new bootstrap.Modal(document.getElementById('successIndustryModal'));
                                successModal.show();
                            } catch (e) { alert('Industry updated'); }
                        } else {
                            alert(json.message || 'Update failed');
                        }
                    } catch (e) { console.error(e); alert('Update failed'); }
                    finally { this.disabled = false; }
                });

                // when modal is hidden, restore add/update button visibility and reset attached id
                document.getElementById('addIndustryModal').addEventListener('hidden.bs.modal', function(){
                    document.getElementById('updateIndustryBtn').style.display = 'none';
                    document.getElementById('addNewIndustryBtn').style.display = 'inline-block';
                    document.getElementById('updateIndustryBtn').removeAttribute('data-id');
                    document.getElementById('industryForm').reset();
                    document.getElementById('industryPreviewImg').style.display='none';
                });

                var logoutBtn = document.getElementById('logoutBtn'); var confirmLogout = document.getElementById('confirmLogout'); var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal')); if(logoutBtn) logoutBtn.addEventListener('click', function(){ logoutModal.show(); }); if(confirmLogout) confirmLogout.addEventListener('click', function(){ window.location.href = 'logout'; });
            });
            </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
