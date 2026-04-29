<?php
$basename_server = basename($_SERVER['SCRIPT_NAME']);

$db = new Postgresql();
$conn = $db->getConnection();

$emp_no = isset($_SESSION['ppc']['emp_no']) ? intval($_SESSION['ppc']['emp_no']) : 0;
$hasDashboardAccess = false;
$hasAdminAccess = false;
$userDept = isset($_SESSION['ppc']['dept_name']) ? $_SESSION['ppc']['dept_name'] : '';

if ($emp_no > 0) {
    $sql = "SELECT 1 FROM M_ACC_USER_MAINTENANCE 
            WHERE emp_no = $emp_no AND is_dashboard = TRUE LIMIT 1";
    $res = pg_query($conn, $sql);
    if ($res && pg_num_rows($res) > 0) {
        $hasDashboardAccess = true;
    }

    $sql1 = "SELECT 1 FROM M_ACC_USER_MAINTENANCE 
             WHERE emp_no = $emp_no AND is_admin = TRUE LIMIT 1";
    $res1 = pg_query($conn, $sql1);
    if ($res1 && pg_num_rows($res1) > 0) {
        $hasAdminAccess = true;
    }
}

$hasSystemAccess = false;

$sql = "
    SELECT 1
    FROM M_AP_USER_MAINTENANCE
    WHERE emp_no = $emp_no
      AND is_system = TRUE
    LIMIT 1
";
$res = pg_query($conn, $sql);
if ($res && pg_num_rows($res) > 0) {
    $hasSystemAccess = true;
}

?>
<style>
    body.sidebar-hidden #leftsidebar {
        transform: translateX(-100%);
        transition: 0.3s;
    }

    body.sidebar-hidden section.content {
        margin-left: 0 !important;
    }

    #leftsidebar {
        transition: 0.3s;
    }
</style>

