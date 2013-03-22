<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Upload class to upload files to a certain location
 */
class Core_Classes_Upload extends Core_Classes_coreObj {

    /**
     * Directory of the uploads location
     *
     * @access protected
     */
    protected $directory;

    /**
     * The input name of the input box which is generated
     *
     * @access protected
     */
    protected $input_name = 'file';

    /**
     * The class constructor
     */
    public function __construct( $class, $args = array() ){

        // Specifies the $_FILES array key
        if( !is_empty( $args[0] ) ){
            $this->setVar( 'input_name', $args[0] );
        }

        $this->setDirectory();
    }

    /**
     * Process uploads
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       array   $extensions  (optional)
     * @param       int     $size (optional)
     *
     * @return      boolean
     */
     public function doUpload( $extensions = array(), $size = 50000 ) {

        $objPlugins = Core_Classes_coreObj::getPlugins();

        $destination = $this->getVar('directory');
        $input_name  = $this->getVar('input_name');

        // Checks if the destination was false (from the getVar())
        if( !$destination ){
            (cmsDEBUG ? memoryUsage('Upload: Failed to upload as desitnation folder was not accessible' ) : '');
            return false;
        }

        // Get the current file extension
        $fileName   = preg_replace('/[^a-zA-Z0-9-_.]/', '', $_FILES[$input_name]['name']);
        $explodedFileName = explode( '.', $fileName );

        echo dump( $explodedFileName );

        // Only vars can be passed by ref
        $extension  = end( $explodedFileName );
        $fileSize   = $_FILES[$input_name]['size'];




        $chk = in_array( $extension, $extensions );
        $chk2 = $fileSize <= $size;

        echo dump( $input_name, 'In array' );
        echo dump( $chk2, 'Filesize' );


        // Check to see that the extension is an allowed extension and the filesize is <= the allowed filesize
        if( in_array( $extension, $extensions ) && ( $fileSize <= $size ) ){

            if( $_FILES[$input_name]['error'] > 0 ){

                (cmsDEBUG ? memoryUsage(sprintf(
                    'Upload: Error uploading file, error code: %s',
                    $_FILES[$input_name]['error']
                )) : '');

                trigger_error( sprintf( 'Upload Failed due to the following error: %s', $_FILES[$input_name]['error'] ) );
                return false;
            } else {
                if( file_exists( $destination . '/' . $fileName ) ) {
                    trigger_error( sprintf( 'The uploaded file already exists: %s/%s', $destination, $fileName ) );
                    return false;
                } else {

                    $moveFile = move_uploaded_file( $_FILES[$input_name]['tmp_name'], $destination . '/' . $fileName );

                    // Check if the file was moved correctly
                    if( $moveFile ){
                        $objSQL  = Core_Classes_coreObj::getDBO();
                        $objUser = Core_Classes_coreObj::getUser();

                        // Setup the data to be inserted into the db
                        $uploadData = array(
                            'uid'        => $objUser->grab('id'),
                            'filename'   => $fileName,
                            'file_type'  => $extension,
                            'timestamp'  => time(),
                            'public'     => 0,
                            'authorized' => 0,
                            'file_size'  => $fileSize,
                            'location'   => $destination,
                        );

                        // Automatically allow admins to have authorized content
                        if( Core_Classes_User::$IS_ADMIN ){
                            $uploadData['authorized'] = 1;
                        }

                        $query = $objSQL->queryBuilder()
                                        ->insertInto('#__uploads')
                                        ->set( $uploadData )
                                        ->build();

                        $result = $objSQL->query( $query );

                        // If all went well, return true
                        if( $result ){

                            // Add a hook to allow developers to add extra functionality
                            $objPlugins->hook( 'CMS_UPLOADED_FILE', $uploadData );

                            (cmsDEBUG ? memoryUsage('Upload: Successfully uploaded the file') : '');
                            return true;
                        }
                    } else {
                        trigger_error( sprintf('Could not move uploaded file to %s/%s', $destination, $fileName ) );
                        return false;
                    }
                }
            }
        }


        return false;
    }

