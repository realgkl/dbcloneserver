<?php
/**
 * @desc 索引队列对象-基类
 * @author gkl
 * @since 20131226
 */
class baseTableIndexList extends baseList
{
	/**
	 * @desc 所属表
	 * @var baseTable
	 */
	protected $table;
	/**
	 * @desc 构造函数
	 * @param baseTable $table 所属表
	 */
	public function __construct( &$table )
	{
		parent::__construct();
		$this->table = &$table;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		unset( $this->table );
		parent::__destruct();
	}
	/**
	 * @desc 添加
	 * @param baseTableIndex $index
	 */
	public function add( $index )
	{
		return $this->__add( $index, $index->getName() );
	}
	/**
	 * @desc 根据索引获取字段
	 * @param integer $key 索引
	 * @return baseTableIndex
	 */
	public function &getByKey( $key )
	{
		return $this->__getByKey( $key );
	}
	/**
	 * @desc 根据字段名获取字段
	 * @param string $index_name 字段名
	 * @return baseTableIndex
	 */
	public function &getByName( $index_name )
	{
		return $this->__getByName( $index_name );
	}
}