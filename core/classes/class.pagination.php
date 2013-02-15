<?php
/*======================================================================*\
||              Cybershade CMS - Your CMS, Your Way                     ||
\*======================================================================*/
if(!defined('INDEX_CHECK')){ die('Error: Cannot access directly.'); }

class Core_Classes_Pagination extends Core_Classes_coreObj {

    protected $instance = '';
    protected $total_per_page = 1;
    protected $total_items = 1;
    protected $total_pages = 1;
    protected $current_page = 1;
    protected $query_string = '';

    public function __construct($instance, $total_per_page, $total_items=0) {
        $this->instance       = $instance;
        $this->total_per_page = $total_per_page;
        $this->total_items    = $total_items;

        //calculate some more basic vars
        $this->total_pages  = ceil($total_items / $total_per_page);
        $this->current_page = doArgs($instance, 1, $_GET, 'is_number');

        //check that the current page is not over the max pages
        if($this->current_page > $this->total_pages){
            $this->current_page = $this->total_pages;
        }

        //check that the current page is not below 0
        if($this->current_page < 1){
            $this->current_page = 1;
        }
    }

    public function getCurrentPage(){
        return $this->current_page;
    }

    public function getLastPage(){
        return $this->total_pages;
    }

    public function getSqlLimit(){
        return (($this->current_page-1) * $this->total_per_page).','.$this->total_per_page;
    }

    public function goLastPage(){
        $this->current_page = ($this->total_pages==0 ? 1 : $this->total_pages);
    }

    public function getPagination($showOne=false, $style=null, $url=null){
        // global $objUser;

        $objUser = Core_Classes_coreObj::getInstance();

        if($this->total_pages <= 1){
            if(!$showOne){ return ''; }
        }

        if(!User::$IS_ONLINE){
            $switch = 1;
        }else{
            $switch = $objUser->grab('paginationStyle');
        }

        if(!is_empty($style)){
            $switch = $style;
        }

        switch($switch){
            default:
            case '1':
                return $this->paginationStyle1($url);
            break;

            case '2':
                return $this->paginationStyle2($url);
            break;

            case 'mini':
                return $this->paginationMini($url);
            break;
        }
    }

    protected function parseQueryString($url){
        $url = explode('?', $url);
        parse_str($url[1], $vars);

        $query_string = '';
        foreach($vars as $key => $value){
            if($key != $this->instance && $value != 'last_page'){
                $query_string .= sprintf('%s=%s&', $key, urlencode($value));
            }
        }
        return sprintf('%s?%s', $url[0], $query_string);
    }


    protected function paginationStyle1($url=null){
        // global $objForm;

        $objForm = Core_Classes_coreObj::getForm();

        $url = $this->parseQueryString((is_empty($url) ? $_SERVER['REQUEST_URI'] : $url));

        $pages = $objForm->start('pagination'.rand(1, 99), array('method' => 'GET', 'action' => $url));
        $pages .= '<table border="0" cellspacing="0" cellpadding="0" class="pagination"><tr>';

        if($this->current_page < 2){
            $pages .= '    <td class="button disabled corners">&lt;&lt; First</td> <td class="button disabled corners">&lt; Back</td>';
        } else {
            $pages .= '    <td><a href="'.$url.$this->instance.'=1" class="button">&lt;&lt; First</a></td>
                            <td><a href="'.$url.$this->instance.'='.($this->current_page-1).'" class="button">&lt; Back</a></td>';
        }

        $pages .= '        <td align="center">Page '.$objForm->inputbox($this->instance, 'inputbox', $this->current_page,
                    array('class'=>'input', 'style'=>'width: 25px; text-align: center')).' of '.$this->total_pages.'</td>';

        if($this->current_page >= $this->total_pages){
            $pages .= '    <td class="button disabled corners">Next &gt;</td> <td class="button disabled corners">Last &gt;&gt;</td>';
        }else{
            $pages .= '    <td><a href="'.$url.$this->instance.'='.($this->current_page+1).'" class="button">Next &gt;</a></td>
                            <td><a href="'.$url.$this->instance.'='.($this->total_pages).'" class="button">Last &gt;&gt;</a></td>';
        }

        $pages .= '</tr></table>';
        $pages .= $objForm->finish();

        return $pages;
    }