<aside id="leftsidebar" class="sidebar" style="height:185vh">
    <div class="menu">
        <ul class="list">
            <li class="header">MAIN NAVIGATION</li>

            <!-- <?php if ($hasDashboardAccess) { ?>
                <li class="<?php echo ($basename_server == 'journal_entries.php' ? 'active' : ''); ?>">
                    <a href="journal_entries.php" class="waves-effect waves-block">
                        <i class="material-icons">dashboard</i>
                        <span>Dashboard</span>
                    </a> 
                </li>
            <?php } ?> -->

            <li class="<?php echo ($basename_server == 'generated_distribution.php' ? 'active' : ''); ?>">
                <a href="generated_distribution.php" class="waves-effect waves-block">
                    <i class="material-icons">dashboard</i>
                    <span>Distributed A/P</span>
                </a>
            </li>
            <!-- <li class="<?php echo ($basename_server == 'ap_distribution.php' ? 'active' : ''); ?>">
                <a href="ap_distribution.php" class="waves-effect waves-block">
                    <i class="material-icons">bookmark</i>
                    <span>A/P Distribution</span>
                </a>
            </li>
            <li class="<?php echo ($basename_server == 'custom_distribution.php' ? 'active' : ''); ?>">
                <a href="custom_distribution.php" class="waves-effect waves-block">
                    <i class="material-icons">bookmark</i>
                    <span>Custom Distribution</span>
                </a>
            </li> -->

            <li class="<?php echo (($basename_server == 'admin_maint.php') ? 'active' : ''); ?>">
                <a href="javascript:void(0);" class="waves-effect waves-block menu-toggle">
                    <i class="material-icons">bookmark</i>
                    <span>Distribution</span>
                </a>
                <ul class="ml-menu">
                    <li class="<?php echo ($basename_server == 'ap_distribution.php' ? 'active' : ''); ?>">
                        <a href="ap_distribution.php" class="waves-effect waves-block">

                            <span>A/P Distribution</span>
                        </a>
                    </li>
                    <li class="<?php echo ($basename_server == 'custom_distribution.php' ? 'active' : ''); ?>">
                        <a href="custom_distribution.php" class="waves-effect waves-block">

                            <span>Custom Distribution</span>
                        </a>
                    </li>

                </ul>
            </li>

            <?php if ($_SESSION['ppc']['admin'] == "1" || $hasAdminAccess) { ?>
                <li class="<?php echo (($basename_server == 'admin_maint.php') ? 'active' : ''); ?>">
                    <a href="javascript:void(0);" class="waves-effect waves-block menu-toggle">
                        <i class="material-icons">history</i>
                        <span>Accrual</span>
                    </a>
                    <ul class="ml-menu">
                        <?php if ($hasAdminAccess) { ?>
                            <li class="<?php echo ($basename_server == 'accrual_customized.php' ? 'active' : ''); ?>">
                                <a href="accrual_customized.php" class="waves-effect waves-block">

                                    <span>Active Accruals</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'reverse_accrual.php' ? 'active' : ''); ?>">
                                <a href="reverse_accrual.php" class="waves-effect waves-block">

                                    <span>Reverse Accrual</span>
                                </a>
                            </li>

                        <?php } ?>


                    </ul>
                </li>
            <?php } ?>


            <?php if ($_SESSION['ppc']['emp_no'] == "10947" || $_SESSION['ppc']['emp_no'] == "10929") { ?>
                <li class="<?php echo ($basename_server == 'user_guide.php' ? 'active' : ''); ?>">
                    <a href="user_guide.php" class="waves-effect waves-block">
                        <i class="material-icons">book</i>
                        <span>User Guide</span>
                    </a>
                </li>
            <?php } ?>

            <?php if ($_SESSION['ppc']['admin'] == "1" || $hasAdminAccess) { ?>
                <li class="<?php echo (($basename_server == 'admin_maint.php') ? 'active' : ''); ?>">
                    <a href="javascript:void(0);" class="waves-effect waves-block menu-toggle">
                        <i class="material-icons">settings</i>
                        <span>Setup</span>
                    </a>
                    <ul class="ml-menu">
                        <?php if ($hasAdminAccess) { ?>
                            <li class="<?php echo ($basename_server == 'dept_maintenance.php' ? 'active' : ''); ?>">
                                <a href="dept_maintenance.php" class="waves-effect waves-block">
                                    <span>Department</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'dept_group.php' ? 'active' : ''); ?>">
                                <a href="dept_group.php" class="waves-effect waves-block">
                                    <span>Department Group</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'category_acc_maintenance.php' ? 'active' : ''); ?>">
                                <a href="category_acc_maintenance.php" class="waves-effect waves-block">
                                    <span>Template Maintenance</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'account_tagging.php' ? 'active' : ''); ?>">
                                <a href="account_tagging.php" class="waves-effect waves-block">
                                    <span>Magic Setup?</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'distribution_cost_maintenance.php' ? 'active' : ''); ?>">
                                <a href="distribution_cost_maintenance.php" class="waves-effect waves-block">
                                    <span>Distribution Percentage</span>
                                </a>
                            </li>
                            <li class="<?php echo ($basename_server == 'date_range_maintenance.php' ? 'active' : ''); ?>">
                                <a href="date_range_maintenance.php" class="waves-effect waves-block">
                                    <span>Setup Date Range</span>
                                </a>
                            </li>
                            <!-- <li class="<?php echo ($basename_server == 'acc_tagging_maintenance.php' ? 'active' : ''); ?>">
                                <a href="acc_tagging_maintenance.php" class="waves-effect waves-block">
                                    <span>Account Tagging</span>
                                </a>
                            </li> -->
                        <?php } ?>

                        <?php if ($userDept === "Management Information System") { ?>
                            <li class="<?php echo ($basename_server == 'user_maintenance.php' ? 'active' : ''); ?>">
                                <a href="user_maintenance.php" class="waves-effect waves-block">
                                    <span>User Access</span>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php } ?>
            <hr />
            <li class="<?php echo ($basename_server == 'report.php' ? 'active' : ''); ?>">
                <a href="report.php" class="waves-effect waves-block">
                    <i class="material-icons">assignment</i>
                    <span>A/R Aging Report</span>
                </a>
            </li>
            <li class="<?php echo ($basename_server == 'accrued_report.php' ? 'active' : ''); ?>">
                <a href="accrued_report.php" class="waves-effect waves-block">
                    <i class="material-icons">assignment</i>
                    <span>Accrued AP Aging Report</span>
                </a>
            </li>
            <hr />
            <p style="padding-left: 15px;">Quick Access</p>
            <?php if ($hasSystemAccess) { ?>
                <li>
                    <a onclick="openModal('http://testapps.teamglac.com/ap_system/issued_payables.php')">
                        <i class="material-icons">open_in_browser</i>
                        <span>A/P Voucher System</span>
                    </a>
                </li>
            <?php } ?>

            <li class="<?php echo ($basename_server == '' ? 'active' : ''); ?>">
                <a onclick="openSplitScreen('https://odoo.teamglac.com/web')" class="waves-effect waves-block">
                    <i class="material-icons">open_in_browser</i>
                    <span>Odoo</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<div id="modalBackdrop" style="
    display:none;
    position:fixed;
    top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.85);
    z-index:99990;
