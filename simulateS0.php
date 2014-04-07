
<?php

$timeToWait = 0.5;
$filenames=array('/sys/class/gpio/gpio11/value',
                '/sys/class/gpio/gpio8/value',
                '/sys/class/gpio/gpio7/value'
                );
/*
                '/mnt/1wire/3A.B8370D000000/PIO.A',
                '/mnt/1wire/3A.B8370D000000/PIO.B'
*/

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
                foreach($this->filenames as $fileName){
                  file_put_contents($fileName,0);
                } // foreach         
                $this->base->exit();
        }
}

class myEventTimer {
    private $base, $ev,$filename,$state;
    
        public function __construct($base,$time,$filename) {
                $this->base = $base;
                $this->filename = $filename;
                $this->state = file_get_contents($this->filename);
                $this->ev = new Event($base, -1,Event::PERSIST | Event::TIMEOUT,array($this,'eventTimerHandler'));
                $this->ev->addTimer($time);
        } // __construct
                                    
        public function eventTimerHandler($args) {
		echo "\ntimerEvent: ".$this->filename."\n";
		print_r($args);
    
                if ($this->state==0) {
                 $this->state=1;
                } else {
                 $this->state=0;
                } // if
                file_put_contents($this->filename,$this->state);
        } // eventTimerHandler

} // class myEventTimer
                                                            
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
        