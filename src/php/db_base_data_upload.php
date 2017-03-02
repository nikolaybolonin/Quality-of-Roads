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
                                            (2, 1, 'tiles', 'small beveled tiles, cracks are imminent, really uncomfortable for small wheels'),
                                            (2, 2, 'плитка', 'мелкая неровная плитка, постоянные грани, дискофортно для маленький колес');";
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
                        (1, 1, 'ехать невозможно'),
                        (2, 1, 'ехать можно, но рискованно'),
                        (3, 1, 'ехать очень неприятно'),
                        (4, 1, 'ехать неприятно'),
                        (5, 1, 'хорошо, но с мелкими раздражающими факторами'),
                        (6, 1, 'хорошо');";
    if ($connection->query($sql_i) === TRUE) {
        echo "<br>quality translations inserted<br>";
    } else {
        echo "error creating table: " . $connection->error;
    }
}

?>
