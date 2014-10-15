<?php
namespace Sudoku\Model;
use Sudoku\Model\TimeoutThread;

class SudokuCellThread extends TimeoutThread{
    var $sudokuCell;
    var $oldPossibleValues;

    function __construct($sudokuCell, $timeoutMax){
        parent::__construct($timeoutMax);
        $this->sudokuCell = $sudokuCell;
        $this->oldPossibleValues = array();
    }

    function update(){
        //Update the cell
        $this->sudokuCell->update();
        //Reset the timeout if anything has changed.
        $possibleValues = $this->sudokuCell->getPossibleValues();
        if(count($possibleValues) != count($this->oldPossibleValues)){
            $this->resetTimeout();
        }
        $this->oldPossibleValues = $possibleValues;
    }
    
    function execute(){
        $this->update();
        $running = !$this->sudokuCell->cellIsSet();
        return $running;
    }
}
?>
