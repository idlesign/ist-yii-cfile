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
 * Usage example:
 *
 *     $cf_file = CFileHelper::get('files/test.txt');
 *
 */
class CFileHelper extends CFile {

    /**
     * @var null|CFile
     */
    private static $_log = array();

    /**
     * Helper method. Return an appropriate CFile instance for a given filesystem object path.
     *
     * @static
     * @param string $filepath Path to the file.
     * @param bool $greedy If `True` file properties (such as 'Size', 'Owner', 'Permission', etc.) would be autoloaded
     * @return CFile
     */
    public static function get($filepath, $greedy=false, $className = __CLASS__) {
        return parent::set($filepath, $greedy, $className);
    }

    /**
     * Returns log data from class property.
     *
     * @return array
     */
    public function getLog() {
        return self::$_log;
    }

    /**
     * Adds log data into into class property.
     *
     * Use {@link getLog} to get data.
     *
     * @param string $message
     * @param string $level
     */
    protected static function addLog($message, $level='info') {
        self::$_log[] = array($level, $message);
    }

    protected static function getPathOfAlias($alias) {
        return null;
    }

    protected static function formatNumber ($number, $format) {
        return (string)$number;
    }

}
