<!DOCTYPE HTML>

<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title id="pageSpecificTitle">OpenStreetMap with Google Maps v3 API</title>
  <meta name="description" content="Maps Demo">
  <meta name="keywords" content="Maps">
  <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
  <meta name="author" content="Nikolay Bolonin, Dmitriy Petuhov" />
  <!-- Убираем выделение элементов на WindowsPhone устройствах -->
  <meta name="msapplication-tap-highlight" content="no"/>
  <!-- MAIN STYLESHEET FILE -->
  <link rel="stylesheet" href="css/system.css"> <!-- 12kb -->

</head>

<body>

  <!-- START SECTIONS -->
  <div id="container_content">
    <!-- ========================================================================
    /* PAGE SPECIFIC HTML CONTENT
    /* ======================================================================== -->

    <!-- START SECTION -->
    <section id="main_section" class="main_central_section">
      <div id="chart_container"></div>

      <button id="refresh_chart">
        Обновить
      </button>
    </section>
    <!-- END OF SECTION -->


    <!-- START SECTION -->
    <section id="map_section" class="main_central_section">
      <div id="meta_data_container" class="map">

        <input id="map_current_parameters_label" class="controls" type="text" placeholder="Map's Parameters" value="">
        <button id="go_to_coords" class="controls">></button>
        <input id="map_address_input" class="controls" type="text" placeholder="Search Box">
        <p id="selected_line_options" class="controls">

        </p>
      </div>
      <div id="map_with_google_api" class="map"></div>
    </section>
    <!-- END OF SECTION -->




  </div><!-- /#container_content -->




  <!-- END OF SECTIONS -->

  <!-- JAVASCRIPT highcharts LIBRARIES -->
  <!-- Другие библиотеки не использовал -->
  <script language="javascript" type="text/javascript" src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?sensor=false&key=AIzaSyBHpAmimxTn6JfSP_-1PavnZ9WvAE6eCtc&libraries=places&callback=initAutocomplete" async defer></script>



  <!-- JAVASCRIPT LIBRARIES -->
  <script language="javascript" type="text/javascript" src="js/system.js"></script>

</body>
</html>












