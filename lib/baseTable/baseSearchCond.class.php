<?php
/**
 * @desc 搜索条件-基类
 * @author gkl
 * @since 20140117
 */
class baseSearchCond
{
	/**
	 * @desc 搜索条件队列
	 * @var baseSearchCondLst
	 */
	protected $cond_lst;
	/**
	 * @desc 运算符
	 * @var array
	 */
	protected $opera_arr = array(
		'>',
		'<',
		'>=',
		'<=',
		'<>',
		'=',
		'is',
		'is not',
		'like',
		'not like',
		'between',
		'in',
		'not in',
	);
	/**
	 * @desc 聚合函数
	 */
	public static $func = array(
		'sum',
		'count',
	);
	
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		$this->cond_lst = new baseSearchCondLst();		
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		$this->cond_lst->clear();
		unset($this->cond_lst);
	}
	/**
	 * @desc 增加搜索条件
	 */
	public function add( $field, $opera, $value_1, $value_2 = null )
	{
		if ( $field != '' && in_array( $opera, $this->opera_arr ) && $value_1 != '' )
		{
			if ( $opera == 'between' && $value_2 == '' )
			{
				return false;
			}
			$cond_record = new baseSearchCondRecord( $field, $opera, $value_1, $value_2 );
			$key = $this->cond_lst->add( $cond_record );
			if ( $key === false )
			{
				$key = $this->cond_lst->getKeyByName( $cond_record->getKey() );
			}
			unset( $cond_record );
			return $key; 
		}
		return false;
	}
	/**
	 * @desc 是否为空
	 */
	public function isEmpty()
	{
		return $this->cond_lst->isEmpty();
	}
	/**
	 * @desc 生成sql
	 */
	public function createSql( &$params, $occ= '?' )
	{
		$params = array();
		$sql_arr = array();
		if ( !$this->cond_lst->isEmpty() )
		{
			$keys = $this->cond_lst->getKeys();
			foreach ( $keys as $key )
			{
				$cond_rec = &$this->cond_lst->getByKey( $key );
				if ( $cond_rec->getOpera() == 'between' )
				{
					$sql_arr[] = "{$cond_rec->getField()} between {$occ} and {$occ}";
					$params[] = $cond_rec->getValue1();
					$params[] = $cond_rec->getValue2();
				}
				else
				{
					$sql_arr[] = "{$cond_rec->getField()} {$cond_rec->getOpera()} {$occ}";
					$params[] = $cond_rec->getValue1();
				}
			}
			return implode( ' AND ', $sql_arr );
		}
		return false;
	}
	/**
	 * @desc 清除搜索条件
	 */
	public function clear()
	{
		$this->cond_lst->clear();
	}
	/**
	 * @desc 获取占位符
	 */
	public function getOcc()
	{
		return $this->occ;
	}
}
/**
 * @desc 搜索条件结构体-基类
 * @author gkl
 * @since 20140117
 */
class baseSearchCondRecord
{
	/**
	 * @desc 字段名
	 * @var string
	 */
	protected $field;
	/**
	 * @desc 操作符
	 * @var string
	 */
	protected $opera;
	/**
	 * @desc 值1 
	 */
	protected $value_1;
	/**
	 * @desc 值2
	 */
	protected $value_2;
	/**
	 * @desc 构造函数
	 */
	public function __construct( $field, $opera, $value_1, $value_2 )
	{
		$this->field = $field;
		$this->opera = $opera;
		$this->value_1 = $value_1;
		$value_2 = is_null( $value_2 ) ? '' : $value_2;
		$this->value_2 = $value_2;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		
	}
	/**
	 * @desc 获取条件的唯一标识
	 */
	public function getKey()
	{
		return $this->field.$this->opera.$this->value_1.$this->value_2;
	}
	/**
	 * @desc 获取字段名
	 */
	public function getField()
	{
		return $this->field;
	}
	/**
	 * @desc 获取操作符
	 */
	public function getOpera()
	{
		return $this->opera;
	}
	/**
	 * @desc 获取值1
	 */
	public function getValue1()
	{
		return $this->value_1;
	}
	/**
	 * @desc 获取值2
	 */
	public function getValue2()
	{
		return $this->value_2;
	}
}
/**
 * @desc 搜索条件队列
 * @author gkl
 * @since 20140117
 */
class baseSearchCondLst extends baseList
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
	 * @desc 添加
	 * @param baseSearchCondRecord $cond_record 搜索条件结构体
	 */
	public function add( $cond_record )
	{
		return $this->__add( $cond_record, $cond_record->getKey() );
	}
	/**
	 * @desc 根据{唯一标识}获取条件
	 * @var baseSearchCondRecord
	 */
	public function &getByName( $name )
	{
		return $this->__getByName( $name );
	}
	/**
	 * @desc 根据{索引}获取条件
	 * @return baseSearchCondRecord
	 */
	public function &getByKey( $key )
	{
		return $this->__getByKey( $key );
	}
	/**
	 * @desc 根据{唯一标识}获取索引
	 */
	public function getKeyByName( $name )
	{
		return $this->__getKeyByName( $name );
	}
}