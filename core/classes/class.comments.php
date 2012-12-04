<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

class Comments extends coreObj {

    /**
     * Set the comments up, this will handle the post form also.
     *
     * @version     1.0
     * @since       0.8.0
     */
    public function start($tplVar=NULL, $paginationVar, $module, $module_id, $perPage=10, $author_id=0){
        $this->setVar('paginationVar',  $paginationVar);
        $this->setVar('module',         $module);
        $this->setVar('module_id',      $module_id);
        $this->setVar('perPage',        $perPage);
        $this->setVar('author_id',      $author_id);

        // TODO: check this out...not sure if it will work ...properly with the new getQueryString
        $this->setVar('aURL', explode('?', $this->config('global', 'fullPath')));

        parse_str($this->aURL[1], $vars);
        $this->fURL = $this->getQueryString($this->aURL[0], $vars, array('mode', 'id'));

        if($tplVar !== NULL){
            $this->getComments($tplVar);
        }
    }

    /**
     * Inserts a comment into the database
     *
     * @version     2.0
     * @since       1.0.0
     * @author      Richard Clifford, Dan Aldridge
     *
     * @param       string  $module         The module name
     * @param       int     $module_id      The Unique ID of the content
     * @param       int     $author         The comment author's UID
     * @param       string  $comment        The comment's content
     *
     * @return      bool
     */
    function insertComment($module, $module_id, $author, $comment){

        // Instanciate the Objects
        $objUser = coreObj::getUser();
        $objSQL  = coreObj::getDBO();

        unset($array);
        $array['module']        = $module;
        $array['module_id']     = $module_id;
        $array['author']        = $author;
        $array['comment']       = secureMe($comment);
        $array['timestamp']     = time();


        $insertQuery = $objSQL->queryBuilder()
                        ->insertInto('#__comments')
                        ->set($array)
                        ->build();

        $insertResult = $objSQL->query( $insertQuery );


        // TODO: log the comments
        $log = 'Comments System: '.$objUser->profile($objUser->grab('id'), RAW).' commented on <a href="'.$this->aURL[1].'">this</a>.';

        if( $insertResult ){
            return true;
        }

        return false;
    }

