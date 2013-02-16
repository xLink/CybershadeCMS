<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_Cache extends Core_Classes_coreObj{
    public $cacheToggle  = false,
           $output       = array(),
           $cacheDir     = '',
           $fileTpl      = '',
           $loadedCaches = array(),
           $failedCaches = array();

    /**
     * The constructor of the cache class
     *
     * @version 1.0.0
     * @since 1.0.0
     * @author Dan Aldridge
     *
     * @param string $name
     * @param array  $args
     */
    public function __construct( $name = '', $args = array() ){
        $this->setVars(array(
            'cacheToggle' => doArgs('useCache', false, $args),
            'cacheDir'    => doArgs('cacheDir', '', $args),
            'fileTpl'     => cmsROOT.'cache/cache_%s.php',
        ));
    }

    /**
     * Initializes a cache store
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     *
     * @param       string    $file
     * @param       string    $query
     * @param       callable  $callback
     *
     * @return      string
     */
    public function setup( $file, $query, $callback = null ) {

        $filename = sprintf($this->getVar('fileTpl'), $file);

        //if we can cache & the file is already there, then include it and return it
        if($this->getVar('cacheToggle') && is_file($filename)){
            include_once($filename);
            $result = $$cacheVar;

            //if we have nothing in the result then we will just regenerate it
            if(is_empty($result)){
                $result = $this->generateCache($file, $query);
            }

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
     * Loads a cache store
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     *
     * @param       string  $file
     *
     * @return      array
     */
    public function load( $file ) {

        $file = trim( $file );

        //make sure we have something to work with
        if( empty( $file ) ) {
            trigger_error( '$file is empty, please provide a non-empty string', E_USER_ERROR );

            // Add the cache to the failed cache's array
            $this->failedCaches[] = $file;

            // The File doesn't exist, return empty array
            return array();
        }

        //normalize the var and see if we already have it done
        $file = strtolower( $file );
        if( isset( $this->cacheFiles[$file] ) ) {

            // Cache was successfully loaded, Make sure we log it ;D
            $this->loadedCaches[] = $file;

            //woo just return now, party later k?
            return $this->cacheFiles[$file];

        //awwh, now we have to do some work :(
        } else {

            //generate the filename
            $path = sprintf ($this->getVar( 'fileTpl' ), $file );

            //if its not readable, then ah shit, lets just try and generate it (hopefully theyre trying to generate a sane cache store)
            if( !is_readable( $path ) ) {
                $cache = $this->doCache( $file );
            }

            //try once again
            if( !is_readable( $path ) ) {
                //if we get in here, then the cache file still hasnt generated, so mebe folder perms, or query issue?

                if( empty( $cache ) ) {
                    trigger_error( 'Sorry, we tried everything, your cache file "'.$file.'" does not wanna load, wtf you trying to do?', E_USER_ERROR );

                    // Add the cache to the failed cache's array
                    $this->failedCaches[] = $file;

                    return false;
                }
            } else {
                include_once( $path );
                $cache = ${$file.'_db'};

                //make sure its not empty, if it is, then regenerate it
                if( is_empty( $cache ) ) {
                    $cache = $this->doCache( $file );

                    //if we regenerate it then we will do another check just to make sure...
                    if( is_empty( $cache ) ) {

                        // Add the cache to the failed cache's array
                        $this->failedCaches[] = $file;

                        return false;
                    }
                }
            }

            //cache apparently worked this time, lets roll :D
            $this->cacheFiles[$file] = $cache;
            $this->loadedCaches[]    = $file;

            return $cache;
        }

        //if we get here for whatever reason, something has fucked up :(
        return false;
    }

    /**
     * Gets the cache store, or loads it if it hasnt been already
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $store
     *
     * @return      array   Returns the cache store in array form
     */
    public function get($store){
        //if we have the store loaded, just return
        if(isset($this->cacheFiles[$store])){
            return $this->cacheFiles[$store];
        }

        //try and load the cache, if it failed, we'll just return false
        if($this->load($store) === false){
            return false;
        }

        //give em what theyve always wanted folks, a cache store! :D $$$
        return $this->cacheFiles[$store];
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
            case 'stores':
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
    public function doCache( $file ) {
        $return = false;
        switch($file){
            case 'config':
                $return = $this->generate_config_cache();
            break;

            case 'routes':
                $return = Core_Classes_coreObj::getRoute()->generate_cache();
            break;

            case 'blocks':
                $return = Core_Classes_coreObj::getBlocks()->generate_cache();
            break;

            case 'statistics':
                $return = $this->generate_stats_cache();
            break;

            case 'plugins':
                $query = Core_Classes_coreObj::getDBO()->queryBuilder()->select('*')->from('#__'.$file)->build();
                $this->setup($file, $query);
            break;

        }

        // TODO: throw a hook in here, and modify this baby so the hook can add to this switch without modifying the core code... hrm ;x - xLink

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

        $objSQL = Core_Classes_coreObj::getDBO();

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

        // I can accept the weird indentation, but not the lack of new lines xD
        if(!$this->getVar('cacheToggle')){
            return;
        }

        $fp = fopen(sprintf($this->getVar('fileTpl'), str_replace('_db', '', $filename)), 'w');

            if(!$fp){
                return false;
            }

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

    /**
     *  Generates the config cache
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     *
     */
    public function generate_config_cache(){
        $objSQL = Core_Classes_coreObj::getDBO();

        $query = $objSQL->queryBuilder()
            ->select('key', 'var', 'value', 'default')
            ->from('#__config')
            ->orderBy('key', 'DESC')
            ->build();

        $results = $objSQL->fetchAll($query);
            if(!count($results)){
                echo $objSQL->getError();
                return false;
            }

        $return = array();
        foreach($results as $row) {

            $return[$row['key']][$row['var']] = (isset($row['value']) && !is_empty($row['value'])
                ? $row['value']
                : $row['default']);
        }

        return $return;
    }
}
?>