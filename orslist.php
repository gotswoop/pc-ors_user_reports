<?php
// Call the REDCap Connect file in the main "redcap" directory. 
require_once __DIR__ . '/../../../redcap_connect.php';

REDCap::allowProjects(7217);

// PID is hardcoded to avoid SQL injection - No variables are passed to SQL query; TODO: change to parameterized sql query with dyanamic pid.
$query = "
select record, max(patient_last), max(patient_first), max(room), max(surgeons), 'ORS'
from (select record, 
case WHEN redcap_data.field_name = 'patient_last' then value end as patient_last, 
case WHEN redcap_data.field_name = 'patient_first' then value end as patient_first,
case WHEN redcap_data.field_name = 'room' then value end as room,
case WHEN redcap_data.field_name = 'surgeons' then SUBSTRING_INDEX(SUBSTRING_INDEX(SUBSTRING_INDEX(redcap_metadata.element_enum, CONCAT(' ', value, ','), -1), '\\\\n', 1), '1,', -1) end as surgeons,
case WHEN redcap_data.field_name = 'service_list' then value end as service_list,
redcap_metadata.element_enum
from redcap_data
left join redcap_metadata on redcap_data.project_id = '7217' and redcap_data.field_name = redcap_metadata.field_name 
where redcap_data.project_id = '7217' 
and redcap_data.field_name in ('surgeons', 'service_list', 'room', 'registry_id', 'patient_last', 'patient_first')
and record in (select record from redcap_data where field_name = 'service_list' and value = 1)
and record in (select record from redcap_data where field_name = 'patient_status' and value = 0)
) a
group by record
order by max(patient_last)";

$result = mysqli_query($conn, $query);

echo "<table style='border-collapse: collapse; border: 1px solid black;'><tr>
<th style=\"border: 1px solid black\">Record_ID</th>
<th style=\"border: 1px solid black\">Patient Last</th>
<th style=\"border: 1px solid black\">Patient First</th>
<th style=\"border: 1px solid black\">Room</th>
<th style=\"border: 1px solid black\">Surgeon</th>
<th style=\"border: 1px solid black\">Service_List</th>
</tr>";
while ($row = mysqli_fetch_assoc($result)) {
    echo '<tr>';
    foreach ($row as $field) {
        echo '<td style="border: 1px solid black">' . htmlspecialchars($field) . '</td>';
    }
    echo '</tr>';
}
echo '</table>';


// OPTIONAL: Display the project footer
