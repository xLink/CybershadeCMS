<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

/**
* Class to create and maintain forms
*
* @version  1.2
* @since    1.0.0
* @author   Dan Aldridge, Kev Bowler
*/
class Core_Classes_Form extends Core_Classes_coreObj {

    /**
     * Starts a new form off
     *
     * @version     1.1
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $name    Name of the form
     * @param       array  $args    Arguments to pass to the form header
     *
     * @return      string
     */
    public function start($name, $args = array()){
        $args['method']        = strtolower(doArgs('method','get',    $args));
        $args['action']        = doArgs('action',         null,       $args);
        $args['onsubmit']      = doArgs('onsubmit',       false,      $args);
        $args['extra']         = doArgs('extra',          null,       $args);
        $args['validate']      = doArgs('validate',       true,       $args);
        $args['style']         = doArgs('style',          null,       $args);
        $args['class']         = doArgs('class',          null,       $args);
        $args['autocomplete']  = doArgs('autocomplete',   true,       $args);
        $args['upload']        = doArgs('upload',         null,       $args);

        if( $this->config('global', 'browser') == 'Chrome' ){
            $args['autocomplete'] = false;
        }

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_START_ARGS', $params);

        return sprintf('<form name="%1$s" id="%1$s"%2$s>',
            $name,
            (
                (!is_empty($args['method'])     ? ' method="'.$args['method'].'"'       : null).
                (!is_empty($args['action'])     ? ' action="'.$args['action'].'"'       : ' action="'.htmlentities($_SERVER['PHP_SELF']).'"').
                ($args['onsubmit']              ? ' onsubmit="'.$args['onsubmit'].'"'   : null).
                (!$args['validate']             ? ' novalidate'                         : null).
                (!$args['autocomplete']         ? ' autocomplete="off"'                 : null).
                (!is_empty($args['style'])      ? ' style="'.$args['style'].'"'         : null).
                (!is_empty($args['class'])      ? ' class="'.$args['class'].'"'         : null).
                (!is_empty($args['upload'])     ? ' enctype="multipart/form-data"'      : null).
                (!is_empty($args['extra'])      ? ' '.$args['extra']                    : null)
            )
        )."\n";
    }

    /**
     * Finishes the form - mebe useful for something else in the future
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @return      string
     */
    public function finish(){
        return '</form>';
    }

