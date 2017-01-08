<?php

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
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];
    $sql_q = "SELECT id, latitude, longitude FROM Nodes WHERE latitude=$latitude AND longitude=$longitude";
    $res = $connection->query($sql_q);
    if($res->num_rows <= 0) {
        return -1;
    } else {
        $row = $res->fetch_assoc();
        return $row["id"];
    }
}

//returns line id from db if such line exists. if it does not, -1 is returned
function get_line_id_by_nodes($connection, $start_node_id, $end_node_id) {
    $sql_q = "SELECT id, start_node_id, end_node_id FROM FLines WHERE (start_node_id=$end_node_id AND end_node_id=$start_node_id) OR (start_node_id=$start_node_id AND end_node_id=$end_node_id)";
    $res = $connection->query($sql_q);
    if($res->num_rows <= 0) {
        return -1;
    } else {
        $row = $res->fetch_assoc();
        return $row["id"];
    }
}

function add_node($connection, $node_coords, $timestamp = NULL, $parent = NULL) {
    $latitude = $node_coords[0];
    $longitude = $node_coords[1];

    //TODO need to decide if I need to workaround timestamp and parent default values (ignoring them and writing NULLs is different)
    $sql_i = "INSERT INTO Nodes (latitude, longitude, osm_date, osm_parent)
                VALUES ($latitude, $longitude, '$timestamp', '$parent')";
    /*$sql_i = "INSERT INTO Nodes (latitude, longitude)
        VALUES ($latitude, $longitude)"; */
    if ($connection->query($sql_i) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

}

function add_line($connection, $start_node_id, $end_node_id, $timestamp = NULL, $parent = NULL) {
    $sql_i = "INSERT INTO FLines (start_node_id, end_node_id, osm_date, osm_parent, pavement_type_id)
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
    $sql_u = "UPDATE flines SET surface_quality=$new_surface_quality WHERE id=$id";
    if ($connection ->query($sql_u) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

function set_line_uncrowded($connection, $id, $new_uncrowded) {
    $sql_u = "UPDATE flines SET uncrowded = $new_uncrowded WHERE id=$id";
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
        osm_parent VARCHAR(20),
        osm_date TIMESTAMP)";

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
        osm_parent VARCHAR(20),
        osm_date TIMESTAMP)";

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
        language_id INT(3) UNSIGNED
        pavement_name VARCHAR(30) NOT NULL,
        pavement_descr VARCHAR(100))";

    if ($conn->query($sql) === TRUE) {
        echo "table Pavement translation created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }

    echo "creating Languagestable";
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

    //and to insert default pavement type (TODO: make this in a normal way)
    $sql_i = "INSERT INTO map_01.pavement (id, pavement_name, pavement_descr) VALUES (1, 'tiles', 'tiles description');";
    if ($conn->query($sql_i) === TRUE) {
        echo "<br>def. pavement inserted<br>";
    } else {
        echo "error creating table: " . $conn->error;
    }
/*
    $conn2 = $conn;

    set_line_uncrowded($conn2, 1, 8);
    $conn2 = $conn;

    set_line_surface_quality($conn2, 1, 7);
 */
}

function find_or_add_node($connection, $node_coords, $timestamp, $parent) {
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

function upload_json($connection, $json){
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
                    pavement.pavement_name as pavement_type
                FROM nodes ns, nodes ne, flines ln
                LEFT JOIN pavement
                ON ln.pavement_type_id = pavement.pavement_id
                WHERE (
                    ln.start_node_id = ns.node_id AND
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
            $rows['liness'][] = $row;
        }
    } else {
        echo "0 results";
    }
    echo "<br>===== rows aquired =====<br>";
    print json_encode($rows);
}

function test_sql($connection) {
    $sql_q = "SELECT id, latitude, longitude FROM nodes";
    $res = $connection->query($sql_q);
    
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            echo "id: " . $row["id"]. " - latitude: ". $row["latitude"]. " - longitude: " . $row["longitude"]. "<br>";
        }
    } else {
        echo "0 results";
    }
}
?>
