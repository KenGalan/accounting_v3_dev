<?php
set_time_limit(0);
ini_set('memory_limit', '-1');
error_reporting(E_ALL);

$db = new Postgresql();

$batch = 5000; 

while (true) {

    $ids = $db->fetchAll("
        SELECT id
        FROM usage_card_line_actual
        WHERE create_date IS NULL
        AND remarks IS NULL
        LIMIT $batch
    ");

    if (!$ids) {
        echo "DONE\n";
        break;
    }

    foreach ($ids as $row) {

        $id = (int)$row['id'];

        $res = $db->fetchRow("
            SELECT wp.write_date
            FROM usage_card_line_actual ucla
            JOIN usage_card_line ucl 
                ON ucla.uc_line_id = ucl.id
            JOIN m_hc0_machine_material m_mat 
                ON m_mat.usage_no = ucl.uc_code
            JOIN m_hc0_machine_name mn 
                ON mn.id = m_mat.main_id
            JOIN maintenance_equipment_mrp_workcenter_productivity_rel rel 
                ON mn.id = rel.mach_setup_id
            JOIN mrp_workcenter_productivity wp 
                ON rel.mrp_workcenter_productivity_id = wp.id
            WHERE ucla.id = $id
            AND rel.mach_setup_id IS NOT NULL
            ORDER BY wp.write_date DESC
            LIMIT 1
        ");

        if ($res && $res['write_date']) {

            $date = $res['write_date'];

            $db->query("
                UPDATE usage_card_line_actual
                SET create_date = '$date'
                WHERE id = $id
            ");

            echo "Updated ID: $id\n";
        }
    }
}