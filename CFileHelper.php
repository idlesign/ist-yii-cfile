<?php


require_once 'CFile.php';


/**
 * Yii base class stab.
 *
 * Introduces property-like access to CFile methods.
 */
class CApplicationComponent {

    public function __get($name) {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
        throw new Exception('Unable to get an unknown property ' . $name);
    }

    public function __set($name, $value) {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            return $this->$setter($value);
        }
        throw new Exception('Unable to set an unknown property ' . $name);
    }

}


/**
 * CFileHelper is used to access CFile functionality from environments
 * without Yii Framework.
 *
 * Do not confuse with CFileHelper from Yii distribution!
 *
 * Usage example:
 *
 *     $cf_file = CFileHelper::get('files/test.txt');
 *
 */
class CFileHelper extends CFile {

    /**
     * @var null|CFile
     */
    private static $_obj = null;
    private static $_log = array();

    /**
     * Helper method. Return an appropriate CFile instance for a given filesystem object path.
     *
     * @static
     * @param string $filepath Path to the file.
     * @param bool $greedy If `True` file properties (such as 'Size', 'Owner', 'Permission', etc.) would be autoloaded
     * @return CFile
     */
    public static function get($filepath, $greedy=false) {
        if (self::$_obj===null) {
            self::$_obj = new self();
        }
        return self::$_obj->set($filepath, $greedy);
    }

    /**
     * Returns log data from class property.
     *
     * @return array
     */
    public function getLog() {
        return self::$_log;
    }

    /* ================================================== */

    public static function getInstance($filepath, $class_name=__CLASS__) {
        return parent::getInstance($filepath, __CLASS__);
    }

    /**
     * Adds log data into into class property.
     *
     * Use {@link getLog} to get data.
     *
     * @param string $message
     * @param string $level
     */
    protected function addLog($message, $level='info') {
        self::$_log[] = array($level, $message);
    }

    protected function getPathOfAlias($alias) {
        return null;
    }

    protected function formatNumber ($number, $format) {
        return (string)$number;
    }

}
