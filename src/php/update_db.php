
<?php
//<?php ini_set(�memory_limit�,�256M�); 

ini_set('max_execution_time', 600); 

//internal functions

function establish_connection() {
	
	require_once('passwords.php');
    $connection = new mysqli($db_server, $db_username, $db_password, $DB_DATA, $db_port);
    
    if (!$connection) {
        $answer_from_server['result'] = 'error';
        $answer_from_server['info'] = 'connection fail: ' . mysql_error();
    }
	return $connection;
}

// используется стандартная нотация latitude, longitude ($node1[0] - latitude)
function sort_nodes($nodes) {
    $node1 = $nodes[0];
    $node2 = $nodes[1];
    if ($node1[0] < $node2[0]) {
        return $nodes;
    }
    elseif ($node1[0] > $node2[0]) {
        return array($node2, $node1);
    }
    elseif ($node1[1] < $node2[1]) {
        return $nodes;
    }
    else {
        return array($node2, $node1);
    }
}

function add_node($connection, $node_coords, $timestamp = NULL, $parent = NULL) {
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];

    //TODO need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    /*$sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                VALUES ($latitude, $longitude, '$timestamp', '$parent')"; */
				
	$sql_i_ins = "INSERT INTO Nodes (latitude, longitude";
	$sql_i_vals = "VALUES ($latitude, $longitude";
				
	if ($timestamp != null) {
		$sql_i_ins .= ", node_osm_date";
		$sql_i_vals .= ", $timestamp";
	}
	if ($parent != null) {
		$sql_i_ins .= ", node_osm_parent";
		$sql_i_vals .= ", $parent";
	}
	
	$sql_i_ins .= ") ";
	$sql_i_vals .= ")";
	$sql_i = $sql_i_ins . $sql_i_vals;
	
    if ($connection->query($sql_i) === TRUE) {
		$answer_from_server['result'] = 'success';
		$answer_from_server['info'] = 'node added';
    } else {
		$answer_from_server['result'] = 'error';
		$answer_from_server['info'] = 'node addition fail - connection error: ' . $sql_d . "<br>" . mysql_error();
    }
}

function get_node_id_by_coords($connection, $node_coords) {
	
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];
    $sql_q = "SELECT node_id FROM Nodes WHERE latitude=$latitude AND longitude=$longitude";
    $res = $connection->query($sql_q);

    if($res->num_rows <= 0) {
        return -1;
    } else {
        $row = $res->fetch_assoc();
        return $row["node_id"];
    }
}


function find_or_add_node($connection, $node_coords, $timestamp = NULL, $parent = NULL) {

    $node_id = get_node_id_by_coords($connection, $node_coords);
    if ($node_id == -1) {
        add_node($connection, $node_coords, $timestamp, $parent);
        $node_id = get_node_id_by_coords($connection, $node_coords);
    }
    return $node_id;
}

function delete_qline($connection, $qline_id) {
	$sql_d = "DELETE FROM qlines WHERE qline_id = $qline_id";
	if ($connection->query($sql_d) === TRUE) {
		$answer_from_server['result'] = 'success';
		$answer_from_server['info'] = 'qline removed';
    } else {
        $answer_from_server['result'] = 'error';
		$answer_from_server['info'] = 'qline removal fail - connection error: ' . $sql_d . "<br>" . mysql_error();
    }
	
}

function add_qline($connection, $start_node_id, $end_node_id, $parent_line_id, $surface_quality = NULL, $pavement_type_id = NULL, $width = NULL) {
    $sorted_nodes = sort_nodes(array($start_node_id, $end_node_id));
    $start_node_id = $sorted_nodes[0];
    $end_node_id = $sorted_nodes[1];
	$sql_i_ins = "INSERT INTO qLines (qline_start_node_id, qline_end_node_id, parent_line_id";
	$sql_i_vals = "VALUES ($start_node_id, $end_node_id, $parent_line_id";
	if ($surface_quality != null) {
		$sql_i_ins .= ", surface_quality";
		$sql_i_vals .= ", $surface_quality";
	}
	if ($pavement_type_id != null) {
		$sql_i_ins .= ", pavement_type_id";
		$sql_i_vals .= ", $pavement_type_id";
	}
	if ($width != null) {
		$sql_i_ins .= ", width";
		$sql_i_vals .= ", $width";
	}
	$sql_i_ins .= ") ";
	$sql_i_vals .= ")";
	$sql_i = $sql_i_ins . $sql_i_vals;

    //TODO same as weith nodes - need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    if ($connection->query($sql_i) === TRUE) {
        $answer_from_server['result'] = 'success';
		$answer_from_server['info'] = 'qline added';
    } else {
        $answer_from_server['result'] = 'error';
		$answer_from_server['info'] = 'qline addition fail - connection error: ' . $sql_d . "<br>" . mysql_error();
    }
}

