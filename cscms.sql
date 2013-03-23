-- phpMyAdmin SQL Dump
-- version 4.0.0-beta1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 23, 2013 at 12:50 AM
-- Server version: 5.5.28a-MariaDB-a1~squeeze-log
-- PHP Version: 5.3.19-1~dotdeb.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cscms`
--

-- --------------------------------------------------------

--
-- Table structure for table `cscms_article_cats`
--

CREATE TABLE IF NOT EXISTS `cscms_article_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text,
  `order` int(11) DEFAULT NULL,
  `perms` int(11) NOT NULL DEFAULT '0',
  `count` int(11) NOT NULL DEFAULT '0',
  `locked` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `cscms_article_cats`
--

INSERT INTO `cscms_article_cats` (`id`, `name`, `order`, `perms`, `count`, `locked`) VALUES
(1, 'CMS Snippets', 1, 0, 0, 0),
(2, 'How-To Articles', 2, 0, 0, 0),
(3, 'User Contributed Articles', 5, 0, 0, 0),
(4, 'Plugin Building', 4, 0, 0, 0),
(5, 'Module Building', 3, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_article_content`
--

CREATE TABLE IF NOT EXISTS `cscms_article_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) DEFAULT NULL,
  `post` text,
  `posted` int(11) NOT NULL,
  `approved_by` int(11) NOT NULL,
  `views` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cscms_article_content`
--

INSERT INTO `cscms_article_content` (`id`, `uid`, `cat_id`, `title`, `post`, `posted`, `approved_by`, `views`) VALUES
(1, 1, 1, 'Testing category index', 'Some really random shit here :)', 1363490365, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_blocks`
--

CREATE TABLE IF NOT EXISTS `cscms_blocks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniqueid` varchar(10) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `order` int(10) NOT NULL,
  `enabled` enum('0','1') NOT NULL DEFAULT '0',
  `file_location` varchar(255) NOT NULL,
  `extra` varchar(255) NOT NULL DEFAULT '[]',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cscms_blocks`
--

INSERT INTO `cscms_blocks` (`id`, `uniqueid`, `title`, `name`, `location`, `order`, `enabled`, `file_location`, `extra`) VALUES
(1, 'fs8fdsf', 'Login Block', '', '_CMSBLOCK.LEFT_MENU', 1, '0', '', '{"module":"Module_core", "method":"login_block"}');

-- --------------------------------------------------------

--
-- Table structure for table `cscms_blocks_routes`
--

CREATE TABLE IF NOT EXISTS `cscms_blocks_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blockID` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_config`
--

CREATE TABLE IF NOT EXISTS `cscms_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `var` varchar(50) NOT NULL,
  `value` text,
  `default` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;

--
-- Dumping data for table `cscms_config`
--

INSERT INTO `cscms_config` (`id`, `key`, `var`, `value`, `default`) VALUES
(1, 'session', 'cookie_domain', NULL, NULL),
(2, 'session', 'cookie_path', NULL, NULL),
(4, 'cms', 'name', 'Cybershade CMS', 'Cybershade CMS'),
(5, 'site', 'title', 'CSDev', 'Cybershade CMS'),
(6, 'site', 'slogan', 'dev', NULL),
(7, 'site', 'theme', 'cybershade', NULL),
(8, 'site', 'language', 'en-GB', 'en-GB'),
(9, 'site', 'keywords', 'dev', NULL),
(10, 'site', 'description', 'dev', NULL),
(11, 'site', 'admin_email', 'xlink@cybershade.org', NULL),
(20, 'site', 'google_analytics', NULL, NULL),
(21, 'login', 'max_login_tries', '5', '5'),
(22, 'login', 'remember_me', '1', '1'),
(23, 'time', 'timezone', '0', '0');

-- --------------------------------------------------------

--
-- Table structure for table `cscms_fforum_cats`
--

CREATE TABLE IF NOT EXISTS `cscms_fforum_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `desc` text,
  `order` int(11) NOT NULL DEFAULT '0',
  `mods` text,
  `last_poster` int(15) NOT NULL DEFAULT '0',
  `last_post_id` int(11) NOT NULL DEFAULT '0',
  `thread_count` int(11) NOT NULL DEFAULT '0',
  `post_count` int(11) NOT NULL DEFAULT '0',
  `auth_view` int(1) NOT NULL DEFAULT '0',
  `auth_read` int(1) NOT NULL DEFAULT '0',
  `auth_post` int(1) NOT NULL DEFAULT '0',
  `auth_reply` int(1) NOT NULL DEFAULT '0',
  `auth_edit` int(1) NOT NULL DEFAULT '0',
  `auth_del` int(1) NOT NULL DEFAULT '0',
  `auth_move` int(1) NOT NULL DEFAULT '0',
  `auth_special` int(1) NOT NULL DEFAULT '0',
  `auth_mod` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=25 ;

