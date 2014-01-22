<?php
/**
 * @desc 字段队列对象-基类
 * @author gkl
 * @since 20131226
 */
class baseTableFieldList extends baseList
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
	 * @desc 添加字段
	 * @param baseTableField $field
	 */
	public function add( $field )
	{
		return $this->__add( $field, $field->getName() );
	}
	/**
	 * @desc 添加引用
	 * @param baseTableField $field
	 */
	public function addPointer( &$field )
	{
		return $this->__addPointer( $field, $field->getName() );
	}
	/**
	 * @desc 根据索引获取字段的引用
	 * @param integer $key 索引
	 * @return baseTableField
	 */
	public function &getByKey( $key )
	{
		return $this->__getByKey( $key );
	}
	/**
	 * @desc 根据字段名获取字段的引用
	 * @param string $field_name 字段名
	 * @return baseTableField
	 */
	public function &getByName( $field_name )
	{
		return $this->__getByName( $field_name );
	}
	/**
	 * @desc 生成字段名的数组
	 */
	public function getFieldsArr()
	{
		return array_keys( $this->name_index );
	}
	/**
	 * @desc 比较字段列表
	 * @param baseTableFieldList $compare 要比较的列表
	 * @return bool 是否不同
	 */
	public function compare( &$compare )
	{
		if ( $this->count()  != $compare->count() )
		{
			return true;
		}
		$fields_arr_1 = $this->getFieldsArr();
		$fields_arr_2 = $compare->getFieldsArr();
		$compare_fields_1 = array_diff( $fields_arr_1, $fields_arr_2 );
		$compare_fields_2 = array_diff( $fields_arr_2, $fields_arr_1 );
		if ( !empty( $compare_fields_1 ) || !empty( $compare_fields_2 ) )
		{
			return true;
		}		
		foreach ( $fields_arr_1 as $field_name )
		{
			$field_1 = $this->getByName( $field_name );
			$field_2 = $compare->getByName( $field_name );
			if ( $field_1->compare( $field_2 ) === true )
			{
				return true;
			}
		}
		return false;
	}
}