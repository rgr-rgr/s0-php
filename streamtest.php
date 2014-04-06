<?php

$streamNames=array(
                'S0/0' => 27,
                'S0/1' => 23,
                'S0/2' => 22,
                'S0/3' => 24,
                'S0/4' => 10,
                'S0/5' => 9
              );
/*
*/


function getTimestamp() { 
     $seconds = microtime(true); // false = int, true = float 
     return round( ($seconds * 1000) ); 
}  // function getTimestamp

// exec('sudo chgrp -R dialout /sys/class/gpio');
// exec('chmod -R g+rw /sys/class/gpio');          

$streams=array();    // Liste der fileDescriptoren
$events=array();     // Liste der Events - einer pro Stream
$streamList=array(); // Zuordnung fileDescriptor zu streamName

$base = new EventBase();

foreach($streamNames as $streamName=>$gpio) {
    
    $streams[$streamName] = fopen("/sys/class/gpio/gpio$gpio/value",'r');
    $streamList[$streams[$streamName]]=$streamName;
    $events[$streamName] = new Event($base, $streams[$streamName], Event::READ | Event::PERSIST | Event::ET, 
        function ($fd, $events, $arg) {
            global $streamList;
            static $max_iterations = 0;
            static $oldTimestamp = 0;
//            global $oldTimestamp;
            
            $timeStamp= getTimestamp();
            
            
            if (++$max_iterations >= 500) {
                /* exit after 5 iterations with timeout of 2.33 seconds */
                echo "Stopping...\n";
                $arg[0]->exit(0);
            }
            if ($oldTimestamp<>0) {
                echo "Zeitdifferenz: " . $streamList[$fd].": ". ($timeStamp - $oldTimestamp) ."ms\n" ;
                echo "\n\n";
            }
            $oldTimestamp=$timeStamp;
        }
    ,array (&$base));
    $events[$streamName]->add();
} // foreach
$base->loop();
                                        
die(0);

$fh = fopen('/sys/class/gpio/gpio9/value','r');
$info=stream_get_meta_data($fh);
print_r($info);
while (!feof($fh)) {
  echo $body .= fgets($fh, 4096);
} // while

fclose($fh);

?>
