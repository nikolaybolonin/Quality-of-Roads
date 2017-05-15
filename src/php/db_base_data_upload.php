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
                                            ('RU', 'Russian', 'Русский');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>languages inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }

    echo "<br>pavement translation<br>";
    $sql_i = "INSERT INTO pavement_translation (pavement_id, language_id, pavement_name, pavement_descr)
                                    VALUES (1, 1, 'asphalt', 'fairly smooth but can have cracks and may include gravel'),
                                            (1, 2, 'асфальт', 'сравнительно ровная поверхность, возможны трещины и может включать в себя гравий'),
                                            (2, 1, 'cobblestone', 'uneven stones, layed together - cracks are imminent, uncomfortable even for medium wheels'),
                                            (2, 2, 'брусчатка', 'неровный булыжник, постоянные грани, дискофортно даже для средкних колес'),
                                            (3, 1, 'compacted earth', 'there is a chance of it being smooth but a good chance of dirt'),
                                            (3, 2, 'утоптанная земля', 'может быть ровной, но не всегда и есть приличный шанс грязи'),
                                            (4, 1, 'concrete', 'fairly smooth but can have cracks'),
                                            (4, 2, 'бетон', 'сравнительно ровная поверхность, возможны трещины'),
                                            (5, 1, 'grass', 'grass?'),
                                            (5, 2, 'трава', 'трава?'),
                                            (6, 1, 'fine gravel', 'lots of tiny stones, small wheels are not advised'),
                                            (6, 2, 'гравий', 'крошечные камни, с маленькими колесами делать нечего'),
                                            (7, 1, 'stone slabs', 'fairly smooth but cracks happen regulary'),
                                            (7, 2, 'каменные плиты', 'довольно ровно, но с регулярными трещинами'),
                                            (8, 1, 'tiles', 'small beveled tiles, cracks are imminent, really uncomfortable for small wheels'),
                                            (8, 2, 'плитка', 'мелкая неровная плитка, постоянные грани, дискофортно для маленький колес'),
                                            (9, 1, 'sand', 'small and medium wheels have no chance but wide may have a chance'),
                                            (9, 2, 'песок', 'маленьким и средним колесам делать тут нечего, хотя у широких есть шанс'),
                                            (10, 1, 'stairway', 'you are gonna carry that weight'),
                                            (10, 2, 'лестница', 'несем этот груз'),
                                            (11, 1, 'gravel', 'lots of small stones, medium wheels are not advised'),
                                            (11, 2, 'щебень', 'небольшие камни, со средними колесами будут проблемы');";
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
                        (1, 2, 'ехать невозможно'),
                        (2, 2, 'ехать можно, но рискованно'),
                        (3, 2, 'ехать очень неприятно'),
                        (4, 2, 'ехать неприятно'),
                        (5, 2, 'хорошо, но с мелкими раздражающими факторами'),
                        (6, 2, 'хорошо');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>quality translations inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
}

?>