    /**
     * Mould for the input tag, this supports a fair amount of tags
     *
     * @version     1.3
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $name
     * @param       string  $type
     * @param       string  $value
     * @param       array   $args
     *
     * @return      string
     */
    public function inputBox($name, $type='text', $value='', $args=array()){
        $args['id']             = doArgs('id',               $name,  $args);
        $args['name']           = doArgs('name',             $name,  $args);
        $args['class']          = doArgs('class',            null,   $args);
        $args['checked']        = doArgs('checked',          false,  $args);
        $args['disabled']       = doArgs('disabled',         false,  $args);
        $args['br']             = doArgs('br',               false,  $args);
        $args['style']          = doArgs('style',            null,   $args);
        $args['extra']          = doArgs('extra',            null,   $args);
        $args['xssFilter']      = doArgs('xssFilter',        true,   $args);
        $args['prepend']        = doArgs('prepend',          false,  $args);
        $args['append']         = doArgs('append',           false,  $args);

        $args['required']       = doArgs('required',         false,  $args);
        $args['placeholder']    = doArgs('placeholder',      null,   $args);
        $args['autofocus']      = doArgs('autofocus',        false,  $args);
        $args['min']            = doArgs('min',              0,      $args, 'is_number');
        $args['max']            = doArgs('max',              0,      $args, 'is_number');
        $args['step']           = doArgs('step',             0,      $args, 'is_number');

        $args['autocomplete']   = doArgs('autocomplete',     true,   $args);

        $typeVali = array( 'button', 'checkbox', 'file', 'hidden', 'image', 'password', 'radio', 'reset', 'submit', 'text',
                            //html5 specials
                            'email', 'url', 'number', 'range', 'search', 'datetime-local', 'datetime', 'date', 'time', 'week', 'month', 'tel' );


        $return = null;
        $inputVal = '<input type="%1$s" name="%2$s" id="%3$s"%4$s/>'."\n";

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_INPUTBOX_ARGS', $params);

        return  sprintf($inputVal,
                    (in_array($type, $typeVali) ? $type : 'text'),
                    $name, $args['id'],
                    (
                        (!is_empty($args['style'])          ? ' style="'.$args['style'].'"'             : null).
                        (!is_empty($args['class'])          ? ' class="'.$args['class'].'"'             : null).

                        ($args['checked']===true            ? ' checked="checked"'                      : null).
                        ($args['disabled']===true           ? ' disabled="disabled"'                    : null).
                        ($args['autocomplete']===false      ? ' autocomplete="off"'                     : null).

                        (!is_empty($args['prepend'])        ? ' data-prepend="'.$args['prepend'].'"'    : null).
                        (!is_empty($args['append'])         ? ' data-append="'.$args['append'].'"'      : null).

                        (!is_empty($args['placeholder'])    ? ' placeholder="'.$args['placeholder'].'"' : null).
                        (!is_empty($args['autofocus'])      ? ' autofocus="'.$args['autofocus'].'"'     : null).

                        (!is_empty($args['min'])            ? ' min="'.$args['min'].'"'                 : null).
                        (!is_empty($args['max'])            ? ' max="'.$args['max'].'"'                 : null).
                        (!is_empty($args['step'])           ? ' step="'.$args['step'].'"'               : null).

                        ($args['required']===true           ? ' required="required"'                    : null).
                        (!is_empty($args['extra'])          ? ' '.$args['extra']                        : null).
                        ($args['xssFilter']===true          ? ' value="'.htmlspecialchars($value).'" '  : ' value="'.$value.'"')
                    )
                ).
                ($args['br']===true ? '<br />'."\n" : '');
    }

    /**
     * Output a textarea input box
     *
     * @version     1.3
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $name
     * @param       string  $value
     * @param       array   $args
     *
     * @return      string
     */
    public function textarea($name='textarea', $value=null, $args=array()){
        $args['cols']        = doArgs('cols',              45,     $args);
        $args['rows']        = doArgs('rows',              5,      $args);

        $args['id']          = doArgs('id',                $name,  $args);
        $args['class']       = doArgs('class',             null,   $args);
        $args['disabled']    = doArgs('disabled',          false,  $args);
        $args['br']          = doArgs('br',                false,  $args);
        $args['style']       = doArgs('style',             null,   $args);
        $args['extra']       = doArgs('extra',             null,   $args);
        $args['xssFilter']   = doArgs('xssFilter',         true,   $args);
        $args['placeholder'] = doArgs('placeholder',       null,   $args);

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_TEXTAREA_ARGS', $params);

        $extra = (
            (!is_empty($args['class'])          ? ' class="'.$args['class'].'"'             : null) .
            (is_number($args['cols'])           ? ' cols="'.$args['cols'].'"'               : null) .
            (is_number($args['rows'])           ? ' rows="'.$args['rows'].'"'               : null) .
            (!is_empty($args['placeholder'])    ? ' placeholder="'.$args['placeholder'].'"' : null) .
            (!is_empty($args['style'])          ? ' style="'.$args['style'].'"'             : null) .
            (!is_empty($args['extra'])          ? ' '.$args['extra']                        : null) .
            ($args['disabled']===true           ? ' disabled="disabled"'                    : null)
        );

        $pluginExtras = Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_TEXTAREA_EXTRAS');
            if( is_array($pluginExtras) && !is_empty($pluginExtras) ){
                foreach($pluginExtras as $e){
                    $extra .= ' '.$e;
                }
            }

        return sprintf('<textarea name="%2$s" id="%3$s" %4$s>%1$s</textarea>',
            ($args['xssFilter']===true ? htmlspecialchars($value) : $value),
            $name,
            $args['id'],
            $extra
        ) . ( $args['br'] === true ? '<br />' . "\n" : '');
    }

