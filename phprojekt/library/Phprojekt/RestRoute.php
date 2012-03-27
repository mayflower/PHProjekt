<?php
class Phprojekt_RestRoute extends Zend_Rest_Route
{
    protected function _checkRestfulController($moduleName, $controllerName)
    {
        $controllerName = ucfirst($moduleName) . '_' . ucfirst($controllerName) . 'Controller';
        if (!@class_exists($controllerName)) {
            return false;
        }
        $class = new ReflectionClass($controllerName);
        if ($class->isSubclassOf('Phprojekt_RestController')) {
            return true;
        }
        return false;
    }
}
