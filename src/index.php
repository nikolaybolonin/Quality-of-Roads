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
  <link rel="stylesheet" href="css/styles.css"> <!-- 12kb -->

</head>

<body>

  <!-- START SECTIONS -->
  <div id="container_content">
    <!-- ========================================================================
    /* PAGE SPECIFIC HTML CONTENT
    /* ======================================================================== -->


    <!-- START SECTION -->
    <section id="map_section" class="main_central_section">
      <div id="meta_data_container" class="map">

        <input id="map_current_parameters_label" class="controls" type="text" placeholder="Map's Parameters" value="">
        <button id="go_to_coords" class="controls">></button>
        <input id="map_address_input" class="controls" type="text" placeholder="Search Box">
        <p id="selected_line_options" class="controls">
        </p>
        <button id="update_lines" class="controls">update lines</button>
    <p id="line_breaking_text" class="controls">
    Break percent:
    <input id="break_percent" name="break_perc" min="0" max="99.9" step="0.1" value="" type="number">
        </p>
    <button id="break_line" class="controls">break line</button>
    <button id="join_lines" class="controls">join lines</button>
      </div>
      <div id="map_with_google_api" class="map"></div>
    </section>
    <!-- END OF SECTION -->




  </div><!-- /#container_content -->




  <!-- END OF SECTIONS -->

  <!-- JAVASCRIPT  LIBRARIES -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maps.googleapis.com/maps/api/js?sensor=false&key=AIzaSyB8xXdwtq6zIPTpDTmL4aHph2QqNp_ZfZg&libraries=places&callback=googleMapInit" async defer></script>



  <!-- JAVASCRIPT LIBRARIES -->
  <script language="javascript" type="text/javascript" src="js/bundle.min.js"></script>

</body>
</html>












