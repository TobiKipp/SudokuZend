<?php
namespace Sudoku\Model;
use Sudoku\Model\Helper;
use Sudoku\Model\TimeoutThread;
class SudokuGroupThread extends TimeoutThread{
    var $sudokuCells;
    var $maxClusterSize;

    function __construct($sudokuCells, $timeoutMax){
        parent::__construct($timeoutMax);
        $this->sudokuCells = $sudokuCells;
        $this->maxClusterSize = 4;
    }

    function getStatistic(){
        $possibleValueCounters = array();
        foreach ($this->sudokuCells as $sudokuCell){
            $possibleValues = $sudokuCell->getPossibleValues();
            foreach ($possibleValues as $value){
                $oldCounter = 0;
                if (array_key_exists($value, $possibleValueCounters)){
                    $oldCounter = $possibleValueCounters[$value];
                }
                $possibleValueCounters[$value] = $oldCounter+1;
            }
        
        }
        return $possibleValueCounters;
    }

    function getSudokuCells(){
        return $this->sudokuCells;
    }

    function getCellsWithOneOf($clusterValues){
        $matchingSudokuCells = array();
        foreach ($this->sudokuCells as $sudokuCell){
            $possibleValues = $sudokuCell->getPossibleValues();    
            foreach ($possibleValues as $possibleValue){
                if(in_array($possibleValue, $clusterValues)){
                    $matchingSudokuCells[] = $sudokuCell;
                    break;
                }
            }
        }
        return $matchingSudokuCells;
    }
    
    function getFirstSetCellIndex(){
        $cellIndex = -1;
        for($i = 0; $i < count($this->sudokuCells); $i++){
            $cell = $this->sudokuCells[$i];
            if ($cell->cellIsSet()){
                $cellIndex = $i;
                break;
            }
        }
        return $cellIndex;
    }

    function handleSetCell($cellIndex){
        $this->resetTimeout();
        $sudokuCell = $this->sudokuCells[$cellIndex];
        $value = $sudokuCell->toString();
        //remove its value from the possibles of the others
        for($i = 0; $i < count($this->sudokuCells); $i++){
            if($i != $cellIndex){
                $this->sudokuCells[$i]->removePossibleValue($value);   
            }
        }
        //The cell has no more valuable information and is removed
        unset($this->sudokuCells[$cellIndex]);
        $this->sudokuCells = array_values($this->sudokuCells);
    }

    /*
     * allCombinations is like a return value
     */
    function generateAllNElementCombinations($n, $combination, $valuesLeft, &$allCombinations){
        if($n==1){
            foreach ($valuesLeft as $value){
                 $finalCombination = Helper::arrayCopy($combination);
                 $finalCombination[] = $value;
                 $allCombinations[] = $finalCombination;
            }
        }
        else{
            $valuesLeftWithoutLastN = array_slice($valuesLeft, 0, count($valuesLeft) - $n + 1);
            
            //The valuesLeftCopy will hagve the currently selected element removed in each loop, so it will
            //get smaller with each loop and prevent duplicated combinations.
            $valuesLeftCopy = Helper::arrayCopy($valuesLeft);
            foreach ($valuesLeftWithoutLastN as $key => $value){
                 $interCombination = Helper::arrayCopy($combination);
                 $interCombination[] = $value;
                 $valuesLeftCopy = array_values(array_diff($valuesLeftCopy, array($value)));
                 $this->generateAllNElementCombinations($n-1, $interCombination, $valuesLeftCopy, $allCombinations);
            }
        }
    }

    function findExactlyNCells($allCombinations, $n, &$matchCells, &$combination){
        for($i = 0 ; $i < count($allCombinations); $i++){
            $combi = $allCombinations[$i];
            $potentialCells = $this->getCellsWithOneOf($combi);
            if (count($potentialCells) == $n){
                foreach ($combi as $val){
                    $combination[] = $val;
                }
                foreach( $potentialCells as $cell){
                    $matchCells[] = $cell;
                }
                break;
            }
        }
    }

    function eliminateWithCluster($clusterSize){
        $eliminated = false;
        //Search the statistic for count of clusterSize. 
        $matchCountValues = array();
        $possibleValueCounters = $this->getStatistic(); 
        $values = array_keys($possibleValueCounters);
        foreach ($values as $value){
            if ($possibleValueCounters[$value] == $clusterSize){
                $matchCountValues[] = $value;
            }
        }
        //If there are at least clusterSize values with this count then test all combinations of selecting 
        //clusterSize elements from them. Stop early when a match is found.
        if (count($matchCountValues) >= $clusterSize){
           $allCombinations = array();
           $combination = array();
           $this->generateAllNElementCombinations($clusterSize, $combination, $matchCountValues, $allCombinations);
           $matchCells = array();
           $matchCombination = array();
           $this->findExactlyNCells($allCombinations, $clusterSize, $matchCells, $matchCombination);
           if(count($matchCells) != 0){
               foreach ($this->sudokuCells as $sudokuCell){
                   if(in_array($sudokuCell, $matchCells)){
                       $sudokuCell->limitPossibleValues($matchCombination);
                   }
                   else{
                       $sudokuCell->removePossibleValues($matchCombination);
                   }
               }
               $eliminated = true;
           }
        }
    return $eliminated;
    }

    function handlePotentialCluster(){
        for ($clusterSize = 1; $clusterSize < $this->maxClusterSize; $clusterSize++){
            $foundPair = $this->eliminateWithCluster($clusterSize);
            if ($foundPair) break;
        }
    }


    function execute(){
        $cellIndex = $this->getFirstSetCellIndex();
        if ($cellIndex != -1){
            $this->handleSetCell($cellIndex);
        }
        else{
            $this->handlePotentialCluster(); 
        }
        $running = (count($this->sudokuCells) != 0);
        return $running;
    }
}
?>
