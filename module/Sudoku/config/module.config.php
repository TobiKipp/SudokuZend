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
             'sudoku9rest' => array(
                 'type' => 'segment',
                 'options' => array(
                     'route' => '/rest/sudoku9',
                     'defaults' => array(
                         'controller' => 'Sudoku\Controller\Sudoku',
                         'action'     => 'sudoku9',
                     ),
                 ),
             ),

         ),
     ),


    'view_manager' => array(
        'template_path_stack' => array(
            'album' => __DIR__ . '/../view',
        ),
    ),
);
