
<?php
//<?php ini_set(�memory_limit�,�256M�); 

ini_set('max_execution_time', 600); 

function add_to_log($log_name, $str_to_add) {
    $myfile = file_put_contents($log_name, $str_to_add.PHP_EOL , FILE_APPEND | LOCK_EX);
}

function generate_random_string ($str_length) {
    $rand_char_list = "1234567890qwertyuiopasdfghjklzxcvbnm";
    $rand_char_num = strlen($rand_char_list);
    $rand_string = '';
    for ($i = 0; $i < $str_length; $i++) {
        $rand_string .= $rand_char_list[rand(0, $rand_char_num - 1)];
    }
    return $rand_string;
}

//gets an array of two nodes, returns those sorted
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

function clear_db($connection) {
    $connection->query('SET foreign_key_checks = 0');
    if ($result = $connection->query("SHOW TABLES")) {
        while($row = $result->fetch_array(MYSQLI_NUM)) {
            $connection->query('DROP TABLE IF EXISTS '.$row[0]);
        }
    }
    $connection->query('SET foreign_key_checks = 1');
    $connection->close();
}

//returns node id from db if such node exists. if it does not, -1 is returned
function get_node_id_by_coords($connection, $node_coords) {
    echo "<br>== getting node id by coords==";
    var_dump($node_coords);
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];
    $sql_q = "SELECT node_id FROM Nodes WHERE latitude=$latitude AND longitude=$longitude";
    $res = $connection->query($sql_q);
    echo "===========and we get =================";
    //echo $res;

    if($res->num_rows <= 0) {
        echo "== and it's just another -1 ==<br>";
        return -1;
    } else {
        $row = $res->fetch_assoc();
        echo " == and it's different!!!-! ==<br>";
        return $row["node_id"];
    }
}

function get_node_id_by_osm_parent($connection, $osm_parent) {
    echo "<br>== getting node id by osm parent==<br>";
    var_dump($node_coords);
    $sql_q = "SELECT node_id FROM Nodes WHERE node_osm_parent=$osm_parent";
    $res = $connection->query($sql_q);
    echo "===========and we get =================";
    echo "<br>osm_parent = ". $osm_parent . "<br>";

    if($res->num_rows <= 0) {
        echo "== and it's just another -1 ==<br>";
        return -1;
    } else {
        $row = $res->fetch_assoc();
        echo "<br>line id = ". $row["node_id"] . "<br>";
        return $row["node_id"];
    }
}

//returns line id from db if such line exists. if it does not, -1 is returned
function get_line_id_by_nodes($connection, $start_node_id, $end_node_id) {
    $sql_q = "SELECT line_id, start_node_id, end_node_id FROM FLines WHERE (start_node_id=$end_node_id AND end_node_id=$start_node_id) OR (start_node_id=$start_node_id AND end_node_id=$end_node_id)";
    $res = $connection->query($sql_q);
    if($res->num_rows <= 0) {
        return -1;
    } else {
        $row = $res->fetch_assoc();
        return $row["line_id"];
    }
}

function add_node($connection, $node_coords, $timestamp = NULL, $parent = NULL) {
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];

    //TODO need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    echo "lat = " . $latitude . "; lon = " . $longitude . "; time = " . $timestamp . "; parent = " . $parent . "<br>";
    $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                VALUES ($latitude, $longitude, '$timestamp', '$parent')";
    /*$sql_i = "INSERT INTO Nodes (latitude, longitude)
        VALUES ($latitude, $longitude)"; */
    if ($connection->query($sql_i) === TRUE) {
        echo "New record for the node ". $latitude . " - " . $longitude . " was was created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }
}

function add_line($connection, $start_node_id, $end_node_id, $timestamp = NULL, $parent = NULL) {
    $sorted_nodes = sort_nodes(array($start_node_id, $end_node_id));
    $start_node_id = $sorted_nodes[0];
    $end_node_id = $sorted_nodes[1];
    $sql_i = "INSERT INTO BaseLines (bline_start_node_id, bline_end_node_id, line_osm_date, line_osm_parent)
                VALUES ($start_node_id, $end_node_id, '$timestamp', '$parent')";
    //TODO same as weith nodes - need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    if ($connection->query($sql_i) === TRUE) {
        echo "New line record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }
}

