<?php
$db = new Postgresql();

$deptGroupQuery = "SELECT ID, DEPT_GROUP FROM M_ACC_DEPARTMENT_GROUPS";
$deptGroups = $db->fetchAll($deptGroupQuery);

if (!empty($deptGroups)) {
?>
    <option value=""></option>
    <?php
    foreach ($deptGroups as $dept) {
    ?>
        <option value="<?php echo $dept['id'] ?>"><?php echo $dept['dept_group'] ?></option>
<?php
    }
}
