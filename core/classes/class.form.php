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
* @author   Dan Aldridge
*/
class form extends coreClass{

    /**
     * Starts a new form off
     *
     * @version     1.0
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string $name    Name of the form
     * @param       array  $args    Arguments to pass to the form header
     *
     * @return      string
     */
    public function start($name, $args=array()){
        $args = array(
            'method'        => strtolower(doArgs('method','get',    $args)),
            'action'        => doArgs('action',         null,       $args),
            'onsubmit'      => doArgs('onsubmit',       false,      $args),
            'extra'         => doArgs('extra',          null,       $args),
            'validate'      => doArgs('validate',       true,       $args),

            'autocomplete'  => doArgs('autocomplete',   true,       $args),
        );

        if($this->config('global', 'browser')=='Chrome'){
            $args['autocomplete'] = false;
        }

        return sprintf('<form name="%1$s" id="%1$s"%2$s>',
            $name,
            (
                (!is_empty($args['method'])     ? ' method="'.$args['method'].'"'       : null).
                (!is_empty($args['action'])     ? ' action="'.$args['action'].'"'       : 'action="'.$_SERVER['PHP_SELF'].'" ').
                ($args['onsubmit']              ? ' onsubmit="'.$args['onsubmit'].'"'   : null).
                (!$args['validate']             ? ' novalidate'                         : null).
                (!$args['autocomplete']         ? ' autocomplete="off"'                 : null).
                (!is_empty($args['extra'])      ? $args['extra']                        : null)
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
     * @version     1.2
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
    public function inputbox($name, $type='text', $value='', $args=array()){
        $args = array(
            'id'           => doArgs('id',               $name,  $args),
            'name'         => doArgs('name',             $name,  $args),
            'class'        => doArgs('class',            null,   $args),
            'checked'      => doArgs('checked',          false,  $args),
            'disabled'     => doArgs('disabled',         false,  $args),
            'br'           => doArgs('br',               false,  $args),
            'style'        => doArgs('style',            null,   $args),
            'extra'        => doArgs('extra',            null,   $args),
            'xssFilter'    => doArgs('xssFilter',        true,   $args),

            //HTML5 tag additions
            'required'     => doArgs('required',         false,  $args),
            'placeholder'  => doArgs('placeholder',      null,   $args),
            'autofocus'    => doArgs('autofocus',        false,  $args),
            'min'          => doArgs('min',              0,      $args, 'is_number'),
            'max'          => doArgs('max',              0,      $args, 'is_number'),
            'step'         => doArgs('step',             0,      $args, 'is_number'),

            //CMS addition - will set the field to auto complete usernames
            'autocomplete' => doArgs('autocomplete',     true,  $args),
        );

        $typeVali = array( 'button', 'checkbox', 'file', 'hidden', 'image', 'password', 'radio', 'reset', 'submit', 'text',
                            //html5 specials
                            'email', 'url', 'number', 'range', 'search', 'datetime-local', 'datetime', 'date', 'time', 'week', 'month', 'tel' );


        $return = null; $inputVal = '<input type="%1$s" name="%2$s" id="%3$s"%4$s/>'."\n";
        return  sprintf($inputVal,
                    (in_array($type, $typeVali) ? $type : 'text'),
                    $name, $args['id'],
                    (
                        (!is_empty($args['style'])          ? ' style="'.$args['style'].'"'             : null).

                        ($args['checked']===true            ? ' checked="checked"'                      : null).
                        ($args['disabled']===true           ? ' disabled="disabled"'                    : null).
                        ($args['autocomplete']===false      ? ' autocomplete="off"'                     : null).

                        (!is_empty($args['placeholder'])    ? ' placeholder="'.$args['placeholder'].'"' : null).
                        (!is_empty($args['autofocus'])      ? ' autofocus="'.$args['autofocus'].'"'     : null).

                        (!is_empty($args['min'])            ? 'min="'.$args['min'].'" '                 : null).
                        (!is_empty($args['max'])            ? 'max="'.$args['max'].'" '                 : null).
                        (!is_empty($args['step'])           ? 'step="'.$args['step'].'" '               : null).

                        ($args['required']===true           ? ' required="required"'                    : null).
                        (!is_empty($args['extra'])          ? $args['extra']                            : null).
                        ($args['xssFilter']===true          ? ' value="'.htmlspecialchars($value).'" '   : ' value="'.$value.'" ')
                    )
                ).
                ($args['br'] ? '<br />'."\n" : null);
    }

    /**
     * Output a textarea input box
     *
     * @version     1.2
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
        $args = array(
             'cols'        => doArgs('cols',              45,     $args),
             'rows'        => doArgs('rows',              5,      $args),

             'id'          => doArgs('id',                $name,  $args),
             'class'       => doArgs('class',             null,   $args),
             'disabled'    => doArgs('disabled',          false,  $args),
             'br'          => doArgs('br',                false,  $args),
             'style'       => doArgs('style',             null,   $args),
             'extra'       => doArgs('extra',             null,   $args),
             'xssFilter'   => doArgs('xssFilter',         true,   $args),
             'placeholder' => doArgs('placeholder',       null,   $args),

             'resize'      => doArgs('resize',            true,   $args),
             'allowTab'    => doArgs('allowTab',          true,   $args),
        );

        return sprintf('<textarea name="%2$s" id="%3$s" %4$s>%1$s</textarea>',
            ($args['xssFilter']===true ? htmlspecialchars($value) : $value),
            $name,
            $args['id'],
                (
                    (!is_empty($args['class'])          ? ' class="'.$args['class'].'"'             : null).
                    (is_number($args['cols'])           ? ' cols="'.$args['cols'].'"'               : null).
                    (is_number($args['rows'])           ? ' rows="'.$args['rows'].'"'               : null).
                    (!is_empty($args['placeholder'])    ? ' placeholder="'.$args['placeholder'].'"' : null).
                    (!is_empty($args['style'])          ? ' style="'.$args['style'].'"'             : null).
                    (!is_empty($args['extra'])          ? $args['extra']                            : null).
                    ($args['disabled']===true           ? ' disabled="disabled"'                    : null)
                )
        ). ($args['br']===true ? '<br />'."\n" : '');
    }

    /**
     * Output a submit or reset button
     *
     * @version     1.2
     * @since       1.0.0
     * @author      Dan Aldridge
     *
     * @param       string  $name
     * @param       string  $value
     * @param       array   $args
     *
     * @return      string
     */
    public function button($name=null, $value, $args=array()){
        $args['name']  = doArgs('name', $name, $args);
        $args['class'] = (!isset($args['class']) ? 'button' : $args['class'].' button');
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
        $args = array(
            'id'         => doArgs('id', $name, $args),
            'class'      => doArgs('class', null, $args),
            'style'      => doArgs('style', null, $args),
            'disabled'   => doArgs('disabled', false, $args),
            'br'         => doArgs('br', false, $args),
            'xssFilter'  => doArgs('xssFilter', true, $args),
            'showLabels' => doArgs('showLabels', true, $args),

            'showValue'  => doArgs('showValue', true, $args),
        );

        $return = null; $inputVal = '<input type="radio" name="%1$s" id="%2$s"%3$s/>'."\n"; $count = 0;
        foreach($values as $key => $value){
            $value = ($args['xssFilter']===true ? htmlspecialchars($value) : $value);

            $return .=  ($args['showLabels']===true ? '<label for="'.$args['id'].'">' : null).
                            sprintf($inputVal,
                                $name, $args['id'],
                                (
                                    (!is_empty($args['style'])      ? ' style="'.$args['style'].'"'         : null).
                                    ($defaultSetting==$key          ? ' checked="checked"'                  : null).
                                    ($args['xssFilter']===true      ? ' value="'.htmlspecialchars($key).'" ' : ' value="'.$key.'" ')
                                )
                            ). $value.
                        ($args['showLabels']===true ? '</label>' : mull).
                        (!$args['br'] ?: '<br />'."\n");
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
     * @param       string     $name
     * @param       string     $value
     * @param       bool     $checked
     * @param       array     $args
     *
     * @return      string
     */
    public function checkbox($name='check', $value='', $checked=false, $args=array()){
        $args['checked'] = $checked;

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
        $args = array(
            'id'        => doArgs('id',         $name,  $args),
            'selected'  => doArgs('selected',   null,   $args),
            'noKeys'    => doArgs('noKeys',     false,  $args),
            'multi'     => doArgs('multi',      false,  $args),
            'search'    => doArgs('search',     false,  $args),

            'class'     => doArgs('class',      null,   $args),
            'disabled'  => doArgs('disabled',   false,  $args),
            'style'     => doArgs('style',      null,   $args),
            'extra'     => doArgs('extra',      null,   $args),
            'opt_extra' => doArgs('opt_extra',  null,   $args),
            'xssFilter' => doArgs('xssFilter',  true,   $args),
            'fancy'     => doArgs('fancy',      true,   $args),
        );

        //added support for multiple selections
        if($args['multi']===true){
            $name = $name.'[]';
            $args['extra'] .= ' multiple="multiple"';
        }

        if(defined('CSCMS')){
            //add support for Chosen
            $args['extra'] .= ' data-search="'.($args['search']===true ? 'true' : 'false').'"';

            if($args['fancy']){
                $args['class'] .= 'chzn-select';
            }
        }

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
                                    doArgs('opt_extra', null, $args)
                                );
                    }
                }else{
                    $val .= sprintf($option,
                        (md5($k)==md5($selected) ? ' selected' : null),
                        ($noKeys===true ? $k : $v),
                        doArgs('opt_extra', null, $args)
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
            }else{
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
            }else{
                $val .= '<option value="'.$k.'"'.(md5($k)==md5($selected) ? ' selected="true" ' : null).doArgs('opt_extra', null, $args).'>'.
                            ($noKeys===true ? $v : $k).'</option>'."\n";
            }
        }
        return $val;
    }


    public function outputForm($vars, $elements, $options=array()){
        //echo dump($elements);
        //make sure we have something to use before continuing
        if(is_empty($elements)){ $this->setError('Nothing to output'); return false; }

        if(!isset($elements['field']) || is_empty($elements['field'])){
            $this->setError('Fields are blank or undetectable, make sure they are set using \'field\' key.');
            return false;
        }

        //init the template, give it a rand id to stop it clashing with anything else
        $randID = inBetween('name="', '"', $vars['FORM_START']);
        $this->objTPL->set_filenames(array(
            'form_body_'.$randID => 'modules/core/template/outputForm.tpl',
        ));

        if(!doArgs('border', true, $options)){
            $vars['EXTRA'] = ' class="noBorder"';
        }

        if(doArgs('id', false, $options)){
            $vars['SECTION_ID'] = doArgs('id', null, $options);
        }

        $dediHeader = doArgs('dedicatedHeader', false, $options);
        $this->objTPL->assign_vars($vars);

        $this->objTPL->reset_block_vars('form_error');
        if(isset($elements['errors']) && !is_empty($elements['errors'])){
            $this->objTPL->assign_block_vars('form_error', array(
                'ERROR_MSG' => implode('<br />', $elements['errors']),
            ));
        }

        $count = 0;
        $this->objTPL->reset_block_vars('field');
        //loop thru each element
        foreach($elements['field'] as $label => $field){
            if(is_empty($field)){ continue; }

            $formVars = array();

            //grab the description before we play with the $label
            $desc = $elements['desc'][$label];

            //upper care the words
            $label = ucwords($label);

            //if its a header, set it as one with a hr under
            if($field == '_header_'){
                $label = sprintf(doArgs('header', '%s', $options), $label);
            }

            $header = ($field == '_header_' ? true : false);
            $this->objTPL->assign_block_vars('_form_row', array());
            if($dediHeader && $header){
                $this->objTPL->assign_block_vars('_form_row._header', array(
                    'L_LABEL' => $label,
                ));
            }else{
                //assign some vars to the template
                $this->objTPL->assign_block_vars('_form_row._field', array(
                    'F_ELEMENT'  => $header ? null : $field,
                    'F_INFO'     => (doArgs('parseDesc', false, $options) ? contentParse($desc) : $desc),
                    'CLASS'      => $header ? ' title' : ($count++%2 ? ' row_color2' : ' row_color1'),

                    'L_LABEL'    => $label,
                    'L_LABELFOR' => inBetween('name="', '"', $field),
                ));

                //if this isnt a 'header' then output the label
                if(!$header){
                    $this->objTPL->assign_block_vars('_form_row._field._label', array());
                }

                //if we have a description, lets output it with the label
                if(!is_empty($desc)){
                    $this->objTPL->assign_block_vars('_form_row._field._desc', array());
                }
            }
        }

        //return the html all nicely parsed etc
        return $this->objTPL->get_html('form_body_'.$randID);
    }

    function loadCaptcha($var){
        return $this->objPlugins->hook('CMSForm_Captcha', $var);
    }
}
?>