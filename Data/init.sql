-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.6.17 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  9.3.0.4996
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 paikezone2.0 的数据库结构
CREATE DATABASE IF NOT EXISTS `paikezone` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `paikezone`;
set NAMES utf8;


-- 导出  表 paikezone2.0.pkz_album 结构
CREATE TABLE IF NOT EXISTS `pkz_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '相册id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `tid` int(11) NOT NULL DEFAULT '0' COMMENT '相册分类id',
  `name` varchar(50) NOT NULL DEFAULT '0' COMMENT '相册名字',
  `description` varchar(200) DEFAULT '0' COMMENT '相册描述',
  `limit` int(11) NOT NULL DEFAULT '0' COMMENT '相册权限--1公开  2私密',
  `cover_id` int(11) DEFAULT '0' COMMENT '封面图片id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='相册表';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_album_type 结构
CREATE TABLE IF NOT EXISTS `pkz_album_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '相册分类id',
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='相册分类';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_collect 结构
CREATE TABLE IF NOT EXISTS `pkz_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `pic_id` int(11) NOT NULL DEFAULT '0' COMMENT '收藏的图片id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收藏表';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_dynamic 结构
CREATE TABLE IF NOT EXISTS `pkz_dynamic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(11) DEFAULT '0' COMMENT '用户id',
  `album_id` int(11) DEFAULT '0' COMMENT '相册id',
  `pic_ids` varchar(50) DEFAULT '0' COMMENT '上传的图片组',
  `create_time` int(11) DEFAULT '0' COMMENT '上传时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='动态表';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_pic 结构
CREATE TABLE IF NOT EXISTS `pkz_pic` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '图片id',
  `path` varchar(200) NOT NULL DEFAULT '0' COMMENT '图片地址',
  `name` varchar(200) NOT NULL COMMENT '图片名称',
  `album_id` int(11) NOT NULL DEFAULT '0' COMMENT '相册id',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='图片表\r\n';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_thumb 结构
CREATE TABLE IF NOT EXISTS `pkz_thumb` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '缩略图id',
  `pic_id` int(11) NOT NULL DEFAULT '0' COMMENT '原图的id',
  `path` varchar(50) NOT NULL DEFAULT '0' COMMENT '缩略图路径',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='缩略图表';

-- 数据导出被取消选择。


-- 导出  表 paikezone2.0.pkz_user 结构
CREATE TABLE IF NOT EXISTS `pkz_user` (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '用户密码',
  `email` varchar(64) NOT NULL DEFAULT '' COMMENT '用户邮箱',
  `avatar` varchar(50) NOT NULL DEFAULT 'Uploads/Avatar/default.jpg' COMMENT '用户头像',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `birthday` date NOT NULL DEFAULT '0000-00-00' COMMENT '生日',
  `sign` varchar(200) NOT NULL DEFAULT '' COMMENT '个性签名',
  `login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员状态',
  PRIMARY KEY (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员表';

-- 数据导出被取消选择。
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;


INSERT INTO `pkz_album_type` (`id`, `name`) VALUES
  (1, '生活'),
  (2, '人物'),
  (3, '旅游'),
  (4, 'lomo风'),
  (5, '其他');