<?php
/**
 * @desc 数据库表列表-基类
 * @author gkl
 * @since 20131226
 */
class baseTableList extends baseList
{
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		parent::__construct();
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		parent::__destruct();
	}
	/**
	 * @desc 添加表
	 * @param baseTable $table 表对象
	 */
	public function add( $table, $name = '' )
	{
		if ( $name === '' )
		{
			return $this->__add( $table, $table->getName() );
		}
		else
		{
			return $this->__add( $table, $name );
		}
	}
	/**
	 * @desc 根据表名获取表的引用
	 * @param string $table_name 表名
	 * @return baseTable
	 */
	public function &getByName( $table_name )
	{
		return $this->__getByName( $table_name );
	}
	/**
	 * @desc 根据索引获取表对象的引用
	 * @return baseTableMysql|baseTableOracle
	 * @return baseTable
	 */
	public function &getByKey( $key )
	{
		return $this->__getByKey( $key );
	}
}