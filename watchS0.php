
<?php

// resolutions means amount of Impulses per Kilowatt
$streams=array(
                array("gpio"=> 27, "name"=> "S0/0", "resolution"=>1000),
                array("gpio"=> 23, "name"=> "S0/1", "resolution"=>1000),
                array("gpio"=> 22, "name"=> "S0/2", "resolution"=>1000),
                array("gpio"=> 24, "name"=> "S0/3", "resolution"=>1000),
                array("gpio"=> 10, "name"=> "S0/4", "resolution"=>1000),
                array("gpio"=> 09, "name"=> "S0/5", "resolution"=>1000)
              );
                                                                                                              
//-------------------------------------------------
// don't touch below

class myEventSignal {
    private $base, $ev,$filenames;
    
        public function __construct($base,&$filenames) {
                $this->filenames = $filenames;
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
    private $base, $ev,$stream,$streamName.$oldTimestamp,$resolution;
    
        public function __construct($base,$filename,$stream,$streamName,$resolution) {
                $this->base = $base;
                $this->stream = fopen($filename,'r');
                $this->streamName=$streamName;
                $this->resolution=$resolution;
                $this->powerFactorPerMilliSecond=$this->resolution/(1000*(60*60*1000))
                $this->ev = new Event($base, $this->stream,Event::READ | Event::PERSIST | Event::ET, ,array($this,'eventStreamHandler'));
                $this->ev->add();
        } // __construct
                                    
        public function eventStreamHandler($args) {
                $timestamp = getTimestamp();
                if ($this->oldTimestamp<>0) {
                   echo "Zeitdifferenz: " . $this->streamName.": ". ($timestamp - $this->oldTimestamp) ."ms\n" ;
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
$c    = new myEventSignal($base,$filenames);

foreach($filenames as $i) {
 $e[$i] = new myEventTimer($base,$timeToWait,$i);
 usleep(330);
 $base->loop(EventBase::LOOP_ONCE);
} // foreach
$base->loop();

?>
        