--
-- Dumping data for table `cscms_fforum_cats`
--

INSERT INTO `cscms_fforum_cats` (`id`, `parentid`, `title`, `desc`, `order`, `mods`, `last_poster`, `last_post_id`, `thread_count`, `post_count`, `auth_view`, `auth_read`, `auth_post`, `auth_reply`, `auth_edit`, `auth_del`, `auth_move`, `auth_special`, `auth_mod`) VALUES
(1, 0, 'General', NULL, 0, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(2, 1, 'Suggestions, Ideas and Feedback', 'A dedicated place to discuss all the suggestions, ideas and give feedback.', 2, NULL, 1, 40, 4, 15, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(3, 1, 'Introductions', 'Introduce yourself to the rest of the community.', 1, NULL, 13, 29, 13, 61, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(4, 1, 'Lounge', 'A place to talk about anything and everything.', 3, NULL, 10, 36, 4, 8, 1, 1, 1, 1, 1, 3, 3, 3, 0),
(5, 0, 'Community Projects', NULL, 2, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 5, 'Project Talk', 'Push your ideas for community projects here.', 1, NULL, 4, 18, 2, 9, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(7, 0, 'Cybershade CMS', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 7, 'Core Development', 'Talk about the core CMS development here.', 1, NULL, 1, 10, 1, 3, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(9, 7, 'Modules', 'Get help with your module development here.', 2, NULL, 7, 41, 7, 19, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(10, 7, 'Plugins / Hooks', 'Hooks allow you to expand the funcitonality of the core without disrupting the internal code.', 3, NULL, 1, 11, 1, 0, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(11, 7, 'Themes and Templates', 'Unsure how the override system works? or how to setup your theme? Ask Here.', 4, NULL, 1, 30, 1, 0, 0, 0, 1, 1, 1, 3, 3, 3, 0),
(12, 4, 'Programmer''s Lounge', 'A place to talk 1''s and 0''s', 0, NULL, 1, 22, 3, 11, 0, 0, 1, 1, 1, 3, 3, 3, 3),
(13, 4, 'Designer''s Lounge', 'Creative conversation at its best.', 0, NULL, 0, 0, 0, 0, 0, 0, 1, 1, 1, 3, 3, 3, 3),
(14, 5, 'CScreenie', 'Screenshot program written by [user]Biber[/user] to help make Dev''s lives easier with screenshotting. \r\n[url]http://cscreenie.sourceforge.net/[/url]', 2, NULL, 10, 38, 1, 8, 0, 0, 1, 1, 1, 1, 3, 3, 3),
(15, 1, 'Staff Hangout', 'Place for Staff to talk etc', 4, NULL, 0, 0, 0, 0, 2, 2, 2, 2, 2, 2, 3, 3, 3),
(16, 7, 'Support', 'Use this forum for getting support on a new install. [b]WARNING[/b]: We will not support installations that have had their core edited.', 5, NULL, 1, 31, 1, 0, 0, 0, 1, 1, 1, 1, 3, 3, 3),
(17, 0, 'RECURSE', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(18, 17, 'ALL', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(19, 18, 'THE', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(20, 19, 'THINGS', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(21, 20, 'x5', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(22, 21, 'x6', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(23, 22, 'x7', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(24, 23, 'x8', NULL, 1, NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_forum_auth`
--

CREATE TABLE IF NOT EXISTS `cscms_forum_auth` (
  `group_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `auth_view` int(1) NOT NULL DEFAULT '0',
  `auth_read` int(1) NOT NULL DEFAULT '0',
  `auth_post` int(1) NOT NULL DEFAULT '0',
  `auth_reply` int(1) NOT NULL DEFAULT '0',
  `auth_edit` int(1) NOT NULL DEFAULT '0',
  `auth_del` int(1) NOT NULL DEFAULT '0',
  `auth_move` int(1) NOT NULL DEFAULT '0',
  `auth_special` int(1) NOT NULL DEFAULT '0',
  `auth_mod` int(1) NOT NULL DEFAULT '0',
  KEY `cat_id` (`cat_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_forum_cats`
--

CREATE TABLE IF NOT EXISTS `cscms_forum_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `desc` text,
  `order` int(11) NOT NULL DEFAULT '0',
  `last_post_id` int(11) NOT NULL DEFAULT '0',
  `postcounts` int(11) NOT NULL DEFAULT '0',
  `auth_view` int(1) NOT NULL DEFAULT '0',
  `auth_read` int(1) NOT NULL DEFAULT '0',
  `auth_post` int(1) NOT NULL DEFAULT '0',
  `auth_reply` int(1) NOT NULL DEFAULT '0',
  `auth_edit` int(1) NOT NULL DEFAULT '0',
  `auth_del` int(1) NOT NULL DEFAULT '0',
  `auth_move` int(1) NOT NULL DEFAULT '0',
  `auth_special` int(1) NOT NULL DEFAULT '0',
  `auth_mod` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_forum_posts`
--

CREATE TABLE IF NOT EXISTS `cscms_forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `thread_id` int(2) NOT NULL DEFAULT '0',
  `author` int(15) DEFAULT '0',
  `post` text,
  `timestamp` int(15) NOT NULL DEFAULT '0',
  `poster_ip` varchar(15) NOT NULL DEFAULT '',
  `edited` int(5) NOT NULL DEFAULT '0',
  `edited_by` int(15) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `thread_id` (`thread_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_forum_threads`
--

CREATE TABLE IF NOT EXISTS `cscms_forum_threads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `author` int(15) NOT NULL DEFAULT '0',
  `subject` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `first_post_id` int(11) NOT NULL DEFAULT '0',
  `last_uid` int(15) NOT NULL DEFAULT '0',
  `locked` int(1) NOT NULL DEFAULT '0',
  `mode` int(1) NOT NULL DEFAULT '0',
  `views` int(1) NOT NULL DEFAULT '0',
  `old_cat_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_forum_watch`
--

CREATE TABLE IF NOT EXISTS `cscms_forum_watch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0',
  `thread_id` int(11) NOT NULL DEFAULT '0',
  `seen` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_groups`
--

CREATE TABLE IF NOT EXISTS `cscms_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci,
  `moderator` int(11) unsigned NOT NULL DEFAULT '0',
  `single_user_group` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `order` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `cscms_groups`
--

INSERT INTO `cscms_groups` (`id`, `type`, `name`, `description`, `moderator`, `single_user_group`, `color`, `order`) VALUES
(1, 1, 'Admin', 'Site Administrator', 1, 1, '#ff0000', 1),
(2, 1, 'Mods', 'Site Moderator', 1, 0, '#146eca', 3),
(3, 0, 'Users', 'Registered User', 1, 0, '#b7b7b7', 10);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_group_subs`
--

CREATE TABLE IF NOT EXISTS `cscms_group_subs` (
  `uid` int(11) NOT NULL,
  `gid` int(11) NOT NULL,
  `pending` tinyint(1) NOT NULL DEFAULT '1',
  KEY `gid` (`gid`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cscms_group_subs`
--

INSERT INTO `cscms_group_subs` (`uid`, `gid`, `pending`) VALUES
(1, 1, 0),
(1, 2, 0),
(1, 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_menus`
--

CREATE TABLE IF NOT EXISTS `cscms_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_name` varchar(50) DEFAULT NULL,
  `link_url` text,
  `link_title` varchar(50) DEFAULT NULL,
  `order` int(11) NOT NULL DEFAULT '0',
  `color` varchar(20) DEFAULT NULL,
  `perms` int(1) NOT NULL DEFAULT '0',
  `external` int(1) NOT NULL DEFAULT '0',
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=27 ;

--
-- Dumping data for table `cscms_menus`
--

INSERT INTO `cscms_menus` (`id`, `menu_name`, `link_url`, `link_title`, `order`, `color`, `perms`, `external`, `parent_id`) VALUES
(1, 'menu_mm', '/', 'Site home', 1, NULL, 0, 0, 0),
(2, 'menu_mm', '/admin/', 'Admin Panel', 10, '#FF0000', 3, 0, 0),
(3, 'menu_mm', '/forum/', 'Forum', 2, NULL, 0, 0, 0),
(5, 'menu_mm', '/messages/', 'Private Messages', 3, NULL, 1, 0, 0),
(6, 'menu_mm', '/user/', 'User Control Panel', 4, NULL, 1, 0, 0),
(7, 'menu_mm', '/mod/', 'Moderator Panel', 9, NULL, 3, 0, 0),
(8, 'main_nav', '/', 'Site Home', 1, NULL, 0, 0, 0),
(9, 'main_nav', '/profile/', 'Profile', 2, NULL, 1, 0, 0),
(10, 'main_nav', '/forum/', 'Forum', 3, NULL, 0, 0, 0),
(11, 'main_nav', '/articles/', 'Articles', 4, NULL, 0, 0, 0),
(12, 'main_nav', '/codebase/', 'Codebase', 5, NULL, 0, 0, 0),
(13, 'main_nav', '/pastebin/', 'PasteBin', 6, NULL, 0, 0, 0),
(14, 'menu_nav', '/blog/', 'Blog', 7, NULL, 0, 0, 0),
(15, 'admin_menu', '{ADMIN_ROOT}', 'Dashboard', 0, NULL, 1, 0, 0),
(16, 'admin_menu', '#', 'System', 1, NULL, 0, 0, 0),
(17, 'admin_menu', '{ADMIN_ROOT}core/siteconfig/', 'Site Configuration', 1, NULL, 0, 0, 16),
(18, 'admin_menu', '{ADMIN_ROOT}core/menus/edit', 'Edit a Menu', 2, NULL, 0, 0, 24),
(19, 'admin_menu', '#', 'Users', 3, NULL, 1, 0, 0),
(20, 'admin_menu', '{ADMIN_ROOT}core/users/search', 'Search', 1, NULL, 1, 0, 19),
(21, 'admin_menu', '{ADMIN_ROOT}core/users/manage', 'Manage User ', 2, NULL, 1, 0, 19),
(22, 'admin_menu', '{ADMIN_ROOT}core/users/add', 'Add new User', 3, NULL, 1, 0, 19),
(23, 'admin_menu', '{ADMIN_ROOT}core/systeminfo/', 'System Info', 10, NULL, 1, 0, 16),
(24, 'admin_menu', '#', 'Menus', 2, NULL, 0, 0, 0),
(25, 'admin_menu', '{ADMIN_ROOT}core/menus/add', 'Add a Menu', 1, NULL, 0, 0, 24),
(26, 'admin_menu', '{ADMIN_ROOT}core/menus/newlink', 'New Link', 3, NULL, 0, 0, 24);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_modules`
--

CREATE TABLE IF NOT EXISTS `cscms_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `path` text NOT NULL,
  `version` varchar(10) NOT NULL,
  `hash` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_plugins`
--

CREATE TABLE IF NOT EXISTS `cscms_plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `priority` enum('1','2','3') NOT NULL DEFAULT '1',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `cscms_plugins`
--

INSERT INTO `cscms_plugins` (`id`, `name`, `path`, `priority`, `enabled`) VALUES
(1, 'Recache stuff', './plugins/cms/recache.php', '1', 1);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_routes`
--

CREATE TABLE IF NOT EXISTS `cscms_routes` (
  `id` tinyint(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(50) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `method` enum('ANY','HEAD','PUT','GET','OPTIONS','POST','DELETE','TRACE','CONNECT','PATCH') NOT NULL DEFAULT 'ANY',
  `pattern` varchar(255) NOT NULL,
  `arguments` text NOT NULL,
  `requirements` text NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `redirect` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `cscms_routes`
--

INSERT INTO `cscms_routes` (`id`, `module`, `label`, `method`, `pattern`, `arguments`, `requirements`, `status`, `redirect`) VALUES
(1, '88b91c187cc01b74e9e7fcc06cc286eb', 'forumIndex', 'ANY', '/forum', '{"module":"Modules_forum","method":"viewIndex"}', '[]', 1, NULL),
(2, '88b91c187cc01b74e9e7fcc06cc286eb', 'viewCat', 'ANY', '/forum/:cat', '{"module":"Modules_forum","method":"viewCategory"}', '{"cat":"\\\\w+"}', 1, NULL),
(3, '88b91c187cc01b74e9e7fcc06cc286eb', 'newThread', 'ANY', '/forum/:cat/new_thread.html', '{"module":"Modules_forum","method":"newThread"}', '{"cat":"\\\\w+"}', 1, NULL),
(4, '88b91c187cc01b74e9e7fcc06cc286eb', 'viewThread', 'ANY', '/forum/:cat/:name-:id.html', '{"module":"Modules_forum","method":"viewThread"}', '{"cat":"\\\\w+","id":"\\\\d+"}', 1, NULL),
(5, '88b91c187cc01b74e9e7fcc06cc286eb', 'newReply', 'ANY', '/forum/:cat/:name-:id.html?reply', '{"module":"Modules_forum","method":"newReply"}', '{"cat":"\\\\w+","id":"\\\\d+"}', 1, NULL),
(7, 'a74ad8dfacd4f985eb3977517615ce25', 'login_get', 'GET', '/login', '{"module":"Modules_core","method":"login_form", "request":"get"}', '[]', 1, NULL),
(9, 'a74ad8dfacd4f985eb3977517615ce25', 'login_process', 'POST', '/login', '{"module":"Modules_core","method":"login_process", "request":"post"}', '[]', 1, NULL),
(10, 'a74ad8dfacd4f985eb3977517615ce25', 'index', 'ANY', '/', '{"module":"Modules_core","method":"viewIndex"}', '[]', 1, NULL),
(11, 'a74ad8dfacd4f985eb3977517615ce25', 'logout', 'GET', '/logout', '{"module":"Modules_core","method":"logout"}', '[]', 1, NULL),
(12, 'dba5d91846ce1a5e63734dfcbcb481cb', 'articles_listCategories', 'ANY', '/articles', '{"module":"Modules_articles","method":"listCategories"}', '[]', 1, NULL),
(13, 'dba5d91846ce1a5e63734dfcbcb481cb', 'articles_viewCategory', 'ANY', '/articles/:name-:id', '{"module":"Modules_articles","method":"viewCategory"}', '[]', 1, NULL),
(14, 'dba5d91846ce1a5e63734dfcbcb481cb', 'articles_viewArticle', 'ANY', '/articles/:cat-:catid/:title-:id.html', '{"module":"Modules_articles","method":"viewArticle"}', '[]', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_sessions`
--

CREATE TABLE IF NOT EXISTS `cscms_sessions` (
  `uid` int(11) NOT NULL,
  `sid` varchar(32) NOT NULL DEFAULT '',
  `hostname` varchar(128) DEFAULT NULL,
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `useragent` varchar(255) NOT NULL,
  `mode` enum('active','kill','ban','update') NOT NULL DEFAULT 'active',
  `admin` int(1) NOT NULL DEFAULT '0',
  `login_time` int(11) NOT NULL DEFAULT '0',
  `login_attempts` int(2) NOT NULL DEFAULT '0',
  `store` longblob,
  PRIMARY KEY (`sid`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cscms_sessions`
--

INSERT INTO `cscms_sessions` (`uid`, `sid`, `hostname`, `timestamp`, `useragent`, `mode`, `admin`, `login_time`, `login_attempts`, `store`) VALUES
(1, '2603ab25ecdc05f9e42caf6965a7cc5b', '86.8.3.164', 1363979039, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22', 'active', 0, 0, 0, 0x613a333a7b733a31333a2273657373696f6e5f7374617274223b693a313336333937393033383b733a343a2275736572223b613a34363a7b733a373a22757365726b6579223b733a33323a223236303361623235656364633035663965343263616636393635613763633562223b733a393a2274696d657374616d70223b693a313336333937393831353b733a323a226964223b733a313a2231223b733a383a22757365726e616d65223b733a353a22784c696e6b223b733a383a2270617373776f7264223b733a33343a222a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a223b733a333a2270696e223b733a303a22223b733a31333a2272656769737465725f64617465223b733a31303a2231333339363736373935223b733a31313a226c6173745f616374697665223b693a313336333937313936323b733a383a2275736572636f6465223b733a363a22673664747774223b733a353a22656d61696c223b733a32303a22784c696e6b40637962657273686164652e6f7267223b733a31303a2273686f775f656d61696c223b733a313a2230223b733a363a22617661746172223b4e3b733a353a227469746c65223b4e3b733a383a226c616e6775616765223b733a323a22656e223b733a383a2274696d657a6f6e65223b733a333a22302e30223b733a353a227468656d65223b733a373a2264656661756c74223b733a363a2268696464656e223b733a313a2230223b733a363a22616374697665223b733a313a2231223b733a393a22757365726c6576656c223b733a313a2233223b733a363a2262616e6e6564223b733a313a2230223b733a31333a227072696d6172795f67726f7570223b733a313a2231223b733a31343a226c6f67696e5f617474656d707473223b733a313a2233223b733a31323a2270696e5f617474656d707473223b733a313a2230223b733a393a226175746f6c6f67696e223b733a313a2230223b733a31313a2272656666657265645f6279223b733a313a2230223b733a31353a2270617373776f72645f757064617465223b733a313a2230223b733a393a2277686974656c697374223b733a313a2230223b733a31353a2277686974656c69737465645f697073223b4e3b733a383a227761726e696e6773223b733a313a2230223b733a333a22756964223b733a313a2231223b733a383a226269727468646179223b733a31303a2232312f31322f31393930223b733a333a22736578223b733a313a2231223b733a31323a22636f6e746163745f696e666f223b4e3b733a353a2261626f7574223b4e3b733a393a22696e74657265737473223b4e3b733a393a227369676e6174757265223b4e3b733a393a22757365726e6f746573223b733a303a22223b733a31333a22616a61785f73657474696e6773223b4e3b733a32313a226e6f74696669636174696f6e5f73657474696e6773223b4e3b733a31353a22666f72756d5f73686f775f73696773223b733a313a2230223b733a31353a22666f72756d5f6175746f7761746368223b733a313a2230223b733a31363a22666f72756d5f717569636b7265706c79223b733a313a2230223b733a31353a22666f72756d5f6361745f6f72646572223b4e3b733a31333a22666f72756d5f747261636b6572223b733a3130363a22613a313a7b693a313b613a343a7b733a323a226964223b733a313a2231223b733a363a226361745f6964223b733a313a2232223b733a31313a226c6173745f706f73746572223b733a31303a2231333339363736373935223b733a343a2272656164223b623a303b7d7d223b733a31363a22706167696e6174696f6e5f7374796c65223b733a313a2231223b733a31383a2270617373776f72645f706c61696e74657874223b733a343a2274657374223b7d733a353a22746f6b656e223b733a33323a226333636466643737336235353736636263666335356531396162343335306639223b7d),
(1, '41a07a2663223e8e0ec13965d7d4f20f', '86.9.255.55', 1363998694, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0', 'active', 0, 0, 0, 0x613a333a7b733a31333a2273657373696f6e5f7374617274223b693a313336333939383639343b733a343a2275736572223b613a34363a7b733a373a22757365726b6579223b733a33323a223431613037613236363332323365386530656331333936356437643466323066223b733a393a2274696d657374616d70223b693a313336343030323138373b733a323a226964223b733a313a2231223b733a383a22757365726e616d65223b733a353a22784c696e6b223b733a383a2270617373776f7264223b733a33343a222a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a223b733a333a2270696e223b733a303a22223b733a31333a2272656769737465725f64617465223b733a31303a2231333339363736373935223b733a31313a226c6173745f616374697665223b693a313336333931373731313b733a383a2275736572636f6465223b733a363a22673664747774223b733a353a22656d61696c223b733a32303a22784c696e6b40637962657273686164652e6f7267223b733a31303a2273686f775f656d61696c223b733a313a2230223b733a363a22617661746172223b4e3b733a353a227469746c65223b4e3b733a383a226c616e6775616765223b733a323a22656e223b733a383a2274696d657a6f6e65223b733a333a22302e30223b733a353a227468656d65223b733a373a2264656661756c74223b733a363a2268696464656e223b733a313a2230223b733a363a22616374697665223b733a313a2231223b733a393a22757365726c6576656c223b733a313a2233223b733a363a2262616e6e6564223b733a313a2230223b733a31333a227072696d6172795f67726f7570223b733a313a2231223b733a31343a226c6f67696e5f617474656d707473223b733a313a2233223b733a31323a2270696e5f617474656d707473223b733a313a2230223b733a393a226175746f6c6f67696e223b733a313a2230223b733a31313a2272656666657265645f6279223b733a313a2230223b733a31353a2270617373776f72645f757064617465223b733a313a2230223b733a393a2277686974656c697374223b733a313a2230223b733a31353a2277686974656c69737465645f697073223b4e3b733a383a227761726e696e6773223b733a313a2230223b733a333a22756964223b733a313a2231223b733a383a226269727468646179223b733a31303a2232312f31322f31393930223b733a333a22736578223b733a313a2231223b733a31323a22636f6e746163745f696e666f223b4e3b733a353a2261626f7574223b4e3b733a393a22696e74657265737473223b4e3b733a393a227369676e6174757265223b4e3b733a393a22757365726e6f746573223b733a303a22223b733a31333a22616a61785f73657474696e6773223b4e3b733a32313a226e6f74696669636174696f6e5f73657474696e6773223b4e3b733a31353a22666f72756d5f73686f775f73696773223b733a313a2230223b733a31353a22666f72756d5f6175746f7761746368223b733a313a2230223b733a31363a22666f72756d5f717569636b7265706c79223b733a313a2230223b733a31353a22666f72756d5f6361745f6f72646572223b4e3b733a31333a22666f72756d5f747261636b6572223b733a3130363a22613a313a7b693a313b613a343a7b733a323a226964223b733a313a2231223b733a363a226361745f6964223b733a313a2232223b733a31313a226c6173745f706f73746572223b733a31303a2231333339363736373935223b733a343a2272656164223b623a303b7d7d223b733a31363a22706167696e6174696f6e5f7374796c65223b733a313a2231223b733a31383a2270617373776f72645f706c61696e74657874223b733a343a2274657374223b7d733a353a22746f6b656e223b733a33323a226366623439643135666231336539383233303066323938386637666531613539223b7d),
(0, '49798e9a43fb786292120cbf2846eae7', '86.9.255.55', 1363988751, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22', 'active', 0, 0, 0, 0x613a323a7b733a31333a2273657373696f6e5f7374617274223b693a313336333938383734323b733a343a2275736572223b613a313a7b733a373a22757365726b6579223b733a33323a223439373938653961343366623738363239323132306362663238343665616537223b7d7d),
(0, '509aee2ea44cae767a643d5856056a77', '86.9.255.55', 1363974440, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0', 'active', 0, 0, 0, 0x613a323a7b733a31333a2273657373696f6e5f7374617274223b693a313336333937343434303b733a343a2275736572223b613a313a7b733a373a22757365726b6579223b733a33323a223530396165653265613434636165373637613634336435383536303536613737223b7d7d),
(0, '96b3d70a8205eacf5310ada460a14126', '86.9.255.55', 1363988751, 'Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Brackets/0.21.0.0 Safari/537.1', 'active', 0, 0, 0, 0x613a323a7b733a31333a2273657373696f6e5f7374617274223b693a313336333938383735313b733a343a2275736572223b613a313a7b733a373a22757365726b6579223b733a33323a223936623364373061383230356561636635333130616461343630613134313236223b7d7d),
(0, 'd3d6f2b030546990e33d853413861d83', '86.9.255.55', 1363963461, 'Mozilla/5.0 (Linux; U; Android 4.1.2; en-gb; HTC One X Build/JZO54K; CyanogenMod-10.0.0) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30', 'active', 0, 0, 0, 0x613a323a7b733a31333a2273657373696f6e5f7374617274223b693a313336333736333836363b733a343a2275736572223b613a313a7b733a373a22757365726b6579223b733a33323a226433643666326230333035343639393065333364383533343133383631643833223b7d7d);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_uploads`
--

CREATE TABLE IF NOT EXISTS `cscms_uploads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `file_type` varchar(5) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `timestamp` int(12) NOT NULL,
  `authorized` enum('1','0') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `location` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `public` enum('1','0') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `file_size` int(12) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `cscms_userkeys`
--

CREATE TABLE IF NOT EXISTS `cscms_userkeys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uData` varchar(11) NOT NULL DEFAULT '0',
  `uAgent` text NOT NULL,
  `uIP` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `cscms_userkeys`
--

INSERT INTO `cscms_userkeys` (`id`, `uData`, `uAgent`, `uIP`) VALUES
(1, '00429:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(2, 'c49c3:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(3, '2bd67:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(4, '28297:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(5, 'e9122:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(6, 'da853:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1'),
(7, 'c9254:1', 'a08aaaebc93b57e69beb7ce74f6c6d86', '127.0.0.1');

-- --------------------------------------------------------

--
-- Table structure for table `cscms_users`
--

CREATE TABLE IF NOT EXISTS `cscms_users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` char(34) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pin` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `register_date` int(11) NOT NULL DEFAULT '0',
  `last_active` int(11) NOT NULL DEFAULT '0',
  `usercode` varchar(6) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `show_email` tinyint(1) NOT NULL DEFAULT '0',
  `avatar` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` char(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'en',
  `timezone` decimal(5,1) NOT NULL DEFAULT '0.0',
  `theme` varchar(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `userlevel` tinyint(1) NOT NULL DEFAULT '0',
  `banned` tinyint(1) NOT NULL DEFAULT '0',
  `primary_group` int(5) NOT NULL DEFAULT '0',
  `login_attempts` int(3) NOT NULL DEFAULT '0',
  `pin_attempts` int(3) NOT NULL DEFAULT '0',
  `autologin` tinyint(1) NOT NULL DEFAULT '0',
  `reffered_by` int(11) unsigned NOT NULL DEFAULT '0',
  `password_update` tinyint(1) NOT NULL DEFAULT '0',
  `whitelist` tinyint(1) NOT NULL DEFAULT '0',
  `whitelisted_ips` text COLLATE utf8_unicode_ci,
  `warnings` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `usercode` (`usercode`),
  KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `cscms_users`
--

INSERT INTO `cscms_users` (`id`, `username`, `password`, `pin`, `register_date`, `last_active`, `usercode`, `email`, `show_email`, `avatar`, `title`, `language`, `timezone`, `theme`, `hidden`, `active`, `userlevel`, `banned`, `primary_group`, `login_attempts`, `pin_attempts`, `autologin`, `reffered_by`, `password_update`, `whitelist`, `whitelisted_ips`, `warnings`) VALUES
(1, 'xLink', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtwt', 'xLink@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 1, 3, 0, 0, 0, 0, 0, NULL, 0),
(2, 'NoelDavies', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtws', 'Noeldavies@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 1, 0, 0, 1, 0, 0, 0, NULL, 0),
(3, 'DarkMantis', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtwq', 'DarkMantis@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 1, 0, 0, 1, 0, 0, 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `cscms_users_extras`
--

CREATE TABLE IF NOT EXISTS `cscms_users_extras` (
  `uid` int(11) unsigned NOT NULL,
  `birthday` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '00/00/0000',
  `sex` tinyint(1) NOT NULL DEFAULT '0',
  `contact_info` text COLLATE utf8_unicode_ci,
  `about` text COLLATE utf8_unicode_ci,
  `interests` text COLLATE utf8_unicode_ci,
  `signature` text COLLATE utf8_unicode_ci,
  `usernotes` text COLLATE utf8_unicode_ci NOT NULL,
  `ajax_settings` text COLLATE utf8_unicode_ci,
  `notification_settings` text COLLATE utf8_unicode_ci,
  `forum_show_sigs` tinyint(1) NOT NULL DEFAULT '0',
  `forum_autowatch` tinyint(1) NOT NULL DEFAULT '0',
  `forum_quickreply` tinyint(1) NOT NULL DEFAULT '0',
  `forum_cat_order` text COLLATE utf8_unicode_ci,
  `forum_tracker` text COLLATE utf8_unicode_ci,
  `pagination_style` int(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `uid_2` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cscms_users_extras`
--

INSERT INTO `cscms_users_extras` (`uid`, `birthday`, `sex`, `contact_info`, `about`, `interests`, `signature`, `usernotes`, `ajax_settings`, `notification_settings`, `forum_show_sigs`, `forum_autowatch`, `forum_quickreply`, `forum_cat_order`, `forum_tracker`, `pagination_style`) VALUES
(1, '21/12/1990', 1, NULL, NULL, NULL, NULL, '', NULL, NULL, 0, 0, 0, NULL, 'a:1:{i:1;a:4:{s:2:"id";s:1:"1";s:6:"cat_id";s:1:"2";s:11:"last_poster";s:10:"1339676795";s:4:"read";b:0;}}', 1),
(2, '00/00/0000', 0, NULL, NULL, NULL, NULL, '', NULL, NULL, 0, 0, 0, NULL, 'a:1:{i:1;a:4:{s:2:"id";s:1:"1";s:6:"cat_id";s:1:"2";s:11:"last_poster";s:10:"1339676795";s:4:"read";b:0;}}', 1),
(3, '00/00/0000', 0, NULL, NULL, NULL, NULL, '', NULL, NULL, 0, 0, 0, NULL, 'a:1:{i:1;a:4:{s:2:"id";s:1:"1";s:6:"cat_id";s:1:"2";s:11:"last_poster";s:10:"1339676795";s:4:"read";b:0;}}', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
