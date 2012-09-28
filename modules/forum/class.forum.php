<?php
/*======================================================================*\
||                 Cybershade CMS - Your CMS, Your Way                  ||
\*======================================================================*/
defined('INDEX_CHECK') or die('Error: Cannot access directly.');

class forum extends Module{

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

    /**
     * Returns information about a specific forum
     *
     * @version 1.0
     * @since   1.0.0
     * @author  Dan Aldridge
     *
     * @param   int     $id
     * @param   bool    $subCategories
     */
    public function getForumInfo($id=0, $subCategories=false){
        $objSQL = coreObj::getDBO();

        if(is_empty($this->forum)){
            $this->forum = array();

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
            $result = $objSQL->fetchAll($query);
                if(!count($result)){
                    trigger_error('The forum cannot be queried at this time. Please try again later!');
                }
        }
    }

    public function viewIndex(){
        $mainCats = $this->getForumInfo('*');

        echo dump($mainCats);
    }
}
?>