    /**
     * Output a submit or reset button
     *
     * @version     1.3
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $name
     * @param       string  $value
     * @param       array   $args
     *
     * @return      string
     */
    public function button($name=null, $value='submit', $args=array()){
        $args['name']  = doArgs('name', $name, $args);
        $args['class'] = (!isset($args['class']) ? 'btn' : 'btn ' . $args['class']);
        $type          = doArgs('type', 'button', $args);

        if(in_array(strtolower($name), array('submit', 'reset'))){
            $type = strtolower($name);
        }

        return self::inputbox($args['name'], $type, $value, $args);
    }

    /**
     * New Radio Button
     *
     * @version     1.2
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $name
     * @param       array   $values
     * @param       string  $defaultSetting
     * @param       array   $args
     *
     * @return      string
     */
    public function radio($name='radio', $values=array(), $defaultSetting=null, $args=array()){
        $args['id']         = doArgs('id',            $name,  $args);
        $args['class']      = doArgs('class',         null,   $args);
        $args['style']      = doArgs('style',         null,   $args);
        $args['disabled']   = doArgs('disabled',      false,  $args);
        $args['br']         = doArgs('br',            false,  $args);
        $args['xssFilter']  = doArgs('xssFilter',     true,   $args);
        $args['showLabels'] = doArgs('showLabels',    true,   $args);
        $args['showValue']  = doArgs('showValue',     true,   $args);

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_RADIO_ARGS', $params);

        $return   = null;
        $inputVal = '<input type="radio" name="%1$s" id="%2$s"%3$s/>'."\n";
        $count    = 0;

        if( !is_array($values) ){
            $values = array( $values => '' );
        }

        foreach($values as $key => $value){
            $value = ($args['xssFilter']===true ? htmlspecialchars($value) : $value);

            $return .=  ($args['showLabels']===true ? '<label for="'.$args['id'].'_'.(++$counter).'">' : null).
                            sprintf($inputVal,
                                $name, $args['id'].'_'.$counter,
                                (
                                    (!is_empty($args['style'])      ? ' style="'.$args['style'].'"'             : null).
                                    ($defaultSetting==$key          ? ' checked="checked"'                      : null).
                                    ($args['xssFilter']===true      ? ' value="'.htmlspecialchars($key).'"'    : ' value="'.$key.'"')
                                )
                            ). $value.
                        ($args['showLabels']===true ? '</label>' : '').
                        ($args['br']===true ? '<br />'."\n" : '');
        }


        return $return;
    }

    /**
     * Create a new checkbox
     *
     * @version     1.1
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string      $name
     * @param       string      $value
     * @param       bool        $checked
     * @param       array       $args
     *
     * @return      string
     */
    public function checkbox($name='check', $value='', $checked=false, $args=array()){
        $args['checked'] = filter_var($checked, FILTER_VALIDATE_BOOLEAN);

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_CHECKBOX_ARGS', $params);

        return self::inputbox($name, 'checkbox', $value, $args);
    }

