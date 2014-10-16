<?php
namespace Sudoku\Model;
use Sudoku\Model\SudokuCell;
use Sudoku\Model\SudokuCellThread;
use Sudoku\Model\SudokuGroupThread;

class SamuraiSudoku{

    private $sudokuField = array();
    private $skipIndex = array( 
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(),
            array(),
            array(),
            array(0, 1, 2, 3 ,4, 5, 15, 16, 17, 18, 19, 20),
            array(0, 1, 2, 3 ,4, 5, 15, 16, 17, 18, 19, 20),
            array(0, 1, 2, 3 ,4, 5, 15, 16, 17, 18, 19, 20),
            array(),
            array(),
            array(),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11),
            array(9, 10, 11)
            );
    private $rowLength;
    static $possibleValues = array("1", "2", "3", "4", "5", "6", "7", "8", "9");
    
    function __construct($config, $operation){
        if($config == null) $config = "";
        if($operation == null) $operation = "";
        $this->init($config);
        if($operation == "solve"){
            $this->solve();
        }
    }

    function init($config){
        $this->rowLength = array();
        for($y = 0; $y < 21; $y++){
            $this->rowLength[] = 21 - count($this->skipIndex[$y]);
            $this->sudokuField[$y] = array();
        }
        $this->loadConfig($config);

    
    }
    /*
     * The digits 1 to 9 are interpreted as sudokuField value is known. Any other Stringacter will be interpreted
     * as the value is unknown.
     */
    function loadConfig($config){
       $configlength = strlen($config);
       for ($i = 0; $i < 369; $i++){
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
           $sudokuCell = new SudokuCell(SamuraiSudoku::$possibleValues);
           if (!$cellValue == ""){
               $sudokuCell->setValue($cellValue);
           }
          
           $this->sudokuField[$y][$x] = $sudokuCell;
       }
    }

    /*
     * Turns an index to the array coordinates.
     */
    function indexToXY($i){
        if ($i >= 369 ) return array(-1, -1);
        $rest = $i;
        $y = 0;
        while($rest >= $this->rowLength[$y]){
            $rest -= $this->rowLength[$y];
            $y++;
        }
        $x = -1; 
        while ($rest >= 0){
            $rest--;
            $x++;
            while (in_array($x, $this->skipIndex[$y])){
                $x++;        
            }
        } 
        return array($x, $y);
    }
    
    function getField(){
        $field = array();
        for ($y = 0; $y < 21; $y++){
            for($x = 0; $x < 21; $x++){
                if(array_key_exists($x, $this->sudokuField[$y])){
                    $field[$y][$x] = $this->sudokuField[$y][$x]->toString();
                }
                else{
                    $field[$y][$x] = "_";
                }
            }
        }
        return $field;
    }

    function toConfig(){
        $config = "";
        for ($y = 0; $y < 21; $y++){
            for($x = 0; $x < 21; $x++){
                if(in_array($x, $this->sudokuField[$y])){
                    $cellValue = $this->sudokuField[$y][$x]->toString();
                    if ($cellValue == ""){
                        $cellValue = "0";
                    }
                    $config .= $cellValue;
                }
            }
        }
        return $config;
    }

    function solve(){
        $timeoutCell = 40;
        $timeoutGroup = 20;
        $allThreads = array();
        for ($i=0; $i < 369; $i++){
           $xy = $this->indexToXY($i);
           $x = $xy[0];
           $y = $xy[1];
            
           $thread = new SudokuCellThread($this->sudokuField[$y][$x], $timeoutCell);
           $allThreads[] = $thread;
        }
        //First collect all indices.
        //41 blocks
        //45 rows
        //45 columns
        $sudokuGroups = array();
        $offsetsX = array(0, 12, 6,  0, 12);
        $offsetsY = array(0,  0, 6, 12, 12);
        //rows and colums and the blocks for the 4 outer sudoku9x9
        $blockOffset = 0;
        for($sudoku9x9 = 0; $sudoku9x9 < 5; $sudoku9x9++){
            $offsetX = $offsetsX[$sudoku9x9];        
            $offsetY = $offsetsY[$sudoku9x9];        
            for($i = 0; $i < 9; $i++){
                $blockTop = floor($i/3)*3;
                $blockLeft = $i%3*3;
                for($element=0; $element < 9; $element++){
                    //rows
                    $sudokuGroups[$i+$sudoku9x9*18][$element] = array($offsetY + $i, $offsetX + $element);
                    //columns
                    $sudokuGroups[$i+9+$sudoku9x9*18][$element] = array($offsetY + $element, $offsetX + $i);
                    //rows and colums take indices 0 to 89
                    //blocks starting at 90
                    if($sudoku9x9 != 2){
                        $y = floor($element/3) + $offsetY + $blockTop;
                        $x = $element%3 + $offsetX + $blockLeft;
                        $sudokuGroups[$i + $blockOffset * 9 + 90][$element] = array($y, $x);
                    }
                }
            }
            if($sudoku9x9 != 2) $blockOffset++;
        }
        //As check for the index to be at the correct poisition. The last used blockOffset is 3 and i is 9.
        // The last used index is: 8 + 3*9 + 90 = 125. This leaves 126 to 130 for the last 5 groups
        $boxStartX = array(9, 6, 9 , 12,  9);
        $boxStartY = array(6, 9, 9 ,  9, 12);
        for($box = 0; $box < 5; $box++){
            for($element=0; $element < 9; $element++){
                $y = floor($element/3) + $boxStartY[$box];
                $x = $element%3 + $boxStartX[$box];
                $sudokuGroups[126+$box][$element] = array($y, $x);
            }
        }

        //Then get the groups of SudokuCells according to the indices
        $sudokuCellGroups = array();
        for ($group=0; $group < 131; $group++){
            for($element = 0; $element < 9; $element++){
                $address = $sudokuGroups[$group][$element];
                $y = $address[0];
                $x = $address[1];
                $sudokuCellGroups[$group][$element] = $this->sudokuField[$y][$x];
            }
        }
         
        foreach ($sudokuCellGroups as $sudokuCellGroup){
            $thread = new SudokuGroupThread($sudokuCellGroup, $timeoutGroup);
            $allThreads[] = $thread;
        }

        //StatusThread for debugging in test mode. Shows up the so far solved field when running mvn package.
        //Running as daemon
        //Thread statusThread = new SudokuFieldStatusThread($this->sudokuField);
        //statusThread.start();
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
