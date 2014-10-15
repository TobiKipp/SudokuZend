<?php
namespace Sudoku\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Sudoku\Model\Sudoku9;

class SudokuController extends AbstractActionController
{

    public function indexAction()
    {
         return new ViewModel(array(
             'data' => array("A"=>"B")
             )
         );
    }

    public function sudoku9Action(){
        $response = $this->getResponse();
        $response->setStatusCode(200);

        $defaultValues = array("config" => "", "operation" => "");
        $params = array();
        foreach($defaultValues as $param => $default){
            if(array_key_exists($param, $_GET)) $value = $_GET[$param];
            else $value = $default;
            $params[$param] = $value;
        }
        $sudoku9 = new Sudoku9($params["config"], $params["operation"]);

        $response->setContent(json_encode($sudoku9->getField())); 
        return $response;
    }

   
}
