
<?php

// resolutions means amount of Impulses per Kilowatt
$streams=array(
                array("gpio"=> 27, "name"=> "S0/0", "resolution"=>1000),
                array("gpio"=> 23, "name"=> "S0/1", "resolution"=>1000),
                array("gpio"=> 22, "name"=> "S0/2", "resolution"=>1000),
                array("gpio"=> 24, "name"=> "S0/3", "resolution"=>1000),
                array("gpio"=> 10, "name"=> "S0/4", "resolution"=>2000),
                array("gpio"=> 9, "name"=> "S0/5", "resolution"=>1000)
              );
                                                                                                              
//-------------------------------------------------
// don't touch below

class myEventSignal {
    private $base, $ev;
    
        public function __construct($base) {
                $this->base = $base;
                $this->ev = Event::signal($base,SIGINT, array($this, 'eventSighandler'));
                $this->ev->add();
        }
                                    
        public function eventSighandler($no, $c) {
                global $filenames;
                echo "Caught signal $no\n";
                $this->base->exit();
        }
}

class myEventStream {
    private $base, $ev,$stream,$streamName,$oldTimestamp,$resolution;
    
        public function __construct($base,$filename,$streamName,$resolution) {
                $this->base = $base;
                $this->stream = fopen($filename,'r');
                $this->streamName=$streamName;
                $this->resolution=$resolution;
                $this->powerFactorPerMilliSecond=((60*60*1000))/($this->resolution * 1000);
                $this->ev = new Event($base, $this->stream , Event::READ | Event::PERSIST | Event::ET, array($this,'eventStreamHandler'));
                $this->ev->add();
        } // __construct
                                    
        public function eventStreamHandler($args) {
                $timestamp = getTimestamp();
                if ($this->oldTimestamp<>0) {
                   $timeDiff=($timestamp - $this->oldTimestamp);
                   echo "Zeitdifferenz: " . $this->streamName.": $timeDiff ms entspricht ".$timeDiff*$this->powerFactorPerMilliSecond ." Watt\n" ;
                   echo "\n\n";
                } // if
                $this->oldTimestamp=$timestamp;                                                                                           
        } // eventStreamHandler
} // class myEventStream
                                                            
function getTimestamp() { 
     $seconds = microtime(true); // false = int, true = float 
     return round( ($seconds * 1000) ); 
}  // function getTimestamp

$base = new EventBase();
$c    = new myEventSignal($base);
$e = array();

foreach($streams as $i) {
 $filename="/sys/class/gpio/gpio".$i["gpio"]."/value";
 echo "Filename: $filename\n";
 $e[] = new myEventStream($base,$filename,$i["name"],$i["resolution"]);
} // foreach
$base->loop();

?>
        