"></div>

<div id="urlViewer" style="
    display:none; 
    position:fixed; top:0; left:0; 
    width:90%; height:90%; 
    background:white; 
    z-index:99999;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
">

    <div id="dragBar" style="
        width:100%;
        height:30px;
        background:#cccccccc;
        color:#000000;
        cursor:move;
        display:flex;
        font-weight: 800px;
        align-items:center;
        justify-content:center;
        padding:0 10px;
        box-sizing:border-box;
        font-weight:bold;
        font-size:12pt;
        text-align:center ! important;
    ">- DRAG THIS IF YOU WANT TO MOVE THE FRAME -
    </div>

    <button id="closeUrlViewer" style="
            position:absolute; 
            top:100; right:100; 
            background:#4a6ea9; 
            color:white; 
            border:none; 
            padding:5px 15px; 
            border-radius:6px; 
            cursor:pointer; 
            z-index:100000;
            font-weight: 800;
            font-size: 8pt;
        ">
        CLOSE
    </button>

    <iframe id="urlFrame" style="width:100%; height:100%; border:none;"></iframe>
</div>
<script>
    function openModal(url) {
        const viewer = document.getElementById("urlViewer");
        const backdrop = document.getElementById("modalBackdrop");
        const iframe = document.getElementById("urlFrame");

        iframe.src = url;

        backdrop.style.display = "block";
        viewer.style.display = "block";

        document.body.style.overflow = "hidden";
    }

    document.getElementById("closeUrlViewer").addEventListener("click", () => {
        const viewer = document.getElementById("urlViewer");
        const backdrop = document.getElementById("modalBackdrop");
        const iframe = document.getElementById("urlFrame");

        iframe.src = "";
        viewer.style.display = "none";
        backdrop.style.display = "none";
        document.body.style.overflow = "auto";
    });

    const viewer = document.getElementById("urlViewer");
    const dragBar = document.getElementById("dragBar");

    let offsetX = 0,
        offsetY = 0;
    let isDragging = false;

    dragBar.addEventListener("mousedown", (e) => {
        isDragging = true;
        offsetX = e.clientX - viewer.offsetLeft;
        offsetY = e.clientY - viewer.offsetTop;
        document.body.style.userSelect = "none";
    });

    document.addEventListener("mousemove", (e) => {
        if (!isDragging) return;

        viewer.style.left = (e.clientX - offsetX) + "px";
        viewer.style.top = (e.clientY - offsetY) + "px";
    });

    document.addEventListener("mouseup", () => {
        isDragging = false;
        document.body.style.userSelect = "auto";
    });

    function openSplitScreen(url) {
        const screenW = window.screen.availWidth;
        const screenH = window.screen.availHeight;

        const win = window.open(
            url,
            "_blank",
            `width=${screenW / 2},
         height=${screenH},
         left=${screenW / 2}, 
         top=0,
         toolbar=no,
         menubar=no,
         resizable=no,
         scrollbars=yes`
        );
    }

    $('#toggleSidebarBtn').on('click', function() {
        $('body').toggleClass('sidebar-hidden');

        // save state
        if ($('body').hasClass('sidebar-hidden')) {
            localStorage.setItem('sidebarHidden', '1');
        } else {
            localStorage.setItem('sidebarHidden', '0');
        }
    });
</script>