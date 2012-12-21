<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Unit extends coreObj{

    protected $_reportData = array();
    protected $_backtrace = array();


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
    public function &test( $testItem, $expectedResult, $testName = '', $notes = '' ){

        // Allowed results
        $allowedResults = array(
            'is_object',
            'is_string',
            'is_bool',
            'is_int',
            'is_numeric',
            'is_float',
            'is_double',
            'is_array',
            'is_null',
        );

        if(function_exists('is_true')) {
            $allowedResults[] = 'is_true';
        }

        if(function_exists('is_false')) {
            $allowedResults[] = 'is_false';
        }

        $result = false;
        $key    = array_search( $expectedResult, $allowedResults);

        if( $key !== false ){

            $expectedResult = str_replace('is_float', 'is_double', $expectedResult);
            $test_method    = $allowedResults[$key];
            $result         = $test_method($testItem);

            // Get the backtrace
            if( !count( $this->_backtrace ) ){
                $this->_backtrace = debug_backtrace();
            }

            $report = array(
                'test_name'       => $testName,
                'test_datatype'   => gettype($testItem),
                'result_datatype' => $test_method,
                'result'          => ($result === true ? 'Passed.' : 'Failed.'),
                'file'            => $this->_backtrace[0]['file'],
                'line'            => $this->_backtrace[0]['line'],
                'notes'           => $notes,
                'raw_result'      => $result,
            );

            // Get the backtrace
            $this->_reportData[] = $report;
        }
        $this->_backtrace = array();
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

    /**
     * Checks to see if the given variable is explicitly true
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $testItem
     *
     * @return  obj     $this
     */
    public function &assertTrue( $testItem ){
        $this->_backtrace = debug_backtrace();
        return $this->test($testItem, 'is_true');
    }

    /**
     * Checks to see if the given variable is explicitly false
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Richard Clifford
     *
     * @param   mixed   $testItem
     *
     * @return  obj     $this
     */
    public function &assertFalse( $testItem ){
        $this->_backtrace = debug_backtrace();
        return $this->test($testItem, 'is_false');
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
                                <th>Result Test</th>
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