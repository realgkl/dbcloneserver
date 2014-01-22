<?php
/**
 * @desc 数据库元素-基类
 * @author gkl
 * @since 20131226
 */
class baseTableElement
{
	/**
	 * @desc 名称
	 * @var string
	 */
	protected $name;
	/**
	 * @desc 数据库连接对象
	 * @var baseModelEx
	 */
	protected $conn_obj;
	/**
	 * @desc 获取名称
	 * @return string 名称
	 */
	public function getName()
	{
		return $this->name;
	}
	/**
	 * @desc 构造函数
	 */
	public function __construct( &$conn_obj, $name )
	{
		$this->conn_obj = &$conn_obj;
		$this->name = $name;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		if ( !is_null( $this->conn_obj ) )
		{
			$this->conn_obj = null;
		}
		unset( $this->conn_obj );
	}
}
/**
 * @desc 数据库子元素-基类
 * @author gkl
 * @since 20131226
 */
class baseTableChildElement extends baseTableElement
{
	/**
	 * @desc 所属表
	 * @var baseTable
	 */
	protected $table;
	/**
	 * @desc 构造函数
	 * @param baseTable $table 所属表
	 * @param string $name 名称
	 */
	public function __construct( &$table, $name )
	{
		$this->table = &$table;
		$conn_obj = $table->getConnObj();
		parent::__construct( $conn_obj, $name );
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		if ( !is_null( $this->table ) )
		{
			$this->table = null;
		}
		unset( $this->table );
		parent::__destruct();
	}
	/**
	 * @desc 获取所属表
	 */
	public function &getTable()
	{
		return $this->table;
	}
}