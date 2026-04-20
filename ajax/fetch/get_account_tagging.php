<?php
$db = new Postgresql();

$select = "
    SELECT
        AAT.ID,
        CONCAT(FAA.CODE, ' ', FAA.NAME) AS FROM_ACCOUNT,
        ADG.DEPT_GROUP,
        CONCAT(TAA.CODE, ' ', TAA.NAME) AS TO_ACCOUNT
    FROM M_ACC_ACC_TAGGING AAT
    JOIN ACCOUNT_ACCOUNT FAA ON FAA.ID = AAT.FROM_ACCOUNT_ID
    JOIN ACCOUNT_ACCOUNT TAA ON TAA.ID = AAT.TO_ACCOUNT_ID
    JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAT.DEPT_GROUP_ID
    WHERE ADG.DEPT_GROUP = 'GENERAL & ADMIN'; 
";

$result = $db->fetchAll($select); 

if (!empty($result) && is_array($result)) {
    foreach ($result as $row) {
        ?>
        <tr data-group="<?php echo htmlspecialchars($row['dept_group']); ?>">
            <td><?php echo htmlspecialchars($row['dept_group']); ?></td>
            <td><?php echo htmlspecialchars($row['to_account']); ?></td>
            <td><?php echo htmlspecialchars($row['from_account']); ?></td>
            <td>
                <i class="material-icons btn-danger delAccTagBtn"
                   style="border-radius: 5px; padding: 3px;"
                   id-attr="<?php echo $row['id']; ?>">delete</i>
                <i class="material-icons btn-primary upAccTagBtn"
                   style="border-radius: 5px; padding: 3px;"
                   id-attr="<?php echo $row['id']; ?>">autorenew</i>
            </td>
        </tr>
        <?php
    }
}
?>
