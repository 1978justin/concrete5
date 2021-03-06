<?php
namespace Concrete\Core\Routing;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class Router implements RouterInterface
{

    /**
     * @var RouteActionFactoryInterface
     */
    protected $actionFactory;

    /**
     * @var RouteCollection
     */
    protected $routes;

    public function __construct(
        RouteCollection $routes,
        RouteActionFactoryInterface $actionFactory
    ){
        $this->routes = $routes;
        $this->actionFactory = $actionFactory;
    }

    private function normalizePath($path)
    {
        return '/' . trim($path, '/') . '/';
    }

    private function createRouteBuilder($path, $action, $methods)
    {
        $route = new Route($this->normalizePath($path));
        $route->setMethods($methods);
        $route->setAction($action);
        return new RouteBuilder($this, $route);
    }

    public function buildGroup()
    {
        return new RouteGroupBuilder($this);
    }

    public function get($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['GET']);
    }

    public function head($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['HEAD']);
    }

    public function post($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['POST']);
    }

    public function put($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['PUT']);
    }

    public function patch($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['PATCH']);
    }

    public function delete($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['DELETE']);
    }

    public function options($path, $action)
    {
        return $this->createRouteBuilder($path, $action, ['OPTIONS']);
    }

    public function all($path, $action)
    {
        return $this->createRouteBuilder($path, $action, [
            'GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE', 'OPTIONS'
        ]);
    }

    /**
     * @param Route $route
     * @return RouteActionInterface
     */
    public function resolveAction(Route $route)
    {
        return $this->actionFactory->createAction($route);
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return RouteActionFactoryInterface
     */
    public function getActionFactory()
    {
        return $this->actionFactory;
    }

    public function addRoute(Route $route)
    {
        $this->routes->add($route->getName(), $route);
    }

    /**
     * @param Request $request
     * @return null|MatchedRoute|ResourceNotFoundException|MethodNotAllowedException
     */
    public function matchRoute(Request $request)
    {
        $matcher = new UrlMatcher(
            $this->getRoutes(),
            id(new RequestContext())->fromRequest($request)
        );
        $path = $this->normalizePath($request->getPathInfo());
        $matched = $matcher->match($path);
        if (isset($matched['_route'])) {
            $route = $this->routes->get($matched['_route']);
            $request->attributes->set('_route', $route);
            $request->attributes->add($matched);
            return new MatchedRoute($route, $matched);
        }
    }

    public function loadRouteList(RouteListInterface $list)
    {
        $list->loadRoutes($this);
    }

    /**
     * @deprecated. Use the verb methods instead.
     * @param $path
     * @param $callback
     * @param null $handle
     * @param array $requirements
     * @param array $options
     * @param string $host
     * @param array $schemes
     * @param array $methods
     * @param null $condition
     * @return Route
     */
    public function register(
        $path,
        $callback,
        $handle = null,
        array $requirements = array(),
        array $options = array(),
        $host = '',
        $schemes = array(),
        $methods = array(),
        $condition = null)
    {
        $route = new Route($this->normalizePath($path), [], $requirements, $options, $host, $schemes, $methods, $condition);
        $route->setAction($callback);
        if ($handle) {
            $route->setCustomName($handle);
        }
        $this->addRoute($route);
        return $route;
    }

    /**
     * Registers routes from a config array. This is deprecated. Use the $router object
     * directly in an included file.
     * @deprecated.
     * @param array $routes
     */
    public function registerMultiple(array $routes)
    {
        foreach ($routes as $route => $route_settings) {
            array_unshift($route_settings, $route);
            call_user_func_array(array($this, 'register'), $route_settings);
        }
    }


    /**
     * Returns a route string based on data. DO NOT USE THIS.
     * @deprecated
     * @param $data
     * @return string
     */
    public function route($data)
    {
        if (is_array($data)) {
            $path = $data[0];
            $pkg = $data[1];
        } else {
            $path = $data;
        }

        $path = trim($path, '/');
        $pkgHandle = null;
        if ($pkg) {
            if (is_object($pkg)) {
                $pkgHandle = $pkg->getPackageHandle();
            } else {
                $pkgHandle = $pkg;
            }
        }

        $route = '/ccm';
        if ($pkgHandle) {
            $route .= "/{$pkgHandle}";
        } else {
            $route .= '/system';
        }

        $route .= "/{$path}";

        return $route;
    }


}
