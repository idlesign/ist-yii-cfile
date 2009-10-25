<?php
/**
 * CFile provides common functions to manipulate files with Yii.
 *
 * @version 0.1
 *
 * @todo create method
 * @todo get/setContents methods
 * @todo readable/writable properties
 *
 * @author idle sign <idlesign@yandex.ru>
 * @link http://www.yiiframework.com/extension/cfile/
 * @license LICENSE.txt
 */

class CFile extends CApplicationComponent
{
    /**
     * @var array object instances array with key set to $_filepath
     */
    private static $_instances = array();
    /**
     * @var string file path submitted by user
     */
    private $_filepath;
    /**
     * @var string real file path figured by script on the basis of $_filepath
     */
    private $_realpath;
    /**
     * @var boolean 'true' if file described by $_realpath exists
     */
    private $_exists;
    /**
     * @var string basename of the file (eg. 'myfile.htm' for '/var/www/htdocs/files/myfile.htm')
     */
    private $_basename;
    /**
     * @var string name of the file (eg. 'myfile' for '/var/www/htdocs/files/myfile.htm')
     */
    private $_filename;
    /**
     * @var string directory name of the file (eg. '/var/www/htdocs/files' for '/var/www/htdocs/files/myfile.htm')
     */
    private $_dirname;
    /**
     * @var string file extension(eg. 'htm' for '/var/www/htdocs/files/myfile.htm')
     */
    private $_extension;
    /**
     * @var string file extension(eg. 'text/html' for '/var/www/htdocs/files/myfile.htm')
     */
    private $_mimeType;
    /**
     * @var integer the time the file was last modified (Unix timestamp eg. '1213760802')
     */
    private $_timeModified;
    /**
     * @var string file size formatted (eg. '70.4 KB') or in bytes (eg. '72081') see {@link getSize} parameters
     */
    private $_size;
    /**
     * @var mixed file owner name (eg. 'idle') or in ID (eg. '1000') see {@link getOwner} parameters
     */
    private $_owner;
    /**
     * @var mixed file group name (eg. 'apache') or in ID (eg. '127') see {@link getGroup} parameters
     */
    private $_group;
    /**
     * @var string file permissions (considered octal eg. '0755')
     */
    private $_permissions;


    /**
     * Returns the instance of CFile for the specified file.
     *
     * @param string $filePath Path to file specified by user
     * @return object CFile instance
     */
    public static function getInstance($filePath)
    {
        if(!array_key_exists($filePath, self::$_instances))
        {
            self::$_instances[$filePath] = new CFile($filePath);
        }
        return self::$_instances[$filePath];
    }

    /**
     * Logs a message.
     *
     * @param string $message Message to be logged
     * @param string $level Level of the message (e.g. 'trace', 'warning', 'error', 'info', see
     * CLogger constants definitions)
     */
    private function addLog($message, $level='info'){
        Yii::log($message.' (file: '.$this->realpath.')', $level, 'app.extensions.CFile');
    }

    /**
     * Basic CFile method. Sets CFile object to work with specified file.
     * Essentially file path supplied by user is resolved into real path (see {@link getRealPath}),
     * all the other property getting methods should use that real path.
     *
     * @param string $filePath Path to the file specified by user, if not set exception is raised
     * @param boolean $greedy If true file properties (such as 'Size', 'Owner', 'Permission', etc.) would be autoloaded
     * @return object CFile instance for the specified file
     */
    public function set($filePath, $greedy=false)
    {
        if (trim($filePath)!='')
        {
            clearstatcache();
            $instance = self::getInstance($filePath);
            $instance->_filepath = $filePath;
            $instance->realPath;

            if ($instance->exists)
            {
                $instance->pathInfo();
                if ($greedy){
                    $instance->size;
                    $instance->owner;
                    $instance->group;
                    $instance->permissions;
                    $instance->timeModified;
                    $instance->mimeType;
                }
            }
            return $instance;
        }

        throw new CException('Path to file is not specified');
    }

    /**
     * Populates basic CFile properties (i.e. 'Dirname', 'Basename', etc.) using values
     * resolved by pathinfo() php function.
     */
    private function pathInfo(){
        $pathinfo = pathinfo($this->_realpath);
        $this->_dirname = $pathinfo['dirname'];
        $this->_basename = $pathinfo['basename'];
        $this->_filename = $pathinfo['filename'];
        $this->_extension = $pathinfo['extension'];
    }


