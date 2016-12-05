<?php   /* Конект к базе */

/* Подключаем файл с доступом к БД */
require_once('passwords.php');
// $db_server = "localhost:xxxx";
// $db_username = "uxxxxx_admin";
// $db_password = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
/* Выбираем БД c данными */
// $DB_DATA = "xxxxxxxxxxxxxxx";

/* Соединяемся с базой данных */
$db = mysql_connect($db_server,$db_username,$db_password);
/* Эту строку обязательно ставить после конекта для корректного отображения символов */
mysql_set_charset('utf8',$db);
/* Устанавливаем кодировку для корректного вывода в браузере */
mysql_query("SET NAMES 'utf8';");
mysql_query("SET CHARACTER SET 'utf8';");
/* Устанавливаем кодировку для корректного collation */
mysql_query("SET SESSION collation_connection = 'utf8_general_ci';");

if (!$db) {
	$answer_from_server['result'] = false;
	$answer_from_server['info'] = 'Ошибка соединения: ' . mysql_error();
}


/* Выбираем БД c информацией о структуре таблиц всех баз */
$DB_INFO = "information_schema";
/* Признак не системной таблицы */
$MY_TABLE_TYPE = "TABLE_TYPE = 'BASE TABLE'";


/* Конец конекта к базе */
?>






<?php  /* Описание функций */

/* Функция SELECT поискового запроса */
function my_sql_select_to_geojson($my_query, $my_db){
    /* Вытаскиваем данные по запросу */
	$query_result = mysql_query($my_query,$my_db);
	if (mysql_num_rows($query_result) > 0){

		# Build GeoJSON feature collection array
		$geojson = array(
		   'type'      => 'FeatureCollection',
		   'features'  => array()
		);
		# Loop through rows to build feature arrays
		while($row = mysql_fetch_assoc($query_result)) {

		    $feature = array(
		        'id' => $row['ID'],
		        'type' => 'Feature',
		        'geometry' => array(
		            'type' => 'MultiLineString',
		            # Pass Longitude and Latitude Columns here
		            'coordinates' => array(
		            	array(
		            		array(floatval($row['START_LNG']), floatval($row['START_LAT'])),
		            		array(floatval($row['END_LNG']), floatval($row['END_LAT']))
		            		)
		            	)
		        ),
		        # Pass other attribute columns here
		        'properties' => array(
					"name" => $row['NAME'],
					"surface" => $row['SURFACE'],
					"width" => $row['WIDTH'],
					"populousness" => $row['POPULOUSNESS'],
					"quality" => $row['QUALITY'],
					"rating" => $row['RATING'],
					"color" => $row['COLOR']
		            )
		        );
		    # Add feature arrays to feature collection array
		    array_push($geojson['features'], $feature);
		}
	} else {
		$geojson = false;
	}
	/* Конец данных */
    return $geojson;
}



/* Конец описания функций */
?>









<?php  /* Обработка запроса */

/* Супер-защита от взлома =) */
if (isset($_REQUEST["request"])) $global_request_name = htmlspecialchars(stripslashes(trim($_REQUEST["request"])));



/* Запускаем процедуру в зависимости от запроса */
switch ($global_request_name) {


    case 'select_geojson':

    	/* Обрабатываем входящие параметры */
		if (isset($_REQUEST["nwlng"])) $nwlng = htmlspecialchars(stripslashes(trim($_REQUEST["nwlng"])));
		if (isset($_REQUEST["nwlat"])) $nwlat = htmlspecialchars(stripslashes(trim($_REQUEST["nwlat"])));
		if (isset($_REQUEST["selng"])) $selng = htmlspecialchars(stripslashes(trim($_REQUEST["selng"])));
		if (isset($_REQUEST["selat"])) $selat = htmlspecialchars(stripslashes(trim($_REQUEST["selat"])));

		$bounds = array(
			'nw'  	=> array(
				'lng'  => floatval($nwlng),
		   		'lat'  => floatval($nwlat)
			),
			'se'  	=> array(
				'lng'  => floatval($selng),
		   		'lat'  => floatval($selat)
			)
		);


		/* Имя таблицы */
		$TABLE = "TEMPLATE_DATA_2";

		/* Формируем SQL SELECT запрос который вытаскивает все */
		$select_query = "
			SELECT *
			FROM $DB_DATA.$TABLE
			ORDER BY ID";
		/* Отправляем запрос и формируем трехуровневый массив с результатом */
		$query_result = my_sql_select_to_geojson($select_query,$db);
		if ($query_result != false){
			$answer_from_server['result'] = true;
			$answer_from_server['data']['geojson'] = $query_result;
			$answer_from_server['data']['bounds'] = $bounds;
		}
		if (!isset($answer_from_server['data'])) {
			$answer_from_server['result'] = false;
			$answer_from_server['data'] = false;
		}
        break;

	default:
		/* Если все проверки пройдены, но ни один обработчик запроса не запустился */
		$answer_from_server['result'] = false;
		$answer_from_server['info'] = 'Incorrect request name';
		break;
} /* Конец switch case */



// print_r($answer_from_server['data']); echo '<br><br><br>';
if (isset($global_request_name)){
	/* Create a new instance of Services_JSON */
	require_once('Services_JSON.php');
	$oJson = new Services_JSON();

	/* Выводим результат в формате JSON */
	echo $oJson->encode($answer_from_server);
}

/* Конец обработки запроса */
?>

