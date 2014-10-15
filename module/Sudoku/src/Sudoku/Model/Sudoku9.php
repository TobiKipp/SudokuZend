<?php
namespace Sudoku\Model;
use Sudoku\Model\SudokuCell;
use Sudoku\Model\SudokuCellThread;
use Sudoku\Model\SudokuGroupThread;

function arrayCopy( array $array ) {
    $result = array();
    foreach( $array as $key => $val ) {
        if( is_array( $val ) ) {
            $result[$key] = arrayCopy( $val );
        } elseif ( is_object( $val ) ) {
            $result[$key] = clone $val;
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
    }
class Sudoku9{

    private $sudokuField;
    static $possibleValues = array("1", "2", "3", "4", "5", "6", "7", "8", "9");

    function __construct($config, $operation){
        $this->init($config);
        if($operation == "solve"){
            $this->solve();
        }
    }

    function init($config){
        $this->sudokuField = array();
        for($y = 0; $y < 9; $y++){
            $this->sudokuField[] = array();
            for($x = 0; $x < 9; $x++){
                $this->sudokuField[$y][] = new SudokuCell(Sudoku9::$possibleValues);
            }
        }
        $this->loadConfig($config);
    }

    function loadConfig($config){
        $configlength = strlen($config);
        for ($i = 0; $i < 81; $i++){
            $xy = $this->indexToXY($i);
            $x = $xy[0];
            $y = $xy[1];

            $cellValue = "";
            if ($i < $configlength){
                $configValue = $config[$i];
                if(is_numeric($configValue) && $configValue != '0'){
                    $cellValue .= $configValue;
                }
            }
            $sudokuCell = new SudokuCell(Sudoku9::$possibleValues);
            if ($cellValue != ""){
                $sudokuCell->setValue($cellValue);
            }
            $this->sudokuField[$y][$x] = $sudokuCell;
        }
    }

    static function indexToXY($i){
        $x = $i%9;
        $y = floor($i/9);
        return array($x, $y);
    }

    function getField(){
        $field = array(); 
        for ($y = 0; $y < 9; $y++){
            $row = array();
            for($x = 0; $x < 9; $x++){
                $value = $this->sudokuField[$y][$x]->toString();
                if($value == null) $value = "";
                $row[] = $value;
            }
            $field[] = $row;
        }
        return $field;
    }

    function toConfig(){
        $config = "";
        for ($y = 0; $y < 9; $y++){
            for($x = 0; $x < 9; $x++){
                $cellValue = $this->sudokuField[$y][$x]->toString();
                if ($cellValue == ""){
                    $cellValue = "0";
                }
                $config .= $cellValue;
            }
        }
        return $config;
    }

    function solve(){
        $timeoutCell = 40;
        $timeoutGroup = 20;
        $allThreads = array();
        for ($i = 0; $i < 81; $i++){
           $xy = $this->indexToXY($i);
           $x = $xy[0];
           $y = $xy[1];
           $thread = new SudokuCellThread($this->sudokuField[$y][$x], $timeoutCell);
           $allThreads[] = $thread;
        }
        //First collect all indices.
        //9 blocks 9 rows 9 colums are 27 groups
        //each group has 9 elements
        //each element has 2 components x and y to access it. 
        $sudokuGroups = array();//Note in Java it was: new int[27][9][2];
        for($i = 0; $i < 9; $i++){
            $blockTop = floor($i/3)*3;
            $blockLeft = $i%3*3;
            for($element=0; $element < 9; $element++){
                //rows
                $sudokuGroups[$i][$element] = array($i, $element);
                //columns
                $sudokuGroups[$i+9][$element] = array($element, $i);
                
                //blocks
                $y = floor($element/3);
                $x = $element%3;
                $sudokuGroups[$i+18][$element] = array($blockTop+$y, $blockLeft+$x);
            }
        }
        //for ($group = 0; $group < 27; $group++){
        //    echo $group.":\n";
        //    for($element = 0; $element < 9; $element++){
        //        $address = $sudokuGroups[$group][$element];
        //        echo $address[0].",".$address[1]."=> ".$this->sudokuField[$address[0]][$address[1]]->toString().";  ";
        //     }
        //    echo "<br>";
        //}
        
        //Then get the groups of SudokuCells according to the indices
        $sudokuCellGroups = array();//Note in Java it was: new SudokuCell[27][9];
        for ($group = 0; $group < 27; $group++){
            for($element = 0; $element < 9; $element++){
                $address = $sudokuGroups[$group][$element];
                $y = $address[0];
                $x = $address[1];
                $sudokuCellGroups[$group][$element] = $this->sudokuField[$y][$x];
            }
        }
         
        foreach ($sudokuCellGroups as $sudokuCellGroup){
            $allThreads[] = new SudokuGroupThread($sudokuCellGroup, $timeoutGroup);
        }

        //StatusThread for debugging in test mode. Shows up the so far solved field when running mvn package.
        //Running as daemon
        //Thread statusThread = new SudokuFieldStatusThread(this.sudokuField);
        //statusThread.start();

        //////Start all threads
        ////foreach ($allThreads as $thread){
        ////    $thread->start();
        ////}
        //////Wait for all threads to finish
        ////foreach ($allThreads as $thread){
        ////    try{
        ////        $thread->join();
        ////    }
        ////    catch (Exception $e){
        ////    }
        ////}
        while(count($allThreads) != 0){ 
            $removeThreads = array();
            foreach ($allThreads as $key => $thread){
                try{
                    $thread->timeout -= 1;
                    if($thread->timeout < 0){
                        unset($allThreads[$key]);
                    }
                    $thread->execute();
                }
                catch (Exception $e){
                    //echo 'Exception: '.$e->getMessage();
                }
            }
        }
    }
        
}

if(count(debug_backtrace()) == 0)//run only if called directly
{
$config = $_GET["config"];
$operation = $_GET["operation"];
$sudoku9 = new Sudoku9($config, $operation);
echo '{';
echo '"field":'.json_encode($sudoku9->getField());
echo '}';
}
?>
