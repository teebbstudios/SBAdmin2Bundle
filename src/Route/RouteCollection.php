<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teebb\SBAdmin2Bundle\Route;

use Symfony\Component\Routing\Route;

/**
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class RouteCollection
{
    /**
     * @var Route[]
     */
    protected $elements = [];

    /**
     * @var string
     */
    protected $baseCodeRoute;

    /**
     * @var string
     */
    protected $baseRouteName;

    /**
     * @var string
     */
    protected $baseControllerName;

    /**
     * @var string
     */
    protected $baseRoutePattern;

    /**
     * @param string $baseCodeRoute
     * @param string $baseRouteName
     * @param string $baseRoutePattern
     * @param string $baseControllerName
     */
    public function __construct($baseCodeRoute, $baseRouteName, $baseRoutePattern, $baseControllerName)
    {
        $this->baseCodeRoute = $baseCodeRoute;
        $this->baseRouteName = $baseRouteName;
        $this->baseRoutePattern = $baseRoutePattern;
        $this->baseControllerName = $baseControllerName;
    }

    /**
     * Add route.
     *
     * @param string $name
     * @param string $pattern   Pattern (will be automatically combined with @see $this->baseRoutePattern and $name
     * @param string $host
     * @param string $condition
     *
     * @return RouteCollection
     */
    public function add(
        $name,
        $pattern = null,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        $host = '',
        array $schemes = [],
        array $methods = [],
        $condition = ''
    ) {
        $pattern = $this->baseRoutePattern.'/'.($pattern ?: $name);
        $code = $name;
        $routeName = $this->baseRouteName.'_'.$name;

        if (!isset($defaults['_controller'])) {
            $actionJoiner = false === strpos($this->baseControllerName, '\\') ? ':' : '::';
            if (':' !== $actionJoiner && false !== strpos($this->baseControllerName, ':')) {
                $actionJoiner = ':';
            }

            $defaults['_controller'] = $this->baseControllerName.$actionJoiner.$this->actionify($code);
        }

        if (!isset($defaults['_teebb_admin'])) {
            $defaults['_teebb_admin'] = $this->baseCodeRoute;
        }

        $defaults['_teebb_name'] = $routeName;

        $this->elements[$name] = function () use (
            $pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition) {
            return new Route($pattern, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
        };

        return $this;
    }

    /**
     * @return RouteCollection
     */
    public function addCollection(self $collection)
    {
        foreach ($collection->getElements() as $code => $route) {
            $this->elements[$code] = $route;
        }

        return $this;
    }

    /**
     * @return Route[]
     */
    public function getElements()
    {
        foreach ($this->elements as $name => $element) {
            $this->elements[$name] = $this->resolve($element);
        }

        return $this->elements;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return \array_key_exists($name, $this->elements);
    }

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function get($name)
    {
        if ($this->has($name)) {

            $this->elements[$name] = $this->resolve($this->elements[$name]);

            return $this->elements[$name];
        }

        throw new \InvalidArgumentException(sprintf('Element "%s" does not exist.', $name));
    }

    public function getRouteName($name)
    {
        return $this->get($name)->getDefault('_teebb_name');
    }

    /**
     * @param string $name
     *
     * @return RouteCollection
     */
    public function remove($name)
    {
        unset($this->elements[$name]);

        return $this;
    }

    /**
     * Remove all routes except routes in $routeList.
     *
     * @param string[]|string $routeList
     *
     * @return RouteCollection
     */
    public function clearExcept($routeList)
    {
        if (!\is_array($routeList)) {
            $routeList = [$routeList];
        }

        $routeCodeList = [];
        foreach ($routeList as $name) {
            $routeCodeList[] = $name;
        }

        $elements = $this->elements;
        foreach ($elements as $key => $element) {
            if (!\in_array($key, $routeCodeList, true)) {
                unset($this->elements[$key]);
            }
        }

        return $this;
    }

    /**
     * Remove all routes.
     *
     * @return RouteCollection
     */
    public function clear()
    {
        $this->elements = [];

        return $this;
    }

    /**
     * Convert a word in to the format for a symfony action action_name => actionName.
     *
     * @param string $action Word to actionify
     *
     * @return string Actionified word
     */
    public function actionify($action)
    {
        if (false !== ($pos = strrpos($action, '.'))) {
            $action = substr($action, $pos + 1);
        }

        // if this is a service rather than just a controller name, the suffix
        // Action is not automatically appended to the method name
        if (false === strpos($this->baseControllerName, ':')) {
            $action .= 'Action';
        }

        return lcfirst(str_replace(' ', '', ucwords(strtr($action, '_-', '  '))));
    }

    /**
     * @return string
     */
    public function getBaseCodeRoute()
    {
        return $this->baseCodeRoute;
    }

    /**
     * @return string
     */
    public function getBaseControllerName()
    {
        return $this->baseControllerName;
    }

    /**
     * @return string
     */
    public function getBaseRouteName()
    {
        return $this->baseRouteName;
    }

    /**
     * @return string
     */
    public function getBaseRoutePattern()
    {
        return $this->baseRoutePattern;
    }

    private function resolve($element): Route
    {
        if (\is_callable($element)) {
            return \call_user_func($element);
        }

        return $element;
    }
}