    /**
     * Select box tag - convert any array to a select box...i think :D
     *
     * @version     1.2
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string    $name
     * @param       array     $options
     * @param       array     $args
     *
     * @return      string
     */
    public function select($name, $options, $args=array()){
        $args['id']        = doArgs('id',         $name,  $args);
        $args['selected']  = doArgs('selected',   null,   $args);
        $args['noKeys']    = doArgs('noKeys',     false,  $args);
        $args['multi']     = doArgs('multi',      false,  $args);
        $args['search']    = doArgs('search',     false,  $args);

        $args['class']     = doArgs('class',      null,   $args);
        $args['disabled']  = doArgs('disabled',   false,  $args);
        $args['style']     = doArgs('style',      null,   $args);
        $args['extra']     = doArgs('extra',      null,   $args);
        $args['opt_extra'] = doArgs('opt_extra',  null,   $args);
        $args['xssFilter'] = doArgs('xssFilter',  true,   $args);
        $args['fancy']     = doArgs('fancy',      true,   $args);

        //added support for multiple selections
        if($args['multi'] === true){
            $name = $name.'[]';
            $args['extra'] .= ' multiple="multiple"';
        }

        // setup a hook for the args
        $params = array( &$args );
        Core_Classes_coreObj::getPlugins()->hook('CMS_FORM_SELECT_ARGS', $params);

        $extra    = $args['extra'];
        $selected = $args['selected'];
        $noKeys   = $args['noKeys'];

        $option = '<option value="%1$s"%2$s%4$s>%3$s</option>'."\n";
        $val = sprintf('<select name="%1$s" id="%2$s"%3$s%4$s%5$s%6$s>',
            $name,
            $args['id'],
            (!is_empty($args['class'])  ? ' class="'.$args['class'].'"' : null),
            ($args['disabled']===true   ? ' disabled="disabled"'        : null),
            (!is_empty($args['extra'])  ? ' '.$args['extra']            : null),
            (!is_empty($args['style'])  ? ' style="'.$args['style'].'"' : null)
        )."\n";

        //if we are playing with noKeys
        if($noKeys){
            $option = '<option%1$s%3$s>%2$s</option>'."\n";
            //loop thru each of the values
            foreach($options as $k){
                //if its an array, throw an group in there, and process the sub array
                if(is_array($k)){
                    $val .= sprintf('<optgroup label="%s">'."\n", $k);
                    foreach($k as $a){
                        $val .= sprintf($option,
                            (md5($a)==md5($selected) ? ' selected' : null),
                            $a,
                            $args['opt_extra']
                        );
                    }
                } else {
                    $val .= sprintf($option,
                        (md5($k)==md5($selected) ? ' selected' : null),
                        ($noKeys===true ? $k : $v),
                        $args['opt_extra']
                    );

                }
            }
            $val .= '</select>'."\n";
            return $val;
        }

        //else carry on as normal
        foreach($options as $k => $v){
            if(is_array($v)){
                $val .= sprintf('<optgroup label="%s">'."\n", $k);
                foreach($v as $a => $b){
                    if(is_array($b)){
                        $val .= self::processSelect($b, $args);
                    }else{
                        $val .= sprintf($option,
                            $a,
                            (md5($a)==md5($selected) ? ' selected="true"' : null),
                            $b,
                            doArgs('opt_extra', null, $args)
                        );
                    }
                }
            } else {
                $val .= sprintf($option,
                    $k,
                    (md5($k)==md5($selected) ? ' selected="true"' : null),
                    $v,
                    doArgs('opt_extra', null, $args)
                );
            }
        }
        $val .= '</select>'."\n";
        return $val;
    }

    /**
     * Private recursion for select tag.
     *
     * @version     1.1
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       array     $options
     * @param       array     $args
     *
     * @return      string
     */
    private function processSelect($options, $args=array()){
        $selected = doArgs('selected', false, $args);
        $noKeys   = doArgs('noKeys', false, $args);

        foreach ($options as $k => $v){
            if(is_array($v)){
                foreach($v as $a => $b){
                    if(is_array($b)){
                        $val .= self::processSelect($b, $args);
                    }else{
                        $val .= '<option value="'.$a.'"'.(md5($a)==md5($selected) ? ' selected="true" ' : null).doArgs('opt_extra', null, $args).'>'.
                                    ($noKeys===true ? $b : $a).'</option>'."\n";
                    }
                }
            } else {
                $val .= '<option value="'.$k.'"'.(md5($k)==md5($selected) ? ' selected="true" ' : null).doArgs('opt_extra', null, $args).'>'.
                            ($noKeys===true ? $v : $k).'</option>'."\n";
            }
        }
        return $val;
    }