    /**
     * Returns real file path figured by script (see {@link realPath}) on the basis of user supplied $_filepath.
     * If $_realpath property is set, returned value is read from that property.
     *
     * @param string $dir_separator Directory separator char (depends upon OS)
     * @return string Real file path
     */
    public function getRealPath($dir_separator=DIRECTORY_SEPARATOR)
    {
        if (!isset($this->_realpath))
            $this->_realpath = $this->realPath($this->_filepath, $dir_separator);

        return $this->_realpath;
    }

    /**
     * Base real file path resolving method.
     * Returns real file path resolved from the supplied path.
     *
     * @param string $currentPath Path from which real file path should be resolved
     * @param string $dir_separator Directory separator char (depends upon OS)
     * @return string Real file path
     */
    private function realPath($currentPath, $dir_separator=DIRECTORY_SEPARATOR){

        if (!strlen($currentPath))
            return $dir_separator;

        $winDrive = '';

        // Windows OS path type detection
        if (!strncasecmp(PHP_OS, 'win', 3))
        {
            $currentPath = preg_replace('/[\\\\\/]/', $dir_separator, $currentPath);
            if (preg_match('/([a-zA-Z]\:)(.*)/', $currentPath, $matches)) {
                $winDrive = $matches[1];
                $currentPath = $matches[2];
            } else {
                $workingDir = getcwd();
                $winDrive = substr($workingDir, 0, 2);
                if ($currentPath{0} !== $dir_separator{0}) {
                    $currentPath = substr($workingDir, 3).$dir_separator.$currentPath;
                }
            }
        }
        elseif ($currentPath{0} !== $dir_separator)
        {
            $currentPath = getcwd().$dir_separator.$currentPath;
        }

        $pathsArr = array();
        foreach (explode($dir_separator, $currentPath) as $path)
        {
            if (strlen($path) && $path !== '.')
            {
                if ($path == '..')
                {
                    array_pop($pathsArr);
                }
                else
                {
                    $pathsArr[] = $path;
                }
            }
        }

        $realpath = $winDrive.$dir_separator.implode($dir_separator, $pathsArr);
        Yii::trace('User file "'.$this->_filepath.'" resolved into "'.$realpath.'"', 'app.extensions.CFile');

        return $realpath;
    }

    /**
     * Tests current file existance and returns boolean (see {@link exists}).
     * If $_exists property is set, returned value is read from that property.
     *
     * @return boolean 'True' if file exists, overwise 'false'
     */
    public function getExists(){
        if (!isset($this->_exists))
            $this->_exists = $this->exists();

        return $this->_exists;
    }

    /**
     * Base file existance resolving method.
     * Tests current file existance and returns boolean.
     *
     * @return boolean 'True' if file exists, overwise 'false'
     */
    private function exists()
    {
        if (!isset($this->_exists))
        {
            if (file_exists($this->_realpath) && is_file($this->_realpath)){
                Yii::trace("File availability test: ".$this->_realpath, 'app.extensions.CFile');
                $this->_exists = true;
            } else {
                $this->_exists = false;
            }
        }

        if ($this->_exists)
            return true;

        $this->addLog('File not found');
        return false;
    }

    /**
     * Returns owner of current file (UNIX systems).
     * Returned value depends upon $getName parameter value.
     * If $_owner property is set, returned value is read from that property.
     *
     * @param boolean $getName Defaults to 'true', meaning that owner name instead of ID should be returned.
     * @return mixed Owner name, or ID if $getName set to 'false'
     */
    public function getOwner($getName=true)
    {
        if (!isset($this->_owner))
            $this->_owner = $this->exists()?fileowner($this->_realpath):null;
                if ($getName != false)
                {
                    $this->_owner = posix_getpwuid($this->_owner);
                    $this->_owner = $this->_owner['name'];
                }

        return $this->_owner;
    }

