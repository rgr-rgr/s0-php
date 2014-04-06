
<?php

class MyEventSignal {
    private $base, $ev;
    
        public function __construct($base) {
                $this->base = $base;
                $this->ev = Event::signal($base,SIGINT, array($this, 'eventSighandler'));
                $this->ev->add();
        }
                                    
        public function eventSighandler($no, $c) {
                global $filenames;
                echo "Caught signal $no\n";
                foreach($filenames as $fileName){
                  file_put_contents($fileName,0);
                } // foreach         
                $this->base->exit();
        }
}
                                                            
function getTimestamp() { 
     $seconds = microtime(true); // false = int, true = float 
     return round( ($seconds * 1000) ); 
}  // function getTimestamp
          
$base = new EventBase();

$c    = new MyEventSignal($base);

$n = 0.2;


$filenames=array('/sys/class/gpio/gpio11/value',
                '/sys/class/gpio/gpio8/value',
                '/sys/class/gpio/gpio7/value',
                '/mnt/1wire/3A.B8370D000000/PIO.A',
                '/mnt/1wire/3A.B8370D000000/PIO.B'
                );

foreach($filenames as $i) {
$e[$i] = new Event($base, -1,Event::PERSIST | Event::TIMEOUT,
function($fd, $events, $arg) use (&$e) {
    static $oldTimestamp = 0;
    static $flag=0;
    
    $timestamp=getTimestamp();
    $timeDiff=$timestamp-$oldTimestamp;
    if ($timeDiff<1000) {
//      echo "$timeDiff milliseconds elapsed $flag ".$arg."\n";
    } // if
    
    if ($flag==0) {
     $flag=1;
    } else {
     $flag=0;
    } // if
    file_put_contents($arg,$flag);
    $oldTimestamp=$timestamp;
    
    // $e->delTimer();
}
, $i);

$e[$i]->addTimer($n);
$n+=0.1;
usleep(330);
$base->loop(EventBase::LOOP_ONCE);
} // foreach
$base->loop();
?>
        