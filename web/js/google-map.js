var map;
var marker=null;
var markers = [];
var lines = [];

function initialize(listener) {
    f = false;
    
    if($('#outlet_lat').val() !== undefined && $('#outlet_lat').val()!='' && $('#outlet_lng').val()!=''){
        myMarker = new google.maps.LatLng($('#outlet_lat').val(), $('#outlet_lng').val());
        f = true;
    }
    if($('#center_lat').val() === undefined || $('#center_lat').val()=='' && $('#center_lng').val()==''){
        if(f) center = myMarker;
        else center = new google.maps.LatLng(50.007, 36.251);
    }
    else {
        center = new google.maps.LatLng($('#center_lat').val(), $('#center_lng').val());
    }
    rad = false;
    if($('#radius_lat').val() !== undefined && $('#radius_lat').val()!='' && $('#radius_lng').val()!='' && $('#radius').val()!=''){
        center = new google.maps.LatLng($('#radius_lat').val(), $('#radius_lng').val());
        rad = true;
    }
    
    var mapOptions = {
      center: center,
      zoom: 11,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), mapOptions);
    if(f){
        addMarker(map,myMarker,listener);
    }
    if(rad){
        new google.maps.Circle({
            strokeWeight: 1,
            map: map,
            center: center,
            radius: parseInt($('#radius').val())
        });
    }
    if(listener){
        google.maps.event.addListener(map, 'click', function(event) {
          addMarker(map, event.latLng,listener);
        });
    }
}
//google.maps.event.addDomListener(window, 'load', initialize);

function addMarker(map,location,draggable) {
    if(marker==null){
        marker = new google.maps.Marker({
          position: location,
          map: map,
          draggable: draggable
        });
    }
    else {
        marker.setPosition(location);
    }
    google.maps.event.addListener(marker, 'dragend', function () {
        $('#outlet_lat').val(marker.getPosition().lat());
        $('#outlet_lng').val(marker.getPosition().lng());
    });
    $('#outlet_lat').val(location.lat());
    $('#outlet_lng').val(location.lng());
}

function viewMarker(id,map,markers,lat,lng,content,icon) {
    point = new google.maps.LatLng(lat, lng);
    markers[id] = new google.maps.Marker({
        position: point,
        map: map,
        icon: icon
    });

    var infowindow = new google.maps.InfoWindow({
      content: content
    });
    google.maps.event.addListener(markers[id], 'mouseover', function() {
        infowindow.open(map,markers[id]);
    });
    google.maps.event.addListener(markers[id], 'mouseout', function() {
        infowindow.close();
    });
}

function addPolyLine(id,map,lines,lat1,lng1,lat2,lng2,content,icon) {
    point = new google.maps.LatLng(lat2, lng2);
    marker = new google.maps.Marker({
        position: point,
        map: map,
        icon: icon
    });
    lines[id] = {};
    lines[id]['marker'] = marker;
    var infowindow = new google.maps.InfoWindow({
      content: content
    });
    google.maps.event.addListener(marker, 'mouseover', function() {
        infowindow.open(map,marker);
    });
    google.maps.event.addListener(marker, 'mouseout', function() {
        infowindow.close();
    });
    
  var lineSymbol = {
    path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW
  };

  var lineCoordinates = [
    new google.maps.LatLng(lat1, lng1),
    new google.maps.LatLng(lat2, lng2)
  ];

  var line = new google.maps.Polyline({
    path: lineCoordinates,
    strokeColor: "#FF0000",
    strokeOpacity: 1.0,
    strokeWeight: 2,
    icons: [{
      icon: lineSymbol,
      offset: '80%'
    },
    {
      icon: lineSymbol,
      offset: '50%'
    },
    {
      icon: lineSymbol,
      offset: '20%'
    }],
    map: map
  });
  line.setMap(map);
  lines[id]['line'] = line;
}