<?php
/**
 * Gantt Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Gantt Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Gantt_IndexController extends IndexController
{
	/**
	 * Return a list of projects with the info nessesary for make the gantt chart
	 * 
	 * @requestparam integer nodeId
	 *
	 * @return void
	 */
	public function jsonGetProjectsAction()
	{
		$projectId    = (int) $this->getRequest()->getParam('nodeId', null);
		$data['data'] = array();
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, $projectId);
        $tree->setup();
        $min = mktime(0,0,0,12,31,2030);
        $max = mktime(0,0,0,1,1,1970);  
        foreach ($tree as $node) {
        	if ($node->id > 1) {
                $key    = $node->id;
                $parent = ($node->getParentNode()) ? $node->getParentNode()->id : 0;
                list($startYear, $startMonth, $startDay) = split("-", $node->startDate);
                list($endYear, $endMonth, $endDay) = split("-", $node->endDate);
                $start  = mktime(0,0,0,$startMonth,$startDay,$startYear);
                $end    = mktime(0,0,0,$endMonth,$endDay,$endYear); 
                if ($start < $min) {
                	$min = $start;
                }
                if ($end > $max) {
                	$max = $end;
                }
                $data['data']["projects"][] = array('id'      => $key,
                                                    'level'   => $node->getDepth() * 10,
                                                    'childs'  => count($node->getChildren()),
                                                    'caption' => $node->title,
                                                    'name'    => "p:".$parent."|own:".$key,
                                                    'start'   => $start,
                                                    'end'     => $end);
            }
        }
        $data['data']['min'] = mktime(0,0,0,1,1,date("Y", $min));
        $data['data']['max'] = mktime(0,0,0,12,31,date("Y", $min));        
        $data['data']['step'] = (date("L", $min)) ? 366 : 365;
        echo Phprojekt_Converter_Json::convert($data);
	}
	
    /**
     * Save the new values of the projects dates
     * 
     * @requestparam array projects
     *
     * @return void
     */	
	public function jsonSaveAction()
	{
        $projects = $this->getRequest()->getParam('projects', array());
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        foreach ($projects as $project) {
        	list($id,$startDate,$endDate) = split(",",$project);
        	$activeRecord->find($id);
        	$activeRecord->startDate = $startDate;
        	$activeRecord->endDate   = $endDate;
            if ($activeRecord->recordValidate()) {
            	$activeRecord->save();
            }
        }
        
        $message = Zend_Registry::get('translate')->translate(self::EDIT_MULTIPLE_TRUE_TEXT);
                
        $return  = array('type'    => 'success',
                         'message' => $message,
                         'code'    => 0,
                         'id'      => 0);
        
        echo Phprojekt_Converter_Json::convert($return);        
	}
}