    public function outputForm($vars, $elements, $options=array()){
        //make sure we have something to use before continuing
        if(is_empty($elements)){ $this->setError('Nothing to output'); return false; }

        if(!isset($elements['field']) || is_empty($elements['field'])){
            $this->setError('Fields are blank or undetectable, make sure they are set using \'field\' key.');
            return false;
        }

        //init the template, give it a rand id to stop it clashing with anything else
        $randID = inBetween('name="', '"', $vars['FORM_START']);

        $objTPL = Core_Classes_coreObj::getTPL();
        $objTPL->set_filenames(array(
            'form_body_'.$randID => 'modules/core/views/outputForm.tpl',
        ));

        if(!doArgs('border', true, $options)){
            $vars['EXTRA'] = ' class="noBorder"';
        }

        if(doArgs('id', false, $options)){
            $vars['SECTION_ID'] = doArgs('id', null, $options);
        }

        $dediHeader = doArgs('dedicatedHeader', false, $options);
        $objTPL->assign_vars($vars);

        $objTPL->reset_block_vars('form_error');
        if( isset($elements['errors']) && !is_empty($elements['errors']) ){
            $objTPL->assign_block_vars('form_error', array(
                'ERROR_MSG' => implode('<br />', $elements['errors']),
            ));
        }

        if( isset($vars['FORM_INFO']) && !empty($vars['FORM_INFO']) ){
            $objTPL->assign_block_vars('form_info', array(
                'INFO_MSG' => $vars['FORM_INFO'],
            ));
        }

        $count = 0;
        $objTPL->reset_block_vars('field');

        //loop thru each element
        foreach( $elements['field'] as $label => $field ){
            if( is_empty($field) ){ continue; }

            $formVars = array();

            //grab the description before we play with the $label
            $desc = $elements['desc'][$label];

            //upper care the words
            $label = ucwords($label);

            //if its a header, set it as one with a hr under
            if( strtolower($field) == '_header_' ){
                $label = sprintf( doArgs('header', '%s', $options), $label );
            }

            $header = ( strtolower($field) == '_header_' ? true : false );
            $objTPL->assign_block_vars('_form_row', array());
            if($dediHeader && $header){
                $objTPL->assign_block_vars('_form_row._header', array(
                    'L_LABEL' => $label,
                ));
            } else {
                // assign some vars to the template
                $objTPL->assign_block_vars('_form_row._field', array(
                    'F_ELEMENT'  => $header ? null : $field,
                    'F_INFO'     => (doArgs('parseDesc', false, $options) ? contentParse($desc) : $desc),
                    'CLASS'      => $header ? ' title' : ($count++%2 ? ' row_color2' : ' row_color1'),
                    'L_LABEL'    => $label,
                    'L_LABELFOR' => inBetween('name="', '"', $field),
                ));

                // if this isnt a 'header' then output the label
                if( $header === false ){
                    $objTPL->assign_block_vars('_form_row._field._label', array());
                }

                // if we have a description, lets output it with the label
                if( is_empty($desc) === false ){
                    $objTPL->assign_block_vars('_form_row._field._desc', array());
                }

                // see if we need to prepend or append anything to the field
                $pre = inBetween('data-prepend="', '"', $field);
                $app = inBetween('data-append="', '"', $field);

                if( !is_empty($pre) ){
                    $objTPL->assign_block_vars('_form_row._field._prepend', array('ADDON' => $pre));
                } else if( !is_empty($app) ){
                    $objTPL->assign_block_vars('_form_row._field._append', array('ADDON' => $app));
                }else{
                    $objTPL->assign_block_vars('_form_row._field._normal', array());

                }
            }
        }

        unset($_SESSION['errors']);

        //return the html all nicely parsed etc
        return $objTPL->get_html('form_body_'.$randID);
    }

    function loadCaptcha($var){
        $objPlugins = Core_Classes_coreObj::getPlugins();
        return $objPlugins->hook('CMSForm_Captcha', $var);
    }
}
?>
