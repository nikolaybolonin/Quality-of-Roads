
<?php
//<?php ini_set(�memory_limit�,�256M�); 

ini_set('max_execution_time', 600); 

function add_to_log($log_name, $str_to_add) {
    $myfile = file_put_contents($log_name, $str_to_add.PHP_EOL , FILE_APPEND | LOCK_EX);
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
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

function add_line($connection, $start_node_id, $end_node_id, $timestamp = NULL, $parent = NULL) {
    $sql_i = "INSERT INTO FLines (start_node_id, end_node_id, line_osm_date, line_osm_parent, pavement_type_id)
                VALUES ($start_node_id, $end_node_id, '$timestamp', '$parent', 1)";
    //TODO same as weith nodes - need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    if ($connection->query($sql_i) === TRUE) {
        echo "New line record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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

function set_line_surface_quality($connection, $id, $new_surface_quality) {
    $sql_u = "UPDATE flines SET surface_quality=$new_surface_quality WHERE line_id=$id";
    if ($connection ->query($sql_u) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

function set_line_uncrowded($connection, $id, $new_uncrowded) {
    $sql_u = "UPDATE flines SET uncrowded = $new_uncrowded WHERE line_id=$id";
    if ($connection ->query($sql_u) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

function create_tables($conn) {
    echo "creating lines table";
    $sql = "CREATE TABLE FLines (
        line_id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        start_node_id INT(8) NOT NULL, 
        end_node_id INT(8) NOT NULL,
        modified_date TIMESTAMP,
        surface_quality INT(1),
        uncrowded INT(1),
        pavement_type_id INT(2),
        line_osm_parent VARCHAR(20),
        line_osm_date TIMESTAMP)";

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

            $lat = $coord[0];
            $lon = $coord[1];
            echo "<br>==lat and long=<br>";
            echo $lat. " , ".$lon."<br>";
            echo "<br>=============<br>";
            $node_id = find_or_add_node($connection, $coord, $json_timestamp, $feature_id);

            if ($i>0) { 
                echo "<br>===========lines inc=================<br>";
                $prev_coord = $coords_arr[$i-1];
                $prev_lat = $prev_coord[0];
                $prev_lon = $prev_coord[1];
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

function upload_geodata($connection, $json){
    echo "<br>uploading some serious data<br>";
    //echo "<h2>parsing json</h2>";
    //echo "=============<br>";
    $json_data = json_decode($json, true);
    $json_osm3s = $json_data['osm3s'];
    $json_timestamp_string = $json_osm3s['timestamp_osm_base'];
    // if this works, need to remove some redundancy
    $json_timestamp = date("Y-m-d H:i:s", strtotime($json_timestamp_string));
    //echo "<br>=============<br>";
    //echo "timestamp_string = ". $json_timestamp_string;
    //echo "<br>timestamp = ". $json_timestamp;
    //echo "<br>=============<br>";
    $geoj_arr = $json_data['elements'];

    //to start with adding nodes to db

    //$this->db->trans_start();

     $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                VALUES ";
    foreach ($geoj_arr as $geoj_element) {
        $element_type = $geoj_element['type'];
        if ($element_type == "node") {

            $element_id = $geoj_element['id'];
            //echo "feature_id = ". $geoj_element['id'];
            //echo "<br>=============<br>";
           
            $lat = $geoj_element['lat'];
            $lon = $geoj_element['lon'];
            //echo "<br>==lat and long=<br>";
            //echo $lat. " , ".$lon."<br>";
            //echo "<br>=============<br>";
            //$coord = array($lat, $lon);
            //$node_id = find_or_add_node($connection, $coord, $json_timestamp, $element_id);

            $sql_i .= "($lat, $lon, '$json_timestamp', '$element_id'), ";
            $q_len = strlen($sql_i);
            if ($q_len > 10000) {
                //echo "<br>adding nodes<br>";
                $sql_i .= "(60.6, 30.3, '$json_timestamp', '777')";
                //echo $sql_i;
                if ($connection->query($sql_i) === TRUE) {
                    //echo "nodes inserted";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
                $sql_i = "INSERT INTO Nodes (latitude, longitude, node_osm_date, node_osm_parent)
                            VALUES ";
            }
        }
    }

    /*
    echo "<br>adding nodes<br>";
    $sql_i .= "(60.6, 30.3, '$json_timestamp', '777'), ";
    echo $sql_i;
    if ($connection->query($sql_i) === TRUE) {
        echo "nodes inserted";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    */
    echo "<br>nodes are done<br>";
    //now onto lines
    foreach ($geoj_arr as $geoj_element) {

        //$sql_i .= "($lat, $lon, '$json_timestamp', '$element_id'), ";
        //echo "<br>1<br>";
        $element_type = $geoj_element['type'];
        if ($element_type == "way") {
            //echo "<br>2<br>";

            $element_id = $geoj_element['id'];
            //echo "feature_id = ". $geoj_element['id'];
            //echo "<br>=============<br>";
            $nodes_arr = $geoj_element['nodes'];
            var_dump($nodes_arr);
            echo "<br>";
            for ($i = 0; $i < count($nodes_arr); ++$i) {
                //echo "<br>3<br>";
                $node_osm_id = $nodes_arr[$i];
                //echo "<br>==dropping coords=<br>";
                //var_dump($node_osm_id);
                //echo "<br>=============<br>";

                $node_id = get_node_id_by_osm_parent($connection, $node_osm_id);

                if ($i>0) { 
                    echo "<br>===========lines inc=================<br>";
                    $prev_node_osm_id = $nodes_arr[$i-1];

                    $prev_node_id = get_node_id_by_osm_parent($connection, $prev_node_osm_id);


                find_or_add_line($connection, $node_id, $prev_node_id, $json_timestamp, $element_id);
                }
            }
        }
    
    }
    echo "<br>= and lines are done =<br>";
    //$this->db->trans_complete();
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
    $sql_q = "SELECT ln.line_id as line_id,
                    ns.latitude as start_latitude,
                    ns.longitude as start_longitude,
                    ne.latitude as end_latitude,
                    ne.longitude as end_longitude,
                    ln.surface_quality as surface_quality,
                    pavement_translation.pavement_name as pavement_type
                    
                FROM nodes ns, nodes ne, flines ln
                LEFT JOIN pavement_translation
                ON ln.pavement_type_id = pavement_translation.pavement_id
                WHERE (
                    ln.start_node_id = ns.node_id AND
                    pavement_translation.language_id = 1 AND
                    ln.end_node_id = ne.node_id
                    AND ((
                        (ns.latitude < $lat_max AND
                        ne.latitude > $lat_min
                        ) OR (
                        ns.latitude > $lat_min AND
                        ne.latitude < $lat_max)
                    ) OR (
                        (ns.longitude < $lon_max AND
                        ne.longitude > $lon_min
                        ) OR (
                        ns.longitude > $lon_min AND
                        ne.longitude < $lon_max)))
                    )";

    $res = $connection->query($sql_q);
    $rows = array();
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            echo "id: " . $row["line_id"]. "; start node: ". $row["start_node_id"]. "; end node: " . $row["end_node_id"]. "; pavement type: " . $row["pavement_type"] ."<br>";
            //$rows['liness'][] = $row;
            /*
            $properties = array($row["line_id"],  $row["pavement_type"], $row["surface_quality"]);
            $geometry = array($row["start_latitude"], $row["start_longitude"], $row["end_latitude"], $row["end_longitude"]);
            $rows['liness'][] = array($properties, $geometry);
             */

            //TODO: remove direct "footway" string. gotta move it through db
            //TODO: add color through db
            $properties = array("name"                  => "footway",
                                "line_id"               => $row["line_id"],
                                "pavement_type"         => $row["pavement_type"],
                                "surface_quality"       => $row["surface_quality"]);
            /*
            $geometry = array("start_latitude"          => $row["start_latitude"],
                              "start_longitude"         => $row["start_longitude"],
                              "end_latitude"            => $row["end_latitude"],
                              "end_longitude"           => $row["end_longitude"]);
             */
            $start_coordinates = array($row["start_latitude"], $row["start_longitude"]);
            $end_coordinates =  array($row["end_latitude"], $row["end_longitude"]);

            $geometry = array("type"            => "MultiLineString",
                              "coordinates"     => array($start_coordinates, $end_coordinates));

            //$rows['liness'][] = array($properties, $geometry);
            /*
            $rows['features'][] = array("type"        => "Feature",
                                      "properties"  => $properties,
                                      "geometry"    => $geometry); */

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

function test_sql($connection) {
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

