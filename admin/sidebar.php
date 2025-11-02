<?php
// Renders the admin sidebar. Expect a variable $active set to one of:
// 'dashboard','email','blog','clients','services','industries','setting'
if (!isset($active)) $active = '';
function _is_active($key, $active) { return $key === $active ? 'active' : ''; }
?>
<aside class="sidebar">
    <div class="sidebar-content">
        <div class="sidebar-header">
            <img src="../images/logo.png" alt="Company Logo" class="company-logo">
            <h2>ROYAL ORBIT<br>INNOVATIONS</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li class="<?php echo _is_active('dashboard',$active); ?>"><a href="dashboard.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-th-large"></i> Dashboard</a></li>
                <li class="<?php echo _is_active('email',$active); ?>"><a href="email.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-envelope"></i> Emails</a></li>
                <li class="<?php echo _is_active('blog',$active); ?>"><a href="blog.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-blog"></i> Blogs</a></li>
                <li class="<?php echo _is_active('clients',$active); ?>"><a href="clients.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-users"></i> Clients</a></li>
                <li class="<?php echo _is_active('services',$active); ?>"><a href="services.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-cogs"></i> Services</a></li>
                <li class="<?php echo _is_active('industries',$active); ?>"><a href="industries.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-industry"></i> Industries</a></li>
            </ul>
        </nav>
    </div>
    <div class="sidebar-bottom">
        <ul>
            <li class="<?php echo _is_active('setting',$active); ?>"><a href="setting.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;"><i class="fa fa-user-cog"></i> Setting</a></li>
            <li id="logoutBtn" style="cursor:pointer;"><i class="fa fa-sign-out-alt"></i> <a href="logout.php" style="color:inherit;text-decoration:none;">Logout</a></li>
        </ul>
    </div>
</aside>