function get_qline_property ($connection, $qline_id, $property) {
	$sql_q = "SELECT $property FROM qLines
                WHERE qline_id = '$qline_id'";
				
	$res = $connection->query($sql_q);

    if($res->num_rows <= 0) {
        return -1;
    } else {
        $row = $res->fetch_assoc();

        return $row["$property"];
    }
}

function set_qline_property ($connection, $qline_id, $property, $property_value) {
	$sql_q = " UPDATE qlines
				SET $property = $property_value
                WHERE qline_id = '$qline_id'";

	$res = $connection->query($sql_q);
    if($res->num_rows <= 0) {
		$answer_from_server['result'] = 'success';
		$answer_from_server['info'] = 'qline updated';
    } else {
        $answer_from_server['result'] = 'error';
		$answer_from_server['info'] = 'qline update fail - connection error: ' . $sql_d . "<br>" . mysql_error();
    }
}

function get_qline_node_coords($connection, $qline_id) {
	
	$sql_q = "SELECT ns.latitude as start_latitude,
                     ns.longitude as start_longitude,
                     ne.latitude as end_latitude,
                     ne.longitude as end_longitude
              FROM nodes ns, nodes ne, qlines qln
              WHERE qln.qline_id = $qline_id
              AND qln.qline_start_node_id = ns.node_id
              AND qln.qline_end_node_id = ne.node_id";
			  
	$res = $connection->query($sql_q);
	if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
			// beware - traditional latitude-longitude notation, not the geojson one.
            $start_coordinates = array(doubleval($row["start_latitude"]), doubleval($row["start_longitude"]));
            $end_coordinates =  array(doubleval($row["end_latitude"]), doubleval($row["end_longitude"]));
            $nodes = array($start_coordinates, $end_coordinates);
        }
	}
	return $nodes;
	
}

function get_qline_nodes_ids($connection, $qline_id) {
	$sql_q = "SELECT qline_start_node_id, qline_end_node_id
              FROM qlines
              WHERE qline_id = $qline_id";
			  
	$res = $connection->query($sql_q);
	if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
			// beware - traditional latitude-longitude notation, not the geojson one.
            $nodes = array($row["qline_start_node_id"], $row["qline_end_node_id"]);
        }
	}
	return $nodes;
}

function delete_node_if_unused($connection, $node_id) {
	$sql_q = "SELECT qline_id
              FROM qlines
              WHERE (
			  (qline_start_node_id = $node_id)
			  OR (qline_end_node_id = $node_id)
			  )";
			  
	$res = $connection->query($sql_q);
	if($res->num_rows < 1) {
		$sql_d = "DELETE FROM Nodes WHERE node_id=$node_id";
		if ($connection->query($sql_d) === TRUE) {
			$answer_from_server['result'] = 'success';
			$answer_from_server['info'] = 'node removed';
		} else {
			$answer_from_server['result'] = 'error';
			$answer_from_server['info'] = 'node removal fail - connection error: ' . $sql_d . "<br>" . mysql_error();
		}
	}
	
}

//external functions

function update_lines($ids_list, $quality, $surface_type) {
	
	$connection = establish_connection();
	$upd_string = '';

	if ($quality !='') {
		$upd_string .= "surface_quality = $quality";
	}
	
	if ($surface_type != '-1') {
		if ($upd_string != '') {
			$upd_string .= ", ";
		}
		$upd_string .= "pavement_type_id = $surface_type";
	}
	
    $sql_q = "UPDATE qlines
            SET $upd_string
            WHERE qline_id in ($ids_list)";

	if ($connection->query($sql_q) === TRUE) {
		$answer_from_server['result'] = 'success';
		$answer_from_server['info'] = 'lines updated';
	} else {
		$answer_from_server['result'] = 'error';
		$answer_from_server['info'] = 'lines update error: ' . $sql_q . "<br>" . mysql_error();
	}
}

function break_qline ($line_id, $percent) {
	
	$connection = establish_connection();
	$parent_line_id = get_qline_property($connection, $line_id, 'parent_line_id');
	$surface_quality = get_qline_property($connection, $line_id, 'surface_quality');
	$pavement_type_id = get_qline_property($connection, $line_id, 'pavement_type_id');
	$width = get_qline_property($connection, $line_id, 'width');
	$multiplyer = $percent/100;
	
	$nodes_ids = get_qline_nodes_ids($connection, $line_id);
	$node_0_id = $nodes_ids[0];
	$node_1_id = $nodes_ids[1];
	
	
	$nodes_coords = get_qline_node_coords($connection, $line_id);
	$nodes_coords = sort_nodes($nodes_coords);
	$node_0_coords = $nodes_coords[0];
	$node_1_coords = $nodes_coords[1];
	$break_node_lat = $node_0_coords[0] + round(($node_1_coords[0] - $node_0_coords[0])*$multiplyer, 7);
	$break_node_lon = $node_0_coords[1] + round(($node_1_coords[1] - $node_0_coords[1])*$multiplyer, 7);
	$break_node_coords = array($break_node_lat, $break_node_lon);
	$break_node_id = find_or_add_node($connection, $break_node_coords);
	delete_qline($connection, $line_id);

	add_qline($connection, $node_0_id, $break_node_id, $parent_line_id, $surface_quality, $pavement_type_id, $width);
	add_qline($connection, $break_node_id, $node_1_id, $parent_line_id, $surface_quality, $pavement_type_id, $width);
}

