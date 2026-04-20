
<?php
$db = new Postgresql();

$account_query = "SELECT id, dept_group FROM m_acc_department_groups ORDER BY dept_group ASC";
// $account_result = pg_query($conn, $account_query);


$acc_query_result = $db->fetchAll($account_query);
// var_dump($acc_query_result);
if ($acc_query_result) {
    $deptGroups = array_values($acc_query_result);
}
$department = "SELECT AAA.id, 
CASE
    WHEN AAA.NAME ~ '^[0-9]' THEN
        regexp_replace(AAA.NAME, '^\S+\s*', '')
    ELSE
    AAA.NAME
END DEPT_NAME,
-- AAA.NAME DEPT_NAME,
CASE
    WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
    ELSE ''
END DEPT_CODE,
ADG.DEPT_GROUP, AAA.CREATE_DATE ADDED_ON, '' added_by,'' changed_on, ''changed_by, AAA.active, ''employee_no--, AAA.code DEPT_CODE
          FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
          LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
          WHERE AAA.ACTIVE and aaa.company_id = 1
          ORDER BY id ASC";
$dept_list = $db->fetchAll($department);

include("view/" . getFileName());
