<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-dark.png')); ?>" alt="" height="50">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/logo-light.png')); ?>" alt="" height="50">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <li class="nav-item">
                    <a href="index" class="nav-link">
                        <i class="ri-dashboard-2-line"></i> <span><?php echo app('translator')->get('translation.dashboards'); ?></span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarApps" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarApps">
                        <i class="ri-apps-2-line"></i> <span><?php echo app('translator')->get('translation.settings'); ?></span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarApps">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="institutions" class="nav-link"><?php echo app('translator')->get('translation.list-institutions'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="<?php echo e(route('voting-tables.index')); ?>" class="nav-link"><?php echo app('translator')->get('translation.list-voting-tables'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="managers" class="nav-link"><?php echo app('translator')->get('translation.list-managers'); ?></a>
                            </li>
                            <li class="nav-item">
                                <a href="candidates" class="nav-link"><?php echo app('translator')->get('translation.list-candidates'); ?></a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a href="voting-table-votes" class="nav-link">
                        <i class="ri-keyboard-fill"></i><?php echo app('translator')->get('translation.management'); ?>
                    </a>
                </li>
                <!-- <li class="nav-item">
                    <a href="votes" class="nav-link"><?php echo app('translator')->get('translation.ratings'); ?></a>
                </li> -->

            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH D:\_Mine\corporate\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>