    /**
     * Returns group of current file (UNIX systems).
     * Returned value depends upon $getName parameter value.
     * If $_group property is set, returned value is read from that property.
     *
     * @param boolean $getName Defaults to 'true', meaning that group name instead of ID should be returned.
     * @return mixed Group name, or ID if $getName set to 'false'
     */
    public function getGroup($getName=true)
    {
        if (!isset($this->_group))
            $this->_group = $this->exists()?filegroup($this->_realpath):null;
                if ($getName != false)
                {
                    $this->_group = posix_getgrgid($this->_group);
                    $this->_group = $this->_group['name'];
                }

        return $this->_group;
    }

    /**
     * Returns permissions of current file (UNIX systems).
     * If $_permissions property is set, returned value is read from that property.
     *
     * @return string File permissions in octal format (i.e. '0755') 
     */
    public function getPermissions()
    {
        if (!isset($this->_permissions))
            $this->_permissions = $this->exists()?substr(sprintf('%o', fileperms($this->_realpath)), -4):null;

        return $this->_permissions;
    }

    /**
     * Returns size of current file.
     * Returned value depends upon $formatPrecision parameter value.
     * If $_size property is set, returned value is read from that property.
     *
     * @param mixed $formatPrecision Number of digits after the decimal point or 'false'
     * @return mixed File size formatted (eg. '70.4 KB') or in bytes (eg. '72081') if $formatPrecision set to 'false'
     */
    public function getSize($formatPrecision=1)
    {
        if (!isset($this->_size))
        {
            $this->_size = $this->exists()?sprintf("%u", filesize($this->_realpath)):null;
            if ($formatPrecision !== false)
                $this->_size = $this->formatFileSize($this->_size, $formatPrecision);
        }

        return $this->_size;
    }

    /**
     * Base file size format method.
     * Converts file size in bytes into human readable format (i.e. '70.4 KB')
     * 
     * @param integer $bytes File size in bytes
     * @param integer $precision Number of digits after the decimal point
     * @return string File size in human readable format
     */
    private function formatFileSize($bytes, $precision=2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $expo = floor(($bytes ? log($bytes) : 0) / log(1024));
        $expo = min($expo, count($units)-1);

        $bytes /= pow(1024, $expo);

        return round($bytes, $precision).' '.$units[$expo];
    }

    /**
     * Returns the time current file was last modified.
     * Returned Unix timestamp could be passed to php date() function.
     *
     * @return integer Last modified time Unix timestamp (eg. '1213760802')
     */
    public function getTimeModified()
    {
        if (empty($this->_timeModified))
            $this->_timeModified = $this->exists()?filemtime($this->_realpath):null;

        return $this->_timeModified;
    }

    /**
     * Returns current file extension from $_extension property set by {@link pathInfo}
     * (eg. 'htm' for '/var/www/htdocs/files/myfile.htm').
     *
     * @return string Current file extension without the leading dot
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * Returns current file basename (file name plus extension) from $_basename property set by {@link pathInfo}
     * (eg. 'myfile.htm' for '/var/www/htdocs/files/myfile.htm').
     *
     * @return string Current file basename
     */
    public function getBasename()
    {
        return $this->_basename;
    }

    /**
     * Returns current file name (without extension) from $_filename property set by {@link pathInfo}
     * (eg. 'myfile' for '/var/www/htdocs/files/myfile.htm')
     *
     * @return string Current file name
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Returns current file directory name (without final slash) from $_dirname property set by {@link pathInfo}
     * (eg. '/var/www/htdocs/files' for '/var/www/htdocs/files/myfile.htm')
     *
     * @return string Current file name
     */
    public function getDirname()
    {
        return $this->_dirname;
    }

    /**
     * Sets current file owner, updates $_owner property if success.
     * For UNIX systems.
     *
     * @param mixed $owner New owner name or ID
     * @return mixed Current CFile object on success, 'false' on fail.
     */
    public function setOwner($owner)
    {
        if($this->_exists && chown($this->_realpath, $owner))
        {
            $this->_owner = $owner;
            return $this;
        }

        $this->addLog('Unable to set owner for file');
        return false;
    }

    /**
     * Sets current file group, updates $_group property if success.
     * For UNIX systems.
     *
     * @param mixed $group New group name or ID
     * @return mixed Current CFile object on success, 'false' on fail.
     */
    public function setGroup($group)
    {
        if($this->_exists && chgrp($this->_realpath, $group))
        {
            $this->_group = $group;
            return $this;
        }

        $this->addLog('Unable to set group for file');
        return false;
    }