    protected function paginationStyle2($url=null){
        $url = $this->parseQueryString((is_empty($url) ? $_SERVER['REQUEST_URI'] : $url));

        //defaults
        $adjacents = 2;
        $totalitems = $this->total_pages;
        $page = $this->current_page;

        //other vars
        $prev = $page-1;                                    //previous page is page - 1
        $next = $page+1;                                    //next page is page + 1
        $lastpage = $this->total_pages;                        //lastpage is = total items / items per page, rounded up.
        $lpm1 = $lastpage-1;                                //last page minus 1

        $pagination = '';
        if($lastpage > 1){
            $pagination .= '<div class="pagination">';

            //previous button
            if($this->current_page < 2){
                $pagination .= '    <span class="disabled corners">&lt;&lt; First</span> <span class="disabled corners">&lt; Back</span>';
            } else {
                $pagination .= '    <a href="'.$url.$this->instance.'=1">&lt;&lt; First</a>
                                <a href="'.$url.$this->instance.'='.($this->current_page-1).'">&lt; Back</a> ';
            }


            //pages
            if($lastpage < 7+($adjacents * 2)){    //not enough pages to bother breaking it up
                for($counter = 1; $counter <= $lastpage; $counter++){
                    if($counter == $page){
                        $pagination .= '<span class="current">'.$counter.'</span>';
                    }else{
                        $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'">'.$counter.'</a>';
                    }
                }
            }else if($lastpage >= 7+($adjacents * 2)){    //enough pages to hide some
                //close to beginning; only hide later pages
                if($page < 1 + ($adjacents * 3)){
                    for($counter = 1; $counter < 4+($adjacents * 2); $counter++){
                        if($counter == $page){
                            $pagination .= '<span class="current">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'">'.$counter.'</a>';
                        }
                    }
                    $pagination .= '<span class="elipses">...</span>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lpm1.'">'.$lpm1.'</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lastpage.'">'.$lastpage.'</a>';
                //in middle; hide some front and some back
                }else if($lastpage - ($adjacents*2) > $page && $page > ($adjacents*2)){
                    $pagination .= '<a href="'.$url.$this->instance.'=1">1</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'=2">2</a>';
                    $pagination .= '<span class="elipses">...</span>';
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++){
                        if ($counter == $page){
                            $pagination .= '<span class="current">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'">'.$counter.'</a>';
                        }
                    }
                    $pagination .= "...";
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lpm1.'">'.$lpm1.'</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lastpage.'">'.$lastpage.'</a>';
                //close to end; only hide early pages
                }else{
                    $pagination .= '<a href="'.$url.$this->instance.'=1">1</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'=2">2</a>';
                    $pagination .= '<span class="elipses">...</span>';
                    for($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++){
                        if ($counter == $page){
                            $pagination .= '<span class="current">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'">'.$counter.'</a>';
                        }
                    }
                }
            }

            //next button
            if($this->current_page >= $this->total_pages){
                $pagination .= '    <span class="disabled corners">Next &gt;</span> <span class="disabled corners">Last &gt;&gt;</span>';
            }else{
                $pagination .= '    <a href="'.$url.$this->instance.'='.($next).'" class="button">Next &gt;</a>
                                <a href="'.$url.$this->instance.'='.($this->total_pages).'" class="button">Last &gt;&gt;</a>';
            }


            $pagination .= "</div>\n";
        }

        return $pagination;
    }


    protected function paginationMini($url=null){
        $url = $this->parseQueryString((is_empty($url) ? $_SERVER['REQUEST_URI'] : $url));

        //defaults
        $adjacents = 2;
        $totalitems = $this->total_pages;
        $page = $this->current_page;

        //other vars
        $prev = $page-1;                                    //previous page is page - 1
        $next = $page+1;                                    //next page is page + 1
        $lastpage = $this->total_pages;                        //lastpage is = total items / items per page, rounded up.
        $lpm1 = $lastpage-1;                                //last page minus 1

        $pagination = '';
        if($lastpage > 1){
            $pagination .= '<div class="mini-pagination">';
            //pages
            if($lastpage < 7+($adjacents * 2)){    //not enough pages to bother breaking it up
                for($counter = 1; $counter <= $lastpage; $counter++){
                    if($counter == $page){
                        $pagination .= '<span class="current button">'.$counter.'</span>';
                    }else{
                        $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'" class="button">'.$counter.'</a>';
                    }
                }
            }else if($lastpage >= 7+($adjacents * 2)){    //enough pages to hide some
                //close to beginning; only hide later pages
                if($page < 1 + ($adjacents * 3)){
                    for($counter = 1; $counter < 4+($adjacents * 2); $counter++){
                        if($counter == $page){
                            $pagination .= '<span class="current button">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'" class="button">'.$counter.'</a>';
                        }
                    }
                    $pagination .= '<span class="elipses">...</span>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lpm1.'" class="button">'.$lpm1.'</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lastpage.'" class="button">'.$lastpage.'</a>';
                //in middle; hide some front and some back
                }else if($lastpage - ($adjacents*2) > $page && $page > ($adjacents*2)){
                    $pagination .= '<a href="'.$url.$this->instance.'=1" class="button">1</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'=2" class="button">2</a>';
                    $pagination .= '<span class="elipses">...</span>';
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++){
                        if ($counter == $page){
                            $pagination .= '<span class="current button">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'" class="button">'.$counter.'</a>';
                        }
                    }
                    $pagination .= "...";
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lpm1.'" class="button">'.$lpm1.'</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'='.$lastpage.'" class="button">'.$lastpage.'</a>';
                //close to end; only hide early pages
                }else{
                    $pagination .= '<a href="'.$url.$this->instance.'=1" class="button">1</a>';
                    $pagination .= '<a href="'.$url.$this->instance.'=2" class="button">2</a>';
                    $pagination .= '<span class="elipses">...</span>';
                    for($counter = $lastpage - (1 + ($adjacents * 3)); $counter <= $lastpage; $counter++){
                        if ($counter == $page){
                            $pagination .= '<span class="current button">'.$counter.'</span>';
                        }else{
                            $pagination .= '<a href="'.$url.$this->instance.'='.$counter.'" class="button">'.$counter.'</a>';
                        }
                    }
                }
            }


            $pagination .= "</div>\n";
        }

        return $pagination;
    }
}

?>