<?php

$db = new Postgresql();
$conn = $db->getConnection();

$sql = "SELECT id AS category_id, acc_category AS category_name 
        FROM M_ACC_CATEGORY_TBL 
        ORDER BY acc_category";
// echo $sql;
// exit;


$result = pg_query($conn, $sql);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=accrual_template.xls");
header("Pragma: no-cache");
header("Expires: 0");

echo "<html>";
echo "<head><meta charset='UTF-8'></head>";
echo "<body>";


echo "<table border='1'>";
echo "<tr>
        <th>Category</th>
        <th>ID</th>
      </tr>";

for ($i = 0; $i < 50; $i++) { 
    echo "<tr>
            <td></td>
            <td></td>
          </tr>";
}

echo "</table>";

echo "<br><br>";


echo "<table border='1'>";
echo "<tr>
        <th>Category</th>
        <th>ID</th>
      </tr>";

while ($row = pg_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['category_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "</body>";
echo "</html>";
exit;
?>