<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');


class Upload extends coreObj {

    /**
     * Process uploads
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Richard Clifford
     *
     * @param       string  $destination  (optional)
     * @param       array   $extensions  (optional)
     * @param       int     $size in bits (optional)
     *
     * @return      boolean
     */
     public function doUpload( $destination = '', $extensions = array(), $size = 20000) {
        if( trim( $destination ) == '' ){
            $destination = sprintf( '%sassets/uploads/all', cmsROOT );

            if( !file_exists( $destination ) || !is_writable( $destination ) ){
                
                $destination = sprintf( '%sassets/uploads/all', cmsROOT );

                if( !is_writable( $destination ) ){
                    trigger_error( sprintf( 'The destination folder was not writable, please chmod it to 0775 : %s', $destination ) );
                    return false;
                }
            }
        }
        
        $allowedExts = array();

        if( !is_empty( $extensions ) ){
            $allowedExts[] = $extensions;
        }

        // Get the current file extension
        $fileName  = secureMe( $_FILES['upload']['name'], 'alphanum' );
        $extension = end( explode( '.', $fileName ) );

        if( in_array( $extension, $allowedExts ) && ($_FILES['upload']['size'] < $size ) ){

            if( $_FILES['upload']['error'] > 0 ){
                trigger_error( sprintf( 'Upload Failed due to the following error: %s', $_FILES['upload']['error'] ) );
                return false;
            } else {
                if( file_exists( $destination . '/' . $fileName ) ) {
                    trigger_error( sprintf( 'The uploaded file already exists: %s/%s', $destination, $fileName ) );
                    return false;
                } else {
                    move_uploaded_file( $_FILES['upload']['tmp_name'], $destination . '/' . $fileName ); 
                }
            }
        }

        return false;
    }
}

?>