function show_json($json) {
    echo "<h2>loading json</h2>";
    echo "===============echo===============<br>";
    echo (json_decode($json, true));
    echo "<br>===============var-dump===============<br>";
    var_dump(json_decode($json, true));
    echo "<br>=============<br>";
}

function set_qpart_surface_quality($connection, $id, $new_surface_quality) {
    $sql_u = "UPDATE qparts SET surface_quality=$new_surface_quality WHERE qpart_id=$id";
    if ($connection ->query($sql_u) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $connection->error;
    }
}

function set_line_uncrowded($connection, $id, $new_uncrowded) {
    $sql_u = "UPDATE baselines SET uncrowded = $new_uncrowded WHERE line_id=$id";
    if ($connection ->query($sql_u) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $connection->error;
    }
}

function create_tables($conn) {
    echo "creating base lines table";
    $sql = "CREATE TABLE BaseLines (
        bline_id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        bline_start_node_id INT(8) NOT NULL, 
        bline_end_node_id INT(8) NOT NULL,
        modified_date TIMESTAMP,
        uncrowded INT(1),
        line_osm_parent VARCHAR(20),
        line_osm_date TIMESTAMP)";

    if ($conn->query($sql) === TRUE) {
        echo "table Lines created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }
    echo "<br>";

    echo "creating quality lines table";
    $sql = "CREATE TABLE QLines (
        qline_id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        parent_line_id INT(8) NOT NULL, 
        width FLOAT(3,1),
        qline_start_node_id INT(8) NOT NULL, 
        qline_end_node_id INT(8) NOT NULL,
        surface_quality INT(1),
        pavement_type_id INT(2))";

    if ($conn->query($sql) === TRUE) {
        echo "table Lines created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }
    echo "<br>";

    echo "creating nodes table";
    $sql ="CREATE TABLE Nodes (
        node_id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        latitude DOUBLE PRECISION(9, 7) NOT NULL,
        longitude DOUBLE PRECISION(10, 7) NOT NULL,
        modified_date TIMESTAMP,
        node_osm_parent VARCHAR(20),
        node_osm_date TIMESTAMP)";

    if ($conn->query($sql) === TRUE) {
        echo "table Nodes created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }

    echo "creating Pavement table";
    $sql ="CREATE TABLE Pavement (
        pavement_id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY)";

    if ($conn->query($sql) === TRUE) {
        echo "table Pavement created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }
    
    echo "creating Pavement translation table";
    $sql ="CREATE TABLE Pavement_translation (
        pavement_translation_id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        pavement_id INT(2) UNSIGNED,
        language_id INT(3) UNSIGNED,
        pavement_name VARCHAR(30) NOT NULL,
        pavement_descr VARCHAR(100))";

    if ($conn->query($sql) === TRUE) {
        echo "table Pavement translation created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }

    echo "creating Languages table";
    $sql ="CREATE TABLE Languages (
        language_id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        language_en_name VARCHAR(30) NOT NULL,
        language_en_short_name VARCHAR(5),
        language_local_name VARCHAR(30))";

    if ($conn->query($sql) === TRUE) {
        echo "table Languages created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }

    echo "creating Quality table";
    $sql ="CREATE TABLE Quality (
        quality_id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        quality_value INT(2) UNSIGNED,
        quality_color_hex VARCHAR(6))";

    if ($conn->query($sql) === TRUE) {
        echo "table quality created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }

    echo "creating Quality translation table";
    $sql ="CREATE TABLE Quality_translation (
        quality_translation_id INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        quality_id INT(1) UNSIGNED,
        language_id INT(3) UNSIGNED,
        quality_descr VARCHAR(100))";

    if ($conn->query($sql) === TRUE) {
        echo "table quality translations created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }
}

function find_or_add_node($connection, $node_coords, $timestamp, $parent) {
    //echo "<br> == finding or adding node by ==<br>";
    $node_id = get_node_id_by_coords($connection, $node_coords);
    if ($node_id == -1) {
        add_node($connection, $node_coords, $timestamp, $parent);
        $node_id = get_node_id_by_coords($connection, $node_coords);
    }
    return $node_id;
}