    /**
     * Sets current file permissions, updates $_permissions property if success.
     * For UNIX systems.
     *
     * @param string $permissions New file permissions in numeric (octal, i.e. '0755') format
     * @return mixed Current CFile object on success, 'false' on fail.
     */
    public function setPermissions($permissions)
    {
        if ($this->_exists && is_numeric($permissions))
        {
            // '755' normalize to octal '0755'
            $permissions = str_pad($permissions, 4, "0", STR_PAD_LEFT);

            if(@chmod($this->_realpath, $permissions))
            {
                $this->_group = $permissions;
                return $this;
            }
        }

        $this->addLog('Unable to change permissions for file');
        return false;
    }

    /**
     * Copies current file to specified destination.
     * Destination path supplied by user resolved to real destination path with {@link realPath}
     *
     * @param string $fileDest Destination path for the current file to be copied to
     * @return mixed New CFile object for newly created file on success, 'false' on fail.
     */
    public function copy($fileDest)
    {
        $destRealPath = $this->realPath($fileDest);

        if ($this->_exists && @copy($this->_realpath, $destRealPath))
            return $this->set($fileDest);

        $this->addLog('Unable to copy file');
        return false;
    }

    /**
     * Renames/moves current file to specified destination.
     * Destination path supplied by user resolved to real destination path with {@link realPath}
     *
     * @param string $fileDest Destination path for the current file to be renamed/moved to
     * @return mixed Updated current CFile object on success, 'false' on fail.
     */
    public function rename($fileDest)
    {
        $destRealPath = $this->realPath($fileDest);
        
        if ($this->_exists && @rename($this->_realpath, $destRealPath))
        {
            $this->_filepath = $fileDest;
            $this->_realpath = $destRealPath;
            // update pathinfo properties
            $this->pathInfo();
            return $this;
        }

        $this->addLog('Unable to rename/move file');
        return false;
    }

    /**
     * Alias for {@link rename}
     */
    public function move($fileDest)
    {
        return $this->rename($fileDest);
    }

    /**
     * Deletes current file.
     *
     * @return boolean 'True' if sucessfully deleted, 'false' on fail
     */
    public function delete()
    {
        if ($this->_exists && @unlink($this->_realpath))
        {
            $this->_exists = false;
            return true;
        }

        $this->addLog('Unable to delete file');
        return false;
    }

    // Modified methods taken from Yii CFileHelper.php are listed below
    // ===================================================

    /**
     * Returns the MIME type of the current file.
     * If $_mimeType property is set, returned value is read from that property.
     *
     * This method will attempt the following approaches in order:
     * <ol>
     * <li>finfo</li>
     * <li>mime_content_type</li>
     * <li>{@link getMimeTypeByExtension}</li>
     * </ol>
     * @return mixed the MIME type on success, 'false' on fail.
     */
    public function getMimeType()
    {
        if ($this->_exists)
        {
            if(function_exists('finfo_open'))
            {
                if(($info=@finfo_open(FILEINFO_MIME)) && ($result=finfo_file($info,$this->_realpath))!==false)
                    return $result;
            }

            if(function_exists('mime_content_type') && ($result=@mime_content_type($this->_realpath))!==false)
                return $result;

            $this->_mimeType = $this->getMimeTypeByExtension($this->_realpath);
        }
        if ($this->_mimeType)
            return $this->_mimeType;

        $this->addLog('Unable to get mime type for file');
        return false;
    }

    /**
     * Determines the MIME type based on the extension current file.
     * This method will use a local map between extension name and MIME type.
     * @return string the MIME type. Null is returned if the MIME type cannot be determined.
     */
    public function getMimeTypeByExtension()
    {
        Yii::trace('Trying to get MIME type for "'.$this->_realpath.'" from extension "'.$this->_extension.'"', 'app.extensions.CFile');
        static $extensions;
        if($extensions===null)
            $extensions=require(Yii::getPathOfAlias('system.utils.mimeTypes').'.php');
            
        if(!empty($this->_extension) && isset($extensions[$this->_extension]))
                return $extensions[$this->_extension];

        return false;
    }

}