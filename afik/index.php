<!doctype html>
<html class='no-js' lang='en'>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>What Should I Do Tonight?</title>
    <link rel="stylesheet" href="css/foundation.css" />
    <script src="js/vendor/modernizr.js"></script>
    <link href="css/custom.css" rel="stylesheet">
  </head>
  <body>

    <nav class="top-bar" data-topbar role="navigation">
      <ul class="title-area">
        <li class="name">
          <h1><a href="#">What Should I Do Tonight?</a></h1>
        </li>
      </ul>
    </nav>
    
    <div class="medium-7 medium-centered columns panel">
      <form action="#" method="post">

          <h1><i>Find your ideal night out!</i></h1>
          <h3>Just enter in your parameters and see an ideal night out on the town. Don't like your selection? just run again and you'll get a different option (almost) every time!</h3>
          
          <div class="row">
            <div class="medium-6 columns">Cost ($)<input type="number" name="cost" placeholder="500" value="<?php echo $_POST['cost']; ?>" /></div>
            <div class="medium-6 columns">Radius (miles)<input type="number" name="radius" placeholder="20" value="<?php echo $_POST['radius']; ?>" /></div>
          </div>
          <div class="row">
            <div class="medium-3 columns">Hours<input type="number" name="hours" value="<?php echo $_POST['hours']; ?>" /></div>
            <div class="medium-3 columns">Minutes<input type="number" name="minutes" value="<?php echo $_POST['minutes']; ?>"></div>
            <div class="medium-3 columns">Activities<input type="number" name="activities" placeholder="3" value="<?php echo $_POST['activities']; ?>" /></div>
            <div class="medium-3 columns">Food? <input type="checkbox" name="food" /></div>
          </div>

          <div class="row">
            <div class=" columns medium-centered">
                <button id="submit" name="submit" type="submit" class="button success button-round">Submit</button>
                <button id="clear" name="clear" type="reset" class="button secondary button-round">Clear</button>
            </div>
          </div>


    <?php
      if('POST' == $_SERVER['REQUEST_METHOD']) {

        $servername = "localhost";
        $username = "is425";
        $password = "is425";
        $dbname = "ODS_DB";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Could not connect to locations database, please try again later.");
        }

        //user's current location coordinates
        $origin_x = 0;
        $origin_y = 0;

        //variable default
        $default = 1000000;

        //user preferences
        $radius = (!empty($_POST["radius"]) ? $_POST["radius"] : 20);
        $cost = (!empty($_POST["cost"]) ? $_POST["cost"] : 500);
        $activities = (!empty($_POST["activities"]) ? $_POST["activities"] : 3);
        $time = (!empty($_POST["hours"]) || !empty($_POST["minutes"]) ? ($_POST["hours"]*60) + $_POST["minutes"] : $default);
        $food = (!empty($_POST["food"]) ? $_POST["food"] : false);

        // echo $radius."<br>".$cost."<br>".$activities."<br>".$time."<br>".$food;


        $result = $conn->query("SELECT * FROM places");
        //create $placesarray from the db results
        while($row = $result->fetch_array()){
          $placesarray[] = $row;
        }

        //start trimming from $placesarray using radius parameter 
        foreach($placesarray as $index => $row){
          $distance = sqrt(pow($row["x"] - $origin_x, 2) + pow($row["y"] - $origin_y, 2));
          if($distance >= $radius){
                  // delete this particular object from the $placesarray
                  unset($placesarray[$index]);
              }
        }
        //reindex the values of the array
          $placesarray = array_values($placesarray);

          //array of food
          $foodarray = array();
          //split placesarray into food array and remove foods from main arry
          foreach($placesarray as $index => $row){
          if(strpos($row["category"],"Food") !== false && $row["name"] != ""){
            array_push($foodarray, $row);
                  // delete this particular object from the $placesarray
                  unset($placesarray[$index]);
              }
        }
          $placesarray = array_values($placesarray);


        //set length of results to amount of activities wanted
          $places = array();
          $totalcost = 0;
          $totaltime = 0;

          while($food){
            if(count($foodarray) == 0){
              echo "<p>Sorry! Not all of your food criteria could be matched!</p>";
              $food = false;
            }
            else{
              $position = rand(0, count($foodarray)-1);
              $option = $foodarray[$position];
              if(($totalcost + $option["cost"]) <= $cost && ($totaltime + $option["time"]) <= $time && $option["name"] != ""){
                array_push($places, $option);
                $totalcost += $option["cost"];
                $totaltime += $option["time"];
                $food = false;
              }
              else{
                unset($foodarray[$position]);
                $foodarray = array_values($foodarray);
              }
            }
          }

          while($activities > 0){
            if(count($placesarray) == 0){
              echo "<p>Sorry! Not all of your activity criteria could be matched!</p>";
              $activities = 0;
            }
            else{
              $position = rand(0, count($placesarray)-1);
              $option = $placesarray[$position];
              if(($totalcost + $option["cost"]) <= $cost && ($totaltime + $option["time"]) <= $time && $option["name"] != ""){
                array_push($places, $option);
                //remove this item from the array so it doesnt get picked again
                unset($placesarray[$position]);
                $totalcost += $option["cost"];
                $totaltime += $option["time"];
                $activities--;
              }
              else{
                unset($placesarray[$position]);
                $placesarray = array_values($placesarray);
              }
            }
          }

          if(count($places) > 0)
          echo "<h4>Get ready for:</h4><hr>";

          foreach ($places as $activity) {
            $rating = "";
            for($i = 1; $i <= $activity["rating"]; $i++){
              $rating = $rating."&#9733";
            }
            echo "<p><b>".$activity["name"]."</b><br>".$activity["description"]."<br>".$rating."</p><img class='center' src='img/arrow.png' width='30em'></img>";
          }

          echo "<div><h4><b>Final Cost: </b><br>$".$totalcost."</h4><h4><br><b>Final Time: </b><br>".floor($totaltime/60)." Hours  ".($totaltime%60)." Minutes</h4></div>";

          echo "<div class='row'>
                  <div class='columns'>
                      <button id='submit' name='submit' type='submit' class='button secondary button-round'>Give me something else!</button>
                  </div>
                </div>
                <script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script><div style='overflow:hidden;height:200px;width:700px;'><div id='gmap_canvas' style='height:200px;width:700px;'></div><style>#gmap_canvas img{max-width:none!important;background:none!important}</style><a class='google-map-code' href='http://wordpress-themes.org' id='get-map-data'>wordpress templates</a></div><script type='text/javascript'> function init_map(){var myOptions = {zoom:17,center:new google.maps.LatLng(39.2538015,-76.71427319999998),mapTypeId: google.maps.MapTypeId.ROADMAP};map = new google.maps.Map(document.getElementById('gmap_canvas'), myOptions);marker = new google.maps.Marker({map: map,position: new google.maps.LatLng(39.2538015, -76.71427319999998)});infowindow = new google.maps.InfoWindow({content:'<b>Your Location</b><br/>1000 Hilltop Circle<br/>21250 Baltimore' });google.maps.event.addListener(marker, 'click', function(){infowindow.open(map,marker);});infowindow.open(map,marker);}google.maps.event.addDomListener(window, 'load', init_map);</script>";
        $conn->close();
      }
    ?>
    </form>

    </div>
    
    <script src='js/vendor/jquery.js'></script>
    <script src='js/foundation.min.js'></script>
    <script>
      $(document).foundation();
    </script>
  </body>
</html>
