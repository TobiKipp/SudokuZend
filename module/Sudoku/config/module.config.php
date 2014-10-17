<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Sudoku\Controller\Sudoku' => 'Sudoku\Controller\SudokuController',
        ),
    ),

    'router' => array(
         'routes' => array(
             'sudoku' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/sudoku',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'index',
                     ),
                 ),
             ),
             'samuraisudoku' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/samuraisudoku',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'samuraisudoku',
                     ),
                 ),
             ),
             'sudoku9rest' => array(
                 'type' => 'segment',
                 'options' => array(
                     'route' => '/rest/sudoku9',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'sudoku9rest',
                     ),
                 ),
             ),
             'samuraisudokurest' => array(
                 'type' => 'segment',
                 'options' => array(
                     'route' => '/rest/samuraisudoku',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'samuraisudokurest',
                     ),
                 ),
             ),
             'handleSudoku9' => array(
                 'type' => 'segment',
                 'options' => array(
                     'route' => '/handle/sudoku9',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'handleSudoku9',
                     ),
                 ),
             ),
             'handleSamuraiSudoku' => array(
                 'type' => 'segment',
                 'options' => array(
                     'route' => '/handle/samuraisudoku',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'handleSamuraiSudoku',
                     ),
                 ),
             ),

         ),
     ),


    'view_manager' => array(
        'template_map' => array(
            'Sudoku/layout'           => __DIR__ . '/../view/layout/sudokuLayout.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),

);
