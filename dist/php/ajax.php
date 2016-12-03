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
/* Устанавливаем кодировку для корректного collation*/
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

/* Функция SELECT поискового запроса;
	Если $index_type = ID, то массив индексируется по ID */
function my_sql_select_into_highcharts_data($my_query, $my_db, $name, $y_value){
    /* Вытаскиваем данные по запросу */
	$query_result = mysql_query($my_query,$my_db);
	if (mysql_num_rows($query_result) > 0){
		$query_result_fetch_array = mysql_fetch_array($query_result);
		$row_id = 0;
		do{
			$row_id++;

			foreach ($query_result_fetch_array as $key => $value) {
				if (!is_numeric($key)){
					if ($key == $name) {$query_result_main_array[$row_id]["name"] = $value;}
					if ($key == $y_value) {$query_result_main_array[$row_id]["y"] = $value;}
				}
			}
		}
		while($query_result_fetch_array = mysql_fetch_array($query_result));
	} else {
		$query_result_main_array = false;
	}
	/* Конец данных */
    return $query_result_main_array;
}



/* Конец описания функций */
?>









<?php  /* Обработка запроса */

/* Супер-защита от взлома =) */
if (isset($_REQUEST["request"])) $global_request_name = htmlspecialchars(stripslashes(trim($_REQUEST["request"])));




/* Запускаем процедуру в зависимости от запроса */
switch ($global_request_name) {


    case 'select_earnings':

			/* Имя таблицы */
			$TABLE = "TEMPLATE_DATA_1";

			/* Формируем SQL SELECT запрос который вытаскивает все */
			$select_query = "
				SELECT ITEM AS Item, SUM(QUANTITY * PRICE) AS Earnings
				FROM $DB_DATA.$TABLE
				GROUP BY ITEM
				ORDER BY Earnings DESC";
			/* Отправляем запрос и формируем трехуровневый массив с результатом */
			$query_result = my_sql_select_into_highcharts_data($select_query,$db,"Item","Earnings");
			if ($query_result != false){
				$answer_from_server['result'] = true;
				$answer_from_server['data'] = $query_result;
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