function join_qlines ($qline_1_id, $qline_2_id) {

	$connection = establish_connection();
	$qline_1_parent_id = get_qline_property($connection, $qline_1_id, 'parent_line_id');
	$qline_2_parent_id = get_qline_property($connection, $qline_2_id, 'parent_line_id');
	$surface_quality = get_qline_property($connection, $qline_1_id, 'surface_quality');
	$pavement_type_id = get_qline_property($connection, $qline_1_id, 'pavement_type_id');
	
	if (($qline_1_parent_id == $qline_2_parent_id) && ($qline_1_id != $qline_2_id)) {
		$node_ids_1 = get_qline_nodes_ids($connection, $qline_1_id);
		$node_ids_2 = get_qline_nodes_ids($connection, $qline_2_id);
		if ($node_ids_1[0] == $node_ids_2[0]) {
			$node_to_remove = $node_ids_1[0];
			$node_to_leave_1 = $node_ids_1[1];
			$node_to_leave_2 = $node_ids_2[1];
		} elseif ($node_ids_1[0] == $node_ids_2[1]) {
			$node_to_remove = $node_ids_1[0];
			$node_to_leave_1 = $node_ids_1[1];
			$node_to_leave_2 = $node_ids_2[0];
		} elseif ($node_ids_1[1] == $node_ids_2[0]) {
			$node_to_remove = $node_ids_1[1];
			$node_to_leave_1 = $node_ids_1[0];
			$node_to_leave_2 = $node_ids_2[1];
		} elseif ($node_ids_1[1] == $node_ids_2[1]) {
			$node_to_remove = $node_ids_1[1];
			$node_to_leave_1 = $node_ids_1[0];
			$node_to_leave_2 = $node_ids_2[0];
		} else {
			//no intersecting nodes, we're done here.
			$answer_from_server['result'] = 'fail';
			$answer_from_server['info'] = 'qlines can not be joined - no shared node';
			return null;
		}
		
		delete_qline($connection, $qline_2_id);
		set_qline_property($connection, $qline_1_id, 'qline_start_node_id', $node_to_leave_1);
		set_qline_property($connection, $qline_1_id, 'qline_end_node_id', $node_to_leave_2);
		
		delete_node_if_unused($connection, $node_to_remove);
		
		
	} else {
		$answer_from_server['result'] = 'fail';
		$answer_from_server['info'] = 'qlines can not be joined - only different qlines with one parent can be joined';
    
		
				
	}
	
}

//output

if (isset($_REQUEST["request"])) $global_request_name = htmlspecialchars(stripslashes(trim($_REQUEST["request"])));

switch ($global_request_name) {

	case 'update_lines':
        if (isset($_REQUEST["ids"])) $ids_list = htmlspecialchars(stripslashes(trim($_REQUEST["ids"])));
        if (isset($_REQUEST["quality"])) $quality = htmlspecialchars(stripslashes(trim($_REQUEST["quality"])));
        if (isset($_REQUEST["surface"])) $surface = htmlspecialchars(stripslashes(trim($_REQUEST["surface"])));
        
        //$answer_from_server['result'] = true;
		//$answer_from_server['info'] = 'DB updated';
        $jsonGeoData_arr = update_lines($ids_list, $quality, $surface);
        break;
		
	case 'break_qline':
		if (isset($_REQUEST["id"])) $qline_id = htmlspecialchars(stripslashes(trim($_REQUEST["id"])));
		if (isset($_REQUEST["percent"])) $percent = htmlspecialchars(stripslashes(trim($_REQUEST["percent"])));
		
		break_qline($qline_id, $percent);
		break;
	
	case 'join_qlines':
		if (isset($_REQUEST["ids"])) $ids_string = htmlspecialchars(stripslashes(trim($_REQUEST["ids"])));
		$ids_array = explode(",", $ids_string);
		$line_1 = $ids_array[0];
		$line_2 = $ids_array[1];
		join_qlines($line_1, $line_2);
		break;

    default:
		/* Если все проверки пройдены, но ни один обработчик запроса не запустился */
		$answer_from_server['result'] = 'fail';
		$answer_from_server['info'] = 'Incorrect request name';
		break;
} /* Конец switch case */

if (isset($global_request_name)){

	$res = json_encode($answer_from_server,  JSON_UNESCAPED_SLASHES);
	echo $res;
}

?>

