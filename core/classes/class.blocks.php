<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Blocks extends coreObj{

    private $__cache = array();

    public function __construct() {
    }

    /**
     * Installs a new block from a file to the system
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $uniqueID       Unique ID of this block
     * @param   string  $name           Block name (part of the block function) block_{function-name} { }
     * @param   string  $title          Human readable Name of the block
     * @param   string  $fileLocation   Location of the block's file relative to the cms root
     *
     */
    public function installBlockByFile( $uniqueID, $title, $name, $fileLocation ) {
        $objSQL = coreObj::getDBO();

        // Check the block doesn't already exist
        if( $this->getBlockByUniqueId( $uniqueID ) === false ) {
            trigger_error( 'Error in Installing new block, Unique ID already exists', E_USER_ERROR );
            return false;
        }

        $data = array(
            'uniqueid'      => $uniqueID,
            'title'         => $title,
            'name'          => $name,
            'file_location' => $file_location
        );

        $query = $objSQL->queryBuilder()
                        ->insertInto('#__blocks')
                        ->set( $data )
                        ->build();

        if( !$objSQL->query( $query ) ) {
            trigger_error( 'Error in Installing new block, The query could not be executed properly', E_USER_ERROR );
            return false;
        }

        return true;
    }

    /**
     * Installs a new block from a module to the system
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $moduleName     Name of the module
     * @param   string  $page_id        Page ID
     *
     */
    public function installBlockByModule( $uniqueID, $name, $moduleHash, $moduleMethod, $args ) {
        $objSQL = coreObj::getDBO();

        return true;
    }

    /**
     * Uninstalls a block from the system.
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $moduleName     Name of the module
     * @param   string  $page_id        Page ID
     *
     */
    public function uninstallBlock( $id ) {
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select( 'id' )
                        ->from( '#__blocks' )
                        ->where( 'id', '=', $id )
                        ->build();

        $objSQL->query( $query );

        if( !$objSQL->affectedRows() ) {
            // Could not find any blocks with this id
            return false;
        }

        $query = $objSQL->queryBuilder()
                        ->deleteFrom( '#__blocks_routes' )
                        ->where( 'blockID', '=', $id )
                        ->build();

        $objSQL->query( $query );

        $query = $objSQL->queryBuilder()
                        ->deleteFrom( '#__blocks' )
                        ->where( 'id', '=', $id )
                        ->build();

        if( $objSQL->query( $query ) ) {
            return true;
        }

        return false;
    }

    public function enableBlock() {
        # code...
    }

    public function disableBlock() {
        # code...
    }

    public function updateOrder() {
        # code...
    }

    public function getBlockById( $id ) {

        // If the block exists in the cache, return that.
        if( isset( $this->__cache[$id] ) && !empty( $this->__cache[$id] ) ) {
            return $this->__cache[$id];

        } else {
            return false;
        }

        // Grab a sexy instance of the Database Object
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                    ->select()
                    ->from('#__')
                    ->where()
                    ->build();

        $objSQL->query( $query );

        $results = $objSQL->results();

        if( !empty( $results ) ) {
            echo dump( $blocks );
        }

        // If block can't be found, return false

    }

    public function getBlockByUniqueId( $id ) {
        $id = (int) $id;

        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select( 'id')
                        ->from( '#__blocks')
                        ->where( 'id', '=', $id)
                        ->build();

        $result = $objSQL->query( $query );

        if( !$objSQL->affectedRows() ) {
            return false;
        }

        return $objSQL->results();
    }

    /**
     * Get's a blocks Contents
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   mixed  $block       Block ID | Block Array
     *
     * @return  string
     */
    public function getBlockContents( &$block ) {
        if( is_array( $block ) && isset( $block['id'] ) ) {
            $block = $block['id'];
        }

        $block = $this->getBlockById( $block );

        // Block didn't exist
        if( $block === false ) {
            return false;
        }
    
        // Determine whether this is a dynamic block, or a block from a static file
        if( !empty( $block['file_location'] ) ) {

            // If the file isn't readable, return false
            if( !is_readable( $block['file_location'] ) ) {
                trigger_error( sprintf( 'Block %s (%d) has no readable file attached to it..',
                    $block['name'],
                    $block['id']
                ), E_USER_ERROR );
                return false;
            }

            // Include our block file
            include_once( $block['file_location'] );

            if( !is_callable( 'block_' . $block['name'] ) ) {
                trigger_error( sprintf( 'Block %s (%d) has no callable function to execute within it..',
                    $block['name'],
                    $block['id']
                ), E_USER_ERROR );
                return false;
            }

            $funcName = 'block_' . $block['name'];
            $return   = call_user_func( $funcName, $block );

        } else if( is_array( $block['extra'] ) &&  isset( $block['extra']['method'] ) && isset( $block['extra']['module'] ) ) {
            $args   = array_merge( array('block' => $block ), ( $block['extra']['args'] ?: array() ) );
            $return = reflectMethod( $block['extra']['module'], $block['extra']['method'], $args );

        } else {
            echo dump($block);
            trigger_error( 'Could not find block contents for block ID ' . $block['id'], E_USER_ERROR );
            return false;
        }

        // Tidy up
        unset( $block, $extra, $funcName );

        return $return;
    }

    /**
     * Called from the Page class, Insert all the blocks into current template
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     */
    public function insertBlocks( ) {
        $objTPL = coreObj::getTPL();

        if( empty( $this->__cache ) ) {
            $objCache = coreObj::getCache();
            $this->__cache = $objCache->load('blocks');
        }

        // Loop through the blocks and insert them into the db
        foreach( $this->__cache as $block ) {

            if( !isset( $block['enabled'] ) || $block['enabled'] == '0' ) {
                return false;
            }

            $x = explode( '.', $block['location'] );
            $contentVar = array_splice($x, 1);

            foreach( $x as $tplblock ) {
                $objTPL->assign_block_vars($x[0], array(
                    $contentVar[0] => $this->getBlockContents( $block )
                ));
            }
        }
    }

        /**
     * Generates the cache for the routing system, used as a callback in the caching class
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Daniel Noel-Davies
     *
     * @todo        Use 2 Queries, One to select non-structure url's (without :'s)
     *                  and one with structure'd url's. The first should be listed
     *                  before the second, to allow for successful processing and
     *                  precedence.
     *
     * @return      array
     */
    public static function generate_cache(){
        $output = array();
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select('id', 'uniqueid', 'title', 'name', 'location', 'order', 'enabled', 'file_location', 'extra')
                        ->from('#__blocks')
                        ->orderBy('location', 'order')
                        ->build();

        $results = $objSQL->fetchAll( $query );

        foreach( $results as $result ) {

            $extra = json_decode( $result['extra'], true);
            if( $extra === null ){
                $extra = array();
            }


            // FIX ME
            $output[$result['id']] = array(
                'id'            => $result['id'],
                'uniqueid'      => $result['uniqueid'],
                'title'         => $result['title'],
                'name'          => $result['name'],
                'location'      => $result['location'],
                'order'         => $result['order'],
                'enabled'       => $result['enabled'],
                'file_location' => $result['file_location'],
                'extra'         => $extra,
            );
        }
        return $output;
    }

}
?>