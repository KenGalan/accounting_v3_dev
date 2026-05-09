<?php
// session_start();
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

$setup_pages = [
    'dept_maintenance.php',
    'dept_group.php',
    'category_acc_maintenance.php',
    'account_tagging.php',
    'distribution_cost_maintenance.php',
    'date_range_maintenance.php',
    'user_maintenance.php',
    'sbu_maintenance.php'
];

$isSetupActive = in_array($basename_server, $setup_pages);

$dist_pages = [
    'ap_distribution.php',
    'custom_distribution.php',
    'prepaid_expense.php'
];

$isDistActive = in_array($basename_server, $dist_pages);

$accrual_pages = [
    'accrual_customized.php',
    'reverse_accrual.php'
];

$isAccrualActive = in_array($basename_server, $accrual_pages);

// Added by Ivan - Visitor Count
$sessionDir = __DIR__ . '/active_sessions';
$killDir    = __DIR__ . '/killed_sessions';

if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}

if (!is_dir($killDir)) {
    mkdir($killDir, 0777, true);
}

$empNo    = $_SESSION['ppc']['emp_no'];
$fullName = $_SESSION['ppc']['fullname'];


if (isset($_GET['get_active_users'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }

    echo json_encode(getActiveUsers($sessionDir));
    exit;
}


if (isset($_GET['kill_session'])) {
    while (ob_get_level()) {
        ob_end_clean();
    }

    $currentEmp = $_SESSION['ppc']['emp_no'];

    if ($currentEmp != '10947') {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized'
        ]);
        exit;
    }

    $targetEmp = $_GET['emp_no'];

    if ($targetEmp == $currentEmp) {
        echo json_encode([
            'success' => false,
            'message' => 'You cannot kill your own session.'
        ]);
        exit;
    }

    $activeFile = $sessionDir . '/' . basename($targetEmp) . '.json';
    $killFile   = $killDir . '/' . basename($targetEmp) . '.kill';

    file_put_contents($killFile, time());

    if (file_exists($activeFile)) {
        unlink($activeFile);
    }

    echo json_encode([
        'success' => true,
        'message' => 'User session killed.'
    ]);
    exit;
}


if ($empNo) {
    $killFile = $killDir . '/' . $empNo . '.kill';

    if (file_exists($killFile)) {
        unlink($killFile);

        $activeFile = $sessionDir . '/' . $empNo . '.json';
        if (file_exists($activeFile)) {
            unlink($activeFile);
        }

        session_unset();
        session_destroy();

        header("Location: index.php");
        exit;
    }
}


if ($empNo) {
    file_put_contents($sessionDir . '/' . $empNo . '.json', json_encode([
        'emp_no' => $empNo,
        'name'   => $fullName,
        'time'   => time()
    ]));
}


function getActiveUsers($sessionDir)
{
    $limit = time() - (5 * 60);
    $users = [];

    foreach (glob($sessionDir . '/*.json') as $file) {
        $data = json_decode(file_get_contents($file), true);

        if ($data && $data['time'] >= $limit) {
            $users[] = $data;
        } else {
            unlink($file);
        }
    }

    return $users;
}

$activeUsers = getActiveUsers($sessionDir);
$activeCount = count($activeUsers); // END

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

    .list {
        height: 100% !important;
    }

    .slimScrollDiv {
        height: 100% !important;
    }
</style>

