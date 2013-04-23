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
     * The list of upload logs
     *
     * @access protected
     */
    public $uploadData = array();

    /**
     * A list of errors that happened during upload
     *
     * @access public
     */
    public $uploadErrors = array();

    /**
     * The class constructor
     */
    public function __construct( $className ){
        $this->setDirectory();
    }

    /**
     * Set the field name to be used for file upload
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   string  $input_name       Name of the input field used for fileuploads
     *
     */
    public function setInputName( $input_name ) {
        if( !is_empty( $input_name ) ){
            $this->setVar( 'input_name', $input_name );
        }
    }

    /**
     * Process uploads
     *
     * @version     1.2.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       array   $extensions  (optional)
     * @param       int     $size (optional)
     *
     * @return      boolean
     */
     public function doUpload( $extensions = array(), $allowedSize = 100000 ) {
        $objPlugins = Core_Classes_coreObj::getPlugins();

        $destination = $this->getVar('directory');
        $input_name  = $this->getVar('input_name');

        // Make an alias to the files
        $file = $_FILES[$input_name];

        // Checks if the destination was false (from the getVar())
        if( !$destination ){
            debugLog('Upload: Failed to upload as desitnation folder was not accessible');
            return false;
        }

        // Get the current file extension
        $fileName  = preg_replace('/[^a-zA-Z0-9-_.]/', '', $file['name'][$i]);
        $fileParts = explode( '.', $fileName );
        $extension = end($fileParts);
        $fileSize  = $file['size'][$i];
        $finalPath = $destination . '/' . $fileName;

        $absolutePath = $this->config('global', 'realPath');
        if( strpos($destination, $absolutePath ) === false ){
            $finalPath = $absolutePath . $destination . '/' . $fileName;
        }

        $extensions = array_map( 'strtolower', $extensions );

        // Check to see that the extension is an allowed extension and the filesize is <= the allowed filesize
        if( in_array( strtolower( $extension ), $extensions ) && ( $fileSize <= $allowedSize ) ){

            $imageCount = count( $file['name'][$i] );

            for( $i = 0; $i < $imageCount; $i++ ){
                $error = $file['error'][$i];
                if( $error > 0 ){
                    debugLog( 'Upload: Error uploading file, error code ' . $error );
                    $this->uploadErrors[] = sprintf( 'Upload Failed due to the following error: %s', $error );
                    trigger_error( $error );
                } else {
                    if( file_exists( $finalPath ) ) {
                        $error = sprintf( 'The uploaded file already exists: %s', $finalPath );
                        $this->uploadErrors[] = $error;
                        trigger_error( $error );
                    } else {

                        $moveFile = move_uploaded_file( $file['tmp_name'][$i], $finalPath );

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
                                'location'   => $finalPath,
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
                                $this->uploadData[$input_name] = $uploadData;

                                // Add the insert id for reference to
                                $this->uploadData[$input_name]['fileid'] = $objSQL->fetchInsertId();

                                // Add a hook to allow developers to add extra functionality
                                $objPlugins->hook( 'CMS_UPLOADED_FILE', $uploadData );

                                debugLog('Upload: Successfully uploaded the file');
                            }
                        } else {
                            $error = sprintf('Could not move uploaded file to %s.', $finalPath );
                            $this->uploadErrors[] = $error;
                            trigger_error( $error );
                        }
                    }
                }
            }
        }
        if( isset( $this->uploadErrors ) && !is_empty( $this->uploadErrors ) ){
            trigger_error('There was ' . count( $this->uploadErrors ) . ' errors from the uploads.');
            debugLog( $this->uploadErrors, 'Uploaded Errors' );
            return false;
        }

        return true;
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
            debugLog('Upload: Using default folder');
            $this->setVar('directory', sprintf( '%sassets/uploads/all', cmsROOT ) );
        } else {

            // If create is set then create a new folder
            if( $create === true && !file_exists( $directory ) ){
                $this->_mkDir( $directory );
            }

            // Checks if the given directory is writable
            if( !file_exists( $directory ) || ( file_exists( $directory ) && !is_writable( $directory ) ) ){

                debugLog('Upload: Destination folder was not writable');
                trigger_error( sprintf( 'The destination folder was not writable, please chmod it to 0775 : %s',
                    $directory
                ));

                return false;
            } else {
                debugLog('Upload: Setting upload directory');
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

    /**
     * Retrieves one uploaded image, or set of them
     *
     * @version 1.0
     * @since   1.0
     * @author  Daniel Noel-Davies
     *
     * @param   int|array  $id       Single Upload ID, or array of Upload IDs
     *
     */
    public function getInfo( $id, $onlyPublic = true ) {

        // Check we've got what we need
        if( !is_int( $id ) && !is_numeric( $id ) && !is_array( $id ) ) {
            trigger_error('Invalid arguments supplied for ' . __FUNCTION__ );
            return array();
        }

        $objSQL = Core_Classes_coreObj::getDBO();

        $where  = false;
        $query  = $objSQL->queryBuilder()
                    ->select('*')
                    ->from('#__uploads');

        if( is_array( $id ) ) {
            foreach( $id as $i ) {
                if( is_int( $i ) ) {
                    if( $where == true ) {
                        $query->orWhere( 'id', '=', $i );
                    } else {
                        $query->where('id', '=', $i);
                    }
                }
            }
        } else {
            $query->where( 'id', '=', $id );
        }

        $query = $query->build();
        $info  = $objSQL->fetchAll( $query, 'id' );

        if( sizeOf( $info ) == 1 ) {
            return $info[$id];
        }

        return $info;
    }
}

?>