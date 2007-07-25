<?php

class Phprojekt_Dispatcher extends Zend_Controller_Dispatcher_Standard
{

    /**
     * Format a string into a controller class name.
     *
     * @param string $unformatted
     * @return string
     */
    public function formatControllerName($unformatted)
    {
        $logger = Zend_Registry::get('log');
        $logger->debug("Dispatch unformated {$unformatted}");


        $module = $this->getFrontController()->getRequest()->getModuleName();
        $module = self::formatModuleName($module);

        $controller = parent::formatControllerName($unformatted);

/*        if ($module !== self::getDefaultModule()) {
            $controller = Phprojekt_Loader::getController($module, $controller);
        }
*/
        $logger->debug("Dispatch formated {$controller}");

        return $controller;
    }
}