function find_or_add_line($connection, $start_node_id, $end_node_id, $timestamp, $parent) {
    $line_id = get_line_id_by_nodes($connection, $start_node_id, $end_node_id, $timestamp, $parent);
    if ($line_id == -1) {
        add_line($connection, $start_node_id, $end_node_id, $timestamp, $parent);
        $line_id = get_line_id_by_nodes($connection, $start_node_id, $end_node_id, $timestamp, $parent);
    }
    return $line_id;
}

//that's for a small json that overpass outputs
function upload_geojson($connection, $json){
    echo "<h2>parsing json</h2>";
    echo "=============<br>";
    $json_data = json_decode($json, true);
    $json_timestamp_string = $json_data['timestamp'];
    // if this works, need to remove some redundancy
    $json_timestamp = date("Y-m-d H:i:s", strtotime($json_timestamp_string));
    echo "<br>=============<br>";
    echo "timestamp_string = ". $json_timestamp_string;
    echo "<br>timestamp = ". $json_timestamp;
    echo "<br>=============<br>";
    $geoj_arr = $json_data['features'];

    foreach ($geoj_arr as $geoj_feature) {
        $feature_id = $geoj_feature['id'];
        echo "feature_id = ". $geoj_feature['id'];
        echo "<br>=============<br>";
        $geometry_json = $geoj_feature['geometry'];
        $coords_arr = $geometry_json['coordinates'];
        for ($i = 0; $i < count($coords_arr); ++$i) {
            $coord = $coords_arr[$i];
            echo "<br>==dropping coords=<br>";
            var_dump($coord);
            echo "<br>=============<br>";

            //ffs. why is the order reversed?
            $lat = $coord[1];
            $lon = $coord[0];
            $coord = array($lat, $lon);
            echo "<br>==lat and long=<br>";
            echo $lat. " , ".$lon."<br>";
            echo "<br>=============<br>";
            $node_id = find_or_add_node($connection, $coord, $json_timestamp, $feature_id);

            if ($i>0) { 
                echo "<br>===========lines inc=================<br>";
                $prev_coord = $coords_arr[$i-1];
                $prev_lat = $prev_coord[1];
                $prev_lon = $prev_coord[0];
                $prev_coord = array($prev_lat, $prev_lon);
//                $row = $res->fetch_assoc(); 
                $prev_node_id = find_or_add_node($connection, $prev_coord, $json_timestamp, $feature_id);
                echo "<br>========prev node========<br>";
                echo "pr " . $prev_lat . "_" . $prev_lon . "cur " . $lat . "_" . $lon."<br>";
                echo $prev_node_id . " ==== " . $node_id;
                echo "<br>=============================<br>";

                find_or_add_line($connection, $node_id, $prev_node_id, $json_timestamp, $feature_id);
            }
        }
    }
}

