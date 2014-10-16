
Sudoku with Zend Framework
==========================

This is a pure transfer from the java version to php. A pure php version had issues with the mapping
being the directory structure.  It starts by using the example application with the album. 
This way I can always look up how things where written there.

Sudoku Module
---------------------

#Add the module#

To get things work the root config/application.config.php needs to have to module added.


#Adding the routes#

I will start with a single Controller. The Sudoku modules config/module.config.php contains the 
references to Controllers and the routes.

To get JSON responses I looked up some code, but instead of setBody the method is now called
setContent:

    public function sudoku9Action(){
        $response = $this->getResponse();
        $response->setStatusCode(200);

        $response->setContent(json_encode(array(1=>2)));
        return $response;
    }

The call to the mapped url will respond with {"1":2}.

As next step the passed get parameters shall be added to this array.
Here a missing parameter causes issues. As I did not find a premade soultion in an appropriate ammount
of time I decided to make my own default handling. 

Now http://sudoku.localhost/rest/sudoku9?config=1234 returns:

    {"config":"1234","operation":""}

Now it is time to integrate the models, which were already converted to PHP in my pure PHP attempt.
For some reason the pure php implementation behaves slightly different from the zend version. In the pure
version all was defaulted to empty string, while in zend it shows null.

When using namespaces the constructor must be \_\_construct.


#Fixes# 

The first issue was in the solver again with the array being a map. After using array\_values the index
0 could be used again for a 1 element array. 

The second issue is the bootstrap panel at the top having issues with the placement. This seems to have
to do with the renderer using the basic template and adding the stuff in my phtml file on top of it.
The issue was with one of my css rules. I removed it. In addition I changed the template to only contain
the body. To inject the additional css style the following code can be used:

        $sm = $this->getEvent()->getApplication()->getServiceManager();
        $helper = $sm->get('viewhelpermanager')->get('headLink');
        $helper->prependStylesheet('/css/sudoku.css');

It still uses the title from the application module layout.
By using lines

Module.php:

    public function init($mm)
    {
        $mm->getEventManager()->getSharedManager()->attach(__NAMESPACE__, 'dispatch', function($e) {
            $e->getTarget()->layout('Sudoku/layout');
        });
    }

module.config.php:
    
    'view_manager' => array(
        'template_map' => array(
            'Sudoku/layout'           => __DIR__ . '/../view/layout/sudokuLayout.phtml',
        ),

The layout defined in the Module can be used.

#Samurai Sudoku#

##Rest Service##
This time I will use the json api to fill the field values, instead of using the object directly.
First the Java versions need to be translated.
Here again the floor was needed to turn values to integers. I created a class Helper to use the arrayCopy 
method defined in there.

##The html page##

To find out which browser is used one can make use of:

    $_SERVER[‘HTTP_USER_AGENT’] 

Browsers using Chrome behave different than Firefox like browsers for background and therefore have slightly
different css files.

There were some issues with the display. After setting up the output to properly generate identated html 
and removing some errors it still did not show up correctly. I copied the generated code and then only imported
the css for the sudoku. The layout worked fine then. So what css file is the issue?
The issue is with bootstrap overwriting the default behaviour. After fixing the issues it solved the issue
of different implementations for chrome and firefox like browsers.

