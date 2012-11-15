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
     * Load All the blocks
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     */
    public function loadBlocks( ) {
        $objSQL = coreObj::getDBO();

        $query = $objSQL->queryBuilder()
                        ->select('*')
                        ->from('#__blocks')
                        ->where('enabled', '=', '1')
                        ->orderBy('order')
                        ->build();

        $results = $objSQL->fetchAll( $query );
        $this->__cache = $results;

        return $results;
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
     * @param   int  $id       Block ID
     *
     * @return  string
     */
    public function getBlockContents( $id ) {
        $block = $this->getBlockById( $id );

        // Block didn't exist
        if( $block === false ) {
            return false;
        } else if( $block ) {

        }
    
        // Determine whether this is a dynamic block, or a block from a static file
        if( isset( $block['file_location'] ) ) {

        }
    }

    /**
     * Loads a block into an instance
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   array  $block       Block array from the db
     *
     */
    public function loadBlock( $data ) {
        //return new Block( $data );
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

        // Loop through the blocks and insert them into the db
        foreach( $this->__cache as $block ) {

            $x = explode( '.', $block['location'] );
            $contentVar = array_splice($x, 1);
            foreach( $x as $tplblock ) {
                $objTPL->assign_block_vars( $tplBlock, array());
            }

            $objTPL->assign_block_vars($x, array(
                $contentVar => 'x'
            ));
        }
    }
}
?>