//that's for raw data that overpass produces
function upload_basedata($connection, $json){
    echo "<br>uploading some serious data<br>";
    $json_data = json_decode($json, true);
    $json_osm3s = $json_data['osm3s'];
    $json_timestamp_string = $json_osm3s['timestamp_osm_base'];
    $json_timestamp = date("Y-m-d H:i:s", strtotime($json_timestamp_string));
    $geoj_arr = $json_data['elements'];

     //next part is for adding a test node to get its id and follow it with mass nodes so that new ids can be projected
    $rand_osm_parent = generate_random_string(18);

    $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
        VALUES (60.6, 30.3, '$json_timestamp', '$rand_osm_parent')";

    echo "<br>";
    //echo $sql_i;
    echo "<br>";
    if ($connection->query($sql_i) === TRUE) {
        echo "test node inserted";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }

    $sql_q = "SELECT node_id FROM Nodes WHERE node_osm_parent='$rand_osm_parent'";
    $res = $connection->query($sql_q);

    if($res->num_rows <= 0) {
        echo "== failed inserting test node, apparentrly ==<br>";
    } else {
        $row = $res->fetch_assoc();
        echo "<br>line id = ". $row["node_id"] . "<br>";
        $last_node_id = $row["node_id"];
    }

    $node_counter = $last_node_id + 1;
    $node_ids_for_osm_parents = array();


    $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                VALUES ";
    foreach ($geoj_arr as $geoj_element) {
        $element_type = $geoj_element['type'];
       

        if ($element_type == "node") {

            $element_id = $geoj_element['id'];
           
            $lat = $geoj_element['lat'];
            $lon = $geoj_element['lon'];

            $node_ids_for_osm_parents[(string)$element_id] = $node_counter;
            $node_counter += 1;

            $sql_i .= "($lat, $lon, '$json_timestamp', '$element_id'), ";
            $q_len = strlen($sql_i);
            if ($q_len > 10000) {
                $sql_i = rtrim($sql_i, ", ");
                if ($connection->query($sql_i) === TRUE) {
                    //echo "nodes inserted";
                } else {
                    echo "<br>";
                    //echo $sql_i;
                    echo "<br>";
                    echo "Error: " . $sql . "<br>" . $connection->error;
                }
                $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                            VALUES ";
            }
        }
    }
    $sql_i = rtrim($sql_i, ", ");
    if ($connection->query($sql_i) === TRUE) {
        //echo "nodes inserted";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }

    //cleaning up test node used to get counter value
    $sql_d = "DELETE FROM Nodes WHERE node_osm_parent='$rand_osm_parent'";
    if ($connection->query($sql_d) === TRUE) {
       echo "<br>anchor node removed<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }


    echo "<br>";
    echo "<br>nodes are done<br>";
    

    //adding extra columns
    $sql_addc = "ALTER TABLE BaseLines
                    ADD COLUMN pavement_type_id INT(2) after line_osm_date,
                    ADD COLUMN quality_value INT(1) after pavement_type_id,
                    ADD COLUMN width FLOAT(3,1) after quality_value";
    
    if ($connection->query($sql_addc) === TRUE) {
        echo "columns added";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_addc;
        echo "<br>";
    }
    
    //now onto lines
    $sql_ib = "INSERT INTO BaseLines (bline_start_node_id, bline_end_node_id, line_osm_date, line_osm_parent, pavement_type_id, quality_value, width)
                VALUES ";

    foreach ($geoj_arr as $geoj_element) {

        $element_type = $geoj_element['type'];
        if ($element_type == "way") {

            $element_id = $geoj_element['id'];
            $nodes_arr = $geoj_element['nodes'];
            $element_tags = $geoj_element['tags'];
            
            //getting width
            $width = NULL;
            if (array_key_exists('width', $element_tags)) {
                $width = (float)$element_tags['width'];
            }
            
            //getting some smoothness
            $quality_value = NULL;
            if (array_key_exists('smoothness', $element_tags)) {
                $quality_string = $element_tags['smoothness'];
                switch ($quality_string) {
                    case 'excellent':
                        $quality_value = 4;
                        break;
                    case 'good':
                        $quality_value = 3;
                        break;
                    case 'intermediate':
                        $quality_value = 2;
                        break;
                    case 'bad':
                        $quality_value = 1;
                        break;
                    case 'very_bad':
                        $quality_value = 0;
                        break;
                    case 'horrible':
                        $quality_value = 0;
                        break;
                    case 'very_horrible':
                        $quality_value = 0;
                        break;
                    case 'impassable':
                        $quality_value = 0;
                        break;
                }
            }
            
            //getting pavement type id:
            $pavement_type_id = NULL;
            if (array_key_exists('surface', $element_tags)) {
                $pavement_type_string = $element_tags['surface'];
                switch ($pavement_type_string) {
                    case 'asphalt':
                        $pavement_type_id = 1;
                        break;
                    case 'cobblestone':
                        $pavement_type_id = 2;
                        break;
                    case 'compacted':
                        $pavement_type_id = 3;
                        $quality_value = 1;
                        break;
                    case 'concrete':
                        $pavement_type_id = 4;
                        break;
                    case 'dirt':
                        $pavement_type_id = 3;
                        $quality_value = 0;
                        break;
                    case 'fine_gravel':
                        $pavement_type_id = 6;
                        $quality_value = 2;
                        break;
                    case 'granite':
                        $pavement_type_id = 7;
                        break;
                    case 'grass':
                        $pavement_type_id = 5;
                        $quality_value = 0;
                        break;
                    case 'gravel':
                        $pavement_type_id = 6;
                        $quality_value = 2;
                        break;
                    case 'paving_stones':
                        $pavement_type_id = 2;
                        break;
                    case 'pebblestone':
                        $pavement_type_id = 2;
                        $quality_value = 1;
                        break;
                    case 'sand':
                        $pavement_type_id = 9;
                        $quality_value = 0;
                        break;
                }
            }
            
            echo "<br>";
            for ($i = 0; $i < count($nodes_arr); ++$i) {
                $node_osm_id = (string)$nodes_arr[$i];
                $node_id = $node_ids_for_osm_parents[$node_osm_id];

                if ($i>0) { 
                    $prev_node_osm_id = (string)$nodes_arr[$i-1];

                    $prev_node_id = $node_ids_for_osm_parents[$prev_node_osm_id];

                    //$sql_ib .= "($node_id, $prev_node_id, '$json_timestamp', '$element_id', '$pavement_type_id', '$quality_value', '$width'), ";
                    $sql_ib .= "($node_id, $prev_node_id, '$json_timestamp', '$element_id', ";
                    if ( $pavement_type_id == null) {
                        $sql_ib .= "NULL, "; 
                    } else {
                        $sql_ib .= "'$pavement_type_id',";
                    }
                    if ( $quality_value == null) {
                        $sql_ib .= "NULL, "; 
                    } else {
                        $sql_ib .= "'$quality_value',";
                    }
                    if ( $width == null) {
                        $sql_ib .= "NULL), "; 
                    } else {
                        $sql_ib .= "'$width'),";
                    }
                    $q_len = strlen($sql_ib);
                    if ($q_len > 1000) {
                        $sql_ib = rtrim($sql_ib, ", ");
                        if ($connection->query($sql_ib) === TRUE) {
                            echo "base lines inserted";
                        } else {
                            echo "Error: " . $sql . "<br>" . $connection->error;
                            echo "<br>";
                            echo $sql_ib;
                            echo "<br>";
                        }

                        $sql_ib = "INSERT INTO BaseLines (bline_start_node_id, bline_end_node_id, line_osm_date, line_osm_parent, pavement_type_id, quality_value, width)
                                    VALUES ";
                    }
                }
            }
        }
    
    }
    $sql_ib = rtrim($sql_ib, ", ");
    if ($connection->query($sql_ib) === TRUE) {
        echo "base lines inserted";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_ib;
        echo "<br>";
    }

    $sql_iq =  "INSERT INTO QLines (parent_line_id) SELECT (bline_id) FROM BaseLines";
    if ($connection->query($sql_iq) === TRUE) {
        echo "qlines parents copied from blines";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_iq;
        echo "<br>";
    }
    
    //TODO:gotta understand why this does not work
    /*
    $sql_iq =  "UPDATE QLines AS ql
                LEFT JOIN BaseLines AS bl ON ql.parent_line_id = bl.bline_id 
                SET ql.qline_start_node_id = bl.bline_start_node_id,
                    ql.qline_end_node_id = bl.bline_end_node_id,
                    ql.pavement_type_id = bl.pavement_type_id,
                    ql.surface_quality = bl.quality_value,
                    ql.width = bl.width,";


    if ($connection->query($sql_iq) === TRUE) {
        echo "qlines nodes copied from blines";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_iq;
        echo "<br>";
    }
    */
    
    $sql_iq =  "UPDATE QLines AS ql
                LEFT JOIN BaseLines AS bl ON ql.parent_line_id = bl.bline_id 
                SET ql.qline_start_node_id = bl.bline_start_node_id,
                    ql.qline_end_node_id = bl.bline_end_node_id,
                    ql.pavement_type_id = bl.pavement_type_id,
                    ql.surface_quality = bl.quality_value,
                    ql.width = bl.width";


    if ($connection->query($sql_iq) === TRUE) {
        echo "qlines nodes copied from blines";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_iq;
        echo "<br>";
    }
    
    
    
    $sql_delc = "ALTER TABLE BaseLines
                    DROP COLUMN pavement_type_id,
                    DROP COLUMN quality_value,
                    DROP COLUMN width";
    
    if ($connection->query($sql_delc) === TRUE) {
        echo "columns dropped";
    } else {
        echo "Error: " . $sql . "<br>" . $connection->error;
        echo "<br>";
        echo $sql_delc;
        echo "<br>";
    }
}

//TODO: remove direct "footway" string. gotta move it through db
function create_json($connection, $coords_one, $coords_two) {

    $lat_one = $coords_one[0];
    $lon_one = $coords_one[1];
    $lat_two = $coords_two[0];
    $lon_two = $coords_two[1];

    if ($lat_one > $lat_two) {
        $lat_max = $lat_one;
        $lat_min = $lat_two;
    } else {
        $lat_max = $lat_two;
        $lat_min = $lat_one;
    }

    if ($lon_one > $lon_two) {
        $lon_max = $lon_one;
        $lon_min = $lon_two;
    } else {
        $lon_max = $lon_two;
        $lon_min = $lon_one;
    }
/*
    $lat_max = 30.3556109;
    $lat_min = 30.3556100;
    $lon_max = 59.9440496;
    $lon_min = 59.9440490;
 */
   
    $sql_q = "SELECT qln.qline_id as line_id,
                    ns.latitude as start_latitude,
                    ns.longitude as start_longitude,
                    ne.latitude as end_latitude,
                    ne.longitude as end_longitude,
                    qln.surface_quality as surface_quality,
                    quality.quality_color_hex as color,
                    pavement_translation.pavement_name as pavement_type
                    
                FROM nodes ns, nodes ne, qlines qln
                LEFT JOIN pavement_translation
                ON qln.pavement_type_id = pavement_translation.pavement_id AND
                    pavement_translation.language_id = 1
                LEFT JOIN quality
                ON qln.surface_quality = quality.quality_value
                WHERE (
                    qln.qline_start_node_id = ns.node_id  AND
                    qln.qline_end_node_id = ne.node_id
                    AND ((
                        (ns.latitude < $lat_max AND
                        ne.latitude > $lat_min
                        ) OR (
                        ns.latitude > $lat_min AND
                        ne.latitude < $lat_max)
                    ) AND (
                        (ns.longitude < $lon_max AND
                        ne.longitude > $lon_min
                        ) OR (
                        ns.longitude > $lon_min AND
                        ne.longitude < $lon_max)))
                    )";

    $res = $connection->query($sql_q);
    echo "<br>";
    echo $sql_q;
    echo "<br>";
    echo "<br>";
    var_dump($res);
    echo "<br>";
    $rows = array();
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            echo "id: " . $row["line_id"]. "; start node: (". $row["start_latitude"]. ", ". $row["start_longitude"] . "); end node: (". $row["end_latitude"]. ", ". $row["end_longitude"] . "); pavement type: " . $row["pavement_type"] ."; color: " . $row["color"] . "<br>";
            //null color is white, it means unknown
            $color_hex = "#ffffff";
            if ($row["color"] != null) {
                $color_hex = "#".$row["color"];
            }
            $properties = array("name"                  => "footway",
                                "line_id"               => $row["line_id"],
                                "pavement_type"         => $row["pavement_type"],
                                "quality"               => $row["surface_quality"],
                                "color"                 => $color_hex);
            //
            //$geometry = array("start_latitude"          => $row["start_latitude"],
              //                "start_longitude"         => $row["start_longitude"],
                //              "end_latitude"            => $row["end_latitude"],
                  //            "end_longitude"           => $row["end_longitude"]);
             
            //geosjon format has longitude, latidude order for some odd reason
            $start_coordinates = array(doubleval($row["start_longitude"]), doubleval($row["start_latitude"]));
            $end_coordinates =  array(doubleval($row["end_longitude"]), doubleval($row["end_latitude"]));

            //geojson wants array doubled -_-
            $geometry = array("type"            => "MultiLineString",
                              "coordinates"     => array(array($start_coordinates, $end_coordinates)));



            $rows[] = array("type"        => "Feature",
                            "properties"  => $properties,
                            "geometry"    => $geometry);

        }
    } else {
        echo "0 results";
    }


    echo "<br>===== rows aquired =====<br>";
    print json_encode(array("type" => "FeatureCollection", "features" => $rows));
}

function test($connection) {
    $string = "asdfb,";
    echo "<br>";
    echo $string;
    echo "<br>";
    echo  rtrim($string, ",");
    echo "<br>";


    $sql_q = "SELECT node_id, latitude, longitude FROM nodes";
    $res = $connection->query($sql_q);
    
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            echo "id: " . $row["node_id"]. " - latitude: ". $row["latitude"]. " - longitude: " . $row["longitude"]. "<br>";
        }
    } else {
        echo "0 results";
    }
}
?>

