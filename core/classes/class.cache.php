<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class cache extends coreObj{

    public $cacheToggle = false,
           $output      = array(),
           $cacheDir    = '',
           $fileTpl     = '';

    public function __construct($name='', $args=array()){
        $a = func_get_args();
        echo dump($a);
        $this->setVars(array(
            'cacheToggle' => doArgs('useCache', false, $args),
            'cacheDir'    => doArgs('cacheDir', '', $args),
            'fileTpl'     => cmsROOT.'cache/cache_%s.php',
        ));
    }

    /**
     *  Sets up a cache file
     *
     *
     *
     *
     */
    public function setup($cacheVar, $filename, $query, $result, $callback=null){

        //if we can cache & the file is already there, then include it and return it
        if($this->getVar('cacheToggle') && is_file($this->getVar('cacheDir').$filename)){
            include_once($this->getVar('cacheDir').$filename);
            $result = $$cacheVar;

        //if we have a callback then we will call it
        }else if(is_callable($callback)){
            $result = call_user_func($callback);

        //otherwise we just have to generate a new cache file
        }else{
            $result = $this->generateCache($cacheVar, $filename, $query);
        }

        return $result;
    }

    /**
     *  Registers Cache hooks
     *
     *
     *
     *
     */
    public function registerCache(){

    }

    /**
     *  Generates the Cache Files, if already present it will overwrite.
     *
     *
     *
     *
     */
    public function generateCache($filename){
        //if its there, then kill it first
        if(is_readable(sprintf($this->getVar('fileTpl'), $filename))){
            unlink(sprintf($this->getVar('fileTpl'), $filename));
        }


    }

    /**
     *  Writes the cache files
     *
     *
     *
     *
     */
    public function writeFile($filename, $contents){
        if(!$this->getVar('cacheToggle')){ return; }

        $fp = @fopen(sprintf($this->getVar('fileTpl'), str_replace('_db', '', $file)), 'wb');
            if(!$fp){ return false; }

        $array = var_export($contents, true);

        $file = <<<PHP
<?php
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

${$filename} = {$array};

?>
PHP;

        fwrite($fp, $file);
        fclose($fp);

        return true;
    }
}
?>