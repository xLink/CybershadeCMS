<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Unit extends coreObj{

    public function __construct(){

    }


    /**
     * Runs the unit tests and generates a report
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $test
     * @param   string  $expectedResult
     * @param   string  $testName
     * @param   string  $notes
     *
     * @return  bool    Generates a report using the generateReport() function which returns a bool
     */
    public function run( $test, $expectedResult, $testName = '', $notes = '' ){
        if(is_empty( $test ) || is_empty( $epxectedResult )){
            return false;
        }

        // Allowed results
        $allowedResults = array(
            'is_object',
            'is_string',
            'is_bool',
            'is_true',
            'is_false',
            'is_int',
            'is_numeric',
            'is_float',
            'is_double',
            'is_array',
            'is_null'
        );

        // are we using strict mode?
        $strict = $this->getVar('strictMode');

        /**
        //
        //-- Todo: loop through the setTestItems array and append $test to that array and perform the tests on them
        //
        */
        // Start the checking!
        if( in_array( $expectedResult, $allowedResults, true ) ){
            $expectedResult = str_replace('is_float', 'is_double', $expectedResult);
            $result         = ($expectedResult($testItem) ? true : false);
            $resultType     = str_replace(array('true', 'false'), 'bool', str_replace('is_', '', $expectedResult) );
        } else {
            if($strict){
                $result = ( ( $testItem === $expectedResult ) ? true : false );
            } else {
                $result = ( ( $testItem == $expectedResult ) ? true : false );
            }
        }

        $report = array(
            'test_name' => $test,
            'test_datatype' => gettype($test),
            'result_datatype' => $resultType,
            'result' => ($result === true ? 'Passed.', 'Failed.'),
            'file' => __FILE__,
            'line' => __LINE__,
            'notes' => $notes,
        );

        $generateReport = $this->_generateReport($report);

        return $generateReport;
    }

    public function setTestItms( $testItems = array() ){
        if( !is_empty($testItems) ){
            $this->setVar('testItems', $testItems);
        }

        return $this;
    }

    public function useStrict(){
        $this->setVar('strictMode', true);
        return $this;
    }

    protected function _generateReport( $reportData ){
        return true;
    }
}