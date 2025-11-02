<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'clients';
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
                    <h5 class="modal-title w-100 text-center" id="deleteModalLabel" style="font-weight:700;">Delete Client</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-trash" style="font-size:2.2rem; color:#d9534f; margin-bottom:12px;"></i><br>
                    Do you want to delete this client?
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
                <h1>Clients</h1>
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

            <!-- Clients View Panel -->
            <div class="clients-panel" style="background:#fff; border-radius:16px; margin-top:32px; width:100%; max-width:none; box-shadow:0 2px 12px rgba(44,166,164,0.06); padding:32px 0 0 0; flex:1;">
                <div style="display:flex; gap:32px; padding:0 32px 24px 32px;">
                    <div style="display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px; font-size:1rem; font-weight:500;">
                        Total Clients <span style="margin-left:16px; font-size:1rem; font-weight:normal;">17</span>
                    </div>
                    <div style="flex:1; display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px;">
                        <input type="text" id="clientSearch" placeholder="Search" style="border:none; background:transparent; font-size:1rem; width:100%; outline:none;">
                        <button style="background:none; border:none; color:#222; font-size:1.3rem; cursor:pointer;" onclick="filterClients()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div style="padding:0 32px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody id="clientsTableBody">
                        </tbody>
                    </table>
                </div>
