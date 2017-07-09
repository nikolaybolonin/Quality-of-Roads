
<?php
//<?php ini_set(�memory_limit�,�256M�); 

ini_set('max_execution_time', 600); 

//TODO: decide on empty pavement value
function surfaces_dropdown($ids, $lang) {

    require_once('passwords.php');
    $connection = new mysqli($db_server, $db_username, $db_password, $DB_DATA, $db_port);
    
    if (!$connection) {
        $answer_from_server['result'] = false;
        $answer_from_server['info'] = 'Ошибка соединения: ' . mysql_error();
    }
    
    //first to get lang id
    //i assume it will be given as language_en_short_name
    $sql_q_lang = "SELECT language_id
                    FROM languages
                    WHERE language_en_short_name = '$lang'";
                    
    $res_lang = $connection->query($sql_q_lang);
    if($res_lang->num_rows ==1) {
        while($row = $res_lang->fetch_assoc()) {
            $lang_id = $row["language_id"];
        }
    }
    //echo $lang_id;
    
    // now to get all the options for pavement surface and making
    // dropdown list with nothing selected
    $sql_q_all = "SELECT pavement_id, pavement_name
                FROM pavement_translation 
                WHERE language_id = $lang_id";
    
    $dropdown = '<select id="surface_drop"><option value="-1">...</option>';

    $res_all = $connection->query($sql_q_all);
    if($res_all->num_rows >0) {
        while($row = $res_all->fetch_assoc()) {
            $pavement_id = $row["pavement_id"];
            $pavement_name = $row["pavement_name"];
            $dropdown .= '<option value="' . $pavement_id . '">' . $pavement_name . '</option>';
        }
    }
    $dropdown .= '</select>';
    
    //now to find if it's the only surface in all selection
    $sql_q_surfaces = "SELECT  DISTINCT pavement_type_id
                        FROM qlines
                        WHERE qline_id in ($ids)";

    $res_surfaces = $connection->query($sql_q_surfaces);
    if(($ids != null) && ($res_surfaces->num_rows == 1)) {
        while($row = $res_surfaces->fetch_assoc()) {
            $selected_pavement_type_id = $row["pavement_type_id"];
        }
    } else {
        $selected_pavement_type_id = -1;
    }
    
    $str_to_replace = '"' . $selected_pavement_type_id . '">';
    $replacement = '"' . $selected_pavement_type_id . '" selected>';
    
    //TODO: fix quotes
    $dropdown = str_replace($str_to_replace, $replacement, $dropdown);
    return $dropdown;
}

function quality_value($ids=null) {
	require_once('passwords.php');
    $connection = new mysqli($db_server, $db_username, $db_password, $DB_DATA, $db_port);
    
    if (!$connection) {
        $answer_from_server['result'] = false;
        $answer_from_server['info'] = 'Ошибка соединения: ' . mysql_error();
    }
	
	$sql_uniq_qs = "SELECT DISTINCT surface_quality
                        FROM qlines
                        WHERE qline_id in ($ids)";

    $res_uniq_qs = $connection->query($sql_uniq_qs);
    if($res_uniq_qs->num_rows == 1) {
        while($row = $res_uniq_qs->fetch_assoc()) {
            $selected_qs = $row["surface_quality"];
        }
    } else {
        $selected_qs = '';
    }
    return $selected_qs;
}

if (isset($_REQUEST["request"])) $global_request_name = htmlspecialchars(stripslashes(trim($_REQUEST["request"])));

switch ($global_request_name) {

    case 'surfaces_drop':
        if (isset($_REQUEST["ids"])) $ids_list = htmlspecialchars(stripslashes(trim($_REQUEST["ids"])));
        if (isset($_REQUEST["language"])) $lang = htmlspecialchars(stripslashes(trim($_REQUEST["language"])));
		
		if ($lang == null) {
			$answer_from_server['result'] = false;
			$answer_from_server['info'] = 'no language';
			break;
		}
		if ($ids_list == null) {
			$surface_drop = surfaces_dropdown(null, $lang);
			$answer_from_server['result'] = true;
			$answer_from_server['info'] = 'no selection';
			$answer_from_server['quality'] = $surface_drop;
			break;
		}
        
        $answer_from_server['result'] = true;
		$answer_from_server['info'] = 'got surfaces drop';
        $surface_drop = surfaces_dropdown($ids_list, $lang);
		$answer_from_server['drop'] = $surface_drop;
        
        break;
        
	case 'quality_value':
	
		if (isset($_REQUEST["ids"])) {
			$ids_list = htmlspecialchars(stripslashes(trim($_REQUEST["ids"])));
			if ($ids_list == null) {
				$answer_from_server['result'] = true;
				$answer_from_server['info'] = 'no selection - empty quality';
				$answer_from_server['quality'] = '';
				break;
			}
			$qs = quality_value($ids_list);
			
			$answer_from_server['result'] = true;
			$answer_from_server['info'] = 'got quality value';
			$answer_from_server['quality'] = $qs;
			break;
		}
    default:
		/* Если все проверки пройдены, но ни один обработчик запроса не запустился */
		$answer_from_server['result'] = false;
		$answer_from_server['info'] = 'Incorrect request name';
		break;
} /* Конец switch case */

if (isset($global_request_name)){
	/* Create a new instance of Services_JSON */
	
	$res = json_encode($answer_from_server,  JSON_UNESCAPED_SLASHES);
	echo $res;
}

?>

