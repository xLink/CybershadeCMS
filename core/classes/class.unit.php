<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Unit extends coreObj{

    protected $_reportData = array();

    public function __construct(){
        $this->setVar('strictMode', false);
    }


    /**
     * Runs the unit tests
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $testItem
     * @param   string  $expectedResult
     * @param   string  $testName
     * @param   string  $notes
     *
     * @return  obj     $this
     */
    public function test( $testItem, $expectedResult, $testName = '', $notes = '' ){

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
            'is_null',
            'is_true',
        );

        // are we using strict mode?
        $strict = $this->getVar('strictMode');

        $result = false;

        // Start the checking!
        if( in_array( $expectedResult, $allowedResults, true ) ){

            $expectedResult = str_replace('is_float', 'is_double', $expectedResult);
            if( $expectedResult == 'is_true' ){
                $result = ( $testItem === true ? true : false );
            } elseif( $expectedResult == 'is_false' ){
                $result = ( $testItem === false ? true : false );
            } else {
                $result = ($expectedResult($testItem) ? true : false);
            }


            // Get the result type
            $resultType     = str_replace(array('true', 'false'), 'bool', $expectedResult);

        } else {
            // Explicitly check the types
            if($strict){
                $result = ( ( $testItem === $expectedResult ) ? true : false );
            } else {
                $result = ( ( $testItem == $expectedResult ) ? true : false );
            }

            // Get the result type
            $resultType = gettype( $expectedResult );
        }


        $report = array(
            'test_name' => ( is_null($testItem) ? 'NULL' : $testItem ),
            'test_datatype' => gettype($testItem),
            'result_datatype' => $resultType,
            'result' => ($result === true ? 'Passed.' : 'Failed.'),
            'file' => __FILE__,
            'line' => __LINE__,
            'notes' => $notes,
            'raw_result' => $result,
        );

        $this->_reportData[] = $report;

        return $this;
    }


    /**
     * Sets the use mode to STRICT to explicitly check the data types
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   bool    $state
     *
     * @return  obj     $this
     */
    public function useStrict($state = true){
        $this->setVar('strictMode', ($state ? true : false));
        return $this;
    }

    /**
     * Runs the tests and generates a report
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @return  string  HTML Table string
     */
    public function run(){
        return $this->_generateReport( $this->_reportData );
    }

    public function assertTrue( $var, $strict = true ){
        if( $strict ){
            $this->useStrict();
        }
        return $this->test($var, 'is_true')->run();
    }


    /**
     * Generates the HTML Table Report
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @access  protected
     *
     * @return  string  HTML Table string
     */
    protected function _generateReport( $reportData = array() ){

        $_reportData = $this->getVar('_reportData');

        if( !is_empty($_reportData) ){
            $reportData = $_reportData;
        }

        if( is_empty( $reportData ) ){
            return false;
        }

        /**
         //
         // -- Put into a new template rather than from this class
         //
         */
        $output = '<table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>Test Name</th>
                                <th>Expected Result</th>
                                <th>Actual Result Type</th>
                                <th>Result</th>
                                <th>File</th>
                                <th>Line Number</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>';

        foreach( $reportData as $key => $value ){
            $output .= sprintf('
                        <tr class="%s">
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                            <td>%s</td>
                        </tr>',
                    !$value['raw_result'] ? 'error' : 'success',
                    $value['test_name'],
                    $value['test_datatype'],
                    $value['result_datatype'],
                    $value['result'],
                    $value['file'],
                    $value['line'],
                    $value['notes']
                    );
        }

        $output .=  '</tbody>
                    </table>';

        return $output;
    }
}

?>