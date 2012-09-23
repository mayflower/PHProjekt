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
 * Helper to manage the upload files.
 */
final class Default_Helpers_Upload
{
    /**
     * Init the session with the files uploaded or an empty string.
     *
     * @param Phprojekt_Model_Interface $model  Current module.
     * @param string                    $field  Name of the field in the module.
     * @param integer                   $itemId Id of the current item.
     *
     * @return array of all the files.
     */
    static public function initValue($model, $field, $itemId)
    {
        self::_checkParamField($model, $field);

        $value = '';
        if ($itemId > 0) {
            $model->find($itemId);
            $value = $model->$field;
        }

        $files = self::parseModelValues($value);
        self::_setSessionFiles($files, $field);
        return $files;
    }

    /**
     * Returns the session value.
     *
     * @param Phprojekt_Model_Interface $model  Current module.
     * @param string                    $field  Name of the field in the module.
     *
     * @return array list of files.
     */
    static public function getFiles($model, $field)
    {
        self::_checkParamField($model, $field);

        return self::_getSessionFiles($field);
    }

    /**
     * Upload the file and return the new value.
     *
     * @param Phprojekt_Model_Interface $model  Current module.
     * @param string                    $field  Name of the field in the module.
     * @param integer                   $itemId Id of the current item.
     *
     * @throws Exception On no write access or exceed the Max upload size.
     *
     * @return string Md5 string of all the files separated by ||.
     */
    static public function uploadFile($model, $field, $itemId)
    {
        self::_checkParamField($model, $field);
        self::_checkWritePermission($model, $itemId);

        $config     = Phprojekt::getInstance()->getConfig();

        $files = self::_getSessionFiles($field);

        // Remove all the upload files that are not "uploadedFile"
        foreach (array_keys($_FILES) as $key) {
            if ($key != 'uploadedFile') {
                unset($_FILES[$key]);
            }
        }

        // Fix name for save it as md5
        if (is_array($_FILES) && !empty($_FILES) && isset($_FILES['uploadedFile'])) {
            $md5name                        = md5(mt_rand() . time());
            $addedFile                     = array(
                'md5' => $md5name,
                'name' => self::_makeUniqueName($_FILES['uploadedFile']['name'], $files)
            );
            $_FILES['uploadedFile']['name'] = $md5name;
        }

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($config->uploadPath);

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            foreach ($messages as $index => $message) {
                $messages[$index] = Phprojekt::getInstance()->translate($message);
                if ($index == 'fileUploadErrorFormSize') {
                    $maxSize = (isset($config->maxUploadSize)) ? (int) $config->maxUploadSize :
                        Phprojekt::DEFAULT_MAX_UPLOAD_SIZE;
                    $maxSize           = (int) ($maxSize / 1024);
                    $messages[$index] .= ': ' . $maxSize . ' Kb.';
                }
            }
            throw new Exception(implode("\n", $messages));
        } else {
            $files[] = $addedFile;
            self::addFilesToUnusedFileList(array($addedFile));
        }

