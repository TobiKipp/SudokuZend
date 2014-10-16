<?php
namespace Sudoku\Model;
class SudokuCell{

    var $possibleValues;
    var $value;

    function __construct($possibleValues){
        $this->init();
        $this->setPossibleValues($possibleValues);
    }

    function init(){
        $this->possibleValues = array();
        $this->value = "";
    }

    function setPossibleValues($possibleValues){
        foreach ($possibleValues as $value){
            $this->possibleValues[] = $value;
        }
    }

    function setValue($value){
        $this->value = $value;
    }

    function getPossibleValues(){
        return $this->possibleValues;
    }

    function removePossibleValue($value){
        $this->possibleValues = array_values(array_diff($this->possibleValues, array($value)));
    }

    function cellIsSet(){
        return (!$this->value == "");
    }

    /*
     * Check if only one possible value is left and set it in that case.
     */
    function update(){
        if (count($this->possibleValues) == 1 && $this->value == ""){
            $this->value = array_values($this->possibleValues)[0];
        }
    }

    function toString(){
        return $this->value;
    }

    function limitPossibleValues($combination){
        $this->possibleValues = array_intersect($this->possibleValues, $combination);
    }

    function removePossibleValues($combination){
        $this->possibleValues = array_diff($this->possibleValues, $combination);
    }
}
?>
