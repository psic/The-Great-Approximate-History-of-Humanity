
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
      "end"=>2008,
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
   foreach ($data as $tt_data)
   {
     if ($tt_data["start"] <$tt_start)
         $tt_start = $tt_data["start"];
    if($tt_data["end"] > $tt_end)
        $tt_end = $tt_data["end"];
   }


      echo '<ul class="timeline-events">';
   foreach ($data as $tt_data){
       echo '<li>';
       echo '<h2>' . $tt_data["start"] . ' - ' . $tt_data["end"] . '</h2>';
      echo '<h3>' . $tt_data["title"] . '</h3>';
       echo '<h4>' . $tt_data["description"] .  '</h4>';

       echo '</li>';
   }
   echo '</ul>';


   echo '<ul class="timelines-years">';
   for($i = $tt_start - $tt_year_pas; $i < $tt_end + $tt_year_pas ; $i = $i + $tt_year_pas){
       echo '<li>';
       echo $i;
       echo '</li>';
   }
   echo '</ul>';


   echo "</body>
</html>";

?>