        self::_setSessionFiles($files, $field);
        return $files;
    }

    /**
     * Makes $name unique with respect to $otherFiles by adding (3) to $name, where 3 is the next free index for $name.
     */
    private static function _makeUniqueName($name, $otherFiles)
    {
        if (!self::_nameExistsInFilesArray($name, $otherFiles)) {
            return $name;
        }

        return $name . ' (' . self::_nextFreeFilenameIndex($name, $otherFiles) . ')';
    }

    private static function _nameExistsInFilesArray($name, $files)
    {
        foreach ($files as $entry) {
            if ($entry['name'] === $name) {
                return true;
            }
        }
        return false;
    }

    private static function _nextFreeFilenameIndex($name, $otherFiles)
    {
        $relevant = array();
        foreach ($otherFiles as $entry) {
            if (stripos($entry['name'], $name . ' (') === 0) {
                $relevant[] = $entry['name'];
            }
        }

        $highestIndex = 0;
        foreach ($relevant as $file) {
            $matches = array();
            if (preg_match('/\((\d+)\)$/', $file, $matches) === 1) {
                $highestIndex = max($highestIndex, $matches[1]);
            }
        }

        return $highestIndex + 1;
    }

    /**
     * Retrieves the file from upload folder.
     *
     * @param Phprojekt_Model_Interface $model  Current module.
     * @param string                    $field  Name of the field in the module.
     * @param integer                   $itemId Id of the current item.
     * @param string                    $hash  Hash of the file.
     *
     * @return string Md5 string of all the files separated by ||.
     */
    static public function downloadFile($model, $field, $itemId, $hash)
    {
        self::_checkParamField($model, $field);
        $files = self::_getSessionFiles($field);
        $file = self::getSessionFileFromHash($hash, $field);

        $permitted = false;
        if ($itemId > 0) {
            $model->find($itemId);
            // The user has download permission?

            if ($model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::DOWNLOAD)) {
                $permitted = true;
            }
        } else if (!is_null($file)) {
            $permitted = true;
        }

        $md5Name  = $file['md5'];
        $fileName = $file['name'];

        if (!$permitted || !self::_isValidFileHash($md5Name) || empty($fileName)) {
            $error = Phprojekt::getInstance()->translate('You don\'t have permission for downloading on this item.');
            throw new RuntimeException($error);
        }

        $md5Name = self::_absoluteFilePathFromHash($md5Name);
        if (file_exists($md5Name)) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            header('Content-Length: ' . filesize($md5Name));
            header("Content-Disposition: attachment; filename=\"" . (string) $fileName . "\"");
            header('Content-Type: download');
            $fh = fopen($md5Name, 'r');
            fpassthru($fh);
        } else {
            $error = Phprojekt::getInstance()->translate('The file does not exists');
            throw new RuntimeException($error);
        }
    }

    /**
     * Delete a files.
     *
     * @param array $files list of files.
     *
     * @throws Exception On no write access.
     *
     * @return void.
     */
    static public function deleteFiles($files)
    {
        foreach ($files as $file) {
            $hash = $file['md5'];
            $fileAbsolutePath = self::_absoluteFilePathFromHash($hash);

            if (self::_isValidFileHash($hash) && file_exists($fileAbsolutePath)) {
                unlink($fileAbsolutePath);
            }
        }
    }

    /**
     * Add files to the List of unused files.
     *
     * @param array $hashes array of file hashes.
     *
     * @return void.
     */
    static public function addFilesToUnusedFileList($files)
    {
        if (count($files) === 0) {
            return;
        }

        $db = Phprojekt::getInstance()->getDb();
        $table = new Zend_Db_Table(array(
            'db' => $db,
            'name' => 'uploaded_unused_files'
        ));

        $rows = array();

        foreach ($files as $file) {
            $rows[] = array(
                "created" => new Zend_Db_Expr('NOW()'),
                "hash" => $file['md5']
            );
        }

        foreach ($rows as $row) {
            $table->insert($row);
        }
    }

    /**
     * Removes files from the List of unused files.
     *
     * @param array $hashes array of file hashes.
     *
     * @return void.
     */
    static public function removeFilesFromUnusedFileList($files)
    {
        if (count($files) === 0) {
            return;
        }

        $db = Phprojekt::getInstance()->getDb();

        $hashes = array();
        foreach ($files as $file) {
            $hashes[] = $file['md5'];
        }

        $where = $db->quoteInto("hash IN (?)", $hashes);
        $db->delete('uploaded_unused_files', $where);
    }

    /**
     * Checks that the 'field' parameter for download and delete file actions is valid.
     * If not, terminates script execution printing an error.
     *
     * @param Phprojekt_Model_Interface $model Current module.
     * @param string                    $field Name of the field in the module.
     *
     * @return void
     */
    static private function _checkParamField($model, $field)
    {
        $valid = false;
        $info  = $model->info();

        if (in_array($field, $info['cols'])) {
            $dbManager = $model->getInformation();
            $fieldType = $dbManager->getType($field);
            if ($fieldType == 'upload') {
                $valid = true;
            }
        }

        if (!$valid) {
            $error  = Phprojekt::getInstance()->translate('Error in received parameter, consult the admin. Parameter:');
            $error .= ' field';

            self::_logError(
                "Error: wrong 'field' parameter trying to Download or Delete a file.",
                array(get_class($model), $field)
            );

            throw new InvalidArgumentException($error);
        }
    }

    /**
     * Checks that the user has permission for modifying the item, in this case for uploading or deleting files.
     *
     * @param Phprojekt_Model_Interface $model  Current module.
     * @param integer                   $itemId Current item id.
     *
     * @throws Exception On no write access.
     *
     * @return void
     */
    static private function _checkWritePermission($model, $itemId)
    {
        if ($itemId != 0) {
            $model->find($itemId);
        }
        if (!$model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::WRITE)) {
            $error = Phprojekt::getInstance()->translate('You don\'t have permission for modifying this item.');

            self::_logError("Error: trying to Delete or Upload a file without write access.",
                array(get_class($model), $itemId));
            throw new Exception($error);
        }
    }

    /**
     * Log the error adding the user id and some extra values.
     *
     * @param string $message The message to log.
     * @param array  $values  Array with values to show.
     *
     * @return void
     */
    static private function _logError($message, $values)
    {
        // Log error
        Phprojekt::getInstance()->getLog()->err($message . " User Id: " . Phprojekt_Auth::getUserId()
            . " - Values: ". implode("," , $values));
    }

    /**
     * Gets the files from the session for the provided field.
     */
    static private function _getSessionFiles($field)
    {
        try {
            $files = unserialize($_SESSION['uploadedFiles_' . $field]);
        } catch (Exception $e) {
            $files = array();
        }

        if (!is_array($files)) {
            $files = array();
        }

        return $files;
    }

    /**
     * Saves the given files into the session for the provided field.
     */
    static private function _setSessionFiles($files, $field)
    {
        $value = serialize($files);
        $_SESSION['uploadedFiles_' . $field] = $value;
        return $value;
    }

    /**
     * Parses the file lists in the model into a usable format.
     *
     * This has to be done to not break backwards compatibility.
     */
    static public function parseModelValues($value)
    {
        $fileFields = explode('||', $value);
        $files = array();

        if ($fileFields[0] !== "") {
            foreach ($fileFields as $fileField) {
                list($md5Name, $fileName) = explode("|", $fileField, 2);
                $files[] = array(
                    'md5' => $md5Name,
                    'name' => $fileName
                );
            }
        }

        return $files;
    }

    /**
     * Returns a file from the session by hash and field.
     *
     * @param string    $hash   Hash of the file.
     * @param string    $field  Name of the field in the module.
     *
     * @return array    The file and hash.
     */
    static public function getSessionFileFromHash($hash, $field)
    {
        $files = self::_getSessionFiles($field);

        foreach ($files as $file) {
            if ($file['md5'] == $hash) {
                return $file;
            }
        }

        return null;
    }

    /**
     * Returns true if the passed hash is a valid filename hash.
     *
     * @param string    $hash   Hash of the file.
     *
     * @return boolean  Correct hash or not.
     */
    static private function _isValidFileHash($hash)
    {
        return preg_match("/^[A-Fa-f0-9]{32,32}$/", $hash);
    }

    /**
     * Returns the absolute path of a file by hash.
     *
     * @param string    $hash   Hash of the file.
     *
     * @return string   The absolute path.
     */
    static private function _absoluteFilePathFromHash($hash)
    {
        return Phprojekt::getInstance()->getConfig()->uploadPath . $hash;
    }

    /**
     * Returns size and creation time of the given file.
     *
     * @param array     $file   The file.
     *
     * @return mixed    size and ctime of the file if file is valid, null else;
     */
    static public function getInfosFromFile($file) {
        if (self::_isValidFileHash($file['md5'])) {
            $stat = lstat(self::_absoluteFilePathFromHash($file['md5']));

            return array(
                "size" => $stat['size'],
                "ctime" => $stat['ctime']
            );
        }

        return null;
    }

    /**
     * Retrieves a list of file hashes that are without association for longer than
     * 2 hours.
     */
    static private function _getOldFileHashes() {
        $db = Phprojekt::getInstance()->getDb();

        $select = $db->select()->from('uploaded_unused_files', array('hash', 'created'))
            ->where('created < ?', new Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 2 HOUR)'));
        $stmt = $select->query();

        $rows = $stmt->fetchAll();
        $hashes = array();

        foreach ($rows as $row) {
            $hashes[] = $row['hash'];
        }

        return $hashes;
    }

    /**
     * Deletes all files in the "unused_upload" table older than 2 hours.
     *
     * @return  void
     */
    static public function cleanUnusedFiles()
    {

        try {
            $files = array();
            $hashes = self::_getOldFileHashes();
            foreach ($hashes as $hash) {
                $files[] = array('md5' => $hash);
            }

            self::removeFilesFromUnusedFileList($files);
            self::deleteFiles($files);
        } catch (Exception $e) {
            //ignore, might be because of missing table before migration
        }
    }
}
