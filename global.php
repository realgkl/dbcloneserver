<?php
/**
 * @desc 全局公用方法
 * @author gkl
 * @since 20131211
 */
/**
 * @desc 从文件/数组中导入定义常量
 * @param string|array source 数组文件路径 或 数组
 * @param string type 定义常量的来源格示 file 文件 / array 数组
 */
function export_define($source = null , $type = 'file') {
	switch ($type) {
		case 'file':
			if(empty($source) || !is_string($source)) {
				return false;
			}
			$source = include($source);
			break;
		case 'array':
			if(empty($source) || !is_array($source)) {
				return false;
			}
			break;
		default :
			if(empty($source) || !is_string($source)) {
				return false;
			}
			$source = include($source);
			break;
	}
	if(!is_array($source) || empty($source)) {
		return false;
	}
	foreach($source as $key => $value){
		if(is_string($value) || is_numeric($value) || is_bool($value) || is_null($value)) {
			if(!defined($value)){
				define(strtoupper($key),$value);
			}
		}
	}
	return $source;
}
/**
 * @desc 获取/实例化 数据模型方法
 * @return
 * bool|dbCloneNewModel|checkUserByDayModel
 * 数据模型
 */
function m( $modelName, $noExistsExit = true )
{
	return baseCommon::m( $modelName, $noExistsExit );
}
// 全局model数组用来判断是否已经实例化
baseCommon::iniModels();
// 导入定义数据库连接常量
export_define( $oracle_db_config, 'array' );
// 导入定义数据库连接常量
export_define( $db_config, 'array' ); 