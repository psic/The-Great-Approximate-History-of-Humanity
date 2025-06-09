
<?php
echo "
<!DOCTYPE html>
<html>
<head>
<link rel='stylesheet' href='timeline.css'>
</head>
<body>
<h1>Timeline</h1>";
echo "Hello World!";

$tt_year_pas = 2;
$data = [
    0 =>[
      "start" =>2004,
      "end"=>2006,
      "title"=>"First event",
      "description"=>"This is the first event"
    ],
      1 =>[
      "start" =>2006,
      "end"=>2007,
      "title"=>"Second event",
      "description"=>"This is the second event"
    ],
      2 =>[
      "start" =>2010,
      "end"=>2012,
      "title"=>"Fourth event",
      "description"=>"This is the fourth event"
    ],
      3 =>[
      "start" =>2008,
      "end"=>2010,
      "title"=>"Third event",
      "description"=>"This is the third event"
    ],
    ];


   $tt_start = $data[0]["start"];
   $tt_end = $data[0]["end"];
   //min and max search
   foreach ($data as $tt_data)
   {
     if ($tt_data["start"] <$tt_start)
         $tt_start = $tt_data["start"];
    if($tt_data["end"] > $tt_end)
        $tt_end = $tt_data["end"];
   }


  //sort by date_start
  usort($data, function ($a, $b) {
if ($a['start'] == $b['start'])
{
   return 0;

}
return ($a['start'] > $b['start']) ? 1 : -1;});

  $width = 100/(count($data)+2);
  $half_width = $width/2;
$left = $half_width;


//TODO : Mettre les évenements qui ont une date de début avant la date fin de l'evenement précédent sur une autre ligne
$data_temp = $data;

//search for the max row (max recouvrement
foreach($data_temp as $row){
    $max_row_row= 1;
    $max_row =1;
      foreach($data_temp as $row2){
        if($row != $row2){
            if(
              ($row2["start"] >= $row["start"] && $row2["start"] <= $row["end"] ) ||
              ($row2["end"] >= $row["start"] && $row2["end"] <= $row["end"] ) ||
              ($row2["start"] <= $row["start"] && $row2["end"] >= $row["end"] )
            )
              $max_row ++;
        }
    }
    if ($max_row > $max_row_row){
      $max_row_row = $max_row;
    }
    $max_row = 1;
}

$data = $data_temp;




      echo '<ul class="timeline-events" style="padding-left:'.  $half_width + $width.'%;">';


   for ($i=0; $i < count($data) ; $i++){
     if($i == count($data)-1) {
              echo '<li style="width:' .($data[$i]["end"] - $data[$i]["start"]) * $width / $tt_year_pas  . '%">';
    }
    else{
       echo '<li style="width:' .($data[$i]["end"] - $data[$i]["start"]) * $width / $tt_year_pas  . '%; padding-right:'. ($data[$i+1]["start"] - $data[$i]["end"]) *$width / $tt_year_pas  .'%";>';
    }
       echo '<h2>' . $data[$i]["start"] . ' - ' . $data[$i]["end"] . '</h2>';
      echo '<h3>' . $data[$i]["title"] . '</h3>';
       echo '<h4>' . $data[$i]["description"] .  '</h4>';
      echo '<div style="background:'.rand_color().' ;height:20px;"/>';
       echo '</li>';
   }
   echo '</ul>';




   echo '<ul class="timelines-years" style="padding-left:'.  $half_width .'%; padding-right:'.  $half_width .'%">';
   for($i = $tt_start - $tt_year_pas; $i < $tt_end + $tt_year_pas ; $i = $i + $tt_year_pas){
       echo '<li style="width:'.$width.'% ">';
    // echo '<li width="300px">';
       echo $i;
       echo '</li>';
   }
   echo '</ul>';


   echo "</body>
</html>";

function rand_color() {
    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
}

?>

