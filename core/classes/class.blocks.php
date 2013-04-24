<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_Blocks extends Core_Classes_coreObj {

    public function __construct() { }

    /**
     * Install a block from a Module method
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $var       Parameter Description
     *
     */
    public function installFromModule( $module, $method ) {
        $objModule = Core_Classes_coreObj::getModule();
        $objSQL    = Core_Classes_coreObj::getDBO();

        $details = $objModule->getDetails( $module );

        // Check method is callable and the module is enabled
        // Not okay
        if( $details === false || $objModule->moduleInstalled(  ) === false ) {
            // Error + return false
            trigger_error( 'Module x is not installed, No block was created' );
            return false;
        }

        $data = array(
            'uniqueid' => randcode(8),
            'label'    => '',
            'title'    => '',
            'region_name' => '',
            'order'     => '',
            'enabled'   => '',
            'info'      => json_encode(),
            'args'      => json_encode(),
            'whitelist' => '',
            'content'   => ''
        );

        // Add into db + display status
        $query = $objSQL->queryBuilder()
                    ->insertInto('#__blocks')
                    ->set($data)
                    ->build();

        $result = $objSQL->insert( $query );

        if( $result ) {
            return true;
        }

        return false;
    }

    /**
     * Install a block from a Menu
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $menu_name       Menu key that a menu is grouped by (menu_name)
     *
     */
    public function installFromMenu( $menu_name ) {
        # code...
    }

    /**
     *  Uninstall block from system
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int  $block_id       Block ID
     *
     */
    public function uninstall( $block_id ) {
        # code...
    }

    /**
     * enable a block using it's ID
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int  $block_id       Block ID
     *
     */
    public function enable( $block_id ) {
        # code...
    }

    /**
     * disable a block using it's ID
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int  $block_id       Block ID
     *
     */
    public function disable( $block_id ) {
        # code...
    }

    /**
     * Retrieve a block from the database using it's ID
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int  $block_id       Block ID
     *
     */
    public function get( $block_id ) {
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
        $objSQL = Core_Classes_coreObj::getDBO();

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

        $objSQL = Core_Classes_coreObj::getDBO();

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

    /*
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
        $objTPL = Core_Classes_coreObj::getTPL();

        if( empty( $this->__cache ) ) {
            $objCache = Core_Classes_coreObj::getCache();
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
    } // END insertBlocks

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
        $objSQL = Core_Classes_coreObj::getDBO();

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