<?php
//define('SHOW_SQL_TIME',true);
/**
 * @desc 配置文件目录信息
 */
define( 'ROOT_PATH', dirname( __FILE__ ) );
/**
 * @desc 数据模型路径
 */
define( 'MODEL_PATH', ROOT_PATH . '/model/' );
/**
 * @desc 控制器路径
 */
define( 'ACT_PATH', ROOT_PATH . '/ctrl/' );
/**
 * @desc LIB路径
 */
define( 'LIB_PATH', ROOT_PATH . '/lib/' );
/**
 * @desc INTERFACE路径
 */
define( 'INT_PATH', ROOT_PATH . '/int/' );
/**
 * @desc VIEW路径
 */
define( 'VIEW_PATH', ROOT_PATH . '/view/' );
/**
 * @desc 默认ctrl、func
 */
define( 'DEFAULT_ACTION', 'index' );
define( 'DEFAULT_FUNC', 'index' );
/**
 * @desc 环境
 */
define( 'ENVIRONMENT', 'local' );
/**
 * @desc 数据库配置
 */
$db_config = array(
	'MYSQL_HOST'=>'localhost',
	'MYSQL_USERNAME'=>'root',
	'MYSQL_PASSWORD'=>'',
	'MYSQL_DBNAME'=>'test',
	'MYSQL_PORT'=>'3306',
	'MYSQL_CHARSET'=>'utf8',
	'mysql_pconnect'=>false,
);
/**
 * @desc oracle数据库配置
 */
$oracle_db_config = array(
	'ORACLE_HOST'			=> 'localhost',
	'ORACLE_DB'				=> 'XE',
	'ORACLE_USER'			=> 'gkl',
	'ORACLE_PASS'			=> 'gkl',
	'ORACLE_CHARSET'	=> 'utf8',
);
