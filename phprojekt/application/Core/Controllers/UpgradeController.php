<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Upgrade Controller.
 */
class Core_UpgradeController extends Core_IndexController
{
    /**
     * Index.
     *
     * If the user is an admin and we need upgrades, print a form.
     * Else, print a message depending on the situation.
     */
    public function indexAction()
    {
        $config = Phprojekt::getInstance()->getConfig();
        $language = Phprojekt_Auth::getRealUser()->getSetting("language", $config->language);

        $this->view->language       = $language;
        $this->view->compressedDojo = (bool) $config->compressedDojo;
        $this->view->frontendMsg    = (bool) $config->frontendMessages;
        $this->view->newVersion     = Phprojekt::getVersion();

        $extensions          = new Phprojekt_Extensions(PHPR_CORE_PATH);
        $migration           = new Phprojekt_Migration($extensions);

        if ($migration->needsUpgrade()) {
            if (!Phprojekt_Auth::isAdminUser()) {
                $this->render('upgradeLocked');
            } else {
                $this->view->modules = $migration->getModulesNeedingUpgrade();
                $this->render('upgrade');
            }
        } else {
            $this->render('upgradeIdle');
        }

    }

    /**
     * Perform all upgrades.
     *
     * Redirects to the index after completion.
     *
     * @return void
     */
    public function upgradeAction()
    {
        if (!Phprojekt_Auth::isAdminUser()) {
            throw new Zend_Controller_Action_Exception('Insufficient rights.', 403);
        }

        $extensions = new Phprojekt_Extensions(PHPR_CORE_PATH);
        $migration  = new Phprojekt_Migration($extensions);

        $migration->performAllUpgrades();

        // TODO: Notify the user that we're happy
        $config = Phprojekt::getInstance()->getConfig();
        $this->_redirect('index.php');
    }

    /**
     * Perform the upgrade for a single module.
     *
     * The module is taken from the 'upgradeModule' parameter of the request.
     *
     * @return void
     */
    public function jsonUpgradeAction()
    {
        if (!Phprojekt_Auth::isAdminUser()) {
            throw new Zend_Controller_Action_Exception('Insufficient rights.', 403);
        }

        $extensions = new Phprojekt_Extensions(PHPR_CORE_PATH);
        $migration  = new Phprojekt_Migration($extensions);

        $failed = true;
        try {
            $migration->performUpgrade(
                $this->getRequest()->getParam('upgradeModule')
            );
            $failed = false;
        } catch (Phprojekt_Migration_IKilledTheDatabaseException $e) {
            Phprojekt::getInstance()->getLog()->debug(
                 "IKilledTheDatabaseException occurred while migrating: " . $e->getFile() . ':' . $e->getLine() . "\n"
                 . $e->getMessage() . "\n"
                 . $e->getTraceAsString() . "\n"
            );
            Phprojekt_Converter_Json::echoConvert(
                array(
                    'type' => 'fatalFailure',
                    'message' => 'A fatal error has occured.'
                )
            );
        } catch (Exception $e) {
            Phprojekt::getInstance()->getLog()->debug(
                 "Exception occurred while migrating: " . $e->getFile() . ':' . $e->getLine() . "\n"
                 . $e->getMessage() . "\n"
                 . $e->getTraceAsString() . "\n"
            );
            Phprojekt_Converter_Json::echoConvert(
                array(
                    'type' => 'failure',
                    'message' => 'An error has occured.'
                )
            );
        }

        if (!$failed) {
            Phprojekt_Converter_Json::echoConvert(
                array(
                    'type' => 'success',
                    'message' => 'The module was upgraded correctly'
                )
            );
        }
    }
}
