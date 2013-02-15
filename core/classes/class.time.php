<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Core_Classes_Time extends Core_Classes_coreObj{

	public $currentLanguage = '';

	public function __construct( ) {
		$this->currentLanguage = $this->config('site', 'language');
	}

    /**
     * Generate a date string from a timestamp
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       int       $timestamp
     * @param       string    $format
     * @param       bool      $format
     *
     * @return      string
     */
	public function mk_time( $timestamp, $format='db', $return = 0 ) {

		// Get the instances we need
		$objUser = Core_Classes_coreObj::getUser();

		$translate = array();
		$format    = ( $format == 'db'     ? $this->config( 'site', 'time', 'jS F h:ia' )  : $format );
		$timestamp = ( isset( $timestamp ) ? $timestamp                                    : time() );
		$timestamp = ( date( 'I' ) == 0    ? $this->mod_time( $timestamp, 0, 0, 1 )        : $timestamp);

		// If User is logged in, Use his/her timezone
		if( User::$IS_ONLINE && $objUser->grab( 'timezone' ) ) {
			$this->mod_time( $timestamp, 0, 0, $objUser->grab( 'timezone' ) );
		}

		// Translate the date if it's possible
		if( empty( $translate ) && $this->currentLanguage != 'en' ) {
		    $lang_date = langVar('DATETIME');

			reset($lang_date);

			while(list($match, $replace) = each($lang_date)){
				$translate[$match] = $replace;
			}
		}

		// If we're not meant to return anything,
		if( $return === 0 ) {
			$return = gmdate( $format, $timestamp );

			// Execute translation if there is a translation
			if( !empty( $translate ) ) {
				$return = strtr( $return, $translate );
			}

		} else {
			$return = $timestamp;
		}

		// Tidy up
		unset( $objUser, $translate, $format, $timestamp, $lang_date, $match, $replace, $format );

		return $return;
	}

    /**
     * Modify a timestamp
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       int       $timestamp
     * @param       int       $seconds
     * @param       int       $minutes
     * @param       int       $hours
     * @param       string    $mode
     *
     * @return      int
     */
 	static function mod_time( $timestamp, $seconds=0, $minutes=0, $hours=0, $mode='ADD' ){

		$second   = 1;
		$minute   = 60;
		$hour 	  = 3600;
		$mode     = strtolower( $mode );
		$time     = ( isset( $timestamp ) ? $timestamp : time() );

		$rseconds = $second * $seconds;
		$rminute  = $minute * $minutes;
		$rhours   = $hour   * $hours;

		if( $mode === 'add' ) {
			$return = $time + $rseconds + $rminute + $rhours;
		}

		$return =  $time - $rseconds - $rminute - $rhours;

		// Tidy up
		unset( $second, $seconds, $minute, $minutes, $hour, $hours, $mode, $time, $rseconds, $rminute, $rhours );

		return $return;
	}

    /**
     * Determine the time between two timestamps
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       int       $t1
     * @param       int       $t2
     * @param       int       $format
     * @param       int       $return
     *
     * @return      int
     */
	public function timer( $t1, $t2=NULL, $format='yfwdhms', $return=0 ){

	    $format  = ( $format === null ? 'yfwdhms' : $format );
		$t2      = ( $t2     === null ? time()    : $t2);
		$sign    = ( $t2     > $t1    ? 1         : -1);
		$s       = abs($t2 - $t1);

		if( $return === 1 ){
			return $s;
		}

		$i            = 0;
		$out          = array();
		$left         = $s;
		$format       = array_unique( str_split( preg_replace( '`[^yfwdhms]`', '', strtolower( $format ) ) ) );
		$format_count = count( $format );
		$a            = array( 'y'=>31556926, 'f'=>2629744, 'w'=>604800, 'd'=>86400, 'h'=>3600, 'm'=>60, 's'=>1 );

    	foreach( $a as $k => $v ) {
            if( in_array( $k, $format ) ) {

        		++$i;
        		if( $i != $format_count ){
        			$out[$k] = $sign * (int)( $left / $v );
        			$left    = $left % $v;

        		} else {
        			$out[$k] = $sign * ( $left / $v );
        		}

    		}else{
                $out[$k]=0;
            }
		}

		if( $return == 2 ) {
			return $out;
		}

		$str='';

		foreach( $out as $k => $v ) {
			if( $v > 0 ) {
				$str .= (int) $v . " " . $k . " ";
			}
		}

		$vals  = array( 'y', 'f', 'w', 'd', 'h', 'm', 's' );
		$words = array( 'YEARS', 'MONTHS', 'WEEKS', 'DAYS', 'HOURS', 'MINUTES', 'SECONDS' );
		$str   = str_replace( $vals, $words, $str );

		// Tidy up
		unset( $t1, $t2, $format, $return, $sign, $s, $i, $out, $left, $format_count, $a, $vals, $words );

		return ucwords( strtolower( $str ) );
	}

    /**
     * Determine how long till next birthday
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       int       $day
     * @param       int       $month
     * @param       int       $year
     * @param       int       $return
     *
     * @return      int
     */
	function calc_birthday($day=1, $month=1, $year=NULL, $return=0){
	    if($year === NULL){
	    	$year = date('y');
	    }

	    $_NOW = time();
        $time = mktime(0,0,0, $month, $day, $year);

	    if( $time < $_NOW ) {
	        $year = $year + 1;
            $time = mktime( 0,0,0, $month, $day, $year );
        }

        $return = $this->timer( $_NOW, $time, NULL, $return );

		// Tidy up
        unset( $day, $month, $year, $_NOW, $time );

        return $return;
    }
}
?>