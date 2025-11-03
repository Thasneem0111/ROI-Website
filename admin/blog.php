<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login');
    exit;
}
require_once 'db_connect.php';
// active tab for sidebar
$active = 'blog';
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
                    <h5 class="modal-title w-100 text-center" id="deleteModalLabel" style="font-weight:700;">Delete Blog</h5>
                    <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center" style="font-size:1.15rem; color:#222; padding:24px 12px 12px 12px;">
                    <i class="fa fa-trash" style="font-size:2.2rem; color:#d9534f; margin-bottom:12px;"></i><br>
                    Do you want to delete this blog?
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
                <h1>Blogs</h1>
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

            <!-- Blogs View Panel -->
            <div class="blogs-panel" style="background:#fff; border-radius:16px; margin-top:32px; width:100%; max-width:none; box-shadow:0 2px 12px rgba(44,166,164,0.06); padding:32px 0 0 0; flex:1;">
                <div style="display:flex; gap:32px; padding:0 32px 24px 32px;">
                    <div style="display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px; font-size:1rem; font-weight:500;">
                        Total Blogs <span style="margin-left:16px; font-size:1rem; font-weight:normal;">07</span>
                    </div>
                    <div style="flex:1; display:flex; align-items:center; background:#f7f8fa; border-radius:8px; padding:6px 18px;">
                        <input type="text" id="blogSearch" placeholder="Search" style="border:none; background:transparent; font-size:1rem; width:100%; outline:none;">
                        <button style="background:none; border:none; color:#222; font-size:1.3rem; cursor:pointer;" onclick="filterBlogs()"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div style="padding:0 32px;">
                    <table style="width:100%; border-collapse:collapse;">
                        <tbody id="blogsTableBody">
                        </tbody>
                    </table>
                </div>
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:24px; padding:32px 32px;">
                    <button class="action-btn update-btn">Update Blog</button>
                    <button class="action-btn add-btn" id="addBlogModalBtn" data-bs-toggle="modal" data-bs-target="#addBlogModal">
                        <span class="add-icon-circle"><i class="fa fa-plus"></i></span>
                        Add new Blog
                    </button>
                    <!-- Add New Blog Modal -->
                    <div class="modal fade" id="addBlogModal" tabindex="-1" aria-labelledby="addBlogModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addBlogModalLabel">Total Blogs 7</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="blogEditorForm">
                                        <div class="mb-3">
                                            <label class="form-label">Blog Topic</label>
                                            <input type="text" class="form-control" id="blogTopic" placeholder="Type your service name here....">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Blog Description</label>
                                            <textarea class="form-control" id="blogDesc" rows="3" placeholder="Write About Blog main description"></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Upload Image</label>
                                            <div class="input-group">
                                                <input type="file" class="form-control" id="blogUploadImage" accept="image/*">
                                                <button class="btn btn-outline-secondary" type="button" id="blogUploadBtn"><i class="fa fa-upload"></i></button>
                                            </div>
                                            <img id="blogPreviewImg" src="#" alt="Preview" style="max-width:120px; margin-top:10px; display:none;" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Add</label>
                                            <div id="blogModalBlocks"></div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-success" id="updateBlogModalBtn">Update Blog</button>
                                    <button type="button" class="btn btn-primary" id="addNewBlogModalBtn">Add new Blog</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Modal -->
                    <div class="modal fade" id="successBlogModal" tabindex="-1" aria-labelledby="successBlogModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="successBlogModalLabel">Blog Successfully Added</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <i class="fa fa-check-circle" style="font-size:2.2rem; color:#2ca6a4; margin-bottom:12px;"></i><br>
                                    <span style="font-size:1.15rem; color:#222;">Thank you! The new blog has been added to the system.<br>Would you like to add another blog or return to the dashboard?</span>
                                </div>
                                <div class="modal-footer justify-content-center" style="gap:16px;">
                                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Return to Dashboard</button>
                                    <button type="button" class="btn btn-primary px-4" id="addAnotherBlogBtn">Add Another Blog</button>
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
                #blogsTableBody tr {
                    border-bottom: 1px solid #eee;
                    height: 56px;
                }
                </style>
            </div>

            <script>
            // Blog block types
            const blockTypes = [
                { value: 'h1', label: 'Main Heading (h1)' },
                { value: 'h2', label: 'Sub Heading (h2)' },
                { value: 'h3', label: 'Normal Heading (h3)' },
                { value: 'p', label: 'Paragraph' },
                { value: 'list', label: 'List' }
            ];

            function createModalBlock(type = 'h1', value = '') {
                const block = document.createElement('div');
                block.className = 'blog-block';
                block.style.marginBottom = '24px';
                block.style.display = 'flex';
                block.style.flexDirection = 'column';
                block.style.gap = '8px';
                // Select
                const select = document.createElement('select');
                select.className = 'form-select';
                select.style.maxWidth = '220px';
                select.style.marginBottom = '8px';
                blockTypes.forEach(opt => {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.label;
                    select.appendChild(o);
                });
                select.value = type;
                block.appendChild(select);

                // Content area
                const contentDiv = document.createElement('div');
                contentDiv.className = 'blog-block-content';
                contentDiv.style.display = 'flex';
                contentDiv.style.flexDirection = 'column';
                contentDiv.style.gap = '8px';
                block.appendChild(contentDiv);

                // Button row for actions
                const buttonRow = document.createElement('div');
                buttonRow.style.display = 'flex';
                buttonRow.style.flexDirection = 'row';
                buttonRow.style.gap = '8px';
                buttonRow.style.marginTop = '8px';
                buttonRow.style.justifyContent = 'flex-end';

                // Add more button
                const addMoreBtn = document.createElement('button');
                addMoreBtn.type = 'button';
                addMoreBtn.className = 'btn btn-link';
                addMoreBtn.style.fontWeight = '500';
                addMoreBtn.style.color = '#888';
                addMoreBtn.textContent = type === 'list' ? 'Add More List +' : 'Add More +';
                addMoreBtn.style.marginLeft = '0';
                addMoreBtn.style.alignSelf = 'flex-end';

                // Add another block button (for after list)
                const addAnotherBlockBtn = document.createElement('button');
                addAnotherBlockBtn.type = 'button';
                addAnotherBlockBtn.className = 'btn btn-link';
                addAnotherBlockBtn.style.fontWeight = '500';
                addAnotherBlockBtn.style.color = '#2ca6a4';
                addAnotherBlockBtn.textContent = 'Add Another Option';
                addAnotherBlockBtn.style.alignSelf = 'flex-end';
                addAnotherBlockBtn.style.marginLeft = '0';

                // Remove block button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-link';
                removeBtn.style.color = '#d9534f';
                removeBtn.textContent = 'Remove';
                removeBtn.style.alignSelf = 'flex-end';

                // Block content logic
                function renderContent() {
                    contentDiv.innerHTML = '';
                    buttonRow.innerHTML = '';
                    if (select.value === 'list') {
                        // List block: multiple textareas
                        if (!block.listItems) block.listItems = [''];
                        block.listItems.forEach((item, idx) => {
                            const ta = document.createElement('textarea');
                            ta.className = 'form-control';
                            ta.style.minHeight = '38px';
                            ta.style.marginBottom = '8px';
                            ta.placeholder = 'List item...';
                            ta.value = item;
                            ta.oninput = () => { block.listItems[idx] = ta.value; };
                            contentDiv.appendChild(ta);
                        });
                        // Add more list button
                        addMoreBtn.textContent = 'Add More List +';
                        addMoreBtn.onclick = () => {
                            block.listItems.push('');
                            renderContent();
                        };
                        // Add another block button
                        addAnotherBlockBtn.onclick = () => {
                            addModalBlock();
                        };
                        buttonRow.appendChild(addMoreBtn);
                        buttonRow.appendChild(addAnotherBlockBtn);
                        buttonRow.appendChild(removeBtn);
                    } else {
                        // Heading/paragraph block: single textarea
                        const ta = document.createElement('textarea');
                        ta.className = 'form-control';
                        ta.style.minHeight = '38px';
                        ta.placeholder = select.options[select.selectedIndex].text + '...';
                        ta.value = block.value || '';
                        ta.oninput = () => { block.value = ta.value; };
                        contentDiv.appendChild(ta);
                        addMoreBtn.textContent = 'Add More +';
                        addMoreBtn.onclick = () => {
                            addModalBlock(select.value);
                        };
                        buttonRow.appendChild(addMoreBtn);
                        buttonRow.appendChild(removeBtn);
                    }
                    contentDiv.appendChild(buttonRow);
                }

                select.onchange = () => {
                    if (select.value === 'list') {
                        block.listItems = [''];
                        block.value = undefined;
                    } else {
                        block.value = '';
                        block.listItems = undefined;
                    }
                    renderContent();
                };
                removeBtn.onclick = () => {
                    block.remove();
                };
                renderContent();
                return block;
            }

            function addModalBlock(type = 'h1') {
                const blocksDiv = document.getElementById('blogModalBlocks');
                blocksDiv.appendChild(createModalBlock(type));
            }

            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('addBlogModalBtn').onclick = () => {
                    // Reset modal fields
                    document.getElementById('blogTopic').value = '';
                    document.getElementById('blogDesc').value = '';
                    document.getElementById('blogUploadImage').value = '';
                    document.getElementById('blogPreviewImg').style.display = 'none';
                    const blocksDiv = document.getElementById('blogModalBlocks');
                    blocksDiv.innerHTML = '';
                    addModalBlock();
                };
                document.getElementById('blogUploadBtn').onclick = function() {
                    document.getElementById('blogUploadImage').click();
                };
                document.getElementById('blogUploadImage').addEventListener('change', function(e) {
                    var file = e.target.files[0];
                    var preview = document.getElementById('blogPreviewImg');
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(ev) {
                            preview.src = ev.target.result;
                            preview.style.display = 'block';
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.style.display = 'none';
                    }
                });

                // Add new blog button logic
                document.getElementById('addNewBlogModalBtn').addEventListener('click', function() {
                    // Validate required fields
                    var valid = true;
                    var topic = document.getElementById('blogTopic');
                    var desc = document.getElementById('blogDesc');
                    var imageInput = document.getElementById('blogUploadImage');
                    // Add style for invalid fields if not present
                    if (!document.getElementById('blog-invalid-style')) {
                        var style = document.createElement('style');
                        style.id = 'blog-invalid-style';
                        style.innerHTML = `.is-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 0.2rem rgba(220,53,69,.25) !important; }`;
                        document.head.appendChild(style);
                    }
                    // Validate topic
                    if (!topic.value || topic.value.trim() === '') {
                        topic.classList.add('is-invalid');
                        valid = false;
                    } else {
                        topic.classList.remove('is-invalid');
                    }
                    // Validate description
                    if (!desc.value || desc.value.trim() === '') {
                        desc.classList.add('is-invalid');
                        valid = false;
                    } else {
                        desc.classList.remove('is-invalid');
                    }
                    // Validate image
                    if (!imageInput.files || imageInput.files.length === 0) {
                        imageInput.classList.add('is-invalid');
                        valid = false;
                    } else {
                        imageInput.classList.remove('is-invalid');
                    }
                    // Validate at least one block exists and has content
                    var blocksDiv = document.getElementById('blogModalBlocks');
                    var blocks = blocksDiv.querySelectorAll('.blog-block');
                    if (blocks.length === 0) valid = false;
                    blocks.forEach(function(block) {
                        var textarea = block.querySelector('textarea');
                        if (!textarea || !textarea.value.trim()) {
                            textarea.classList.add('is-invalid');
                            valid = false;
                        } else {
                            textarea.classList.remove('is-invalid');
                        }
                    });
                    if (!valid) {
                        // Scroll to first invalid field
                        var firstInvalid = document.querySelector('.is-invalid');
                        if (firstInvalid) firstInvalid.scrollIntoView({behavior:'smooth', block:'center'});
                        return;
                    }
                    // Build payload: collect blocks into respective columns
                    var blocksDiv = document.getElementById('blogModalBlocks');
                    var blocks = blocksDiv.querySelectorAll('.blog-block');
                    var mainArr = [], subArr = [], normalArr = [], paraArr = [], listArr = [];
                    blocks.forEach(function(block) {
                        var sel = block.querySelector('select');
                        if (!sel) return;
                        var type = sel.value;
                        if (type === 'list') {
                            // collect all textareas inside this block
                            var items = Array.from(block.querySelectorAll('textarea')).map(t => t.value.trim()).filter(Boolean);
                            if (items.length) listArr.push(items.join('\n'));
                        } else {
                            var ta = block.querySelector('textarea');
                            var val = ta ? ta.value.trim() : '';
                            if (!val) return;
                            if (type === 'h1') mainArr.push(val);
                            else if (type === 'h2') subArr.push(val);
                            else if (type === 'h3') normalArr.push(val);
                            else if (type === 'p') paraArr.push(val);
                        }
                    });

                    var fd = new FormData();
                    fd.append('blog_topic', topic.value.trim());
                    fd.append('description', desc.value.trim());
                    if (imageInput.files && imageInput.files[0]) fd.append('image', imageInput.files[0]);
                    if (mainArr.length) fd.append('main_heading', mainArr.join('\n\n'));
                    if (subArr.length) fd.append('sub_heading', subArr.join('\n\n'));
                    if (normalArr.length) fd.append('normal_heading', normalArr.join('\n\n'));
                    if (paraArr.length) fd.append('paragraph', paraArr.join('\n\n'));
                    if (listArr.length) fd.append('list', listArr.join('\n\n'));

                    // send to server
                    document.getElementById('addNewBlogModalBtn').disabled = true;
                    fetch('api_upload_blog', { method: 'POST', body: fd }).then(r => r.json()).then(function(resp){
                        document.getElementById('addNewBlogModalBtn').disabled = false;
                        if (resp && resp.success) {
                            var addBlogModalEl = document.getElementById('addBlogModal');
                            var addBlogModal = bootstrap.Modal.getInstance(addBlogModalEl);
                            if (addBlogModal) addBlogModal.hide();
                            // Clear form and image preview
                            document.getElementById('blogEditorForm').reset();
                            document.getElementById('blogPreviewImg').style.display = 'none';
                            document.querySelectorAll('.is-invalid').forEach(function(f){f.classList.remove('is-invalid');});
                            document.getElementById('blogUploadImage').classList.remove('is-invalid');
                            // Show success modal
                            var successBlogModal = new bootstrap.Modal(document.getElementById('successBlogModal'));
                            successBlogModal.show();
                        } else {
                            alert((resp && resp.message) || 'Failed to add blog');
                        }
                    }).catch(function(err){
                        document.getElementById('addNewBlogModalBtn').disabled = false;
                        console.error(err);
                        alert('Request failed');
                    });
                });

                // Success modal: Add Another button logic
                document.getElementById('addAnotherBlogBtn').addEventListener('click', function() {
                    var successBlogModalEl = document.getElementById('successBlogModal');
                    var successBlogModal = bootstrap.Modal.getInstance(successBlogModalEl);
                    successBlogModal.hide();
                    // Reopen the add blog modal, form is already cleared
                    var addBlogModalEl = document.getElementById('addBlogModal');
                    var addBlogModal = new bootstrap.Modal(addBlogModalEl);
                    addBlogModal.show();
                });
            });
            </script>

            <script>
            // Blog listing, update and delete (mirrors industries behavior)
            let blogs = [];
            const apiListBlogs = 'get_blogs';
            const apiUploadBlog = 'api_upload_blog';
            const apiUpdateBlog = 'api_update_blog';
            const apiDeleteBlog = 'api_delete_blog';

            function escapeHtml(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

            function renderBlogs(list) {
                const tbody = document.getElementById('blogsTableBody');
                tbody.innerHTML = '';
                list.forEach((it,i) => {
                    const dateText = it.createdAt ? new Date(it.createdAt).toLocaleDateString() : '';
                    tbody.innerHTML += `<tr>
                        <td style='padding:12px 0; width:40px;'><input type='checkbox' name='blogSelect' data-id='${it.id}'></td>
                        <td style='font-weight:600;'>${escapeHtml(it.topic||'')}</td>
                        <td style='color:#888; text-align:right;'>${escapeHtml(dateText)}</td>
                        <td style='text-align:right; width:60px;'><i class='fa fa-trash' style='color:#222; cursor:pointer;' data-index='${i}' data-id='${it.id}'></i></td>
                    </tr>`;
                });

                // attach delete handlers
                Array.from(document.querySelectorAll('.fa-trash[data-index]')).forEach(icon => {
                    icon.addEventListener('click', function(){
                        var id = this.getAttribute('data-id');
                        document.getElementById('confirmDelete').setAttribute('data-id', id);
                        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                        deleteModal.show();
                    });
                });

                // attach checkbox behavior: single-select and enable update button
                const checkboxes = Array.from(document.querySelectorAll('input[name="blogSelect"]'));
                checkboxes.forEach(cb => cb.addEventListener('change', function(){
                    if (this.checked) checkboxes.forEach(other => { if (other !== this) other.checked = false; });
                    const selected = checkboxes.filter(c => c.checked);
                    document.querySelectorAll('.update-btn').forEach(b=>b.disabled = (selected.length !== 1));
                }));
            }

            async function loadBlogs(){
                try {
                    const res = await fetch(apiListBlogs);
                    if (!res.ok) throw new Error('Network');
                    blogs = await res.json();
                    renderBlogs(blogs);
                    // disable update buttons initially
                    document.querySelectorAll('.update-btn').forEach(b=>b.disabled = true);
                } catch (e) { console.error('Failed to load blogs', e); }
            }

            document.addEventListener('DOMContentLoaded', function(){
                loadBlogs();

                // hide update button in modal by default
                var updBtnEl = document.getElementById('updateBlogModalBtn'); if (updBtnEl) updBtnEl.style.display = 'none';

                // Delete confirm handler
                document.getElementById('confirmDelete').addEventListener('click', async function(){
                    const id = this.getAttribute('data-id');
                    if (!id) return;
                    try {
                        const fd = new FormData(); fd.append('id', id);
                        const r = await fetch(apiDeleteBlog, { method: 'POST', body: fd });
                        const j = await r.json();
                        if (j.success) {
                            blogs = blogs.filter(it => String(it.id) !== String(id));
                            renderBlogs(blogs);
                            var delModalEl = document.getElementById('deleteModal'); var delModal = bootstrap.Modal.getInstance(delModalEl); if(delModal) delModal.hide();
                            // show success modal
                            try {
                                document.getElementById('successBlogModalLabel').innerText = 'Blog Deleted';
                                var sm = new bootstrap.Modal(document.getElementById('successBlogModal'));
                                // hide add-another in this context
                                document.getElementById('addAnotherBlogBtn').style.display = 'none';
                                sm.show();
                            } catch(e) { console.warn(e); }
                        } else {
                            alert(j.message || 'Delete failed');
                        }
                    } catch (e) { console.error(e); alert('Delete failed'); }
                });

                // Update flow: when Update Blog top button clicked
                document.querySelectorAll('.update-btn').forEach(btn => btn.addEventListener('click', function(){
                    const sel = Array.from(document.querySelectorAll('input[name="blogSelect"]')).find(c => c.checked);
                    if (!sel) { alert('Please select one blog to update.'); return; }
                    const id = sel.getAttribute('data-id');
                    const item = blogs.find(it => String(it.id) === String(id));
                    if (!item) { alert('Selected item not found'); return; }

                    // populate form
                    document.getElementById('blogTopic').value = item.topic || '';
                    document.getElementById('blogDesc').value = item.description || '';
                    document.getElementById('blogPreviewImg').style.display = item.image ? 'block' : 'none';
                    if (item.image) document.getElementById('blogPreviewImg').src = item.image;
                    document.getElementById('blogUploadImage').value = '';

                    // populate blocks
                    const blocksDiv = document.getElementById('blogModalBlocks'); blocksDiv.innerHTML = '';
                    if (item.main_heading) {
                        item.main_heading.split('\n\n').forEach(v => { const b = createModalBlock('h1'); blocksDiv.appendChild(b); b.querySelector('textarea').value = v; });
                    }
                    if (item.sub_heading) {
                        item.sub_heading.split('\n\n').forEach(v => { const b = createModalBlock('h2'); blocksDiv.appendChild(b); b.querySelector('textarea').value = v; });
                    }
                    if (item.normal_heading) {
                        item.normal_heading.split('\n\n').forEach(v => { const b = createModalBlock('h3'); blocksDiv.appendChild(b); b.querySelector('textarea').value = v; });
                    }
                    if (item.paragraph) {
                        item.paragraph.split('\n\n').forEach(v => { const b = createModalBlock('p'); blocksDiv.appendChild(b); b.querySelector('textarea').value = v; });
                    }
                    if (item.list) {
                        // item.list may contain multiple list-blocks separated by double newlines
                        item.list.split('\n\n').forEach(v => { const b = createModalBlock('list'); blocksDiv.appendChild(b); const ta = b.querySelector('textarea'); if (ta) ta.value = v; });
                    }
                    if (blocksDiv.children.length === 0) addModalBlock();

                    // show modal and switch buttons
                    var addModalEl = document.getElementById('addBlogModal'); var addModal = new bootstrap.Modal(addModalEl);
                    document.getElementById('updateBlogModalBtn').style.display = 'inline-block';
                    document.getElementById('addNewBlogModalBtn').style.display = 'none';
                    addModal.show();
                    document.getElementById('updateBlogModalBtn').setAttribute('data-id', id);
                }));

                // handle update submit
                document.getElementById('updateBlogModalBtn').addEventListener('click', async function(){
                    const id = this.getAttribute('data-id'); if (!id) { alert('Missing id'); return; }
                    // collect same as add
                    var topic = document.getElementById('blogTopic');
                    var desc = document.getElementById('blogDesc');
                    var imageInput = document.getElementById('blogUploadImage');
                    // minimal validation
                    if (!topic.value.trim()) { alert('Topic required'); return; }

                    var blocksDiv = document.getElementById('blogModalBlocks');
                    var blocks = blocksDiv.querySelectorAll('.blog-block');
                    var mainArr = [], subArr = [], normalArr = [], paraArr = [], listArr = [];
                    blocks.forEach(function(block) {
                        var sel = block.querySelector('select'); if (!sel) return; var type = sel.value;
                        if (type === 'list') { var items = Array.from(block.querySelectorAll('textarea')).map(t => t.value.trim()).filter(Boolean); if (items.length) listArr.push(items.join('\n'));
                        } else { var ta = block.querySelector('textarea'); var val = ta ? ta.value.trim() : ''; if (!val) return; if (type==='h1') mainArr.push(val); else if (type==='h2') subArr.push(val); else if (type==='h3') normalArr.push(val); else if (type==='p') paraArr.push(val); }
                    });

                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('blog_topic', topic.value.trim());
                    fd.append('description', desc.value.trim());
                    if (imageInput.files && imageInput.files[0]) fd.append('image', imageInput.files[0]);
                    if (mainArr.length) fd.append('main_heading', mainArr.join('\n\n'));
                    if (subArr.length) fd.append('sub_heading', subArr.join('\n\n'));
                    if (normalArr.length) fd.append('normal_heading', normalArr.join('\n\n'));
                    if (paraArr.length) fd.append('paragraph', paraArr.join('\n\n'));
                    if (listArr.length) fd.append('list', listArr.join('\n\n'));

                    this.disabled = true;
                    try {
                        const resp = await fetch(apiUpdateBlog, { method: 'POST', body: fd });
                        const json = await resp.json();
                        if (json.success) {
                            // update local array
                            blogs = blogs.map(it => {
                                if (String(it.id) === String(id)) {
                                    return Object.assign({}, it, {
                                        topic: json.blog_topic || topic.value.trim() || it.topic,
                                        description: json.description || desc.value.trim() || it.description,
                                        image: json.image || it.image
                                    });
                                }
                                return it;
                            });
                            renderBlogs(blogs);
                            var addModalEl = document.getElementById('addBlogModal'); var addModal = bootstrap.Modal.getInstance(addModalEl); if(addModal) addModal.hide();
                            try {
                                document.getElementById('successBlogModalLabel').innerText = 'Blog Updated';
                                document.getElementById('addAnotherBlogBtn').style.display = 'none';
                                var successModal = new bootstrap.Modal(document.getElementById('successBlogModal'));
                                successModal.show();
                            } catch(e) { alert('Blog updated'); }
                        } else {
                            alert(json.message || 'Update failed');
                        }
                    } catch (e) { console.error(e); alert('Update failed'); }
                    finally { this.disabled = false; }
                });

                // when modal is hidden, restore add/update button visibility and reset
                document.getElementById('addBlogModal').addEventListener('hidden.bs.modal', function(){
                    document.getElementById('updateBlogModalBtn').style.display = 'none';
                    document.getElementById('addNewBlogModalBtn').style.display = 'inline-block';
                    document.getElementById('updateBlogModalBtn').removeAttribute('data-id');
                    document.getElementById('blogEditorForm').reset();
                    document.getElementById('blogPreviewImg').style.display='none';
                    document.getElementById('blogModalBlocks').innerHTML='';
                });
            });
            </script>

            <script>
            // Blog block types
            <!-- const blockTypes = [
                { value: 'h1', label: 'Main Heading (h1)' },
                { value: 'h2', label: 'Sub Heading (h2)' },
                { value: 'h3', label: 'Normal Heading (h3)' },
                { value: 'p', label: 'Paragraph' },
                { value: 'list', label: 'List' }
            ]; -->

            function createBlock(type = 'h1', value = '') {
                const block = document.createElement('div');
                block.className = 'blog-block';
                block.style.marginBottom = '24px';
                // Select
                const select = document.createElement('select');
                select.className = 'form-select';
                select.style.maxWidth = '220px';
                blockTypes.forEach(opt => {
                    const o = document.createElement('option');
                    o.value = opt.value;
                    o.textContent = opt.label;
                    select.appendChild(o);
                });
                select.value = type;
                block.appendChild(select);

                // Content area
                const contentDiv = document.createElement('div');
                contentDiv.className = 'blog-block-content';
                contentDiv.style.display = 'flex';
                contentDiv.style.alignItems = 'center';
                contentDiv.style.gap = '16px';
                block.appendChild(contentDiv);

                // Add more button
                const addMoreBtn = document.createElement('button');
                addMoreBtn.type = 'button';
                addMoreBtn.className = 'btn btn-link';
                addMoreBtn.style.fontWeight = '600';
                addMoreBtn.style.color = '#888';
                addMoreBtn.textContent = type === 'list' ? 'Add More List +' : 'Add More +';
                addMoreBtn.style.marginLeft = '8px';

                // Remove block button
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn btn-link';
                removeBtn.style.color = '#d9534f';
                removeBtn.textContent = 'Remove';
                removeBtn.style.marginLeft = '8px';

                // Block content logic
                function renderContent() {
                    contentDiv.innerHTML = '';
                    if (select.value === 'list') {
                        // List block: multiple textareas
                        if (!block.listItems) block.listItems = [''];
                        block.listItems.forEach((item, idx) => {
                            const ta = document.createElement('textarea');
                            ta.className = 'form-control';
                            ta.style.minHeight = '38px';
                            ta.style.marginBottom = '8px';
                            ta.placeholder = 'List item...';
                            ta.value = item;
                            ta.oninput = () => { block.listItems[idx] = ta.value; };
                            contentDiv.appendChild(ta);
                        });
                        // Add more list button
                        addMoreBtn.textContent = 'Add More List +';
                        addMoreBtn.onclick = () => {
                            block.listItems.push('');
                            renderContent();
                        };
                        contentDiv.appendChild(addMoreBtn);
                    } else {
                        // Heading/paragraph block: single textarea
                        const ta = document.createElement('textarea');
                        ta.className = 'form-control';
                        ta.style.minHeight = '38px';
                        ta.placeholder = select.options[select.selectedIndex].text + '...';
                        ta.value = block.value || '';
                        ta.oninput = () => { block.value = ta.value; };
                        contentDiv.appendChild(ta);
                        addMoreBtn.textContent = 'Add More +';
                        addMoreBtn.onclick = () => {
                            addBlogBlock(select.value);
                        };
                        contentDiv.appendChild(addMoreBtn);
                    }
                    contentDiv.appendChild(removeBtn);
                }

                select.onchange = () => {
                    if (select.value === 'list') {
                        block.listItems = [''];
                        block.value = undefined;
                    } else {
                        block.value = '';
                        block.listItems = undefined;
                    }
                    renderContent();
                };
                removeBtn.onclick = () => {
                    block.remove();
                };
                renderContent();
                return block;
            }

            function addBlogBlock(type = 'h1') {
                const blocksDiv = document.getElementById('blogBlocks');
                blocksDiv.appendChild(createBlock(type));
            }

            document.addEventListener('DOMContentLoaded', function() {
                addBlogBlock(); // Add initial block
                document.getElementById('addBlogBlockBtn').onclick = () => addBlogBlock();
            });
            </script>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logout modal logic
            var logoutBtn = document.getElementById('logoutBtn');
            var logoutModalEl = document.getElementById('logoutModal');
            var confirmLogout = document.getElementById('confirmLogout');
            var logoutModal = new bootstrap.Modal(logoutModalEl);
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function() {
                    logoutModal.show();
                });
            }
            if (confirmLogout) {
                confirmLogout.addEventListener('click', function() {
                    window.location.href = 'logout';
                });
            }
        });
    </script>
</body>
</html>
