<?php

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

function show_json($json) {
    echo "<h2>loading json</h2>";
    echo "===============echo===============<br>";
    echo (json_decode($json, true));
    echo "<br>===============var-dump===============<br>";
    var_dump(json_decode($json, true));
    echo "<br>=============<br>";
}

function create_osm_tables($conn) {
    echo "creating osm lines table";
    $sql = "CREATE TABLE OSMLines (
        id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        start_node_id INT(8) NOT NULL, 
        end_node_id INT(8) NOT NULL,
        modified_date TIMESTAMP)";

    if ($conn->query($sql) === TRUE) {
        echo "table OSMLines created successfully";
    } else {
        echo "error creating table: " . $conn->error;
    }
    echo "<br>";

    echo "creating osm nodes table";
    $sql ="CREATE TABLE OSMNodes (
        id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        latit DOUBLE PRECISION(9, 7) NOT NULL,
        longit DOUBLE PRECISION(10, 7) NOT NULL,
        modified_date TIMESTAMP)";


    if ($conn->query($sql) === TRUE) {
        echo "table OSMNodes created successfully";
    } else {
        echo "error creating table: " . $conn->error;
        }
}

function add_node_2_osmnodes($connection, $lat, $lon) {
    $sql_q = "SELECT id, latit, longit FROM OSMNodes WHERE latit=$lat AND longit=$lon";
    $res = $connection->query($sql_q);
    echo "<br>==result=<br>";
    var_dump($res);
    echo"<br>===sel result dump end===<br>";
    if($res->num_rows <= 0) {
        $sql_i = "INSERT INTO OSMNodes (latit, longit)
            VALUES ($lat, $lon)";
        if ($connection->query($sql_i) === TRUE) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "exists already";
    }
    $res = $connection->query($sql_q);
    $row = $res->fetch_assoc();
    echo "<br>==node after ins=<br>";
    echo "id: " . $row["id"]. " - latitude: ". $row["latit"]. " - longitude: " . $row["longit"]. "<br>";
    echo"<br>==node after ins===<br>";
    return $row["id"];
}

function add_2_osmlines($connection, $start_node_id, $end_node_id) {
    $sql_lines_q = "SELECT id, start_node_id, end_node_id FROM OSMLines WHERE (start_node_id=$end_node_id AND end_node_id=$start_node_id) OR (start_node_id=$start_node_id AND end_node_id=$end_node_id)";
    $res = $connection->query($sql_lines_q);
    echo "<br>==result=<br>";
    var_dump($res);
    echo"<br>===sel result dump end===<br>";

    if($res->num_rows <= 0) {
        $sql_lines_i = "INSERT INTO OSMLines (start_node_id, end_node_id)
                        VALUES ($start_node_id, $end_node_id)";
        if ($connection->query($sql_lines_i) === TRUE) {
            echo "New line record created successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "line exists already";
    }
}

function upload_json($connection, $json){
    echo "<h2>parsing json</h2>";
    echo "=============<br>";
    $json_data = json_decode($json, true);
    $geoj_arr = $json_data['features'];

    foreach ($geoj_arr as $geoj_feature) {
        echo $geoj_feature['id'];
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
            $node_id = add_node_2_osmnodes($connection, $lat, $lon);

            if ($i>0) {
                echo "<br>===========lines inc=================<br>";
                $prev_coord = $coords_arr[$i-1];
                $prev_lat = $prev_coord[0];
                $prev_lon = $prev_coord[1];
                $row = $res->fetch_assoc(); */
                $prev_node_id = add_node_2_osmnodes($connection, $prev_lat, $prev_lon);
                echo "<br>========prev node========<br>";
                echo "pr " . $prev_lat . "_" . $prev_lon . "cur " . $lat . "_" . $lon."<br>";
                echo $prev_node_id . " ==== " . $node_id;
                echo "<br>=============================<br>";

                add_2_osmlines($connection, $node_id, $prev_node_id);
            }
        }
    }
}

function test_sql($connection) {
    $sql_q = "SELECT id, latit, longit FROM osmnodes";
    $res = $connection->query($sql_q);
    
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            echo "id: " . $row["id"]. " - latitude: ". $row["latit"]. " - longitude: " . $row["longit"]. "<br>";
        }
    } else {
        echo "0 results";
    }
}
?>
