<?php
namespace Sudoku\Model;
class TimeoutThread{// extends Thread{
    var $timeoutMax;
    var $timeout;

    function __construct($timeoutMax){
        $this->timeoutMax = $timeoutMax;
        $this->timeout = $timeoutMax;
    }
    
    function run(){
        try{
            $running = true;
            while ($running){
                $this->timeout -= 1;
                if($this->timeout < 0){
                    break;
                }
                $running = $this->execute();
                sleep(100);
            }
        }
        catch (Exception $e){
            echo 'Exception: '.$e->getMessage();
        }
    }

    /*
     * A single loop cycle inside the thread run method. On returning false 
     * the thread will stop.
     */
    function execute(){
        return false;
    }

    /*
     *   The timeout can be reset to make the thread only run out of time, when
     *   no more change occurs for the timeout.
     */
    function resetTimeout(){
        $this->timeout = $this->timeoutMax;
    }
}
?>