    /**
     * Grabs all avalible comments for the requested module and id
     *
     * @version     1.0
     * @since       1.0.0
     * @autor       xLink
     *
     * @param       string  $tplVar
     */
    function getComments($tplVar){

        $objTPL  = coreObj::getTPL();
        $objUser = coreObj::getUser();
        $objSQL  = coreObj::getDBO();

        //set the template for the comments
        $objTPL->set_filenames(array(
            'comments'  =>  'modules/core/template/comments/viewComments.tpl'
        ));

        if(User::$IS_ONLINE){

            $dontShow = false;

            switch($_GET['mode']){

                case 'postComment':
                    if(HTTP_POST){
                        if(doArgs('comment_'.$this->getVar('module_id'), false, $_SESSION[$this->module]) != $_POST['sessid']){
                            trigger_error('Error: Cant remember where you were posting to.');
                        } else {
                            $comment = $this->insertComment( $this->getVar('module'), $this->getVar('module_id'), $objUser->grab('id'), $_POST['comment']);

                            if(!$comment){
                                trigger_error('Error: Your comment wasnt posted, please try again.');
                            }
                            unset($_SESSION[$module]);
                        }

                        $dontShow = true;
                    }

                break;

                case 'ajPostComment':
                    if(HTTP_AJAX && HTTP_POST){
                        if(doArgs('comment_'.$this->getVar('module_id'), false, $_SESSION[$this->getVar('module')]) != $_POST['sessid']){
                            die('1 <script>console.log('.json_encode(array('comment_'.$this->getVar('module_id'), $_SESSION[$this->getVar('module')], $_POST['sessid'], $_POST)).');</script>');
                        }else{

                            $comment = $this->insertComment($this->getVar('module'), $this->getVar('module_id'), $objUser->grab('id'), $_POST['comment']);

                            if(!$comment){
                                die('0');
                            }

                            echo $this->getLastComment($comment);
                        }
                        exit;
                    }
                break;

                case 'deleteComment':
                    $id = doArgs('id', 0, $_GET, 'is_number');

                    $query = $objSQL->queryBuilder()
                                    ->select('*')
                                    ->from('#__comments')
                                    ->where('id', '=', $id)
                                    ->build();

                    $comment = $objSQL->query($query);

                    if(!$comment){
                        msg('FAIL', 'Error: Comment not found.', '_ERROR');
                        break;
                    }

                    //check if user has perms
                    if(User::$IS_ADMIN || User::$IS_MOD ||
                        (User::$IS_ONLINE && ($objUser->grab('id') == $comments['author'] || $objUser->grab('id')==$this->getVar('author_id')))){

                        //do teh the delete
                        $log = 'Comments System: '.$objUser->profile($objUser->grab('id'), RAW).' deleted comment from <a href="'.$this->aURL[1].'">this</a>.';

                        $deleteQuery = $objSQL->queryBuilder()
                                                ->deleteFrom('#__comments')
                                                ->where('id', '=', $id)
                                                ->build();

                        $delete = $objSQL->query( $deleteQuery );

                        if(!$delete){
                            trigger_error('Error: The comment was not deleted.');
                        }else{
                            msg('INFO', 'The comment was successfully deleted.');
                        }
                    }
                break;

                case 'ajDelComment':
                    if(HTTP_AJAX && HTTP_POST){

                        $id = doArgs('id', 0, $_GET, 'is_number');

                        $commentQuery = $objSQL->queryBuilder()
                                                ->select('*')
                                                ->from('#__comments')
                                                ->where('id','=',$id)
                                                ->build();

                        $comment = $objSQL->fetchLine( $commentQuery );

                        if(!$comment){
                            die('-1');
                        }

                        //check if user has perms
                        if(User::$IS_ADMIN || User::$IS_MOD ||
                            (User::$IS_ONLINE && ($objUser->grab('id') == $comments['author'] || $objUser->grab('id') == $this->getVar('author_id')))){

                            //do teh the delete
                            $log = 'Comments System: '.$this->objUser->profile($this->objUser->grab('id'), RAW).' deleted comment from <a href="'.$this->aURL[1].'">this</a>.';

                            $deleteQuery = $objSQL->queryBuilder()
                                                    ->deleteFrom('#__comments')
                                                    ->where('id', '=', $id)
                                                    ->build();

                            $delete = $objSQL->query( $deleteQuery );

                            die((!$delete ? '0' : '1'));
                        }
                    }else{
                        die('-1');
                    }
                    die('0');
                break;
            }

            //make sure the submit form only shows when we want it to
            if(!$dontShow){
                $this->makeSubmitForm();
            }
        }

        //get a comments count for this module and id
        $commentsCount = $this->getCount();

        // TODO: fix the pagination
        $objPagniation = coreObj::getPagination();
        $comPagination = new pagination('commentsPage', $this->perPage, $commentsCount);

            //check to see if we have a positive number
            if($commentsCount){

                //now lets actually grab the comments

                $commentDataQuery = $objSQL->queryBuilder()
                                            ->select('*')
                                            ->from('#__comments')
                                            ->where(
                                                sprintf('module = "%s" AND module_id = %d ',
                                                    $this->getVar('module'),
                                                    $this->getVar('module_id')
                                            ))
                                            ->limit($comPagination->getSqlLimit())
                                            ->build();


                $commentsData = $objSQL->fetchAll($commentDataQuery)

                if(!$commentsData){
                    //something went wrong
                    trigger_error('Error loading comments.');
                }else{

                    $objTPL->assign_var('COM_PAGINATION', $comPagination->getPagination());

                    $i=0;

                    //assign the comments to the template
                    foreach($commentsData as $comments){
                        $objTPL->assign_block_vars('comment', array(
                            'ID'        => $comments['id'],
                            'cID'       => 'comment-'.$comments['id'],
                            'ROW'       => $i%2 ? 'row_color2' : 'row_color1',
                            'ALT_ROW'   => $i%2 ? 'row_color1' : 'row_color2',

                            'AUTHOR'    => $this->objUser->profile($comments['author']),
                            'POSTED'    => $this->objTime->mk_time($comments['timestamp']),
                            'POST'      => contentParse($comments['comment']),
                        ));

                        if(User::$IS_ADMIN || User::$IS_MOD ||
                            (User::$IS_ONLINE && ($objUser->grab('id')==$comments['author'] || $objUser->grab('id') == $this->getVar('author_id')))){

                            $this->objTPL->assign_block_vars('comment.functions', array(
                                'URL'   => $this->aURL[0].'?mode=deleteComment&id='.$comments['id'],
                            ));
                        }
                    $i++;
                    }
                }
        }else{
            //we have no comments so output a msg box saying so
            msg('INFO', 'No Comments.', '_ERROR');
        }

        //and then output the comments to the parent template
        $this->objTPL->assign_var_from_handle($tplVar, 'comments');
    }


