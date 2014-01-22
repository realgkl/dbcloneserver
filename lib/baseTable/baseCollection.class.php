<?php
/**
 * @desc 集合-基类
 * @author gkl
 * @since 20140110
 */
class baseCollection extends baseList
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
	 * @desc 添加数据
	 * @param baseRecord $record 
	 */
	public function add( $record )
	{
		$primary_value = $record->getByField( $record->getPirmary() );
		$primary_value = is_null( $primary_value ) ? '' : $primary_value;
		$key = $this->__add( $record, $primary_value ); 
		return $key;
	}
	/**
	 * @desc 获取最大主键
	 */
	public function getMaxPrimary()
	{
		$lst =  $this->index_name; // 名称就是主键值
		asort( $lst );
		return array_pop( $lst );
	}
	/**
	 * @desc 获取数据
	 * @return baseRecord 结构体
	 */
	public function &getByKey( $key )
	{
		return $this->__getByKey( $key );
	}
}