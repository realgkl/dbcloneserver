<?php
/**
 * @desc 结构体-基类
 * @author gkl
 * @since 20140110
 */
class baseRecord
{
	/**
	 * @desc 存放数据的数组
	 * $field => $value
	 * @var array
	 */
	protected $data;
	/**
	 * @desc 主键
	 */
	protected $primary;
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		$this->data = array();
		$this->primary = '';
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		$this->data = array();
	}
	/**
	 * @desc 设置数据
	 */
	public function setData( $data, $primary = '' )
	{
		if ( is_array( $data ) && !empty( $data ) )
		{
			foreach ( $data as $field => $value )
			{
				$this->data[strtolower( $field )] = $value; 
			}
		}
		if ( $primary !== '' && in_array( $primary, $this->getFields() ) )
		{
			$this->primary = $primary;
		}
	}
	/**
	 * @desc 根据字段获取数据
	 */
	public function getByField( $field )
	{
		$data = isset( $this->data[$field] ) ? $this->data[$field] : null;  
		return $data;
	}
	/**
	 * @desc 根据字段赋值
	 */
	public function setByField( $field, $value, $is_primary = false )
	{
		$this->data[$field] = $value;
		if ( $is_primary === true && $field != $this->primary )
		{
			$this->primary = $field;
		}
	}
	/**
	 * @desc 获取字段列表
	 */
	public function getFields()
	{
		return array_keys( $this->data );
	}
	/**
	 * @desc 获取数据数量
	 */
	public function getFieldCount()
	{
		return count( $this->data );
	}
	/**
	 * @desc 获取主键
	 */
	public function getPirmary()
	{
		return $this->primary;
	}
	/**
	 * @desc 设置主键
	 */
	public function setPrimary( $value )
	{
		if ( in_array( $value, $this->getFields() ) )
		{
			$this->primary = $value;
		}
	}
	/**
	 * @desc 生成sql
	 * @param baseTableFieldList $fields
	 */
	public function createSql( &$fields = null )
	{
		return '';
	}
	/**
	 * @desc 生成update sql
	 * @param baseTableFieldList $fields
	 */
	public function createUpSql( &$fields = null )
	{
		return '';
	}
	/**
	 * @desc 生成insert sql
	 * @param baseTableFieldList $fields
	 */
	public function createInsSql( &$fields = null )
	{
		return '';
	}
}