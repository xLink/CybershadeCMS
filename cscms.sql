-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 18, 2012 at 10:02 PM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

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
  `id` tinyint(11) NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

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
(8, 'site', 'language', 'en', NULL),
(9, 'site', 'keywords', 'dev', NULL),
(10, 'site', 'description', 'dev', NULL),
(11, 'site', 'admin_email', 'xlink@cybershade.org', NULL),
(20, 'site', 'google_analytics', NULL, NULL),
(21, 'login', 'max_login_tries', '5', '5'),
(22, 'login', 'remember_me', '1', '1');

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
-- Table structure for table `cscms_menus`
--

CREATE TABLE IF NOT EXISTS `cscms_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `link` text,
  `lname` varchar(50) DEFAULT NULL,
  `blank` int(1) NOT NULL DEFAULT '0',
  `disporder` int(11) NOT NULL DEFAULT '0',
  `color` varchar(20) DEFAULT NULL,
  `perms` int(1) NOT NULL DEFAULT '0',
  `external` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=15 ;

--
-- Dumping data for table `cscms_menus`
--

INSERT INTO `cscms_menus` (`id`, `name`, `link`, `lname`, `blank`, `disporder`, `color`, `perms`, `external`) VALUES
(1, 'menu_mm', '/', 'Site home', 0, 1, NULL, 0, 0),
(2, 'menu_mm', '/admin/', 'Admin Panel', 0, 10, '#FF0000', 3, 0),
(3, 'menu_mm', '/forum/', 'Forum', 0, 2, NULL, 0, 0),
(5, 'menu_mm', '/messages/', 'Private Messages', 0, 3, NULL, 1, 0),
(6, 'menu_mm', '/user/', 'User Control Panel', 0, 4, NULL, 1, 0),
(7, 'menu_mm', '/mod/', 'Moderator Panel', 0, 9, NULL, 3, 0),
(8, 'main_nav', '/', 'Site Home', 0, 1, NULL, 0, 0),
(9, 'main_nav', '/profile/', 'Profile', 0, 2, NULL, 1, 0),
(10, 'main_nav', '/forum/', 'Forum', 0, 3, NULL, 0, 0),
(11, 'main_nav', '/articles/', 'Articles', 0, 4, NULL, 0, 0),
(12, 'main_nav', '/codebase/', 'Codebase', 0, 5, NULL, 0, 0),
(13, 'main_nav', '/pastebin/', 'PasteBin', 0, 6, NULL, 0, 0),
(14, 'menu_nav', '/blog/', 'Blog', 0, 7, NULL, 0, 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `cscms_routes`
--

INSERT INTO `cscms_routes` (`id`, `module`, `label`, `method`, `pattern`, `arguments`, `requirements`, `status`, `redirect`) VALUES
(1, '88b91c187cc01b74e9e7fcc06cc286eb', 'index', 'ANY', '/forum', '{"module":"Module_forum","method":"viewIndex"}', '[]', 1, NULL),
(2, '88b91c187cc01b74e9e7fcc06cc286eb', 'viewCat', 'ANY', '/forum/:cat/', '{"module":"Module_forum","method":"viewCategory"}', '{"cat":"\\\\w+"}', 1, NULL),
(3, '88b91c187cc01b74e9e7fcc06cc286eb', 'newThread', 'ANY', '/forum/:cat/new_thread.html', '{"module":"Module_forum","method":"newThread"}', '{"cat":"\\\\w+"}', 1, NULL),
(4, '88b91c187cc01b74e9e7fcc06cc286eb', 'viewThread', 'ANY', '/forum/:cat/:name-:id.html', '{"module":"Module_forum","method":"viewThread"}', '{"cat":"\\\\w+","id":"\\\\d+"}', 1, NULL),
(5, '88b91c187cc01b74e9e7fcc06cc286eb', 'newReply', 'ANY', '/forum/:cat/:name-:id.html?reply', '{"module":"Module_forum","method":"newReply"}', '{"cat":"\\\\w+","id":"\\\\d+"}', 1, NULL),
(6, NULL, 'backup', 'ANY', '/backup', '{"module":"Module_backup","method":"go"}', '[]', 1, NULL),
(7, 'a74ad8dfacd4f985eb3977517615ce25', 'login_get', 'GET', '/login', '{"module":"Module_core","method":"login_form", "request":"get"}', '[]', 1, NULL),
(9, 'a74ad8dfacd4f985eb3977517615ce25', 'login_process', 'POST', '/login', '{"module":"Module_core","method":"login_process", "request":"post"}', '[]', 1, NULL),
(10, 'a74ad8dfacd4f985eb3977517615ce25', 'index', 'ANY', '/', '{"module":"Module_core","method":"viewIndex"}', '[]', 1, NULL),
(11, 'a74ad8dfacd4f985eb3977517615ce25', 'logout', 'GET', '/logout', '{"module":"Module_core","method":"logout"}', '[]', 1, NULL);

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
(0, '3fc254266556ab8c949e7b061cbda86e', '127.0.0.1', 1353273377, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko/20100101 Firefox/16.0', 'active', 0, 0, 0, 0x613a333a7b733a31333a2273657373696f6e5f7374617274223b693a313335333237333337373b733a343a2275736572223b613a34363a7b733a373a22757365726b6579223b733a33323a223366633235343236363535366162386339343965376230363163626461383665223b733a393a2274696d657374616d70223b693a313335333237363937373b733a323a226964223b733a313a2231223b733a383a22757365726e616d65223b733a353a22784c696e6b223b733a383a2270617373776f7264223b733a33343a222a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a223b733a333a2270696e223b733a303a22223b733a31333a2272656769737465725f64617465223b733a31303a2231333339363736373935223b733a31313a226c6173745f616374697665223b693a313335333235363532333b733a383a2275736572636f6465223b733a363a22673664747774223b733a353a22656d61696c223b733a32303a22786c696e6b40637962657273686164652e6f7267223b733a31303a2273686f775f656d61696c223b733a313a2230223b733a363a22617661746172223b4e3b733a353a227469746c65223b4e3b733a383a226c616e6775616765223b733a323a22656e223b733a383a2274696d657a6f6e65223b733a333a22302e30223b733a353a227468656d65223b733a373a2264656661756c74223b733a363a2268696464656e223b733a313a2230223b733a363a22616374697665223b733a313a2231223b733a393a22757365726c6576656c223b733a313a2233223b733a363a2262616e6e6564223b733a313a2230223b733a31333a227072696d6172795f67726f7570223b733a313a2230223b733a31343a226c6f67696e5f617474656d707473223b733a313a2233223b733a31323a2270696e5f617474656d707473223b733a313a2230223b733a393a226175746f6c6f67696e223b733a313a2230223b733a31313a2272656666657265645f6279223b733a313a2230223b733a31353a2270617373776f72645f757064617465223b733a313a2230223b733a393a2277686974656c697374223b733a313a2230223b733a31353a2277686974656c69737465645f697073223b4e3b733a383a227761726e696e6773223b733a313a2230223b733a333a22756964223b733a313a2231223b733a383a226269727468646179223b733a31303a2230302f30302f30303030223b733a333a22736578223b733a313a2230223b733a31323a22636f6e746163745f696e666f223b4e3b733a353a2261626f7574223b4e3b733a393a22696e74657265737473223b4e3b733a393a227369676e6174757265223b4e3b733a393a22757365726e6f746573223b733a303a22223b733a31333a22616a61785f73657474696e6773223b4e3b733a32313a226e6f74696669636174696f6e5f73657474696e6773223b4e3b733a31353a22666f72756d5f73686f775f73696773223b733a313a2230223b733a31353a22666f72756d5f6175746f7761746368223b733a313a2230223b733a31363a22666f72756d5f717569636b7265706c79223b733a313a2230223b733a31353a22666f72756d5f6361745f6f72646572223b4e3b733a31333a22666f72756d5f747261636b6572223b733a3130363a22613a313a7b693a313b613a343a7b733a323a226964223b733a313a2231223b733a363a226361745f6964223b733a313a2232223b733a31313a226c6173745f706f73746572223b733a31303a2231333339363736373935223b733a343a2272656164223b623a303b7d7d223b733a31363a22706167696e6174696f6e5f7374796c65223b733a313a2231223b733a31383a2270617373776f72645f706c61696e74657874223b733a343a2274657374223b7d733a353a22746f6b656e223b733a33323a223735646663356630373561313061663631393766366166353536393732363164223b7d),
(0, '4367c1abf5edfa38c70b4787b204d323', '86.23.121.54', 1353268686, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko/20100101 Firefox/16.0', 'active', 0, 0, 0, 0x613a323a7b733a31333a2273657373696f6e5f7374617274223b693a313335333236383638363b733a343a2275736572223b613a313a7b733a373a22757365726b6579223b733a33323a223433363763316162663565646661333863373062343738376232303464333233223b7d7d),
(0, 'd6196c23ca70f8b9b0360da0f76b265e', '86.26.136.143', 1353274681, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.64 Safari/537.11', 'active', 0, 0, 0, 0x613a343a7b733a31333a2273657373696f6e5f7374617274223b693a313335333237343638313b733a343a2275736572223b613a34363a7b733a373a22757365726b6579223b733a33323a226436313936633233636137306638623962303336306461306637366232363565223b733a393a2274696d657374616d70223b4e3b733a323a226964223b733a313a2231223b733a383a22757365726e616d65223b733a353a22784c696e6b223b733a383a2270617373776f7264223b733a33343a222a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a223b733a333a2270696e223b733a303a22223b733a31333a2272656769737465725f64617465223b733a31303a2231333339363736373935223b733a31313a226c6173745f616374697665223b693a313335333237343638313b733a383a2275736572636f6465223b733a363a22673664747774223b733a353a22656d61696c223b733a32303a22786c696e6b40637962657273686164652e6f7267223b733a31303a2273686f775f656d61696c223b733a313a2230223b733a363a22617661746172223b4e3b733a353a227469746c65223b4e3b733a383a226c616e6775616765223b733a323a22656e223b733a383a2274696d657a6f6e65223b733a333a22302e30223b733a353a227468656d65223b733a373a2264656661756c74223b733a363a2268696464656e223b733a313a2230223b733a363a22616374697665223b733a313a2231223b733a393a22757365726c6576656c223b733a313a2233223b733a363a2262616e6e6564223b733a313a2230223b733a31333a227072696d6172795f67726f7570223b733a313a2230223b733a31343a226c6f67696e5f617474656d707473223b733a313a2233223b733a31323a2270696e5f617474656d707473223b733a313a2230223b733a393a226175746f6c6f67696e223b733a313a2230223b733a31313a2272656666657265645f6279223b733a313a2230223b733a31353a2270617373776f72645f757064617465223b733a313a2230223b733a393a2277686974656c697374223b733a313a2230223b733a31353a2277686974656c69737465645f697073223b4e3b733a383a227761726e696e6773223b733a313a2230223b733a333a22756964223b733a313a2231223b733a383a226269727468646179223b733a31303a2232312f31322f31393930223b733a333a22736578223b733a313a2231223b733a31323a22636f6e746163745f696e666f223b4e3b733a353a2261626f7574223b4e3b733a393a22696e74657265737473223b4e3b733a393a227369676e6174757265223b4e3b733a393a22757365726e6f746573223b733a303a22223b733a31333a22616a61785f73657474696e6773223b4e3b733a32313a226e6f74696669636174696f6e5f73657474696e6773223b4e3b733a31353a22666f72756d5f73686f775f73696773223b733a313a2230223b733a31353a22666f72756d5f6175746f7761746368223b733a313a2230223b733a31363a22666f72756d5f717569636b7265706c79223b733a313a2230223b733a31353a22666f72756d5f6361745f6f72646572223b4e3b733a31333a22666f72756d5f747261636b6572223b733a3130363a22613a313a7b693a313b613a343a7b733a323a226964223b733a313a2231223b733a363a226361745f6964223b733a313a2232223b733a31313a226c6173745f706f73746572223b733a31303a2231333339363736373935223b733a343a2272656164223b623a303b7d7d223b733a31363a22706167696e6174696f6e5f7374796c65223b733a313a2231223b733a31383a2270617373776f72645f706c61696e74657874223b733a343a2274657374223b7d733a393a22706167655f6c6f6164223b693a313335333130363039383b733a353a22746f6b656e223b733a33323a223038363432353166366237633365613635303065616236343931663662356366223b7d),
(1, 'ee57dc91bd9c1d5a552fc30363050852', '127.0.0.1', 1353274821, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko/20100101 Firefox/16.0', 'active', 0, 0, 0, 0x613a333a7b733a31333a2273657373696f6e5f7374617274223b693a313335333237343832313b733a343a2275736572223b613a34363a7b733a373a22757365726b6579223b733a33323a226565353764633931626439633164356135353266633330333633303530383532223b733a393a2274696d657374616d70223b4e3b733a323a226964223b733a313a2231223b733a383a22757365726e616d65223b733a353a22784c696e6b223b733a383a2270617373776f7264223b733a33343a222a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a2a223b733a333a2270696e223b733a303a22223b733a31333a2272656769737465725f64617465223b733a31303a2231333339363736373935223b733a31313a226c6173745f616374697665223b693a313335333237343832313b733a383a2275736572636f6465223b733a363a22673664747774223b733a353a22656d61696c223b733a32303a22786c696e6b40637962657273686164652e6f7267223b733a31303a2273686f775f656d61696c223b733a313a2230223b733a363a22617661746172223b4e3b733a353a227469746c65223b4e3b733a383a226c616e6775616765223b733a323a22656e223b733a383a2274696d657a6f6e65223b733a333a22302e30223b733a353a227468656d65223b733a373a2264656661756c74223b733a363a2268696464656e223b733a313a2230223b733a363a22616374697665223b733a313a2231223b733a393a22757365726c6576656c223b733a313a2233223b733a363a2262616e6e6564223b733a313a2230223b733a31333a227072696d6172795f67726f7570223b733a313a2230223b733a31343a226c6f67696e5f617474656d707473223b733a313a2233223b733a31323a2270696e5f617474656d707473223b733a313a2230223b733a393a226175746f6c6f67696e223b733a313a2230223b733a31313a2272656666657265645f6279223b733a313a2230223b733a31353a2270617373776f72645f757064617465223b733a313a2230223b733a393a2277686974656c697374223b733a313a2230223b733a31353a2277686974656c69737465645f697073223b4e3b733a383a227761726e696e6773223b733a313a2230223b733a333a22756964223b733a313a2231223b733a383a226269727468646179223b733a31303a2232312f31322f31393930223b733a333a22736578223b733a313a2231223b733a31323a22636f6e746163745f696e666f223b4e3b733a353a2261626f7574223b4e3b733a393a22696e74657265737473223b4e3b733a393a227369676e6174757265223b4e3b733a393a22757365726e6f746573223b733a303a22223b733a31333a22616a61785f73657474696e6773223b4e3b733a32313a226e6f74696669636174696f6e5f73657474696e6773223b4e3b733a31353a22666f72756d5f73686f775f73696773223b733a313a2230223b733a31353a22666f72756d5f6175746f7761746368223b733a313a2230223b733a31363a22666f72756d5f717569636b7265706c79223b733a313a2230223b733a31353a22666f72756d5f6361745f6f72646572223b4e3b733a31333a22666f72756d5f747261636b6572223b733a3130363a22613a313a7b693a313b613a343a7b733a323a226964223b733a313a2231223b733a363a226361745f6964223b733a313a2232223b733a31313a226c6173745f706f73746572223b733a31303a2231333339363736373935223b733a343a2272656164223b623a303b7d7d223b733a31363a22706167696e6174696f6e5f7374796c65223b733a313a2231223b733a31383a2270617373776f72645f706c61696e74657874223b733a343a2274657374223b7d733a353a22746f6b656e223b733a33323a226238383132396565653138383961386338656162336431353031623738386234223b7d);

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
(1, 'xLink', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtwt', 'xlink@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 0, 3, 0, 0, 0, 0, 0, NULL, 0),
(2, 'NoelDavies', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtws', 'Noeldavies@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 0, 0, 0, 1, 0, 0, 0, NULL, 0),
(3, 'DarkMantis', '$J$BEEgzRTdNwdrKAkHPv0/GeAMGuJCv//', NULL, 1339676795, 1339676795, 'g6dtwq', 'DarkMantis@cybershade.org', 0, NULL, NULL, 'en', '0.0', 'default', 0, 1, 3, 0, 0, 0, 0, 1, 0, 0, 0, NULL, 0);

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
