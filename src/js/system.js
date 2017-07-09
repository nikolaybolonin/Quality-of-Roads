/*
  Template Name : Quality of Roads
  Template Version : 1.0
  Template Author : Bolonin Nikolay ( As hackzilla )
  Creation Date : 01 Dec 2016
*/


'use strict';

// ========================================================================
// Промис для XMLHttpRequest
// ========================================================================
function ajaxGet(
  // Дефолтные значения необязательных параметров
  {
    url:        url       ='get_json.php', 
    request:    request   ='200', 
    data:       data      =''
  } = {}) {

  return new Promise(function(resolve, reject) {

    var xhr = new XMLHttpRequest();
    xhr.open('GET', url + '?r=' + Math.random() + '&request=' + request + data, true);

    xhr.onload = function() {
      if (this.status == 200) {
        resolve(this.response);
      } else {
        var error = new Error(this.statusText);
        error.code = this.status;
        reject(error);
      }
    };

    xhr.onerror = function() {
      reject(new Error("Network Error"));
    };

    xhr.send();
  });
  
}








// ========================================================================
//  PAGE INITIALIZATION - INITIALIZATION
// ========================================================================

/*
Center: (30.3409952, 59.9566569)
*/

//old center
/*
var lat = 59.9440020;
var lng = 30.3553289;
var zoom = 18;  
*/

var lat = 59.9536273;
var lng = 30.3496856;
var zoom = 18;
   


// ========================================================================
//  PAGE INITIALIZATION - FUNCTIONS
// ========================================================================
function getLineDescrText(event) {
    var lineOptionsText = '';


    if (event.feature.getProperty('name') != undefined) {
      lineOptionsText = lineOptionsText + 'Name: ' + event.feature.getProperty('name') + '</br>'
    }
    if (event.feature.getProperty('id') != undefined) {
      lineOptionsText = lineOptionsText + 'ID: ' + event.feature.getProperty('id') + '</br>'
    }
    if (event.feature.getProperty('pavement_type') != undefined) {
      lineOptionsText = lineOptionsText + 'Surface: ' + event.feature.getProperty('pavement_type') + '</br>'
    }
    if (event.feature.getProperty('width') != undefined) {
      lineOptionsText = lineOptionsText + 'Width: ' + event.feature.getProperty('width') + 'm</br>'
    }
    if (event.feature.getProperty('populousness') != undefined) {
      lineOptionsText = lineOptionsText + 'Populousness: ' + event.feature.getProperty('populousness') + '</br>'
    }
    if (event.feature.getProperty('quality') != undefined) {
      lineOptionsText = lineOptionsText + 'Quality: ' + event.feature.getProperty('quality') + '</br>'
    }
    if (event.feature.getProperty('rating') != undefined) {
      lineOptionsText = lineOptionsText + 'Rating: ' + event.feature.getProperty('rating') + '</br>'
    }
    if (event.feature.getProperty('color') != undefined) {
      lineOptionsText = lineOptionsText + 'Color: ' + event.feature.getProperty('color') + '</br>'
    }
    if (event.feature.getProperty('descendants') != undefined) {
      lineOptionsText = lineOptionsText + 'Descendants: ' + event.feature.getProperty('descendants') + '</br>'
    }
    return lineOptionsText;
}

