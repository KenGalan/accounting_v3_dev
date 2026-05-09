<?php
$db = new Postgresql();
$yearMonth = $_POST['year_month'];
// $yearMonth = '2026-02';
// $q = "	  
// WITH setup AS (
//     SELECT 
//         move_id, 
//         unnest(sbu) AS sbu_id
//     FROM m_acc_cust_dist
// )
// SELECT
// CD.ID CD_ID,
//     am.name AS journal_entry,
//     am.ref AS reference,
//     am.amount_total,
//     am.date AS accounting_date,
//     am.state AS status,
//     am.id AS am_id,
//     cd.from_date,
//     cd.to_date,
//     cd.id AS cust_dist_id,
//     COALESCE(
//         json_agg(DISTINCT sm.sbu) 
//         FILTER (WHERE setup.sbu_id IS NOT NULL),
//         '[]'::json
//     ) AS sbu,
//     COALESCE(
//         json_agg(DISTINCT setup.sbu_id) 
//         FILTER (WHERE setup.sbu_id IS NOT NULL),
//         '[]'::json
//     ) AS sbu_ids,
//      cd.mo_dist
// FROM account_move am
// JOIN account_move_line aml 
//     ON aml.move_id = am.id 
//     AND aml.debit > 0
// --JOIN m_acc_customized_dist_accounts acd 
//    -- ON acd.reference = aml.ref and acd.active
//     JOIN m_acc_customized_dist_accounts acd 
//     ON acd.account_id = aml.account_id and acd.active
// LEFT JOIN m_acc_cust_dist cd 
//     ON cd.move_id = am.id
// LEFT JOIN setup 
//     ON setup.move_id = am.id
// LEFT JOIN m_acc_sbu_maint sm
//     ON sm.id = setup.sbu_id
// WHERE to_char(am.date, 'YYYY-MM') = '$yearMonth'
// AND am.state = 'posted'
// GROUP BY
// CD.ID,
//     am.name,
//     am.ref,
//     am.amount_total,
//     am.date,
//     am.state,
//     am.id,
//     cd.from_date,
//     cd.to_date,
//     cd.id
// ";

$q = "with main as (
    SELECT
    CD.ID CD_ID,
        am.name AS journal_entry,
        am.ref AS reference,
        am.date AS accounting_date,
        am.state AS status,
        am.id AS am_id,
        sum(aml.debit) amount_total,
        cd.from_date,
        cd.to_date,
        cd.id AS cust_dist_id,
         cd.mo_dist,
        cd.sbu
    FROM account_move am
    JOIN account_move_line aml ON aml.move_id = am.id AND aml.debit > 0
    JOIN m_acc_customized_dist_accounts acd ON acd.account_id = aml.account_id and acd.active
    LEFT JOIN m_acc_cust_dist cd ON cd.move_id = am.id
    WHERE to_char(am.date, 'YYYY-MM') = '$yearMonth'
    AND am.state = 'posted'
    GROUP BY
    CD.ID,
        am.name,
        am.ref,
        am.date,
        am.state,
        am.id,
        cd.from_date,
        cd.to_date,
        cd.id,
         cd.mo_dist
    )
    select 
    m.CD_ID,
       m.journal_entry,
        m.reference,
        m.accounting_date,
        m.status,
        m.am_id,
        m.amount_total,
        m.from_date,
        m.to_date,
        m.cust_dist_id,
        m.mo_dist,
    TO_JSON(m.sbu) AS sbu_ids,
     case when m.sbu is null then null else JSON_AGG(s.sbu ORDER BY s.id) end AS sbu
    from
    main m
    LEFT JOIN m_acc_sbu_maint s ON s.id = ANY(m.sbu)
    group by m.CD_ID,
       m.journal_entry,
        m.reference,
        m.accounting_date,
        m.status,
        m.am_id,
        m.amount_total,
        m.from_date,
        m.to_date,
        m.cust_dist_id,
        m.mo_dist,
        m.sbu";
$result = $db->fetchAll($q);

echo json_encode($result);