    /**
     //
     // -- Finish porting from here
     //
     */


    function getCount(){
        $this->count = $objSQL->getInfo('comments', array('module="%s" AND module_id="%s"', $this->module, $this->module_id));
        return $this->count;
    }

    /**
     * Outputs the submit form for a new comment
     *
     * @version     1.0
     * @since       0.8.0
     */
    function makeSubmitForm(){
        $rand = rand(1, 99);
        $this->objTPL->set_filenames(array(
            'submitCommentsForm_'.$rand => 'modules/core/template/comments/submitComment.tpl'
        ));

        $this->objPage->addJSFile('/'.root().'scripts/comments.js');

        $sessid = $_SESSION[$this->module]['comment_'.$this->module_id] = md5(time().'�');
        $this->objTPL->assign_vars(array(
            'FORM_START'        =>  $this->objForm->start('comments', array('method'=>'POST', 'action'=>$this->aURL[0].'?mode=postComment')),
            'FORM_END'          =>  $this->objForm->finish(),
            'SUBMIT'            =>  $this->objForm->button('submit', 'Submit'),

            'L_SUBMIT_COMMENT'    =>  'Submit a comment:',
            'TEXTAREA'          =>  $this->objForm->textarea('comment', ''),
            'HIDDEN'            =>  $this->objForm->inputbox('sessid', 'hidden', $sessid) .
                                        $this->objForm->inputbox('module', 'hidden', $this->module),
        ));

        //and then output the comments to the parent template
        $this->objTPL->assign_var_from_handle('_NEW_COMMENT', 'submitCommentsForm_'.$rand);
    }

    /**
     * Outputs a comment wrapped in template for ajax purposes
     *
     * @version     1.0
     * @since       0.8.0
     */
    function getLastComment($id){
        //set the template for the comments
        $this->objTPL->set_filenames(array(
            'ajComments'  =>  'modules/core/template/comments/ajaxComments.tpl'
        ));

        $comments = $this->objSQL->getLine($this->objSQL->prepare(
            'SELECT * FROM `$Pcomments` WHERE id = "%d"',
            $id
        ));
            $this->objTPL->assign_block_vars('comment', array(
                'ID'        => $comments['id'],
                'cID'       => 'comment-'.$comments['id'],
                'ROW'       => $i%2 ? 'row_color2' : 'row_color1',
                'ALT_ROW'   => $i%2 ? 'row_color1' : 'row_color2',

                'AUTHOR'    => $this->objUser->profile($comments['author']),
                'POSTED'    => $this->objTime->mk_time($comments['timestamp']),
                'POST'      => contentParse($comments['comment']),
            ));

            if(User::$IS_ADMIN || User::$IS_MOD ||
                (User::$IS_ONLINE && ($this->objUser->grab('id')==$comments['author'] || $this->objUser->grab('id')==$this->author_id))){

                $this->objTPL->assign_block_vars('comment.functions', array(
                    'URL'   => $this->aURL[0].'?mode=deleteComment&id='.$comments['id'],
                ));
            }
        $this->objTPL->parse('ajComments', false);
        return $this->objTPL->get_html('ajComments');
    }
}

?>