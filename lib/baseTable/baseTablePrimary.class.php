<?php
/**
 * @desc 主键对象-基类
 */
class baseTablePrimary extends baseTableChildElement
{
	/**
	 * @desc 字段
	 * @var baseTableField
	 */
	protected $field;
	/**
	 * @desc 构造函数
	 */
	public function __construct( &$table )
	{		
		parent::__construct( $table, '' );
		$this->field = null;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		if ( !is_null( $this->field ) )
		{
			unset( $this->field );			
		}		
		parent::__destruct();
	}
	/**
	 * @desc 设置主键
	 * @param baseTableField $field
	 */
	public function set( &$field )
	{
		$this->field = &$field;
		if ( $this->field->getIsPrimary() !== true )
		{
			$this->field->setIsPrimary( true );
		}
	}
	/**
	 * @desc 获取主键字段
	 */
	public function &get()
	{
		return $this->field;
	}
	/**
	 * @desc 释放
	 */
	public function free()
	{
		$this->field = null;
	}
	/**
	 * @desc 获取主键字段名
	 */
	public function getFieldname()
	{
		return $this->field->getName();
	}
}