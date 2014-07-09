<?php
include_once('./lib/config.php');
include_once('./lib/function.php');
include_once('./lib/db_access.php');

// DB conect
$db = new db_access();
$count = 0;
$rack_data = $db->select(TABLE_RACK, "id, name", "WHERE del_flag != 1");
$pat = "/^([0-9]{0,1})([0-9]{1})[A-Z].*/";
if ($rack_data) {
    foreach($rack_data as $row) {
        $matches = array();
        $params  = array();
        if (preg_match($pat, $row['name'], $matches)) {
            if ($matches[1] == "") {
                $params['dc_loc'] = 1;
            } else {
                $params['dc_loc'] = $matches[1];
            }
            $params['floor'] = $matches[2];
            $rack_id = $row['id'];
            if ($db->update(TABLE_RACK, $params, "WHERE id = {$rack_id}")) {
                $count++;
            }
        }
    }
}
echo "update rows : {$count}";

?>
