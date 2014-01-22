<?php
/**
 * @desc 索引对象-基类
 * @author gkl
 * @since 20131227
 */
class baseTableIndex extends baseTableChildElement
{
	/**
	 * @desc 包含字段数组
	 * @var baseTableFieldList
	 */
	protected $fields;
	/**
	 * @desc 是否重复
	 * @var bool
	 */
	protected $unique;
	/**
	 * @desc 构造函数
	 */
	public function __construct( $table, $name )
	{
		parent::__construct( $table, $name );
		$this->fields = new baseTableFieldList( $table );
		$this->unique = false;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		unset( $this->fields );
		parent::__destruct();
	}
	/**
	 * @desc 设置是否重复
	 */
	public function setUnique( $bool )
	{
		if ( $this->unique !== $bool )
		{
			$this->unique = $bool;
		}
	}
	/**
	 * @desc 增加索引字段
	 * @param baseTableField $field
	 */
	public function addIndexField( &$field )
	{
		return $this->fields->addPointer( $field );
	}
	/**
	 * @desc 获取字段的字符串
	 * @param string $glue 分隔符
	 * @return string
	 */
	public function getIndexFieldsStr( $glue = ',' )
	{
		$arr = array();
		$keys = $this->fields->getKeys();		
		foreach ( $keys as $key )
		{
			$field = $this->fields->getByKey( $key );
			$arr[] = $field->getName();
		}
		return implode( $glue, $arr );
	}
	/**
	 * @desc 获取字段列表
	 */
	public function getFieldList()
	{
		return $this->fields;
	}
	/**
	 * @desc 释放
	 */
	public function free()
	{		
		if ( !$this->fields->isEmpty() )
		{
			$keys = $this->fields->getKeys();
			foreach ( $keys as $key )
			{
				$field = &$this->fields->getByKey( $key );
				$field = null;
			}
		}
	}
}