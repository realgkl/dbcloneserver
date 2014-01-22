<?php
/**
 * @desc 列表-基类
 */
class baseList
{
	/**
	 * @desc 存储的队列
	 * @var array
	 */
	protected $list;
	/**
	 * @desc 名称索引
	 * @var array
	 */
	protected $name_index;
	/**
	 * @desc 索引名称
	 * @var array
	 */
	protected $index_name;
	/**
	 * @desc 删除
	 */
	protected function __delete( $key )
	{
		if ( isset( $this->list[$key] ) )
		{
			unset( $this->list[$key] );
			if ( isset( $this->index_name[$key] ) )
			{
				$name = $this->index_name[$key];
				unset( $this->index_name[$key] );
				if ( isset( $this->name_index[$name] ) )
				{
					unset( $this->name_index[$name] );
				}
			}
		}
	}
	/**
	 * @desc 释放
	 */
	protected function __free()
	{
		$this->__clear();
		unset( $this->list );
		unset( $this->name_index );
		unset( $this->index_name );
	}
	/**
	 * @desc 清空
	 */
	protected function __clear()
	{
		for ( $i = 0; $i < $this->count(); $i++ )
		{
			$this->__delete( $i );
		}
		$this->list = array();
		$this->name_index = array();
		$this->index_name = array();
	}
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		$this->list = array();
		$this->name_index = array();
		$this->index_name = array();
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		$this->__free();
	}
	/**
	 * @desc 添加对象
	 */
	protected function __add( $object, $name )
	{
		if ( isset( $this->name_index[$name] ) )
		{
			return false;
		}
		$key = $this->count();
		$this->list[] = $object;
		$this->index_name[$key] = $name;
		$this->name_index[$name] = $key;
		return $key;
	}
	/**
	 * @desc 添加引用
	 */
	protected function __addPointer( &$object, $name )
	{
		if ( isset( $this->name_index[$name] ) )
		{
			return false;
		}
		$key = $this->count();
		$this->list[] = &$object;
		$this->index_name[$key] = $name;
		$this->name_index[$name] = $key;
		return $key;
	}
	/**
	 * @desc 根据索引获取对象的引用
	 * @param integer $key 索引
	 */
	protected function &__getByKey( $key )
	{
		$result = false;
		if ( isset( $this->list[$key] ) )
		{
			$result = &$this->list[$key];
		}
		return $result;
	}
	/**
	 * @desc 根据名称获取对象的引用
	 * @param string $name 名称
	 */
	protected function &__getByName( $name )
	{
		$result = false;
		$key = $this->__getKeyByName( $name );
		if ( $key !== false )
		{
			$result = &$this->list[$key];
		}
		return $result;
	}
	/**
	 * @desc 根据名称获取索引
	 */
	protected function __getKeyByName( $name )
	{
		$result = false;
		if ( isset( $this->name_index[$name] ) )
		{
			$key = $this->name_index[$name];
			if ( isset( $this->list[$key] ) )
			{
				$result = $key;
			}
		}
		return $result;
	}
	/**
	 * @desc 根据索引删除对象
	 * @param integer $key
	 */
	protected function __delByKey( $key )
	{
		$this->__delete( $key );
	}
	/**
	 * @desc 根据名称删除对象
	 */
	protected function __delByName( $name )
	{
		if ( isset( $this->name_index[$name] ) )
		{
			$key = $this->name_index[$name];
			$this->__delete( $key );
		}
	}
	/**
	 * @desc 是否为空
	 */
	public function isEmpty()
	{
		return $this->count() === 0;
	}
	/**
	 * @desc 获取队列本体
	 */
	public function getList()
	{
		return $this->list;
	}
	/**
	 * @desc 获取队列数量
	 */
	public function count()
	{
		return count( $this->list );
	}
	/**
	 * @desc 获取keys数组
	 */
	public function getKeys()
	{
		return array_keys( $this->list );
	}
	/**
	 * @desc 清空
	 */
	public function clear()
	{
		$this->__clear();
	}	
}