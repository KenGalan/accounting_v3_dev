<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$apiUrl = "http://hris.teamglac.com/api/users";
// $sql = "http://hris.teamglac.com/api/users/login-pending-active?u=" . urlencode($username) . "&p=" . urlencode($password);
$response = file_get_contents($apiUrl);
$json = json_decode($response, true);

$notifQuery = "SELECT emp_no, is_notification, email, is_dashboard, is_admin, is_system FROM M_ACC_USER_MAINTENANCE";
$notifRes = pg_query($conn, $notifQuery);

$notifUsers = [];
while ($row = pg_fetch_assoc($notifRes)) {
    $empNo = intval($row['emp_no']);
    $notifUsers[$empNo] = [
        'is_notification' => $row['is_notification'] === 't',
        'is_dashboard'    => $row['is_dashboard'] === 't',
        'is_admin'    => $row['is_admin'] === 't',
        'is_system'    => $row['is_system'] === 't'
    ];
}

$result = [];
if (isset($json['result'])) {
    if (isset($json['result']['employee_id_no'])) {
        $user = $json['result'];
        $empNo = intval($user['employee_id_no']);
        $user['is_notification'] = isset($notifUsers[$empNo]['is_notification']) ? $notifUsers[$empNo]['is_notification'] : false;
        $user['is_dashboard']    = isset($notifUsers[$empNo]['is_dashboard']) ? $notifUsers[$empNo]['is_dashboard'] : false;
        $user['is_admin']    = isset($notifUsers[$empNo]['is_admin']) ? $notifUsers[$empNo]['is_admin'] : false;
        $user['is_system']    = isset($notifUsers[$empNo]['is_system']) ? $notifUsers[$empNo]['is_system'] : false;
        $result[] = $user;
    } else {
        foreach ($json['result'] as $user) {
            $empNo = intval($user['employee_id_no']);
            $user['is_notification'] = isset($notifUsers[$empNo]['is_notification']) ? $notifUsers[$empNo]['is_notification'] : false;
            $user['is_dashboard']    = isset($notifUsers[$empNo]['is_dashboard']) ? $notifUsers[$empNo]['is_dashboard'] : false;
            $user['is_admin']    = isset($notifUsers[$empNo]['is_admin']) ? $notifUsers[$empNo]['is_admin'] : false;
            $user['is_system']    = isset($notifUsers[$empNo]['is_system']) ? $notifUsers[$empNo]['is_system'] : false;
            $result[] = $user;
        }
    }
}

echo json_encode($result);