<script>
// Dynamic clients admin script: fetch, render, add, update, delete
document.addEventListener('DOMContentLoaded', function() {
    const apiList = './get_clients.php';
    const apiUpload = './api_upload_client.php';
    const apiUpdate = './api_update_client.php';
    const apiDelete = './api_delete_client.php';

    let clients = [];
    let deleteId = null;

    const tbody = document.getElementById('clientsTableBody');
    const searchInput = document.getElementById('clientSearch');
    const addClientModalEl = document.getElementById('addClientModal');
    const addClientModal = new bootstrap.Modal(addClientModalEl);
    const confirmAddModal = new bootstrap.Modal(document.getElementById('confirmAddModal'));
    const successModalEl = document.getElementById('successModal');
    const successModal = new bootstrap.Modal(successModalEl);
    const deleteModalEl = document.getElementById('deleteModal');
    const deleteModal = new bootstrap.Modal(deleteModalEl);

    const addNewClientBtn = document.getElementById('addNewClientBtn');
    const yesAddBtn = document.getElementById('yesAddBtn');
    const noAddBtn = document.getElementById('noAddBtn');
    const addAnotherBtn = document.getElementById('addAnotherClientBtn');
    const updateClientBtn = document.getElementById('updateClientBtn');
    const confirmDeleteBtn = document.getElementById('confirmDelete');

    const companyName = document.getElementById('companyName');
    const companyDesc = document.getElementById('companyDesc');
    const uploadImage = document.getElementById('uploadImage');
    const previewImg = document.getElementById('previewImg');
    const categoryWork = document.getElementById('categoryWork');
    const categorySelectEl = document.getElementById('categorySelect');
    const newClientsCountEl = document.getElementById('newClientsCount');
    const conversationRateEl = document.getElementById('conversationRate');
    const seoRankingEl = document.getElementById('seoRanking');

    function normalize(item) {
        return {
            id: item.id || item.ID || 0,
            title: item.title || item.name || '',
            description: item.description || item.desc || '',
            image: item.image || item.img || null,
            createdAt: item.created_at || item.createdAt || item.created || '',
            category: item.category || null,
            work_category: item.work_category || null,
            new_clients: (typeof item.new_clients !== 'undefined') ? item.new_clients : (item.newClients || 0),
            conversation_rate: (typeof item.conversation_rate !== 'undefined') ? item.conversation_rate : (item.conversationRate || 0),
            seo_rank: (typeof item.seo_rank !== 'undefined') ? item.seo_rank : (item.seoRank || 0)
        };
    }

    function fetchClients() {
        fetch(apiList)
            .then(r => r.json())
            .then(data => {
                const list = Array.isArray(data) ? data : (data.items || data.data || []);
                clients = list.map(normalize);
                renderClients(clients);
            })
            .catch(err => { console.error('Failed to load clients', err); renderClients([]); });
    }

    function renderClients(list) {
        tbody.innerHTML = '';
        list.forEach((c) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style='padding:12px 0;'><input type='checkbox' data-id='${c.id}'></td>
                <td style='font-weight:normal;'>${escapeHtml(c.title)}</td>
                <td style='color:#888; text-align:right;'>${escapeHtml(formatDate(c.createdAt))}</td>
                <td style='text-align:right;'><i class='fa fa-trash' style='color:#222; cursor:pointer;' data-id='${c.id}'></i></td>
            `;
            tbody.appendChild(tr);
        });

        Array.from(tbody.querySelectorAll('input[type="checkbox"][data-id]')).forEach(cb => {
            cb.addEventListener('change', function() {
                if (this.checked) {
                    Array.from(tbody.querySelectorAll('input[type="checkbox"][data-id]')).forEach(other => { if (other !== this) other.checked = false; });
                }
                updateUpdateButtonState();
            });
        });

        Array.from(tbody.querySelectorAll('.fa-trash[data-id]')).forEach(icon => {
            icon.addEventListener('click', function() {
                deleteId = parseInt(this.getAttribute('data-id'));
                deleteModal.show();
            });
        });
    }

    // main update button control
    const mainUpdateBtn = document.querySelector('.action-btn.update-btn');
    if (mainUpdateBtn) mainUpdateBtn.disabled = true;
    function updateUpdateButtonState() {
        const checked = tbody.querySelectorAll('input[type="checkbox"]:checked');
        if (mainUpdateBtn) mainUpdateBtn.disabled = (checked.length !== 1);
    }

    function getSelectedClient() {
        const cb = tbody.querySelector('input[type="checkbox"]:checked');
        if (!cb) return null;
        const id = parseInt(cb.getAttribute('data-id'));
        return clients.find(x => parseInt(x.id) === id) || null;
    }

    function escapeHtml(s) { return (s||'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function formatDate(d) { if (!d) return ''; try { const dt = new Date(d); if (isNaN(dt)) return d; return dt.toLocaleDateString(); } catch(e){return d;} }

    // initial load
    fetchClients();

    // search
    searchInput.addEventListener('input', function() { const q = this.value.trim().toLowerCase(); renderClients(clients.filter(c => (c.title||'').toLowerCase().includes(q))); });

    // Prepare modal modes. If updateClientBtn has a dataset.id we are opening for update, so don't reset.
    addClientModalEl.addEventListener('show.bs.modal', function (e) {
        try {
            var isUpdate = updateClientBtn && updateClientBtn.dataset && updateClientBtn.dataset.id;
            if (isUpdate) {
                // opening for edit: keep pre-filled values
                return;
            }
        } catch (err) { /* ignore and treat as add */ }
        // default: add mode - reset form
        addNewClientBtn.style.display = '';
        if (updateClientBtn) updateClientBtn.style.display = 'none';
        var formEl = document.getElementById('clientForm'); if (formEl) formEl.reset();
        if (previewImg) previewImg.style.display = 'none';
        if (uploadImage) uploadImage.value = '';
    });

    // Update opens modal and pre-fill (robust: create/select category option if missing)
    if (mainUpdateBtn) {
        mainUpdateBtn.addEventListener('click', function() {
            const sel = getSelectedClient(); if (!sel) return alert('Please select a single client to update.');
            companyName.value = sel.title || '';
            companyDesc.value = sel.description || '';
            // ensure category option exists and select it
            try {
                if (categorySelectEl) {
                    const catVal = sel.category || '';
                    let opt = Array.from(categorySelectEl.options).find(o => o.value === catVal);
                    if (!opt && catVal) {
                        opt = document.createElement('option'); opt.value = catVal; opt.textContent = catVal; categorySelectEl.appendChild(opt);
                    }
                    if (opt) categorySelectEl.value = opt.value; else categorySelectEl.value = '';
                }
            } catch (e) { console.warn('Category select populate failed', e); }
            categoryWork.value = sel.work_category || '';
            if (newClientsCountEl) newClientsCountEl.value = (typeof sel.new_clients !== 'undefined') ? sel.new_clients : 0;
            if (conversationRateEl) conversationRateEl.value = (typeof sel.conversation_rate !== 'undefined') ? sel.conversation_rate : '';
            if (seoRankingEl) seoRankingEl.value = (typeof sel.seo_rank !== 'undefined') ? sel.seo_rank : 0;
            // preview image: robust normalization
            if (sel.image) {
                const imgRaw = String(sel.image || '');
                let imgSrc = '';
                // if it's just a filename without any slash, assume it's in /images/
                if (imgRaw.indexOf('/') === -1) {
                    imgSrc = '/images/' + imgRaw;
                } else {
                    // if it already contains /images/ or is an absolute path or URL, resolve it
                    try {
                        if (/^https?:\/\//i.test(imgRaw)) {
                            imgSrc = imgRaw;
                        } else if (imgRaw.startsWith('/')) {
                            imgSrc = imgRaw;
                        } else {
                            // resolve relative paths against site origin
                            imgSrc = new URL(imgRaw, window.location.origin).href;
                        }
                    } catch (e) {
                        // fallback: prefix images
                        imgSrc = '/images/' + imgRaw.replace(/^\/+/, '');
                    }
                }
                previewImg.src = imgSrc;
                previewImg.style.display = 'block';
            } else {
                previewImg.style.display = 'none';
            }
            // clear file input so replacement is optional
            if (uploadImage) uploadImage.value = '';

            addNewClientBtn.style.display = 'none'; updateClientBtn.style.display = ''; updateClientBtn.dataset.id = sel.id; addClientModal.show();
        });
    }

    // Reset modal state on close (matches industries UX)
    addClientModalEl.addEventListener('hidden.bs.modal', function() {
        if (updateClientBtn) { updateClientBtn.style.display = 'none'; updateClientBtn.removeAttribute('data-id'); }
        if (addNewClientBtn) addNewClientBtn.style.display = '';
        var form = document.getElementById('clientForm'); if (form) form.reset();
        if (previewImg) previewImg.style.display = 'none';
        if (uploadImage) uploadImage.value = '';
        Array.from(document.querySelectorAll('.is-invalid')).forEach(el => el.classList.remove('is-invalid'));
        updateUpdateButtonState();
    });

    // Add new client -> show confirm
    addNewClientBtn.addEventListener('click', function() {
        var valid = true; var requiredFields = [companyName, companyDesc, categoryWork, categorySelectEl];
        requiredFields.forEach(function(field) { if (!field || !field.value || field.value.toString().trim() === '') { if (field) field.classList.add('is-invalid'); valid = false; } else { if (field) field.classList.remove('is-invalid'); } });
        if (!uploadImage.files || uploadImage.files.length === 0) { uploadImage.classList.add('is-invalid'); valid = false; } else { uploadImage.classList.remove('is-invalid'); }
        if (!valid) { var firstInvalid = document.querySelector('.is-invalid'); if (firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth', block:'center'}); return; }
        confirmAddModal.show();
    });

    // Confirm Add -> POST
    yesAddBtn.addEventListener('click', function() {
        confirmAddModal.hide(); const fd = new FormData(); fd.append('name', companyName.value.trim()); fd.append('description', companyDesc.value.trim()); fd.append('category', (categorySelectEl && categorySelectEl.value) ? categorySelectEl.value : ''); fd.append('work_category', categoryWork.value.trim()); fd.append('new_clients', newClientsCountEl ? newClientsCountEl.value : 0); fd.append('conversation_rate', conversationRateEl ? conversationRateEl.value : 0); fd.append('seo_rank', seoRankingEl ? seoRankingEl.value : 0); if (uploadImage.files && uploadImage.files[0]) fd.append('image', uploadImage.files[0]);
        fetch(apiUpload, { method: 'POST', body: fd }).then(r=>r.json()).then(resp=>{ if (resp && resp.success) { addClientModal.hide(); showSuccess('Client Successfully Added','The new client has been added to the system.', true); fetchClients(); } else { alert((resp && resp.message) || 'Failed to add client'); } }).catch(err=>{ console.error(err); alert('Request failed'); });
    });

    noAddBtn.addEventListener('click', function() { confirmAddModal.hide(); });

    // Update submission
    updateClientBtn.addEventListener('click', function() {
        const id = this.dataset.id; if (!id) return alert('Missing client id'); if (!companyName.value.trim() || !companyDesc.value.trim()) { alert('Please fill required fields'); return; }
        const fd = new FormData(); fd.append('id', id); fd.append('name', companyName.value.trim()); fd.append('description', companyDesc.value.trim()); fd.append('category', (categorySelectEl && categorySelectEl.value) ? categorySelectEl.value : ''); fd.append('work_category', categoryWork.value.trim()); fd.append('new_clients', newClientsCountEl ? newClientsCountEl.value : 0); fd.append('conversation_rate', conversationRateEl ? conversationRateEl.value : 0); fd.append('seo_rank', seoRankingEl ? seoRankingEl.value : 0); if (uploadImage.files && uploadImage.files[0]) fd.append('image', uploadImage.files[0]);
        fetch(apiUpdate, { method: 'POST', body: fd }).then(r=>r.json()).then(resp=>{ if (resp && resp.success) { addClientModal.hide(); showSuccess('Client Updated','The client details have been updated.', false); fetchClients(); } else { alert((resp && resp.message) || 'Failed to update client'); } }).catch(err=>{ console.error(err); alert('Request failed'); });
    });

    // Delete confirmed
    confirmDeleteBtn.addEventListener('click', function() {
        if (!deleteId) return; fetch(apiDelete, { method: 'POST', body: JSON.stringify({ id: deleteId }), headers: { 'Content-Type': 'application/json' } }).then(r=>r.json()).then(resp=>{ if (resp && resp.success) { deleteModal.hide(); showSuccess('Client Deleted','The client has been removed from the system.', false); fetchClients(); } else { alert((resp && resp.message) || 'Failed to delete'); } }).catch(err=>{ console.error(err); alert('Request failed'); });
    });

    addAnotherBtn.addEventListener('click', function() { successModal.hide(); addClientModal.show(); });

    function showSuccess(title, message, showAddAnother) { const titleEl = document.getElementById('successModalLabel'); const bodySpan = document.querySelector('#successModal .modal-body span'); titleEl.textContent = title; bodySpan.innerHTML = message; document.getElementById('addAnotherClientBtn').style.display = showAddAnother ? '' : 'none'; successModal.show(); }
});
</script>
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:24px; padding:32px 32px;">
                       <button class="action-btn update-btn">
                           Update Client
                       </button>
                       <button class="action-btn add-btn" id="addClientBtn" data-bs-toggle="modal" data-bs-target="#addClientModal">
                           <span class="add-icon-circle"><i class="fa fa-user-plus"></i></span>
                           Add New Client
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
                #clientsTableBody tr {
                    border-bottom: 1px solid #eee;
                    height: 56px;
                }
                </style>
           <!-- Add New Client Modal -->
           <div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-lg modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title" id="addClientModalLabel">New Clients</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body">
                           <form id="clientForm">
                               <div class="row mb-3">
                                   <div class="col-md-4">
                                       <label class="form-label">Total Clients</label>
                                       <input type="text" class="form-control" value="17" readonly>
                                   </div>
                                   <div class="col-md-8">
                                       <label class="form-label">Category <span class="text-danger">*</span></label>
                                       <select class="form-select" id="categorySelect" required>
                                           <option value="">Select Category</option>
                                       </select>
                                   </div>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" id="companyName" placeholder="Type your service name here...." required>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Category of Work <span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" id="categoryWork" placeholder="Type your category of work here...." required>
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Upload Image <span class="text-danger">*</span></label>
                                   <div class="input-group">
                                       <input type="file" class="form-control" id="uploadImage" accept="image/*">
                                       <button class="btn btn-outline-secondary" type="button" id="uploadBtn"><i class="fa fa-upload"></i></button>
                                   </div>
                                   <img id="previewImg" src="#" alt="Preview" style="max-width:120px; margin-top:10px; display:none;" />
                               </div>
                               <div class="mb-3">
                                   <label class="form-label">Company Description <span class="text-danger">*</span></label>
                                   <textarea class="form-control" id="companyDesc" rows="3" placeholder="Write About Company's Service...." required></textarea>
                               </div>
                               <div class="row mb-3">
                                   <div class="col-md-4">
                                       <label class="form-label">New Clients</label>
                                       <div class="input-group">
                                           <button class="btn btn-outline-secondary" type="button" id="decreaseClients">-</button>
                                           <input type="number" class="form-control" id="newClientsCount" value="0" min="0">
                                           <button class="btn btn-outline-secondary" type="button" id="increaseClients">+</button>
                                       </div>
                                   </div>
                                   <div class="col-md-4">
                                       <label class="form-label">Conversation Rate</label>
                                       <input type="text" class="form-control" id="conversationRate" placeholder="Type the rate">
                                   </div>
                                   <div class="col-md-4">
                                       <label class="form-label">SEO Ranking</label>
                                       <div class="input-group">
                                           <button class="btn btn-outline-secondary" type="button" id="decreaseSEO">-</button>
                                           <input type="number" class="form-control" id="seoRanking" value="0" min="0">
                                           <button class="btn btn-outline-secondary" type="button" id="increaseSEO">+</button>
                                       </div>
                                   </div>
                               </div>
                           </form>
                       </div>
                       <div class="modal-footer">
                           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                           <button type="button" class="btn btn-success" id="updateClientBtn">Update Client</button>
                           <button type="button" class="btn btn-primary" id="addNewClientBtn">Add new Client</button>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Confirmation Modal -->
           <div class="modal fade" id="confirmAddModal" tabindex="-1" aria-labelledby="confirmAddModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title w-100 text-center" id="confirmAddModalLabel">Confirm Add Client</h5>
                           <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body text-center" style="border-bottom:1px solid #eee;">
                           <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                           <span style="font-size:1.15rem; color:#222;">Please confirm:<br>Do you want to add this client to the system?</span>
                       </div>
                       <div class="modal-footer justify-content-center" style="gap:16px;">
                           <button type="button" class="btn btn-secondary px-4" id="noAddBtn">No</button>
                           <button type="button" class="btn btn-success px-4" id="yesAddBtn">Yes</button>
                       </div>
                   </div>
               </div>
           </div>

           <!-- Success Modal -->
           <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
               <div class="modal-dialog modal-dialog-centered">
                   <div class="modal-content">
                       <div class="modal-header">
                           <h5 class="modal-title" id="successModalLabel">Client Successfully Added</h5>
                           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                       </div>
                       <div class="modal-body text-center">
                           <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                           <span style="font-size:1.15rem; color:#222;">Thank you! The new client has been added to the system.<br>Would you like to add another client or return to the dashboard?</span>
                       </div>
                       <div class="modal-footer justify-content-center" style="gap:16px;">
                           <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Return to Dashboard</button>
                           <button type="button" class="btn btn-primary px-4" id="addAnotherClientBtn">Add Another Client</button>
                       </div>
                   </div>
               </div>
           </div>
       </main>
       </div>
    </body>
       <!-- Chart.js for graph -->
       <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
       <!-- Bootstrap JS (must be last) -->
       <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
       <script>
       document.addEventListener('DOMContentLoaded', function() {
           // Logout modal logic
           var logoutBtn = document.getElementById('logoutBtn');
           var logoutModalEl = document.getElementById('logoutModal');
           var confirmLogout = document.getElementById('confirmLogout');
           var logoutModal = new bootstrap.Modal(logoutModalEl);
           if (logoutBtn) logoutBtn.addEventListener('click', function() { logoutModal.show(); });
           if (confirmLogout) confirmLogout.addEventListener('click', function() { window.location.href = 'logout.php'; });

           // Service categories
           var categories = [
               'API Development',
               'Conversation Rate',
               'Video Production',
               'Website Call Tracking',
               'Advertise Management: Listing Management Service',
               'Advertise Management: SM Advertising',
               'Advertise Management: SM Management',
               'Development Design: Landing Page Design',
               'Development Design: Social Media Design',
               'Development Design: Website Design & Development',
               'Digital Marketing: Email Marketing',
               'Digital Marketing: PPC Services',
               'Digital Marketing: SEO Services',
               'Google My Business: GMB Management',
               'Google My Business: GMB Optimization Services',
               'Google My Business: GMB Reinstatement Service',
               'Google My Business: GMB Services',
               'Google My Business: GMB Setup Service',
               'Google My Business: GMB Support Service'
           ];
           var categorySelect = document.getElementById('categorySelect');
           categories.forEach(function(cat) {
               var opt = document.createElement('option');
               opt.value = cat;
               opt.textContent = cat;
               categorySelect.appendChild(opt);
           });

           // Upload image logic
           var uploadBtn = document.getElementById('uploadBtn');
           if (uploadBtn) uploadBtn.addEventListener('click', function() { document.getElementById('uploadImage').click(); });
           var uploadImage = document.getElementById('uploadImage');
           if (uploadImage) uploadImage.addEventListener('change', function(e) {
               var file = e.target.files[0];
               var preview = document.getElementById('previewImg');
               if (file) {
                   var reader = new FileReader();
                   reader.onload = function(ev) { preview.src = ev.target.result; preview.style.display = 'block'; };
                   reader.readAsDataURL(file);
               } else { if (preview) preview.style.display = 'none'; }
           });

           // Plus/minus logic for New Clients
           var incClients = document.getElementById('increaseClients');
           var decClients = document.getElementById('decreaseClients');
           if (incClients) incClients.addEventListener('click', function() { var input = document.getElementById('newClientsCount'); input.value = parseInt(input.value) + 1; });
           if (decClients) decClients.addEventListener('click', function() { var input = document.getElementById('newClientsCount'); if (parseInt(input.value) > 0) input.value = parseInt(input.value) - 1; });
           // Plus/minus logic for SEO Ranking
           var incSEO = document.getElementById('increaseSEO');
           var decSEO = document.getElementById('decreaseSEO');
           if (incSEO) incSEO.addEventListener('click', function() { var input = document.getElementById('seoRanking'); input.value = parseInt(input.value) + 1; });
           if (decSEO) decSEO.addEventListener('click', function() { var input = document.getElementById('seoRanking'); if (parseInt(input.value) > 0) input.value = parseInt(input.value) - 1; });

           // Add/update/delete handlers are implemented in the main dynamic script above.
           // Add style for invalid fields
           if (!document.getElementById('client-invalid-style')) {
               var style = document.createElement('style');
               style.id = 'client-invalid-style';
               style.innerHTML = `.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25) !important; }`;
               document.head.appendChild(style);
           }

           var noAddBtn = document.getElementById('noAddBtn');
           if (noAddBtn) noAddBtn.addEventListener('click', function() {
               var confirmModalEl = document.getElementById('confirmAddModal');
               var confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
               confirmModal.hide();
               if (confirm('Do you want to close the form and clear all provided details?')) {
                   var form = document.getElementById('clientForm'); if (form) form.reset();
                   var preview = document.getElementById('previewImg'); if (preview) preview.style.display = 'none';
                   var addClientModalEl = document.getElementById('addClientModal');
                   var addClientModal = bootstrap.Modal.getInstance(addClientModalEl);
                   if (addClientModal) addClientModal.hide();
               }
           });
       });
       </script>
</html>
