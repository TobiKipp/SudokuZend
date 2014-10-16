<?php
namespace Sudoku\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Sudoku\Model\Sudoku9;
use Sudoku\Model\SamuraiSudoku;

class SudokuController extends AbstractActionController
{

    public function indexAction()
    {
        $sm = $this->getEvent()->getApplication()->getServiceManager();
        $helper = $sm->get('viewhelpermanager')->get('headLink');
        $helper->prependStylesheet('/css/sudoku.css');

        $renderer = $this->getServiceLocator()->get('Zend\View\Renderer\PhpRenderer');
        $renderer->headTitle("Sudoku 9x9");
         $config = @$_GET["config"];
         if($config == null) $config = "";
         $operation = @$_GET["operation"];
         if($operation == null) $operation = "";
         $sudoku9 = new Sudoku9($config, $operation); 
         $view =  new ViewModel(array(
            'field' => $sudoku9->getField(),
             )
         );
         return $view;
    }

    public function getParams(){
        $defaultValues = array("config" => "", "operation" => "");
        $params = array();
        foreach($defaultValues as $param => $default){
            if(array_key_exists($param, $_GET)) $value = $_GET[$param];
            else $value = $default;
            $params[$param] = $value;
        }
        return $params;
    }

    public function sudoku9Action(){
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $params = $this->getParams();
        $sudoku9 = new Sudoku9($params["config"], $params["operation"]);
        $response->setContent(json_encode($sudoku9->getField())); 
        return $response;
    }

    public function samuraisudokuAction(){
        $response = $this->getResponse();
        $response->setStatusCode(200);
        $params = $this->getParams();
        $samuraisudoku = new SamuraiSudoku($params["config"], $params["operation"]);
        $response->setContent(json_encode($samuraisudoku->getField())); 
        return $response;
    }

    function extractConfigSudoku9(){
        $orderedValues = array();
        foreach ($_GET as $key => $value){
            if(substr($key,0,1) == "y" && substr($key,2,1) == "x")
            {
                $x = (int) substr($key,3,4);
                $y = (int) substr($key,1,2);
                $orderedValues[$y][$x] = $value; 
            }
        }

        $config = "";
        for ($y = 0; $y < 9; $y++){
            for($x = 0; $x < 9; $x++){
                $value = @$orderedValues[$y][$x];
                if($value == null) $value = "0";
                if($value == "" ) $value = "0";
                $config .= $value;
            }
        }
        return $config;
    }

    public function handleSudoku9Action(){
        $operation = $_GET["operation"];
        $config = $this->extractConfigSudoku9();
        $_GET["$config"] = $config;
        $this->redirect()->toUrl('/?config='.$config.'&operation='.$operation);
    }


   
}
