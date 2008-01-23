<?php
/**
 * make Docbook from text files
 * PHP file called from the command line with arguments
 *
 * @package     CodeBrowser
 * @version     CVS: $Id$
 * @author      Christopher Weckerle <christopher.weckerle@mayflower.de>
 * @copyright   2007 Mayflower GmbH
 */

/** DEBUG */
error_reporting(E_ALL);
/* DUBUG **/



/** create new MakeDocbook Class */
$makeDocbook = new MakeDocbook;
/* create new MakeDocbook Class **/


/**
 * creates Docbook with Textfiles
 * 
 * @copyright   2007 Mayflower GmbH
 */
class MakeDocbook
{

    private $_xmlNodes;
    
    private $_excluded = array();
    
    private $_basePath;
    
    private $_docBookPath   = "/opt/local/var/macports/software/docbook-xsl/1.72.0_0/opt/local/share/xsl/docbook-xsl/html/";
    
    private $_docBookCSS    = "book.css"; 
    
    private $_xmlOutputFile = "output.xml";
    
    /**
     * Constructor calls methods to generate docbook from text files
     *
     */
    function __construct()
    {
        $this->_basePath = dirname(__FILE__).'/documentation/Developer';
        $this->_xmlNodes = simplexml_load_file(dirname(__FILE__).'/template.xml');
        $this->_setExluded();
        $this->_getDirectory($this->_basePath);
        $this->_execCommandLine();
    }
    
    /**
     * setExcluded from command line
     * 
     * @return void
     */
    private function _setExluded()
    {
        // get Exluded Arguments
        $opts = getopt("x:");
        if (array_key_exists('x', $opts)) {
            foreach ((array)$opts['x'] as $exclude) {
                $this->_excluded[] = $exclude;
            }
        }           
    }
    
    /**
     * set ignored files/folders
     *
     * @param array $excluded ignored files and folders
     * 
     * @return array $ignored
     */
    private function _ignoreFiles(array $excluded)
    {
        
        $ignored = array();
        foreach ($excluded as $exclude) {
            $ignored = array_merge($ignored, glob($exclude));
        }
        
        return $ignored;
    }
    
    /**
     * get Directorys from filesystem
     *
     * @param string           $path       path to file or folder
     * @param SimpleXMLElement $parentNode parent node in Nodetree
     * 
     * @return void
     */
    private function _getDirectory ( $path, $parentNode = null)
    {
        
        // Directories to ignore when listing output. Many hosts
        $ignoreDefault = array( 'CVS', '.', '..', '.DS_Store', 'README-WRITE_DOCS');
        
        $oldpwd = getcwd();
        chdir($path);
        // Scan directory
        $nodeType = "chapter";
        $level = 0;
        static $id = 1;
        foreach (scandir('.') as $dir) {
            
            $ignored = array_merge($ignoreDefault, $this->_ignoreFiles($this->_excluded));
            if (in_array($dir, $ignored)) {
                continue;
            }   
            
            $realpath = realpath($dir);
            
            if (!isset($parentNode)) {
                $parentNode = &$this->_xmlNodes;
            }
            
            if ($parentNode->getName() == $nodeType) {
                $level++; 
                $nodeType = "sect".$level;                     
            } else if (substr($parentNode->getName(), 0, 4) == 'sect') {
                $level    = substr($parentNode->getName(), 4)+1;
                $nodeType = "sect".$level;
            }
            
            $name = preg_replace('/^[0-9]{0,2}\s(.*)/i', '\\1', $dir);
                        
            if (is_dir($realpath)) {
                $chapter = $parentNode->addChild($nodeType);
                $chapter->addAttribute("id", $id++);
                $chapter->addChild("title", $name);
                //$chapter->addAttribute("level",$level);
                //print $realpath . "\n";
                $this->_getDirectory($realpath, $chapter, $level);                
            }
            
            if (is_file($realpath)) {
               
                $file = $parentNode->addChild($nodeType);
                $file->addChild("title", $name);
                $this->_parseFile($realpath, $file);
                $file->addAttribute("id", $id++);               
            }
        }
        chdir($oldpwd);
    }
    
    /**
     * parse Files
     *
     * @param string           $realpath real path to file
     * @param SimpleXMLElement $fileNode node in tree to add childs  
     * 
     * @return string file contents
     */
    private function _parseFile($realpath, $fileNode)
    {
        $fileContents = utf8_encode(file_get_contents($realpath));
        
        $in           = array("/\n\r/", "/\r\n/", "/\r/");
        $out          = array("\n", "\n", "\n");
        $fileContents = preg_replace($in, $out, $fileContents);
        $this->_parseLineParagraph($fileContents, $fileNode);
        return $fileContents;
    }
    
    /**
     * parse text files and add docbook paragraphs, formatting
     *
     * @param string           $contents text file contents
     * @param SimpleXMLElement $node     xml node to add childs
     * 
     * @return void
     */
    private function _parseLineParagraph($contents, $node)
    {
        $buffer = '';
        for ($i = 0; $i < strlen($contents); $i++) {
            switch($contents[$i]) {
                case "\n": 
                    if (++$i < strlen($contents) && $contents[$i] == "\n") {
                        $node->addChild('para', trim($buffer));
                        $buffer = '';
                        while ($i < strlen($contents)  && $contents[$i] == "\n" ) $i++;
                    }
                    $i--;
                    break;
                case "[":
                    $buffer = '';
                    $tag = '';
                    while($i < strlen($contents)  && $contents[++$i] != "]") $tag.= $contents[$i];
                    $pos = strpos($contents, '[/'.$tag.']', ++$i);
                    if (!$pos) continue;
                    switch($tag) {
                        case 'emph':
                            $node->addChild('emphasis', substr($contents, $i, $pos - $i));
                            break;
                        case 'code':
                        case 'pre':
                            $node->addChild('literallayout', substr($contents, $i, $pos - $i));
                            break;
                    }
                    $i = $pos + strlen('[/' . $tag . ']');
                    break;
                default: 
                    $buffer .= $contents[$i];
            }
        }
    }
    
    /**
     * exec Commands on Command line to generate Docbook
     *
     * @return void
     */
    private function _execCommandLine()
    {
        exec("xsltproc --stringparam html.stylesheet ".$this->_docBookCSS." "
                                                      .$this->_docBookPath."/docbook.xsl "
                                                      .$this->_xmlOutputFile." > outfile.html");
                                                      
        file_put_contents($this->_xmlOutputFile, $this->_xmlNodes->asXML());
    }
}