    /**
     * Sets the upload directory to a specific location, assuming it's writable
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       string   $directory
     * @param       bool     $create
     *
     * @return      boolean
     */
    public function setDirectory( $directory = '', $create = false ){
        $objPlugins = Core_Classes_coreObj::getPlugins();

        if( trim($directory) === '' ){
            (cmsDEBUG ? memoryUsage('Upload: Using default folder') : '');
            $this->setVar('directory', sprintf( '%sassets/uploads/all', cmsROOT ) );
        } else {

            // If create is set then create a new folder
            if( $create === true && !file_exists( $directory ) ){
                $this->_mkDir( $directory );
            }

            // Checks if the given directory is writable
            if( !file_exists( $directory ) || ( file_exists( $directory ) && !is_writable( $directory ) ) ){

                (cmsDEBUG ? memoryUsage('Upload: Destination folder was not writable') : '');
                trigger_error( sprintf( 'The destination folder was not writable, please chmod it to 0775 : %s',
                    $directory
                ));

                return false;
            } else {
                (cmsDEBUG ? memoryUsage('Upload: Setting upload directory') : '');
                $this->setVar('directory', $directory);
                return true;
            }
        }

        return false;
    }

    /**
     * Creates the upload directory if specified
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       string  $path
     * @param       int     $mode
     *
     * @return      bool
     */
    protected function _mkDir($path, $mode = 0777) {
        if( file_exists($path) ){
            return false;
        }

        $old    = umask(0);
        $result = mkdir($path, $mode);
        umask($old);

        if( $result ){
            return true;
        }

        return false;
    }

    /**
     * Authorizes a peice of uploaded content
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       int     $fid        The file ID
     * @param       bool    $confirm    Confirm with the user that the upload has been authorized
     *
     * @return      bool
     */
    public function authorize( $fid, $confirm = false ){
        if( is_empty( $fid ) ){
            return false;
        }

        $objPlugins = Core_Classes_coreObj::getPlugins();
        $objSQL     = Core_Classes_coreObj::getDBO();
        $objUser    = Core_Classes_coreObj::getUser();

        // Check if the file is already authorized
        $checkAuth = $objSQL->queryBuilder()
            ->select('authorized', 'uid')
            ->from('#__uploads')
            ->where('id', '=', $fid)
            ->build();

        $fileAuth = $objSQL->fetchLine( $checkAuth );

        $objPlugins->hook( 'CMS_AUTHORIZE_UPLOAD' );

        // return true if the file is already authorized
        if( isset( $fileAuth['authorized'] ) && $fileAuth['authorized'] == '1' ){
            return true;
        }

        // Update the uploads content to be authorized
        $query = $objSQL->queryBuilder()
            ->update('#__uploads')
            ->set(array(
                'authorized'    => 1
            ))
            ->where('id', '=', $fid)
            ->build();

        $result = $objSQL->query( $query );

        if( $result ){
            $uid = ( !is_empty( $fileAuth['uid'] ) ? $fileAuth['uid'] : false );
            if( $confirm && $uid ){
                $to      = $objUser->get( 'email', $fileAuth['uid'] );
                $from    = sprintf('no-reply@', ltrim( $_SERVER['SERVER_NAME'], 'www.' ));
                $subject = sprintf('Your upload has been authorized - %s', $_SERVER['SERVER_NAME']);
                $message = sprintf('Your upload has now been authorized at %s', $_SERVER['SERVER_NAME']);

                _mailer( $to, $from, $subject, $message );
            }

            return true;
        }

        return false;
    }

    /**
     * Makes the content public
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       int     $fid        The file ID
     *
     * @return      bool
     */
    public function makePublic( $fid ){
        if( is_empty( $fid ) ){
            return false;
        }

        $objPlugins = Core_Classes_coreObj::getPlugins();
        $objSQL     = Core_Classes_coreObj::getDBO();

        // Check if the file is already public
        $check = $objSQL->queryBuilder()
            ->select('public')
            ->from('#__uploads')
            ->where('id', '=', $fid)
            ->build();

        $fileCheck = $objSQL->fetchLine( $check );

        $objPlugins->hook( 'CMS_PUBLICIZE_UPLOAD' );

        // return true if the file is already public
        if( $fileCheck['public'] == '1' ){
            return true;
        }

        // Update the uploads content to be public
        $query = $objSQL->queryBuilder()
            ->update('#__uploads')
            ->set(array(
                'public'    => 1
            ))
            ->where('id', '=', $fid)
            ->build();

        $result = $objSQL->query( $query );

        if( $result ){
            return true;
        }

        return false;
    }
}

?>