function getLineEditorForm(event) {
    var lineOptionsText = '<form>';
	
    lineOptionsText += 'ID(s): ';
    if (event.feature.getProperty('id') != undefined) {
      var clicked_id = event.feature.getProperty('id');
      if (ids_array.includes(clicked_id)) {
        var index = ids_array.indexOf(clicked_id);
        ids_array.splice(index, 1);
      } else {
        ids_array.push(clicked_id);
      }
      var ids_string = ids_array.toString();
    }
    lineOptionsText += ids_string + '</br>';
	
	var surfaces_drop_string = '';
	
	var xmlhttp = new XMLHttpRequest();
    
    xmlhttp.onreadystatechange = function() {
		if (xmlhttp.status == 200) {
            surfaces_drop_string = JSON.parse(xmlhttp.responseText)['drop'];
        } else {
			surfaces_drop_string = 'bbbb';
		}
    };
    
    var url = 'php/menu_selection_helper.php';
	var ids_string = ids_array.toString();
    xmlhttp.open("GET", url + '?r=' + Math.random() + '&request=' + 'surfaces_drop' + '&ids=' + ids_string + '&language=en', false);
    xmlhttp.send();
	
	lineOptionsText += 'Surface:';
	lineOptionsText += surfaces_drop_string;
	lineOptionsText += '<br>';

	var xmlhttp = new XMLHttpRequest();
	var surface_qs = '';
    
    xmlhttp.onreadystatechange = function() {
        //if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
		if (xmlhttp.status == 200) {
            //var additional_data = String(xmlhttp.responseText);
            surface_qs = String(JSON.parse(xmlhttp.responseText)['quality']);
        }
    };
	
	var url = 'php/menu_selection_helper.php';
	
	var ids_string = ids_array.toString();
    xmlhttp.open("GET", url + '?r=' + Math.random() + '&request=' + 'quality_value' + '&ids=' + ids_string, false);

    xmlhttp.send();
	
    lineOptionsText += 'Surface Quality: <input type="number" id="quality_number" name="quality" min="0" max="5" step="1" value="';
	lineOptionsText += surface_qs.replace(/(\r\n|\n|\r)/gm,"");
	lineOptionsText += '"><br>';
	
    if (event.feature.getProperty('width') != undefined) {
      lineOptionsText += 'Width: ' + event.feature.getProperty('width') + 'm</br>'
    }
    if (event.feature.getProperty('populousness') != undefined) {
      lineOptionsText += 'Populousness: ' + event.feature.getProperty('populousness') + '</br>'
    }
    
    return lineOptionsText;
}
var ids_array = [];
var lprop_quality;
var lprop_surface;
// Создаем карту с API Google Maps (googleMapInit вызывается из HTML)
function googleMapInit() {
  //var ids_array = [];
  var googleMapLatLng = new google.maps.LatLng(lat, lng);
  var GoogleMap = new google.maps.Map(document.getElementById('map_with_google_api'), {
    center: googleMapLatLng,
    zoom: zoom,
    maxZoom: 19,
    mapTypeId: "OSM",
    mapTypeControlOptions: {
      mapTypeIds: ["roadmap", "hybrid", "OSM"],
      position: google.maps.ControlPosition.TOP_RIGHT
    },
    scaleControl: true,
    streetViewControl: true,
    streetViewControlOptions: {
      position: google.maps.ControlPosition.TOP_RIGHT
    },
    zoomControl: true,
    zoomControlOptions: {
      position: google.maps.ControlPosition.TOP_RIGHT
    },
  });
  // Подключаем OpenStreetMap
  GoogleMap.mapTypes.set("OSM", new google.maps.ImageMapType({
    getTileUrl: function(coord, zoom) {
      // "Wrap" x (logitude) at 180th meridian properly to enable horizontal infinite scroll
      // NB: Don't touch coord.x because coord param is by reference, and changing its x property breakes something in Google's lib 
      var tilesPerGlobe = 1 << zoom;
      var x = coord.x % tilesPerGlobe;
      if (x < 0) {
        x = tilesPerGlobe + x;
      }
      return "http://tile.openstreetmap.org/" + zoom + "/" + x + "/" + coord.y + ".png";
    },
    tileSize: new google.maps.Size(256, 256),
    name: "OpenStreetMap",
    maxZoom: 20
  }));

  // Создаем юзер-контрол поиска
  var input = document.getElementById('map_address_input');
  var searchBox = new google.maps.places.SearchBox(input);

  // Bias the SearchBox results towards current map's viewport.
  GoogleMap.addListener('bounds_changed', function() {
    searchBox.setBounds(GoogleMap.getBounds());
  });

  var markers = [];
  // [START region_getplaces]
  // Listen for the event fired when the user selects a prediction and retrieve
  // more details for that place.
  searchBox.addListener('places_changed', function() {
    var places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    // Clear out the old markers.
    markers.forEach(function(marker) {
      marker.setMap(null);
    });
    markers = [];

    // For each place, get the icon, name and location.
    var bounds = new google.maps.LatLngBounds();
    places.forEach(function(place) {
      var icon = {
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(25, 25)
      };

      // Create a marker for each place.
      markers.push(new google.maps.Marker({
        map: GoogleMap,
        icon: icon,
        title: place.name,
        position: place.geometry.location
      }));

      if (place.geometry.viewport) {
        // Only geocodes have viewport.
        bounds.union(place.geometry.viewport);
      } else {
        bounds.extend(place.geometry.location);
      }
    });
    GoogleMap.fitBounds(bounds);
  });
  // [END region_getplaces]

  // NOTE: This uses cross-domain XHR, and may not work on older browsers.
  // Use loadGeoJson to load it from file or URL 
  //GoogleMap.data.addGeoJson(jsonGeoData3);

  // Добавляем инпут в который будут выводиться текущие параметры и координаты клика 
  var infoLabel = document.getElementById('map_current_parameters_label');
  var rounder = 10000000;
  var GoToCoordsButton = $('#go_to_coords');
  var lineOptions = document.getElementById('selected_line_options');

  GoogleMap.addListener('zoom_changed', function() {
    infoLabel.value = 'Zoom: ' + GoogleMap.getZoom();
  });
  GoogleMap.addListener('center_changed', function() {
    var lat = Math.round(GoogleMap.getCenter().lat() * rounder) / rounder;
    var lng = Math.round(GoogleMap.getCenter().lng() * rounder) / rounder;
    infoLabel.value = 'Center: (' + lng + ', ' + lat + ')';
  });
  GoogleMap.addListener('click', function(event) {
    var lat = Math.round(event.latLng.lat() * rounder) / rounder;
    var lng = Math.round(event.latLng.lng() * rounder) / rounder;
    infoLabel.value = 'Coords: (' + lng + ', ' + lat + ')';
    lineOptions.innerHTML = '';
  });


  var getDataButton = document.getElementById('meta_data_container');
  // Клик по кнопке go_to_coords
  //getDataButton.addEventListener("click", function(event) {
  var goToCoords = document.getElementById('go_to_coords');
  var updateLinesButton = document.getElementById('update_lines');
  var breakLineButton = document.getElementById('break_line');
  var joinLinesButton = document.getElementById('join_lines');
  
  goToCoords.addEventListener("click", function(event) {

    var gBounds = GoogleMap.getBounds();
    var bounds = {
      nw: {
        lng: gBounds.b.b,
        lat: gBounds.f.b
      },
      se: {
        lng: gBounds.b.f,
        lat: gBounds.f.f
      }
    }
    
    ajaxGet({
      url: 'php/get_json.php',
      request: 'select_geojson',
      data: '&nwlat=' + bounds.nw.lat + '&nwlng=' + bounds.nw.lng + '&selat=' + bounds.se.lat + '&selng=' + bounds.se.lng
    })
      // 1. Распарсить JSON или вывести ошибку
      .then(
        response => {
          console.info('Fulfilled');

          let data = JSON.parse(response);
          return data;
        },
        error => {
          console.error('Rejected: ' + response);
          return false;
        }
      )
      // 2. Распарсить объект в массив + преобразовать строки Y в числа
      .then(data => {

        if (!!data['result']) {

          console.log(data['data']);
          // NOTE: This uses cross-domain XHR, and may not work on older browsers.
          // Use loadGeoJson to load it from file or URL 
          GoogleMap.data.addGeoJson(data['data']['geojson']);
        } else {
          console.error(data['info']);
        }
      });
 
    event.preventDefault();

  }, false); // Конец addEventListener
  
    updateLinesButton.addEventListener("click", function(event) {
    
    var surface_drop = document.getElementById('surface_drop');
    var quality_input = document.getElementById('quality_number');
    
    var ids_string = ids_array.toString();
    var quality_value = quality_input.value;
    var surface_value = surface_drop.options[surface_drop.selectedIndex].value;
	var a = 'stop';
    
    ajaxGet({
      url: 'php/update_db.php',
      request: 'update_lines',
      data: '&ids=' + ids_string + '&quality=' + quality_value + '&surface=' + surface_value
    })
      // 1. Распарсить JSON или вывести ошибку
      .then(
        response => {
          console.info('Fulfilled');

          let data = JSON.parse(response);
          return data;
        },
        error => {
          console.error('Rejected: ' + response);
          return false;
        }
      )
      // 2. Распарсить объект в массив + преобразовать строки Y в числа
      .then(data => {

        if (!!data['result']) {

          console.log(data['data']);
          // NOTE: This uses cross-domain XHR, and may not work on older browsers.
          // Use loadGeoJson to load it from file or URL 
          // GoogleMap.data.addGeoJson(data['data']['geojson']);


        } else {
          console.error(data['info']);
        }

      });

    event.preventDefault();

  }, false);
  
  breakLineButton.addEventListener("click", function(event) {
	  var percent_input = document.getElementById('break_percent');
	  var percent_value = percent_input.value;
	  
	  var xmlhttp = new XMLHttpRequest();
	  
	  //var resp = '';
    
	  xmlhttp.onreadystatechange = function() {
		if (xmlhttp.status == 200) {
        }
      };
	
	var url = 'php/update_db.php';
	
	var ids_string = ids_array.toString();
    xmlhttp.open("GET", url + '?r=' + Math.random() + '&request=' + 'break_qline' + '&id=' + ids_string + '&percent=' + percent_value, false);
    xmlhttp.send();
	
  }, false);
  
  joinLinesButton.addEventListener("click", function(event) {
	  
	  var xmlhttp = new XMLHttpRequest();
	  xmlhttp.onreadystatechange = function() {
		if (xmlhttp.status == 200) {
        }
      };
	
	var url = 'php/update_db.php';
	
	var ids_string = ids_array.toString();
    xmlhttp.open("GET", url + '?r=' + Math.random() + '&request=' + 'join_qlines' + '&ids=' + ids_string, false);

    xmlhttp.send();
	
  }, false);

  // Функция применяющая стили по триггеру isColorful
  GoogleMap.data.setStyle(function(feature) {
    var color = 'gray';
    var zIndex = 3;
    var strokeWeight = 6;
    if (feature.getProperty('isColorful') == true || feature.getProperty('isColorful') == undefined) {
      color = feature.getProperty('color');
      zIndex = 2;
      strokeWeight = 5;
    }
    return /** @type {google.maps.Data.StyleOptions} */ ({
      fillColor: color,
      strokeColor: color,
      strokeWeight: strokeWeight,
      zIndex: zIndex
    });
  });

  // По клику включаем/выключаем опцию раскраски.
  GoogleMap.data.addListener('click', function(event) {

    var lat = Math.round(event.latLng.lat() * rounder) / rounder;
    var lng = Math.round(event.latLng.lng() * rounder) / rounder;
    infoLabel.value = 'Coords: (' + lng + ', ' + lat + ')';

    if (event.feature.getProperty('isColorful') != true && event.feature.getProperty('isColorful') != undefined) {
      event.feature.setProperty('isColorful', true);
    } else {
      event.feature.setProperty('isColorful', false);
    }
  });

  // Добавляем попап на который будет выводиться инфа
  var infoPopup = new google.maps.InfoWindow({
    // Отступ от точки
    pixelOffset: new google.maps.Size(0, -10),
    // Убираем авто прокрутку если попап не помещается на карте
    disableAutoPan: true
  });

  // When the user hovers, tempt them to click by outlining the letters.
  // Call revertStyle() to remove all overrides. This will use the style rules
  // defined in the function passed to setStyle()
  GoogleMap.data.addListener('mouseover', function(event) {
    GoogleMap.data.revertStyle();
    GoogleMap.data.overrideStyle(event.feature, {
      strokeColor: 'gray',
      strokeWeight: 8,
      zIndex: 4
    });

  });

  GoogleMap.data.addListener('mousemove', function(event) {
    infoPopup.open(GoogleMap);
    infoPopup.setPosition(event.latLng);

    var lineDescr2 = '';
    lineDescr2 = getLineDescrText(event);

    infoPopup.setContent(lineDescr2);

  });

  GoogleMap.data.addListener('mouseout', function(event) {
    GoogleMap.data.revertStyle();
    infoPopup.close(GoogleMap);
  });

  // По клику выводим инфу на лейбл сбоку.
  GoogleMap.data.addListener('click', function(event) {
  

    var lineEdit2 = '';
    lineEdit2 = getLineEditorForm(event);

	lineOptions.innerHTML = lineEdit2;

  });

}











