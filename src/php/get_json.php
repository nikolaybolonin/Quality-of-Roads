
<?php
//<?php ini_set(�memory_limit�,�256M�); 

ini_set('max_execution_time', 600); 

//TODO: remove direct "footway" string. gotta move it through db
function create_json($nw_lat, $nw_lon) {
    require_once('passwords.php');

    $connection = new mysqli($db_server, $db_username, $db_password, $DB_DATA, $db_port);
    
    if (!$connection) {
        $answer_from_server['result'] = false;
        $answer_from_server['info'] = 'connection fail: ' . mysql_error();
    }
    
    $sql_q = "SELECT    qln.qline_id as line_id,
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
                        AND qln.qline_id in (
                            SELECT qline_id FROM line_sections
                            WHERE section_id in (
                                SELECT section_id FROM sections
                                WHERE (
                                    south_lat = $nw_lat AND
                                    west_lon = $nw_lon
                                ))))";

    $res = $connection->query($sql_q);
    $rows = array();
    if($res->num_rows > 0) {
        while($row = $res->fetch_assoc()) {
            $color_hex = "#ffffff";
            if ($row["color"] != null) {
                $color_hex = "#".$row["color"];
            }
            $properties = array("name"                  => "footway",
                                "id"               => $row["line_id"],
                                "pavement_type"         => $row["pavement_type"],
                                "quality"               => $row["surface_quality"],
                                "color"                 => $color_hex);

             
            //geosjon format has longitude, latidude order for some odd reason
            $start_coordinates = array(doubleval($row["start_longitude"]), doubleval($row["start_latitude"]));
            $end_coordinates =  array(doubleval($row["end_longitude"]), doubleval($row["end_latitude"]));

            //geojson wants array doubled -_-
            $geometry = array("type"            => "MultiLineString",
                              "coordinates"     => array(array($start_coordinates, $end_coordinates)));



            $rows[] = array("type"        => "Feature",
                            "properties"  => $properties,
                            "geometry"    => $geometry);

            $geodata_arr = array("type" => "FeatureCollection", "features" => $rows);
			
			$answer_from_server['result'] = 'success';
			$answer_from_server['info'] = 'created geojson';
        }
    } else {
		$answer_from_server['result'] = 'fail';
		$answer_from_server['info'] = 'no data - empty area';
        $geodata_arr = false;
    }

    $geodata_json = json_encode($geodata_arr);

    //this next part is adding borders
    //disabling it for now
    /*
    $opened_geodata_json = rtrim($geodata_json, "}");
    $opened_geodata_json = rtrim($opened_geodata_json, "]");
    //
    $borders = ', {"type":"Feature","properties":{"name":"footway","line_id":"-1","pavement_type":null,"quality":null,"color":"yellow"},"geometry":
                        {"type":"MultiLineString","coordinates":
                            [[[' . $lon_min . ',' . $lat_min . '],
                            [' . $lon_min . ',' . $lat_max . ']]]}}
                , {"type":"Feature","properties":{"name":"footway","line_id":"-1","pavement_type":null,"quality":null,"color":"yellow"},"geometry":
                        {"type":"MultiLineString","coordinates":
                            [[[' . $lon_min . ',' . $lat_max . '],
                            [' . $lon_max . ',' . $lat_max . ']]]}}
                , {"type":"Feature","properties":{"name":"footway","line_id":"-1","pavement_type":null,"quality":null,"color":"yellow"},"geometry":
                        {"type":"MultiLineString","coordinates":
                            [[[' . $lon_max . ',' . $lat_max . '],
                            [' . $lon_max . ',' . $lat_min . ']]]}}
                , {"type":"Feature","properties":{"name":"footway","line_id":"-1","pavement_type":null,"quality":null,"color":"yellow"},"geometry":
                        {"type":"MultiLineString","coordinates":
                            [[[' . $lon_max . ',' . $lat_min . '],
                            [' . $lon_min . ',' . $lat_min . ']]]}}]}';
    $geodata_json = $opened_geodata_json . $borders;
    //return json_encode(array("type" => "FeatureCollection", "features" => $rows));
    */
    
    
    //trying to get it with array to encode through services
    return $geodata_arr;
    //return $geodata_json;
}

if (isset($_REQUEST["request"])) $global_request_name = htmlspecialchars(stripslashes(trim($_REQUEST["request"])));

switch ($global_request_name) {

    case 'select_geojson':

        if (isset($_REQUEST["nwlng"])) $nwlon = htmlspecialchars(stripslashes(trim($_REQUEST["nwlng"])));
        if (isset($_REQUEST["nwlat"])) $nwlat = htmlspecialchars(stripslashes(trim($_REQUEST["nwlat"])));

        $jsonGeoData_arr = create_json($nwlat, $nwlon);
        
        if ($jsonGeoData_arr != false){
			$answer_from_server['result'] = 'success';
			$answer_from_server['data']['geojson'] = $jsonGeoData_arr;
			//$answer_from_server['data']['bounds'] = $bounds;
		}
		if (!isset($answer_from_server['data'])) {
			$answer_from_server['result'] = 'fail';
			$answer_from_server['data'] = false;
		}
        break;

        echo $jsonGeoData;
        
        break;

    default:
		/* Если все проверки пройдены, но ни один обработчик запроса не запустился */
		$answer_from_server['result'] = 'fail';
		$answer_from_server['info'] = 'Incorrect request name';
		break;
} /* Конец switch case */

// print_r($answer_from_server['data']); echo '<br><br><br>';
if (isset($global_request_name)){

	$res = json_encode($answer_from_server,  JSON_UNESCAPED_SLASHES);
	echo $res;
}

?>

