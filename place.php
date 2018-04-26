<?php if(isset($_GET["getCategoryDetails_P"]) && isset($_GET["name"])){
    $catDetails = urlencode($_GET["getCategoryDetails_P"]);
    $url = "https://maps.googleapis.com/maps/api/place/details/json?placeid={$catDetails}&key=YOUR_API_KEY";
    // get the json response
    $resp_json = file_get_contents($url);
    $json_data  = json_decode($resp_json,true);
    if(isset($json_data["result"]["photos"]))
        $top_5_photos = array_slice($json_data["result"]["photos"],0,5,true);
    else 
        $top_5_photos = null;
    
    $index=1;
    if($top_5_photos != null)
    for($i=0;$i<count($top_5_photos); $i++){
        $top5_i = urlencode($top_5_photos[$i]["photo_reference"]);
        $photo_url = "https://maps.googleapis.com/maps/api/place/photo?maxwidth=750&photoreference={$top5_i}&key=YOUR_API_KEY";
        $file_content = file_get_contents($photo_url);
        file_put_contents('photo'. ($i+1) . '.jpeg', $file_content);
        $top_5_photos[$i]["filename"] = "photo". ($i+1) . ".jpeg";
        $index = $index+1;
    }
    if(isset($json_data["result"]["reviews"]))
        $top_5_reviews = array_slice($json_data["result"]["reviews"],0,5,true);
    else 
        $top_5_reviews = null;
    $result_array = json_encode(array($_GET["name"], $top_5_photos, $top_5_reviews));
    echo $result_array;
    exit();
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <style>
            .div-border-box{
                width: 600px;
                border-style: solid;
                border-width: 2px;
                border-color: #c1c1c1;
                margin: auto;
                padding-bottom: 20px;
                background-color: #f9f9f9;
                padding-left: 10px;
            }
            
            .header{
                text-align: center;
                font-size: 30px;
                padding: 0;
                margin: 0;
                font-family: serif;
                font-style: italic;
                font-weight: 600;
            }
            .border{
                color: lightgrey;
            }
            .padded-radio{
                padding: 0;
                margin: 0;
                margin-left: 293.5px;
            }
            .button{
                margin-left: 64px;
            }
            .distanceTextBox{
                width: 130px;
            }
            .detailsTable{
                border-collapse: collapse;
                margin: auto;
                margin-top : 40px;
                padding: 4px;
                z-index: 0;
                width : 100%;
            }
            th, td{
                padding-left : 5px;
                padding-bottom: 5px;
                padding-right: 10px;
                border: 3px solid lightgrey;
                overflow: visible;
            }
            .detailsTableDiv{
                margin-top: 30px;
                margin : auto;
                margin-bottom: 50px;
                width : 1200px;
            }
            .tableCell{
                background-color: Transparent;
                background-repeat:no-repeat;
                border: none;
                cursor: pointer;
                overflow: hidden;
                outline:none;
                position: relative;
                white-space: normal;
                word-wrap: break-word;
                text-align: left;
            }
            .placeDetailsTableDiv{
                width: 1000px;
                margin: auto;
                margin-top: 50px;
            }
            .arrowImg{
                height : 20px;
                width : 35px;
                display : block;
                margin : auto;
                cursor: pointer;
            }
            .profilePicture{
                width: 60px;
                height: 60px;
            }
            .reviewsTable{
                border-collapse: collapse;
                width: 614px;
                margin : auto;
                margin-top: 30px;
                margin-bottom: 50px;
                text-align: center;
                font-size: 15px;
            }
            .reviewsTable tr{
                text-align: center;
                padding-left : 5px;
                padding-bottom: 5px;
                padding-right: 10px;
                border: 3px solid lightgrey;
            }
            .reviewsTable td{
                text-align: left;
            }
            .photosDiv{
                margin: 0px;
                padding: 0px;
                margin: auto;
                width : 614px;
                overflow: hidden;
            }
            .modal {
                display: none; /* Hidden by default */
                position: absolute; /* Stay in place */
                z-index: 1; /* Sit on top */
                padding-top: 100px; /* Location of the box */
                width: 400px; /* Full width */
                height: 300px; /* Full height */
                overflow: auto; /* Enable scroll if needed */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            }
            .floating-panel {
                display: none; /* Hidden by default */
                position: absolute; /* Stay in place */
                z-index: 1; /* Sit on top */
                width: auto; /* Full width */
                height: auto; /* Full height */
                overflow: auto; /* Enable scroll if needed */
                background-color: rgb(0,0,0); /* Fallback color */
                background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
              }
            .mode{
                overflow-y: auto;
                padding: 3px;
                font-size: 13px;
                background-color: #DCDCDC;
                border-color: #DCDCDC;
            }
            .optionStyle{
                padding-bottom: 5px;
                padding-left: 5px;
                padding-right: 5px;
                padding-top: 5px;
            }
        </style>
        
        
        <script type="text/javascript">
            var keywordValue=null, categoryValue=null, distanceValue=null, radioSelectedValue = null, userEnteredLocationValue=null;
            
            var optionsArray = ["default", "cafe", "bakery", "restaurant", "beauty_salon", "casino", "movie_theater","lodging", "airport", "train_station", "subway_station", "bus_station"];
            var userLat, userLong;
            function getGeoLocation(){
                var req ;
                var responseJSON;

                // Browser compatibility check          
                if (window.XMLHttpRequest) {
                   req = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {

                 try {
                   req = new ActiveXObject("Msxml2.XMLHTTP");
                 } catch (e) {

                   //try {
                     req = new ActiveXObject("Microsoft.XMLHTTP");
                   //} catch (e) {}
                 }

                }
                var req = new XMLHttpRequest();
                req.open("GET", "http://ip-api.com/json",true);
                req.onreadystatechange = function () {
                    if(req.readyState == 4 && req.status==200){
                        if(req.responseText){
                            responseJSON = JSON.parse(req.responseText);
                            userLat = responseJSON.lat;
                            userLong = responseJSON.lon;
                            document.getElementById("lati").value = userLat;
                            document.getElementById("longi").value = userLong;
                            if(userLat && userLong){
                            document.getElementById("search").disabled = false;
                            }
                        }
                    }
                }
                req.send(null);
                
            }
            function setRequired(){
                var formValueSelected = document.forms["inputForm"]["location"].value;
                if(formValueSelected == "user-input"){
                    document.forms["inputForm"]["user-entered-location"].required = true;
                    document.forms["inputForm"]["user-entered-location"].disabled = false;
                    return false;
                }else{
                    document.forms["inputForm"]["user-entered-location"].required = false; 
                    document.forms["inputForm"]["user-entered-location"].disabled = true;
                    return true;
                }
            }
            
            function clearAllValues(){
                document.getElementById("keyword").value = "";
                document.getElementById("distance").value = "";
                document.getElementById("here").checked = true;
                document.getElementById("user-entered-location").value = "";
                document.forms["inputForm"]["user-entered-location"].required = false;
                document.forms["inputForm"]["user-entered-location"].removeAttribute("required");
                document.forms["inputForm"]["user-entered-location"].disabled = true;
                document.getElementById("category").options.selectedIndex = 0;  
                window.localStorage.clear();
                var noOfChildren = document.body.childElementCount;
                for(var i=1;i<noOfChildren;i++){
                    document.body.removeChild(document.body.lastChild);
                }
            }
            
            function createTable(){
                var div = document.createElement('div');
                div.className = "detailsTableDiv";
                div.id = "detailsTableDiv";
                div.name = "detailsTableDiv";
                
                var detailsTable = document.createElement('table');
                detailsTable.className = "detailsTable";
                detailsTable.id = "detailsTable";
                detailsTable.name = "detailsTable";
                
                var tableHead = document.createElement('thead');
                var tableRow = document.createElement('tr');
                var th1 = document.createElement('th'); var t1 = document.createTextNode("Category"); th1.appendChild(t1);
                var th2 = document.createElement('th'); var t2 = document.createTextNode("Name"); th2.appendChild(t2);
                var th3 = document.createElement('th'); var t3 = document.createTextNode("Address"); th3.appendChild(t3);
                
                tableRow.appendChild(th1); tableRow.appendChild(th2); tableRow.appendChild(th3);
                tableHead.appendChild(tableRow);
                detailsTable.appendChild(tableHead);
                for(var i=0;i<js_results_array.length;i++){
                    var tableRow = document.createElement('tr');
                    var td1 = document.createElement('td'); var tImg = document.createElement('img'); tImg.src = js_results_array[i]["icon"]; td1.appendChild(tImg); tImg.width = 45; tImg.height = 45;
                    var td2 = document.createElement('td');
                    var b2 = document.createElement("input"); b2.setAttribute("type", "button"); b2.value = js_results_array[i]["name"]; b2.id = (js_results_array[i]["place_id"]);
                    b2.className="tableCell"; b2.onclick=  getCategoryDetails; td2.appendChild(b2);//td2.appendChild(b2);
                    var td3 = document.createElement('td'); td3.id = i; var div3 = document.createElement('div'); div3.className = "wrappable";
                    var b3 = document.createElement("input"); b3.setAttribute("type", "button"); b3.value = js_results_array[i]["vicinity"]; b3.id = (js_results_array[i]["id"]);
                    b3.className="tableCell"; b3.onclick=  getGoogleMap; td3.appendChild(b3);
                    tableRow.appendChild(td1); tableRow.appendChild(td2); tableRow.appendChild(td3);
                    detailsTable.appendChild(tableRow);
                }
                div.appendChild(detailsTable);
                document.body.appendChild(div);
            }
            
            
            function displayError(){
                var div = document.createElement('div');
                div.className = "detailsTableDiv";
                div.id = "detailsTableDiv";
                div.name = "detailsTableDiv";

                var detailsTable = document.createElement('table');
                detailsTable.className = "detailsTable";
                detailsTable.id = "detailsTable";
                detailsTable.name = "detailsTable";
                detailsTable.style.width = '614px'; 
                var tableRow = document.createElement('tr');
                var td2 = document.createElement('td'); var t2 = document.createTextNode("No records have been found");  td2.appendChild(t2);
                td2.style.textAlign = 'center';
                tableRow.appendChild(td2);
                detailsTable.appendChild(tableRow);
                div.appendChild(detailsTable);
                document.body.appendChild(div);
            }
            
            var map, lati,longi;
            
            function getGoogleMap(){
                var checkDiv = document.getElementById("map");
                if(!document.getElementById("map")){
                    var floatingPanel = document.createElement('div');
                    floatingPanel.id = 'floating-panel';
                    floatingPanel.className = 'floating-panel';
                    floatingPanel.style.display = 'block';
                    floatingPanel.style.zIndex = 5;
                    var selectMenu = document.createElement('select'); 
                    selectMenu.id = "mode";
                    selectMenu.className = 'mode';
                    selectMenu.size = 3;
                    var option1 = document.createElement('option'); option1.className="optionStyle"; option1.value = 'WALKING'; var txt1 = document.createTextNode("Walk there"); option1.appendChild(txt1); selectMenu.appendChild(option1);
                    var option2 = document.createElement('option'); option2.className="optionStyle"; option2.value = 'BICYCLING'; var txt2 = document.createTextNode("Bike there"); option2.appendChild(txt2); selectMenu.appendChild(option2);
                    var option3 = document.createElement('option'); option3.className="optionStyle"; option3.value = 'DRIVING'; var txt3 = document.createTextNode("Drive there"); option3.appendChild(txt3); selectMenu.appendChild(option3);
                    floatingPanel.appendChild(selectMenu);
                    
                    var div = document.createElement('div');
                    div.id='map';
                    div.className = 'modal';
                    div.style.display = 'block';
                    lati = js_results_array[this.parentElement.id]["geometry"]["location"].lat;
                    lngi = js_results_array[this.parentElement.id]["geometry"]["location"].lng;
                    var s = document.createElement('script');
                    s.setAttribute('type', 'text/javascript');
                    s.src = "https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap";
                    div.appendChild(s);
                    window.google={};
                    
                    document.getElementById(this.parentElement.id).appendChild(floatingPanel);
                    document.getElementById(this.parentElement.id).appendChild(div);
                }
                else if(document.getElementById("map").style.display ='block'){
                    document.getElementById(this.parentElement.id).removeChild(document.getElementById("map"));
                    document.getElementById(this.parentElement.id).removeChild(document.getElementById("floating-panel"));
                }
                else if(document.getElementById("map").style.display ='none'){
                    document.getElementById("map").style.display = 'block';
                    document.getElementById("floating-panel").style.display = 'block';
                }
            }
            
            var marker=null;
            
            function initMap() {
                
                var directionsDisplay = new google.maps.DirectionsRenderer;
                var directionsService = new google.maps.DirectionsService;
                map = new google.maps.Map(document.getElementById('map'), {
                zoom: 13,
                center: {lat: lati, lng: lngi}
                });
                marker = new google.maps.Marker({
                position: {lat: lati, lng: lngi},
                map: map,
                });
                directionsDisplay.setMap(map);
                calculateAndDisplayRoute(directionsService, directionsDisplay);
                document.getElementById('mode').addEventListener('change', function() {
                  calculateAndDisplayRoute(directionsService, directionsDisplay);
                });
              }

              function calculateAndDisplayRoute(directionsService, directionsDisplay) {
                var selectedMode = document.getElementById('mode').value;
                if(selectedMode){                
                    var coordinates = {lat: Number.parseFloat(userLatPhp), lng:Number.parseFloat(userLongPhp)};
                    directionsService.route({
                      origin: coordinates,
                      destination: {lat: lati, lng: lngi},
                      travelMode: google.maps.TravelMode[selectedMode]
                    }, function(response, status) {
                      if (status == 'OK') {
                          marker.setMap(null);
                          directionsDisplay.setDirections(response);
                      } else {
                        //window.alert('Directions request failed due to ' + status);
                      }
                    });
                }
              }
            
            function getCategoryDetails(){
                var req ;

                // Browser compatibility check          
                if (window.XMLHttpRequest) {
                   req = new XMLHttpRequest();
                    } else if (window.ActiveXObject) {

                 try {
                   req = new ActiveXObject("Msxml2.XMLHTTP");
                 } catch (e) {
                     req = new ActiveXObject("Microsoft.XMLHTTP");
                 }

                }
                var req = new XMLHttpRequest();
                req.open("GET", "place.php?getCategoryDetails_P=" + this.id + "&name=" + this.value,true);
                req.onreadystatechange = function () {
                    if(req.readyState == 4 && req.status==200){
                    var responseJSON = JSON.parse(req.responseText);
                    displayPlaceDetails(responseJSON);
                    }
                }
                req.send(null);
            }
            
            
            function displayPlaceDetails(responseJSON){
                var toRemove = document.getElementById("detailsTableDiv");
                if(toRemove != null)
                    document.body.removeChild(toRemove);
                
                var div = document.createElement('div');
                div.className = "placeDetailsTableDiv";
                div.id = "placeDetailsTableDiv";
                div.name = "placeDetailsTableDiv";
                
                var p1 = document.createElement('p');
                p1.style.textAlign = "center";
                p1.style.fontWeight = 'bold';
                var t1 = document.createTextNode(responseJSON[0]);
                p1.append(t1);
                
                var div2 = document.createElement('div');
                div2.className = "reviewsDiv";
                div2.id = "reviewsDiv";
                div2.name = "reviewsDiv";
                div2.style.display = "none";
                
                var p2 = document.createElement('p');
                p2.id = "reviewsHeading";
                p2.style.textAlign = "center";
                var t2 = document.createTextNode("Click to show reviews");
                p2.append(t2);
                
                var arrowImg1 = document.createElement('img');
                arrowImg1.src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                arrowImg1.id = "reviewsArrow";
                arrowImg1.className="arrowImg";
                arrowImg1.value = responseJSON;
                arrowImg1.onclick=displayReviews;
                
                var p3 = document.createElement('p');
                p3.id = "photosHeading";
                p3.style.textAlign = "center";
                var t3 = document.createTextNode("Click to show photos");
                p3.append(t3);
                
                var div3 = document.createElement('div');
                div3.className = "photosDiv";
                div3.id = "photosDiv";
                div3.name = "photosDiv";
                div3.style.display = "none";
                
                var arrowImg2 = document.createElement('img');
                arrowImg2.src="http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                arrowImg2.id = "photosArrow";
                arrowImg2.className="arrowImg";
                arrowImg2.value = responseJSON;
                arrowImg2.onclick = displayPhotos;
                
                div.appendChild(p1); div.appendChild(p2); div.appendChild(arrowImg1);  div.appendChild(div2); 
                div.appendChild(p3); div.appendChild(arrowImg2); div.appendChild(div3); 
                document.body.appendChild(div);
                
                
            }
            
            function displayReviews(){
                var div = document.getElementById("reviewsDiv");
                
                if(div.style.display == 'block'){
                    div.style.display = 'none';
                    document.getElementById("reviewsArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                    document.getElementById("reviewsHeading").textContent = "Click to show reviews";
                }
                else{
                    var photosDiv = document.getElementById("photosDiv");
                    if(photosDiv.style.display == 'block'){
                        photosDiv.style.display = 'none';
                        document.getElementById("photosArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("photosHeading").textContent = "Click to show photos";
                    }
                    var responseJSON = this.value;
                    document.getElementById("reviewsArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                    
                    
                    if(!responseJSON[2]){
                        if(div.children.length==0){
                                var detailsTable = document.createElement('table');
                                detailsTable.className = "reviewsTable";
                                detailsTable.id = "reviewsTable";
                                detailsTable.name = "reviewsTable";
                                var tableRow = document.createElement('tr');
                                var td = document.createElement('td');
                                var text = document.createTextNode("No Reviews Found");
                                td.style.fontWeight = 'bold';
                                td.style.fontSize = '14px';
                                td.style.textAlign = 'center';
                                td.appendChild(text);
                                tableRow.appendChild(td);
                                detailsTable.appendChild(tableRow);
                                div.appendChild(detailsTable);
                            }
                    }
                    if(div.children.length==0){
                        var detailsTable = document.createElement('table');
                        detailsTable.className = "reviewsTable";
                        detailsTable.id = "reviewsTable";
                        detailsTable.name = "reviewsTable";
                        for(var element in responseJSON[2]){    
                            if(responseJSON[2][element]["profile_photo_url"] != "" || responseJSON[2][element]["author_name"] != ""){
                                var tableRow1 = document.createElement('tr');
                                tableRow1.style.borderCollapse = 'collapse';
                                var td1 = document.createElement('td');
                                
                                if(responseJSON[2][element]["profile_photo_url"] != ""){  
                                    td1.style.fontWeight = 'bold';
                                    td1.style.display = 'inline-block';
                                    td1.style.border = 'none';
                                    var profilePicture = document.createElement('img');
                                    profilePicture.src = responseJSON[2][element]["profile_photo_url"];
                                    profilePicture.className = "profilePicture";
                                    td1.appendChild(profilePicture); 
                                }
                                if(responseJSON[2][element]["author_name"] != ""){
                                    var text1 = document.createTextNode(responseJSON[2][element]["author_name"])
                                    td1.appendChild(text1);
                                }
                                tableRow1.appendChild(td1);
                                detailsTable.appendChild(tableRow1);
                            }
                            if(responseJSON[2][element]["text"] != ""){
                                var tableRow2 = document.createElement('tr');
                                var td2 = document.createElement('td');
                                td2.style.border = 'none';
                                var text2 = document.createTextNode(responseJSON[2][element]["text"])
                                td2.appendChild(text2);
                                tableRow2.appendChild(td2);
                                detailsTable.appendChild(tableRow2);
                            }
                            div.appendChild(detailsTable);
                        }
                    }
                    document.getElementById("reviewsHeading").textContent = "Click to hide reviews";
                    div.style.display = 'block';
                } 
            }
            
            
            function displayPhotos(){
                var div = document.getElementById("photosDiv");
                
                if(div.style.display == 'block'){
                    div.style.display = 'none';
                    document.getElementById("photosArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                    document.getElementById("photosHeading").textContent = "Click to show photos";
                }
                else{
                    var reviewsDiv = document.getElementById("reviewsDiv");
                    if(reviewsDiv.style.display == 'block'){
                        reviewsDiv.style.display = 'none';
                        document.getElementById("reviewsArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_down.png";
                        document.getElementById("reviewsHeading").textContent = "Click to show reviews";
                    }
                    var responseJSON = this.value;
                    document.getElementById("photosArrow").src = "http://cs-server.usc.edu:45678/hw/hw6/images/arrow_up.png";
                    document.getElementById("photosHeading").textContent = "Click to hide photos";
                    
                    if(!responseJSON[1]){
                            if(div.children.length==0){
                                var detailsTable = document.createElement('table');
                                detailsTable.className = "reviewsTable";
                                detailsTable.id = "photosTable";
                                detailsTable.name = "photosTable";
                                detailsTable.style.display = 'contain';
                                detailsTable.style.maxHeight = '200px';
                                var tableRow = document.createElement('tr');
                                var td = document.createElement('td');
                                var text = document.createTextNode("No Photos Found");
                                td.style.fontWeight = 'bold';
                                td.style.fontSize = '14px';
                                td.style.textAlign = 'center';
                                td.appendChild(text);
                                tableRow.appendChild(td);
                                detailsTable.appendChild(tableRow);
                                div.appendChild(detailsTable);
                            }
                    }
                    
                    else{
                        if(div.children.length==0){
                            var detailsTable = document.createElement('table');
                            detailsTable.className = "reviewsTable";
                            detailsTable.id = "photosTable";
                            detailsTable.name = "photosTable";
                            detailsTable.style.display = 'contain';
                            var index = 1;
                            for(var element = 0; element<responseJSON[1].length; element++){
                                var photoName = "photo" + (Number.parseInt(element)+1) + ".jpeg";
                                var tableRow1 = document.createElement('tr');
                                tableRow1.height = '614px';
                                tableRow1.style.borderCollapse = 'collapse';
                                tableRow1.style.height = '100px';
                                tableRow1.style.display = 'contain';
                                var td1 = document.createElement('td');
                                td1.style.padding = '10px';
                                var photoImg = document.createElement('img');
                                photoImg.id=photoName;
                                photoImg.style.display = 'block';
                                photoImg.style.height = '400px';
                                photoImg.style.width = '590px';
                                photoImg.style.cursor = 'pointer';
                                photoImg.src = responseJSON[1][element]["filename"];
                                var photoAnchor = document.createElement('a');
                                photoAnchor.appendChild(photoImg);
                                photoAnchor.href =  responseJSON[1][element]["filename"];
                                photoAnchor.setAttribute('target', '_blank');
                                td1.appendChild(photoAnchor);
                                td1.width = '614px';
                                td1.style.overflow = "hidden";
                                tableRow1.appendChild(td1);
                                detailsTable.appendChild(tableRow1);
                                index = index + 1;
                            }
                            div.appendChild(detailsTable);
                        }
                    }
                    document.getElementById("photosHeading").textContent = "Click to hide photos";
                    div.style.display = 'block';
                }
            }
            
            function displayOriginalImage(){
                const link = this.src;
                var newWindow = window.open();
                newWindow.document.write('<img src="' + link + '" />');   
            }
            
            function setFormValues(){
                if(keywordValue)
                    document.getElementById("keyword").value = keywordValue;
                
                if(categoryValue)
                    document.getElementById("category").selectedIndex = optionsArray.indexOf(categoryValue);
                
                if(distanceValue)
                    document.getElementById("distance").value = distanceValue;
                
                if(userEnteredLocationValue){
                    //console.log(userEnteredLocationValue);
                    document.getElementById("user-entered-location").value = userEnteredLocationValue;
                }
                
                var radioButtonValue = radioSelectedValue;
                if(radioButtonValue == "Here"){
                    document.getElementById("here").checked = true;
                    document.getElementById("user-entered-location").disabled = true;
                }
                else if(radioButtonValue == "user-input"){
                    document.getElementById("user-entered-location").disabled = false;
                    document.getElementById("user-entered").checked = true;
                }
            }
            
            function init(){
                getGeoLocation();
            }
            
        </script>        
        
    </head>
    
    <body onload="init()">    
        
        <div class="div-border-box">
            <form name="inputForm" action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
            <p class="header">Travel and Entertainment Search</p>
            <hr class="border">
            
            <b> Keyword </b> <input type="text" id="keyword" required name="keyword"/> <br> <br>
            <b> Category </b> <select name="category" id="category">
            <option value="default"> default</option>
            <option value="cafe"> Cafe </option>
            <option value="bakery"> Bakery </option>
            <option value="restaurant"> Restaurant </option>
            <option value="beauty_salon"> Beauty Salon </option>
            <option value="casino"> Casino </option>
            <option value="movie_theater"> Movie Theater </option>
            <option value="lodging"> Lodging </option>
            <option value="airport"> Airport </option>
            <option value="train_station"> Train Station </option>
            <option value="subway_station"> Subway Station </option>
            <option value="bus_station"> Bus Station </option>        
            </select>
            
            <br><br>
            <b>Distance (miles)</b> <input type="text" id="distance" placeholder="10" class="distanceTextBox" name="distance"/> <b> from </b>
            <input type="radio" name="location" id="here" value="Here" onclick="setRequired()"? checked> <b> Here </b> <br>
            <input type="radio" name="location" id="user-entered" value="user-input" class="padded-radio" onclick="setRequired()"/> 
            <input type="text" id="user-entered-location" placeholder="location" name="user-entered-location" disabled/> <br> <br>
            <input type="hidden" name="lati" id="lati" value=""/>
            <input type="hidden" name="longi" id="longi" value=""/>
            
            <input type="submit" value="Search" class="button" id="search" disabled name="submit"/> <input type="button" value="Clear" onclick="clearAllValues()"/>
            </form>
        </div> 
 
    </body>
    
    <?php
    $lati =""; $longi ="";
            function setDropDown($name){
                if($name =="default"){
                    if(isset($_POST["category"])){
                        if($_POST["category"] == "default")
                            return "selected";
                        else
                            return "";
                    }
                    else
                        return "selected";
                }
                else{
                    if(isset($_POST["category"])){
                        if($_POST["category"] == $name)
                            return "selected";
                        else
                            return "";
                    }
                    else
                        return "";
                }
            }
            
            function setRadioButton($name){
                if($name == "Here"){
                    if(isset($_POST["location"]))
                        if($_POST["location"] == "Here")
                            return "checked";
                        else
                            return "";
                    else
                        return "checked";
                }
                else{
                    if(isset($_POST["location"]) && $_POST["location"] == "user-input")
                        return "checked";
                    else
                        return "";
                }
            }
    
            function setLocationTextBox(){
                if(isset($_POST["location"]))
                    if($_POST["location"] == "Here")
                        return "disabled";
                    else
                        return "";
                else
                    return "disabled";
            }
                                    
            function getLocationBoxValue(){
                if(isset($_POST["user-entered-location"]))
                    return $_POST["user-entered-location"];
                else
                    return "";
            }
    
        ?>
        
        <?php if(isset($_POST["submit"])){   
        ?>
            <script type = "text/javascript">
                keywordValue = <?php echo json_encode($_POST["keyword"]);?>;
                categoryValue = <?php echo json_encode($_POST["category"]);?>;
                distanceValue = <?php echo isset($_POST["distance"]) ?json_encode($_POST["distance"]):json_encode(null);?>;
                radioSelectedValue = <?php echo json_encode($_POST["location"]);?>;
                userEnteredLocationValue = <?php echo isset($_POST["user-entered-location"])?json_encode($_POST["user-entered-location"]): json_encode(null);?>;
                setFormValues();
            </script>                    
        
        <?php
    
            $radius=""; $keyword=""; $type="";
            $lati = urlencode($_POST["lati"]);
            $longi = urlencode($_POST["longi"]);
        
            function geocode(){
            global $lati, $longi;
            $address = urlencode($_POST["user-entered-location"]);
            
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=YOUR_API_KEY";
            $resp_json = file_get_contents($url);
            $resp = json_decode($resp_json, true);

            if($resp['status']=='OK'){
                $lati = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
                $longi = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";         
                }

                else{
                    $lati = "";
                    $longi = "";
                }
                
            }
    
            function getPlaceResults($lati, $longi){
                
                global $radius, $keyword, $type;
                
                $radius = $_POST["distance"] == "" || !is_numeric($_POST["distance"])? 10 * 1609.344 : $_POST["distance"] * 1609.344;
                $radius = urlencode($radius);
                $keyword = urlencode($_POST["keyword"]);
                
                $type = urlencode($_POST["category"]);
                if($type=="default"){
                    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lati},{$longi}&radius={$radius}&type=&keyword={$keyword}&key=YOUR_API_KEY";         
                }
                else{
                    $url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location={$lati},{$longi}&radius={$radius}&type={$type}&keyword={$keyword}&key=YOUR_API_KEY";   
                }
                $resp_json = file_get_contents($url);
                $json_data  = json_decode($resp_json,true);
                $results_array = $json_data["results"];       
                return $results_array;
            }
            if($_POST["location"] == "user-input"){
                geoCode();
            }
            ?>
            
            <script type="text/javascript">
                var userLatPhp = <?php echo json_encode($lati)?>;
                var userLongPhp = <?php echo json_encode($longi)?>;
            </script>
            
            <?php
            $results_array = getPlaceResults($lati, $longi);
    
            ?>
    
            <script type="text/javascript">
                var js_results_array = <?php echo json_encode($results_array)?>;
                if(!js_results_array || js_results_array.length==0)
                    displayError();
                else
                    createTable();
            </script>
    
            <?php       
        }       
        
        ?>

</html>
