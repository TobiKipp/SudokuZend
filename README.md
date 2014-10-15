
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

When using namespaces the constructor must be __construct.

