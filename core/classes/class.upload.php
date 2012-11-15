<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

/**
 * Upload class to upload files to a certain location
 */
class Upload extends coreObj {

    /**
     * Directory of the uploads location
     *
     * @access protected
     */
    protected $directory;

    /**
     * The class constructor
     */
    public function __construct(){
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
     public function doUpload( $extensions = array(), $size = 20000) {
        $objPlugins = coreObj::getPlugins();

        $destination = $this->getDirectory();
        $allowedExts = array();

        if( !is_empty( $extensions ) ){
            $allowedExts[] = $extensions;
        }

        // Get the current file extension
        $fileName   = secureMe( $_FILES['upload']['name'], 'alphanum' );
        $extension  = end( explode( '.', $fileName ) );
        $fileSize   = $_FILES['upload']['size'];

        // Check to see that the extension is an allowed extension and the filesize is <= the allowed filesize
        if( in_array( $extension, $allowedExts ) && ( $fileSize <= $size ) ){

            if( $_FILES['upload']['error'] > 0 ){

                (cmsDEBUG ? memoryUsage(sprintf(
                    'Upload: Error uploading file, error code: %s',
                    $_FILES['upload']['error']
                )) : '');

                trigger_error( sprintf( 'Upload Failed due to the following error: %s', $_FILES['upload']['error'] ) );
                return false;
            } else {
                if( file_exists( $destination . '/' . $fileName ) ) {
                    trigger_error( sprintf( 'The uploaded file already exists: %s/%s', $destination, $fileName ) );
                    return false;
                } else {
                    $moveFile = move_uploaded_file( $_FILES['upload']['tmp_name'], $destination . '/' . $fileName );

                    // Check if the file was moved correctly
                    if( $moveFile ){
                        $objSQL  = coreObj::getDBO();
                        $objUser = coreObj::getUser();

                        // Setup the data to be inserted into the db
                        $uploadData = array(
                            'uid'        => $objUser->grab('id'),
                            'filename'   => $fileName,
                            'file_type'  => $extension,
                            'timestamp'  => time(),
                            'public'     => 0,
                            'authorized' => 0,
                            'file_size'  => $fileSize,
                        );

                        // Automatically allow admins to have authorized content
                        if( User::$IS_ADMIN ){
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
        $objPlugins = coreObj::getPlugins();

        $objPlugins->hook( 'CMS_SET_UPLOAD_DIR', $directory );

        if( trim($directory) === '' ){
            (cmsDEBUG ? memoryUsage('Upload: Using default folder') : '');
            $this->directory = sprintf( '%sassets/uploads/all', cmsROOT );
        } else {

            // If create is set then create a new folder
            if( $create === true ){
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
                $this->directory = $directory;
                return true;
            }
        }

        return false;
    }

    /**
     * Gets the current upload directory
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @return      string
     */
    public function getDirectory(){
        return $this->directory;
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
     * @return      string
     */
    protected function _mkDir($path, $mode = 0777) {
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

        $objPlugins = coreObj::getPlugins();
        $objSQL     = coreObj::getDBO();
        $objUser    = coreObj::getUsers();

        // Check if the file is already authorized
        $checkAuth = $objSQL->queryBuilder()
                            ->select('authorized', 'uid')
                            ->from('#__uploads')
                            ->where('id', '=', $fid)
                            ->build();

        $fileAuth = $objSQL->fetchLine( $checkAuth );

        $objPlugins->hook( 'CMS_AUTHORIZE_UPLOAD' );

        // return true if the file is already authorized
        if( $fileAuth['authorized'] == '1' ){
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
            if( $confirm ){
                $to      = $objUser->get( 'email', $fileAuth['uid'] );
                $from    = sprintf('no-reply@', ltrim( $_SERVER['SERVER_NAME'], 'www.' ));
                $subject = sprintf('Your upload has been authorized - %s');
                $message = sprintf('Your upload has now been authorized at ', $_SERVER['SERVER_NAME']);

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

        $objPlugins = coreObj::getPlugins();
        $objSQL     = coreObj::getDBO();

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