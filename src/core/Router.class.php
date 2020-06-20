<?php

namespace App\Core;

/**
 * Class Router
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020 - Luvi
 * @link https://github.com/vinibs/luvi
 * @license MIT License
 */
class Router {
    /**
     * The class' instance (singleton model)
     * 
     * @var Router
     */
    private static $instance;

    /**
     * Routes list
     * 
     * @var array
     */
    private $routes;

    /**
     * Group to be used to new routes
     * 
     * @var string
     */
    private $currentGroup;

    /**
     * Initializes attributes values
     * 
     * @return void
     */
    private function __construct () { 
        ob_start();
        $this->routes = array();
        $this->currentGroup = '/';
        $this->group($this->currentGroup);
    }

    /**
     * Registers a route in class' routes list
     * 
     * @param string $path The route path
     * @param string $action What should be called for the route
     * @param string $httpMethod What HTTP method should be listened
     * 
     * @return void
     */
    private function registerRoute (
        string $path, 
        string $action, 
        string $httpMethod
    ) : void {
        // If route's path is empty (root), is considered as slash
        if (empty($path)) {
            $path = '/';
        }

        // Check if route's path format is valid
        $regexUriFormat = '/^\/?(([A-z0-9\_\-\+]+|\{[A-z0-9\_\-]+\})\/?)*$/';
        if (!preg_match($regexUriFormat, $path)) {
            // No, then throws exception
            $tokenParams = [
                'path' => $path,
                'method' => strtoupper($httpMethod)
            ];
            try {
                $errorMessage = I18n::get(
                    'error.invalid_path_route', 
                    $tokenParams
                );
                throw new \Exception ($errorMessage);
            } 
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Checks the route's action format
        $regexActionFormat = '/^[A-z0-9\_]+\:[A-z0-9\_]+$/';
        if (!preg_match($regexActionFormat, $action)) {
            // It is invalid, throws exception
            $tokenParams = [
                'path' => $path,
                'method' => strtoupper($httpMethod)
            ];
            try {
                $errorMessage = I18n::get(
                    'error.invalid_action_route', 
                    $tokenParams
                );
                throw new \Exception ($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $httpMethod = strtolower($httpMethod);

        // If route's last character is "/", removes 
        // it from string
        if (substr($path, -1) === '/' && strlen($path) > 1) {
            $path = substr($path, 0, -1);
        }

        // Sets route's attributes
        $route = [
            'group' => $this->currentGroup,
            'uri' => $path,
            'action' => $action,
            'params' => array()
        ];

        // If the given HTTP method's index doesn't exist in the list
        if (!key_exists($httpMethod, $this->routes)) {
            $this->routes[$httpMethod] = array();
        }

        // If there is no index with for given group in the HTTP method's list
        if (!key_exists($this->currentGroup, $this->routes[$httpMethod])) {
            $this->routes[$httpMethod][$this->currentGroup] = array();
        }

        array_push($this->routes[$httpMethod][$this->currentGroup], $route);
    }

    /**
     * Calls the action registered to process the route
     * 
     * @param array $route The route data
     * 
     * @return void
     * 
     * @throws \Exception If controller could not be found
     * @throws \Exception If method could not be found
     */
    private function callRouteAction (array $route) : void
    {
        // If it can't find the 'action' index in route or
        // the action is null or empty, throws an exception
        // with an error message
        if (
            !isset($route['action']) 
            || is_null($route['action']) 
            || empty($route['action'])
        ) {
            $tokenParams = [
                'route' => $route['uri'],
            ];
            try {
                $errorMessage = I18n::get(
                    'error.undefined_action_route', 
                    $tokenParams
                );   
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Split the route's action parts (controller and method)
        $actionParts = explode(':', $route['action']);
        $controllerName = $actionParts[0];
        $methodName = $actionParts[1];

        // Build the controllers name with namespace
        $controllerFullName = namespacePrefix . 'Controller\\' 
            . $controllerName;

        // Checks if controller's declared class exists
        if (!class_exists($controllerFullName)) {
            // It doesn't, throws exception
            $tokenParams = [
                'controller' => $controllerName,
            ];
            try {
                $errorMessage = I18n::get(
                    'error.controller_not_found', 
                    $tokenParams
                );
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Intantiate the controller's class
        $controller = new $controllerFullName();

        // Checks if the declared action methos exists in class
        if (!method_exists($controller, $methodName)) {
            // It doesn't, throws excenption
            $tokenParams = [
                'method' => $methodName,
                'controller' => $controllerName
            ];
            try {
                $errorMessage = I18n::get(
                    'error.method_not_found', 
                    $tokenParams
                );
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Merge route's params with GET default params
        $requestParams = array_merge($route['params'], $_GET);
        // Also adds all params to global $_GET variable
        $_GET = $requestParams;

        // Runs controller's method, passing the parameters
        $controller->$methodName($requestParams);
        return;
    }

    /**
     * Converts the route in a regex
     * 
     * @param array $route The route data
     * 
     * @return string
     */
    private function generateRouteRegex (array $route) : string
    {
        $routeToRegex = $route['uri'];

        // Adds a conditional to path's beginning slash
        if (substr($routeToRegex, 0, 1) === '/') {
            $routeToRegex = '/?' . substr($routeToRegex, 1);
        }
        // Escape route's slashes
        $regexRoute = '/^' 
            . str_replace('/', '\/', $routeToRegex) . '\/?$/';
        // Change parameters to identify any value without "/"
        $regexRoute = preg_replace(
            '/\{[a-z]+\}/', 
            '[^\/]+', 
            $regexRoute
        );
        
        return $regexRoute;
    }

    /**
      * Gets or creates a singleton's instance
     * 
     * @return Router
     */
    public static function getInstance () : Router
    {
        // It wasn't created yet?
        if (is_null(self::$instance)) {
            // Creates a new instance
            self::$instance = new self;
        }

        // Returns the class' instance
        return self::$instance;
    }

    /**
     * Calls the needed action for the route
     * 
     * @return void
     * 
     * @throws \Exception If a parameter's value is invalid
     */
    public function process () : void
    {
        // Gets current URL
        $requestUri = filter_input(
            \INPUT_SERVER, 
            'REQUEST_URI', 
            \FILTER_SANITIZE_SPECIAL_CHARS
        );

        // Splits request URL in "?", if there 
        // are usual GET parameters
        $uriParts = explode('?', $requestUri);
        // Registers only the first URL part as path
        $requestUri = $uriParts[0];
        
        // If last URL character is "/", remove it from string
        if (substr($requestUri, -1) === '/' && strlen($requestUri) > 1) {
            $requestUri = substr($requestUri, 0, -1);
        }

        // Gets the request's HTTP method
        $httpMethod = strtolower(filter_input(
            \INPUT_SERVER, 
            'REQUEST_METHOD', 
            \FILTER_SANITIZE_SPECIAL_CHARS
        ));

        // Stores the found route
        $foundRoute = null;

        // Is there an index to current HTTP method in routes' array?
        if (isset($this->routes[$httpMethod])) {
            // Passes through groups of routes defined by current HTTP method
            foreach ($this->routes[$httpMethod] as $group => $groupRoutes) {
                // Checks if current URL is part of route's group
                $len = strlen($group);
                if (strncmp($group, $requestUri, $len) !== 0) {
                    // No, then goes to the next group
                    continue;
                }

                // Checks if any route in the group corresponds to current URL
                foreach ($groupRoutes as $index => $route) {
                    // Processes route's path to add its group
                    $processedRoute = $group;

                    // If there is no slash to separate group and 
                    // route path, adds it
                    if (
                        substr($route['uri'], 0, 1) !== '/' && 
                        substr($processedRoute, -1) !== '/'
                    ) {
                        $processedRoute .= '/';
                    }
                    // If group ends in "/" and the route starts with an "/",
                    // removes the slash from the end of group string
                    else if (
                        substr($processedRoute, -1) === '/' &&
                        substr($route['uri'], 0, 1) === '/'
                    ) {
                        $len = strlen($processedRoute) - 1;
                        $processedRoute = substr($processedRoute, 0, $len);
                    }
                    // Adds the basic route to the processed one
                    $processedRoute .= $route['uri'];
                    
                    // Removes slash at the end of processed route, if needed
                    if (
                        substr($processedRoute, -1) === '/' && 
                        strlen($processedRoute) > 1
                    ) {
                        $len = strlen($processedRoute) - 1;
                        $processedRoute = substr($processedRoute, 0, $len);
                    }

                    // Redefine URL with group, avoiding double slashes
                    $processedRoute = str_replace('//', '/', $processedRoute);
                    $route['uri'] = $processedRoute;

                    // Is route exactly the same as the current URL?
                    if (strcmp($processedRoute, $requestUri) === 0) {
                        // Yes, sets the found route
                        $foundRoute = $route;
                        break;
                    }
                    else {
                        // Gets the regex for the currently checked route
                        $regexRoute = $this->generateRouteRegex($route);

                        // Does the current URL correspond to a param route?
                        if (preg_match($regexRoute, $requestUri) === 1) {
                            // Yes, then checks:
                            // There was a found route for this URL?
                            if (!is_null($foundRoute)) {
                                // Yes, then extract the group options to
                                // check if the found route is specific for
                                // this URL group
                                $groupLen = strlen($foundRoute['group']);
                                // Gets a substring from the route with the
                                // group's length
                                $substrGroup = substr(
                                    $foundRoute['uri'], 
                                    0, 
                                    $groupLen
                                );
                                // Gets the rest of route's path, without the
                                // first [group string's length] characters
                                $noGroupUri = substr(
                                    $foundRoute['uri'], 
                                    $groupLen
                                );

                                // Checks if the extracted group is the same
                                // as the defined one and if the route, with
                                // the group, is larger than the route 
                                // without it
                                if (
                                    strcmp(
                                        $foundRoute['group'], 
                                        $substrGroup
                                    ) == 0 &&
                                    strcmp($foundRoute['uri'], $noGroupUri) > 0
                                ) {
                                    // Yes, so ignores current route because
                                    // the previous one is specific for the
                                    // group
                                    break;
                                }
                            }
        
                            // Defines the found route
                            $foundRoute = $route;
                            $routeParams = array();

                            // Splits URL and route in its slashes
                            $routeParts = explode('/', $processedRoute);
                            $urlParts = explode('/', $requestUri);
                            
                            // Checks URL parts to identify its params
                            foreach ($urlParts as $i => $urlPart) {
                                // Regex to identify param definition syntax
                                $regexParam = '/^\{[A-z0-9\_\-]+\}$/';
                                // Checks if route's part corresponds to the
                                // parameter syntax
                                if (
                                    preg_match(
                                        $regexParam, 
                                        $routeParts[$i]
                                    ) === 1
                                ) {
                                    // Yes, then route's part is a param
                                    
                                    // Extracts parameter's name
                                    $paramName = preg_replace(
                                        '/[\{\}]/', 
                                        '', 
                                        $routeParts[$i]
                                    );

                                    // Are the characters used in 
                                    // this parameter valid?
                                    $regexEncodedChars = 
                                        '/^[A-z0-9\-\_\.\+\%]+$/';
                                    if (
                                        preg_match(
                                            $regexEncodedChars, 
                                            $urlPart
                                        ) !== 1
                                    ) {
                                        // No, generates an error message
                                        $tokenParams = [
                                            'value' => $urlPart
                                        ];
                                        try {
                                            $errorMessage = I18n::get(
                                                'error.invalid_value_url_parameter',
                                                $tokenParams
                                            );
                                        }
                                        catch (\Exception $e) {
                                            echo $e->getMessage();
                                        }

                                        // Try to load the error view with
                                        // the message
                                        try {
                                            View::load(
                                                'error/400', 
                                                400,
                                                ['errorMessage' => $errorMessage]
                                            );
                                            return;
                                        }
                                        // Else, generates an exception
                                        catch (\Exception $e) {
                                            http_response_code(400);
                                            throw new \Exception($errorMessage);
                                        }
                                    }
                                    
                                    // Adds the parameter and its value to
                                    // the params array
                                    $routeParams[$paramName] = $urlPart;
                                }
                            }

                            $foundRoute['params'] = $routeParams;
                            break;
                        }
                    }
                }
            }
        }

        // There was found a route to current URL?
        if (!is_null($foundRoute)) {
            // Yes, runs the route's action
            try {
                $this->callRouteAction($foundRoute);
            }
            // Show the exception message if can't load the route's action
            catch (\Exception $e) {
                echo $e->getMessage();
            }
            return;
        }
        
        // Didn't find any route, loads the 404 view
        try {
            View::load('error/404', 404);
        }
        catch (\Exception $e) {
            // If the default error view doesn't exist, 
            // just show the exception message
            echo $e->getMessage();
        }
        return;
    }

    /**
     * Defines the group to be used for the next defined routes
     * 
     * @param string $groupBaseUri The base part of the routes group
     * 
     * @return Router
     */
    public function group ($groupBaseUri) : Router
    {
        $this->currentGroup = $groupBaseUri;
        return $this;
    }

    /**
     * Registers a route for the GET method
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function get ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'get');
        return $this;
    }

    /**
     * Registers a route for the POST method
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function post ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'post');
        return $this;
    }

    /**
     * Registers a route for the PUT method
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function put ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'put');
        return $this;
    }

    /**
     * Registers a route for the DELETE method
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function delete ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'delete');
        return $this;
    }

    /**
     * Registers a route for the OPTIONS method
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function options ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'options');
        return $this;
    }
}