<aside id="leftsidebar" class="sidebar" style="height:100%">
    <div class="menu" style="height:100%">
        <ul class="list" style="height:100% !important;">
            <li class="header">MAIN NAVIGATION</li>

            <?php if ($hasDashboardAccess) { ?>
                <li class="<?php echo ($basename_server == 'dashboard.php' ? 'active' : ''); ?>">
                    <a href="dashboard.php" class="waves-effect waves-block">
                        <i class="material-icons">dashboard</i>
                        <span>Dashboard</span>
                    </a>
                </li>
            <?php } ?>

            <!-- <li class="<?php echo ($basename_server == 'generated_distribution.php' ? 'active' : ''); ?>">
                <a href="generated_distribution.php" class="waves-effect waves-block">
                    <i class="material-icons">dashboard</i>
                    <span>Distributed A/P</span>
                </a>
            </li> -->
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

            <li class="<?php echo ($isDistActive ? 'active' : ''); ?>">
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
                    <li class="<?php echo ($basename_server == 'prepaid_expense.php' ? 'active' : ''); ?>">
                        <a href="prepaid_expense.php" class="waves-effect waves-block">

                            <span>Prepaid Expense Distribution</span>
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
                <li class="<?php echo ($isAccrualActive ? 'active' : ''); ?>">
                    <a href="javascript:void(0);" class="waves-effect waves-block menu-toggle">
                        <i class="material-icons">history</i>
                        <span>Accrual</span>
                    </a>
                    <ul class="ml-menu">
                        <?php if ($hasAdminAccess) { ?>
                            <li class="<?php echo ($basename_server == 'accrual.php' ? 'active' : ''); ?>">
                                <a href="accrual.php" class="waves-effect waves-block">

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


            <!-- <?php if ($_SESSION['ppc']['emp_no'] == "10947" || $_SESSION['ppc']['emp_no'] == "10929") { ?>
                <li class="<?php echo ($basename_server == 'user_guide.php' ? 'active' : ''); ?>">
                    <a href="user_guide.php" class="waves-effect waves-block">
                        <i class="material-icons">book</i>
                        <span>User Guide</span>
                    </a>
                </li>
            <?php } ?> -->

            <?php if ($_SESSION['ppc']['admin'] == "1" || $hasAdminAccess) { ?>
                <li class="<?php echo ($isSetupActive ? 'active' : ''); ?>">
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
                            <!-- <li class="<?php echo ($basename_server == 'dept_group.php' ? 'active' : ''); ?>">
                                <a href="dept_group.php" class="waves-effect waves-block">
                                    <span>Department Group</span>
                                </a>
                            </li> -->
                            <!-- <li class="<?php echo ($basename_server == 'category_acc_maintenance.php' ? 'active' : ''); ?>">
                                <a href="category_acc_maintenance.php" class="waves-effect waves-block">
                                    <span>Template Maintenance</span>
                                </a>
                            </li> -->
                            <?php if ($userDept === "Management Information System") { ?>
                                <li class="<?php echo ($basename_server == 'account_tagging.php' ? 'active' : ''); ?>">
                                    <a href="account_tagging.php" class="waves-effect waves-block">
                                        <span>Custom Account Setup</span>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="<?php echo ($basename_server == 'distribution_cost_maintenance.php' ? 'active' : ''); ?>">
                                <a href="distribution_cost_maintenance.php" class="waves-effect waves-block">
                                    <span>Distribution Template</span>
                                </a>
                            </li>
                            <!-- <li class="<?php echo ($basename_server == 'date_range_maintenance.php' ? 'active' : ''); ?>">
                                <a href="date_range_maintenance.php" class="waves-effect waves-block">
                                    <span>Setup Date Range</span>
                                </a>
                            </li> -->
                            <!-- <li class="<?php echo ($basename_server == 'acc_tagging_maintenance.php' ? 'active' : ''); ?>">
                                <a href="acc_tagging_maintenance.php" class="waves-effect waves-block">
                                    <span>Account Tagging</span>
                                </a>
                            </li> -->
                        <?php } ?>

                           <?php if ($userDept === "Management Information System") { ?>
                            <li class="<?php echo ($basename_server == 'sbu_maintenance.php' ? 'active' : ''); ?>">
                                <a href="sbu_maintenance.php" class="waves-effect waves-block">
                                    <span>SBU Maintenance</span>
                                </a>
                            </li>
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
    <?php if ($_SESSION['ppc']['emp_no'] == "10947") { ?>
        <div class="activeVisitorCount" style="cursor:pointer; padding: 15px;">
            <p>Active Visitors: <span id="visitorCount"><?php echo $activeCount; ?></span></p>
        </div>
    <?php } ?>

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

    //   $(document).on('click', '.activeVisitorCount', function () {
    //     $.ajax({
    //         url: window.location.href,
    //         type: 'GET',
    //         data: { get_active_users: 1 },
    //         success: function (res) {
    //             let users = JSON.parse($.trim(res));
    //             let html = '<div style="text-align:left;">';

    //             users.forEach(function (u) {
    //                 let lastSeen = new Date(u.time * 1000).toLocaleTimeString();

    //                 html += `
    //                     <div style="margin-bottom:10px;">
    //                         <b>${u.name}</b><br>
    //                         <small>Emp No: ${u.emp_no}</small><br>
    //                         <small>Last active: ${lastSeen}</small><br>
    //                         <button type="button" class="killUser btn btn-danger btn-xs" data-emp="${u.emp_no}">
    //                             Kill Session
    //                         </button>
    //                     </div>
    //                     <hr>
    //                 `;
    //             });

    //             html += '</div>';

    //             swal({
    //                 title: "Active Users (" + users.length + ")",
    //                 text: html,
    //                 html: true
    //             });
    //         }
    //     });
    // });

    // $(document).on('click', '.killUser', function (e) {
    //     e.preventDefault();
    //     e.stopPropagation();

    //     console.log('Kill button clicked');

    //     let empNo = $(this).data('emp');
    //     killUserSession(empNo);
    // });

    // function killUserSession(empNo) {
    //     console.log('Function called:', empNo);

    //     $.ajax({
    //         url: window.location.href,
    //         type: 'GET',
    //         data: {
    //             kill_session: 1,
    //             emp_no: empNo
    //         },
    //         success: function (res) {
    //             let data = JSON.parse($.trim(res));
    //             console.log(data);

    //             if (data.success) {
    //                 swal("Success", data.message, "success");
    //             } else {
    //                 swal("Warning", data.message, "warning");
    //             }
    //         },
    //         error: function (xhr) {
    //             console.log(xhr.responseText);
    //             swal("Error", "Cannot kill session.", "error");
    //         }
    //     });
    // }
</script>