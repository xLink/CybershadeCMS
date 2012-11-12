<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class Module_forum extends Module {

    public function __construct(){
        $objPage = coreobj::getPage();
        $objPage->setMenu('forum');
        $objPage->addJSFile('/'.root().'modules/forum/scripts/forum.js');
        $objPage->addCSSFile(array(
            'href'     => '/'.root().'modules/forum/styles/forum.css',
            'type'     => 'text/css',
            'rel'      => 'stylesheet',
            'priority' => LOW
        ));


        //reset the forum tracker
        /*if(User::$IS_ONLINE){
            $this->forumTrackerInit();
        }*/

    }

    public function viewIndex(){
        $mainCats = $this->getForumInfo('*');

        //and then find out which main cats the user can see
        $categories = array();
        foreach($mainCats as $cat){
            if(/*$this->auth[$cat['id']]['auth_view'] &&*/ $cat['parent_id'] == 0){
                $categories[] = $cat;
            }
        }

        $this->setView();
    }


/**
 //
 //-- Helper Functions
 //
**/

    /**
     * Returns information about a specific forum
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int     $id
     * @param   bool    $subCategories
     *
     * @return  mixed   false or an array with the forum info in
     */
    public function getForumInfo($id=0, $subCategories=false){
        $objSQL = coreObj::getDBO();

        if(is_empty($this->forum)){
            $this->forum = array();

            // grab the categories from the forum
            $query = $objSQL->queryBuilder()
                            ->select(array(
                                'f.*',
                                'uid' => 'u.id',
                                'tid' => 't.id',
                                'thread_name' => 't.subject',
                                'thread_posted' => 't.timestamp',
                                'last_author' => 'p.author',
                                'last_posted' => 'p.timestamp',
                            ))

                            ->from(array('f' => '#__forum_cats'))

                            ->leftJoin(array('p' => '#__forum_posts'))
                                ->on('p.id', '=', 'f.last_post_id')

                            ->leftJoin(array('t' => '#__forum_threads'))
                                ->on('p.thread_id', '=', 't.id')

                            ->leftJoin(array('u' => '#__users'))
                                ->on('u.id', '=', 'p.author')

                            ->orderBy('f.id', 'f.order', 'ASC')
                            ->build();

            $results = $objSQL->fetchAll($query);
                if(!count($results)){
                    trigger_error('The forum cannot be queried at this time. Please try again later!');
                    return;
                }

            // throw the categories into a cache for later use
            foreach($results as $row){
                $this->forum[$row['id']] = $row;
            }

            // grab the permissions for the user
            //$this->auth = $this->auth(AUTH_ALL, AUTH_LIST_ALL, $cats);

            // query for the thread & post counts
            $query = $objSQL->queryBuilder()
                            ->select(array(
                                'c.id',
                                'c.postcounts',
                                'thread_count' => 'COUNT(DISTINCT t.id)',
                                'post_count' => 'COUNT(DISTINCT p.id)',
                            ))

                            ->from(array('c' => '#__forum_cats'))

                            ->leftJoin(array('t' => '#__forum_threads'))
                                ->on('t.cat_id', '=', 'c.id')

                            ->leftJoin(array('p' => '#__forum_posts'))
                                ->on('t.id', '=', 'p.thread_id')

                            ->groupBy('c.id')
                            ->build();

            $result = $objSQL->fetchAll($query);
                if(!count($result)){
                    trigger_error('The forum cannot be queried at this time. Please try again later!');
                    return;
                }

            foreach($result as $row){
                if(is_empty($row['postcounts'])){ continue; }

                $this->forum[$row['id']]['thread_count'] = $row['thread_count'];
                $this->forum[$row['id']]['post_count'] = $row['post_count'];
            }
        }

        // if we want subcats
        if($subCategories !== false){
            $cats = array(); $forums = $this->forum;

            foreach($forum as $category){
                if($this->auth[$cat['id']]['auth_view'] && $car['parent_id'] == $id){
                    $cats[$cat[$cat['id']]] = $cat;
                }
            }

            return $cats;

        // looks like we want a specific categories, or all of them
        }else{
            // if we have $id == 0 or * then send back ... ALL THE FORUMS
            if($id == '*' || $id == 0){
                return $this->forum;
            }

            // if they gave us a forum id, then give them what they asked for :)
            if(is_number($id) && isset($this->$forum[$id])){
                return array($this->forum[$id]);
            }
        }

        // apparently we cannt accommodate them so we'll just return false here
        return false;
    }

    /**
     * Outputs categories into a template and returns the contents
     *
     * @version 2.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   array   $categories
     * @param   bool    $index
     * @param   string  $title
     *
     * @return
     */
    public function outputCats($categories, $index=false, $title=null){
        $objTPL = coreObj::getTPL();
        $objSQL = coreObj::getDBO();

        $objTPL->set_filenames(array(
            'categories' => 'modules/forum/template/assets/categoryOutput.tpl',
        ));


        $forum_moderators = array();

        $query = $objSQL->queryBuilder()
                        ->select(array('aa.catid', 'u.id', 'u.username'))
                        ->from(array(
                            'aa' => '#__forum_auth',
                            'ug' => '#__group_subs',
                            'g' => '#__groups',
                            'u' => '#__users'
                        ))
                        ->where('aa.auth_mod = 1')
                            ->andWhere('g.single_user_group = 1')
                            ->andWhere('ug.gid = aa.group_id')
                            ->andWhere('g.id = aa.group_id')
                            ->andWhere('u.id = ug.uid')
                        ->groupBy('u.id, u.username, aa.cat_id')
                        ->orderBy('aa.cat_id ASC, u.id ASC')
                        ->build();
        $results = $objSQL->fetchAll( $query );
            if( $results === false ){
                $msg = 'Could not query forum moderator information';
                trigger_error($msg);
                hmsgDie('FAIL', $msg);
            }













        $return = $objTPL->get_html('categories');
        $objTPL->reset_block_vars('forum');
        return $return;
    }
}

?>