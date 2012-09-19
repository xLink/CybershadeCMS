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

    /**
     *
     *
     *
     *
     *
     */
    public function __construct($name='', $args=array()){
        $this->setVars(array(
            'cacheToggle' => doArgs('useCache', false, $args),
            'cacheDir'    => doArgs('cacheDir', '', $args),
            'fileTpl'     => cmsROOT.'cache/cache_%s.php',
        ));
    }

    /**
     * Sets up a cache file
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     *
     * @param       string    $file   [desc]
     * @param       string    $query      [desc]
     * @param       callable  $callback   [desc]
     *
     * @return      string   ?
     */
    public function setup($file, $query, $callback=null){

        $filename = sprintf($this->getVar('fileTpl'), $file);

        //if we can cache & the file is already there, then include it and return it
        if($this->getVar('cacheToggle') && is_file($filename)){
            include_once($filename);
            $result = $$cacheVar;

        //if we have a callback then we will call it
        }else if(is_callable($callback)){
            $result = call_user_func($callback);

        //otherwise we just have to generate a new cache file
        }else{
            $result = $this->generateCache($file, $query);
        }

        return $result;
    }

    /**
     * Removes a specific set of cache files
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $type
     *
     * @return      bool
     */
    public function remove($type) {
        $cacheFiles = '';
        switch($type){
            case 'config':
                $cacheFiles = glob(cmsROOT.'cache/cache_*.php');
            break;

            case 'media':
                $cacheFiles = glob(cmsROOT.'cache/media/minify_*');
            break;

            case 'template':
                $cacheFiles = glob(cmsROOT.'cache/template/tpl_*');
            break;
        }

        if(is_empty($cacheFiles)){ return false; }

        if(is_array($cacheFiles) && !is_empty($cacheFiles)){
            foreach($cacheFiles as $file){
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Registers Cache hooks
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $file    Alias for Cache Store
     */
    public function doCache($file){
        $objSQL = coreObj::getDBO();
        $objRoute = coreObj::getRoute();

        $return = false;
        switch($file){
            case 'config':
                $query = $objSQL->queryBuilder()->select('*')->from('#__config')->build();
                $this->setup($file, $query);
            break;
            case 'routes':
                $x = is_callable( array($objRoute, 'generate_cache') );
                echo dump( $x );
                $return = $objRoute->generate_cache();
            break;

            case 'statistics':
                $return = $this->generate_stats_cache();
            break;

        }

        if($return !== false){
            $this->writeFile($file, $return);
        }
    }

    /**
     * Regenerates the Cache Store.
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $file    Alias for Cache Store
     */
    public function regenerateCache($file){
        //if its there, then kill it first
        if(is_readable(sprintf($this->getVar('fileTpl'), $file))){
            unlink(sprintf($this->getVar('fileTpl'), $file));
        }

        $this->doCache($file);
    }

    /**
     * Writes the cache files
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $file    Alias for Cache Store
     * @param       string $query   Query that will generate the cache store
     */
    public function generateCache($file, $query){
        $this->output = '';

        $objSQL = coreObj::getDBO();

        $this->output = $objSQL->fetchAll($query);
            if($this->output === false){
                return false;
            }

        return $this->writeFile($file, $this->output);
    }


    /**
     * Actually writes the Cache to file.
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $filename   Filename to store cache as
     * @param       string $contents   Contents to store in the file
     */
    public function writeFile($filename, $contents){
        if(!$this->getVar('cacheToggle')){ return; }

        $fp = @fopen(sprintf($this->getVar('fileTpl'), str_replace('_db', '', $filename)), 'wb');
            if(!$fp){ return false; }

        $contents = var_export($contents, true);
        $variable = '$'.$filename.'_db';

        $file = <<<PHP
<?php
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

$variable = {$contents};

?>
PHP;

        fwrite($fp, $file);
        fclose($fp);

        return $contents;
    }


/**
  //
  //-- Call Back Funcs
  //
**/

    /**
     *  Generates the statistics cache
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     *
     */
    public function generate_stats_cache(){

    }
}
?>