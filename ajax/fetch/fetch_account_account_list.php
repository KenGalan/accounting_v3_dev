<?php
$db = new Postgresql();

$accountQuery = "SELECT ID, NAME FROM ACCOUNT_ACCOUNT";
$accounts = $db->fetchAll($accountQuery);

if (!empty($accounts)) {
?>
    <option value=""></option>
    <?php
    foreach ($accounts as $acc) {
    ?>
        <option value="<?php echo $acc['id'] ?>"><?php echo $acc['name'] ?></option>
<?php
    }
}
