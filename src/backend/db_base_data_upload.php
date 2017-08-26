<?php
// Half-assed function to get some basic data in for future testing. TODO: get some proper basic data upload functionality)
function upload_base_data($connection) {
    echo "<br>== Inserting base data ==<br>";
    echo "<br>pavement<br>";
    $sql_i = "INSERT INTO pavement (pavement_id) VALUES (1), (2);";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>def. pavement ids inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
    echo "<br>Languages<br>";
    $sql_i = "INSERT INTO languages (language_en_short_name, language_en_name, language_local_name)
                                    VALUES ('EN', 'English', 'English'),
                                            ('RU', 'Russian', 'Ðóññêèé');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>languages inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
    echo "<br>pavement translation<br>";
    $sql_i = "INSERT INTO pavement_translation (pavement_id, language_id, pavement_name, pavement_descr)
                                    VALUES (1, 1, 'asphalt', 'fairly smooth but can have cracks and may include gravel'),
                                            (1, 2, 'àñôàëüò', 'ñðàâíèòåëüíî ðîâíàÿ ïîâåðõíîñòü, âîçìîæíû òðåùèíû è ìîæåò âêëþ÷àòü â ñåáÿ ãðàâèé'),
                                            (2, 1, 'cobblestone', 'uneven stones, layed together - cracks are imminent, uncomfortable even for medium wheels'),
                                            (2, 2, 'áðóñ÷àòêà', 'íåðîâíûé áóëûæíèê, ïîñòîÿííûå ãðàíè, äèñêîôîðòíî äàæå äëÿ ñðåäêíèõ êîëåñ'),
                                            (3, 1, 'compacted earth', 'there is a chance of it being smooth but a good chance of dirt'),
                                            (3, 2, 'óòîïòàííàÿ çåìëÿ', 'ìîæåò áûòü ðîâíîé, íî íå âñåãäà è åñòü ïðèëè÷íûé øàíñ ãðÿçè'),
                                            (4, 1, 'concrete', 'fairly smooth but can have cracks'),
                                            (4, 2, 'áåòîí', 'ñðàâíèòåëüíî ðîâíàÿ ïîâåðõíîñòü, âîçìîæíû òðåùèíû'),
                                            (5, 1, 'grass', 'grass?'),
                                            (5, 2, 'òðàâà', 'òðàâà?'),
                                            (6, 1, 'gravel', 'losts of tiny stones, small wheels are not advised'),
                                            (6, 2, 'ãðàâèé', 'ìåëêèå êàìíè, ñ ìàëåíüêèìè êîëåñàìè äåëàòü íå÷åãî'),
                                            (7, 1, 'stone slabs', 'fairly smooth but cracks happen regulary'),
                                            (7, 2, 'êàìåííûå ïëèòû', 'äîâîëüíî ðîâíî, íî ñ ðåãóëÿðíûìè òðåùèíàìè'),
                                            (8, 1, 'tiles', 'small beveled tiles, cracks are imminent, really uncomfortable for small wheels'),
                                            (8, 2, 'ïëèòêà', 'ìåëêàÿ íåðîâíàÿ ïëèòêà, ïîñòîÿííûå ãðàíè, äèñêîôîðòíî äëÿ ìàëåíüêèé êîëåñ'),
                                            (9, 1, 'sand', 'small and medium wheels have no chance but wide may have a chance'),
                                            (9, 2, 'ïåñîê', 'ìàëåíüêèì è ñðåäíèì êîëåñàì äåëàòü òóò íå÷åãî, õîòÿ ó øèðîêèõ åñòü øàíñ'),
                                            (10, 1, 'stairway', 'you are gonna carry that weight'),
                                            (10, 2, 'ëåñòíèöà', 'íåñåì ýòîò ãðóç');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>def. pavement translation inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
    echo "<br>quality<br>";
    $sql_i = "INSERT INTO quality (quality_value, quality_color_hex)
                VALUES (0, '000000'),
                        (1, 'ac1417'),
                        (2, 'dc7519'),
                        (3, 'cbc324'),
                        (4, 'afeb20'),
                        (5, '26b62b');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>qualities inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
    echo "<br>quality translation<br>";
    $sql_i = "INSERT INTO quality_translation (quality_id, language_id, quality_descr)
                VALUES (1, 1, 'impossible to ride'),
                        (2, 1, 'possible to ride but really risky'),
                        (3, 1, 'really uncomfortable'),
                        (4, 1, 'uncomfortable'),
                        (5, 1, 'good with minor irritation'),
                        (6, 1, 'good'),
                        (1, 2, 'åõàòü íåâîçìîæíî'),
                        (2, 2, 'åõàòü ìîæíî, íî ðèñêîâàííî'),
                        (3, 2, 'åõàòü î÷åíü íåïðèÿòíî'),
                        (4, 2, 'åõàòü íåïðèÿòíî'),
                        (5, 2, 'õîðîøî, íî ñ ìåëêèìè ðàçäðàæàþùèìè ôàêòîðàìè'),
                        (6, 2, 'õîðîøî');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>quality translations inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
}
?>