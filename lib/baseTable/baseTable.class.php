<?php
/**
 * @desc 数据库表对象-基类
 * @author gkl
 * @since 20131226
 */
class baseTable extends baseTableElement
{
	/**
	 * @desc 原名称
	 * @var string
	 */
	protected $src_name;
	/**
	 * @desc 主键
	 * @var baseTablePrimary
	 */
	protected $primary;
	/**
	 * @desc 索引
	 * @var baseTableIndexList
	 */
	protected $indexs;
	/**
	 * @desc 字段队列
	 * @var baseTableFieldList
	 */
	protected $fields;
	/**
	 * @desc 数据集合
	 * @var baseCollection
	 */
	protected $collection;
	/**
	 * @desc 搜索条件对象
	 */
	protected $search_cond;
	/**
	 * @desc 获取字段
	 */
	protected $select;
	/**
	 * @desc 分组字段
	 */
	protected $group_by;
	/**
	 * @desc 排序字段
	 */
	protected $order_by;
	/**
	 * @desc 构造函数
	 * @param baseModelEx $conn_obj
	 * @param string $name 表名
	 */
	public function __construct( &$conn_obj, $name, $src_name = '' )
	{
		parent::__construct( $conn_obj, $name );
		$this->fields = new baseTableFieldList( $this );
		$this->indexs = new baseTableIndexList( $this );
		$this->primary = new baseTablePrimary( $this );
		$this->src_name = $src_name;
		$this->collection = new baseCollection();
		$this->search_cond = new baseSearchCond();
		$this->select = array();
		$this->group_by = array();
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		unset( $this->group_by );
		unset( $this->select );
		unset( $this->search_cond );
		unset( $this->collection );
		unset( $this->fields );
		unset( $this->indexs );
		unset( $this->primary );
		parent::__destruct();
	}
	/**
	 * @desc 获取conn_obj
	 * @return baseModelEx  数据库模块
	 */
	public function getConnObj()
	{
		return $this->conn_obj;
	}
	/**
	 * @desc 初始化字段
	 */
	protected function __initFields()
	{
		return false;
	}
	/**
	 * @desc 是否存在表
	 */
	protected function __existsTable()
	{
		return false;
	}
	/**
	 * @desc 删除表
	 */
	protected function __dropTable()
	{
		return false;
	}
	/**
	 * @desc 创建表
	 */
	protected function __createTable()
	{
		return false;
	}
	/**
	 * @desc 初始化索引
	 */
	protected function __initIndexs()
	{
		return false;
	}
	/**
	 * @desc 初始化主键
	 */
	protected function __initPrimary()
	{
		return false;
	}
	/**
	 * @desc 判断是否结构变动
	 * @param baseTable $compare 比较表
	 * @return bool 是否不同
	 */
	protected function __isChange( &$compare )
	{
		return false;
	}
	/**
	 * @desc 修改表结构
	 * @param baseTable $compare 比较表
	 * @return bool 是否更改成功
	 */
	protected function __modify( &$compare )
	{
		return false;
	}
	/**
	 * @desc 获取现上数据库
	 * @return baseTable
	 */
	protected function &__getNow( &$conn_obj, $name )
	{
		$now = new baseTable( $conn_obj, $name );
		return $now;
	}
	/**
	 * @desc 获取数据
	 * @param integer $limit 取数据的数量限制
	 * @param integer $primary_begin 主键开始
	 * @return baseCollection
	 */
	protected function &__getData( $raw_update_time, $end_time, $limit = 0, $primary_begin = 0 )
	{
		$this->collection->clear();
		return $this->collection;
	}
	/**
	 * @desc 获取最后更新时间
	 */
	protected function __getLastRawupdatetime()
	{
		return false;
	}
	/**
	 * @desc 保存数据
	 * @param baseCollection $datas 数据集;
	 */
	protected function __saveData( &$datas )
	{
		return false;
	}
	/**
	 * @desc 是否存在数据
	 * @param baseRecord $data
	 */
	protected function __existsData( &$data )
	{
		return false;
	}
	/**
	 * @desc 根据{条件}获取数据
	 * @param integer $limit 限制条数
	 * @param unknown $primary_begin 主键开始值
	 * @param string $primary_field 主键字段
	 */
	protected function &__getDataByCond( $limit = 0, $primary_begin = false, $primary_field = '' )
	{
		$this->collection->clear();
		return $this->collection;
	}	
	/**
	 * @desc 增加{搜索条件}
	 * @param baseSqlFunc $sql_func_1 对值1的sql函数
	 * @param baseSqlFunc $sql_func_2 对值2的sql函数
	 */
	protected function __where( $field, $opera, $value_1, $value_2 = null, &$sql_func_1 = null, &$sql_func_2 = null )
	{
		return $this->search_cond->add( $field, $opera, $value_1, $value_2, $sql_func_1, $sql_func_2 );
	}
	/**
	 * @desc 初始化
	 */
	public function init()
	{
		if ( !$this->__existsTable() )
		{
			baseCommon::__die( "表 {$this->name} 不存在。" );
		}
		if ( !$this->__initFields() || !$this->__initIndexs() )
		{
			baseCommon::__die( "表 {$this->name} 初始化出错。" );
		}
	}
	/**
	 * @desc 占位符替换
	 * @param string $sql 需要替换的sql
	 * @param string $src_occ 源占位符
	 * @param string $dst_occ 目标占位符
	 * @param bool $autoincrease 是否自增
	 */
	protected function __replaceOcc( $sql, $src_occ, $dst_occ, $autoincrease = true )
	{
		return '';
	}
	/**
	 * @desc 创建
	 * @return integer
	 * 0 创建失败
	 * 1 创建成功
	 * 2 已存在
	 */
	public function create()
	{
		if ( $this->__existsTable() )
		{
			$compare = $this->__getNow( $this->conn_obj,  $this->name );
			$compare->init();
			if ( $this->__isChange( $compare ) )
			{
				$res = $this->__modify( $compare );
				if ( $res === false )
				{
					return 0;
				}
			}
			return 2;
		}
		$res= $this->__createTable();
		if ( $res === false )
		{
			return 0;
		}
		return 1;
	}
	/**
	 * @desc 获取字段列表
	 * @return baseTableFieldList
	 */
	public function &getFieldList()
	{
		return $this->fields;
	}
	/**
	 * @desc 获取索引列表
	 */
	public function getIndexList()
	{
		return $this->indexs;
	}
	/**
	 * @desc 获取主键类
	 */
	public function getPrimary()
	{
		return $this->primary;
	}
	/**
	 * @desc 插入字段
	 * @param baseTableField $field
	 */
	public function addField( $field )
	{
		return $this->fields->add( $field );
	}
	/**
	 * @desc 插入索引
	 * @param baseTableIndex $index
	 */
	public function addIndex( $index )
	{
		return $this->indexs->add( $index );
	}
	/**
	 * @desc 设置主键
	 */
	public function setPrimary( &$field )
	{
		$this->primary->set( $field );
	}
	/**
	 * @desc 删除表
	 */
	public function dropTable()
	{
		return $this->__dropTable();
	}
	/**
	 * @desc 设置原表名
	 */
	public function setSrcName( $name )
	{
		$this->src_name = $name;
	}
	/**
	 * @desc 获取原表名
	 */
	public function getSrcName()
	{
		return $this->src_name;
	}
	/**
	 * @desc 获取数据
	 */
	public function &getData( $raw_update_time, $end_time, $limit = 0, $primary_begin = 0 )
	{
		return $this->__getData( $raw_update_time, $end_time, $limit, $primary_begin );
	}
	/**
	 * @desc 获取最后更新时间
	 */
	public function getLastRawupdatetime()
	{
		return $this->__getLastRawupdatetime();
	}
	/**
	 * @desc 保存数据
	 * @param baseCollection $datas;
	 */	
	public function saveData( &$datas )	
	{
		return $this->__saveData( $datas );
	}
	/**
	 * @desc 是否存在数据
	 * @param baseRecord $data
	 */
	public function existsData( &$data )
	{
		return $this->__existsData( $data );
	}
	/**
	 * @desc 增加{搜索条件}
	 * @param string $field 字段名
	 * @param string $opera 操作符
	 * @param unknown $value_1 值1
	 * @param unknown $value_2 值2
	 * @param string $sql_func_1 对值1的sql函数
	 * @param string $sql_func_2 对值2的sql函数
	 * @param array $sql_func_param_1 函数1的参数
	 * @param array $sql_func_param_2 函数2的参数
	 */
	public function where( $field, $opera, $value_1, $value_2 = null,
			$sql_func_1 = null, $sql_func_param_1 = array(),  $sql_func_2 = null, $sql_func_param_2 = array() )
	{
		if ( !is_null( $sql_func_1 ) )
		{
			$sql_name = $sql_func_1;
			$sql_func_1 = new baseSqlFuncOci( $sql_name, $sql_func_param_1 );
			if ( $sql_func_1->isValid() === false )
			{
				$sql_func_1 = null;
			}
		}
		if ( !is_null( $sql_func_2 ) )
		{
			$sql_name = $sql_func_2;
			$sql_func_2 = new baseSqlFuncOci( $sql_name,  $sql_func_param_2 );
			if ( $sql_func_2->isValid() === false )
			{
				$sql_func_2 = null;
			}
		}
		return $this->__where( $field, $opera, $value_1, $value_2, $sql_func_1, $sql_func_2 );
	}
	/**
	 * @desc 增加{查询字段}
	 */
	public function select( $field, $func = '', $alias = '' )
	{
		$field_arr = $this->fields->getFieldsArr();
		if ( in_array( $field, $field_arr ) )
		{
			$key = count( $this->select );
			if ( in_array( $func, baseSearchCond::$func ) )
			{
				$this->select[$key] = "{$func}({$field})";
			}
			else 
			{
				$this->select[$key] = $field;
			}
			if ( $alias != '' )
			{
				$this->select[$key] = $this->select[$key] . ' as ' . $alias; 
			}
			return $key;
		}
		else if ( $field === '*' )
		{
			return true;
		}
		return false;
	}
	/**
	 * @desc 添加分组字段
	 */
	public function groupBy( $field )
	{
		$field_arr = $this->fields->getFieldsArr();
		if ( in_array( $field, $field_arr ) )
		{
			$key = count( $this->group_by );
			$this->group_by[$key] = $field;
			return $key;
		}
		return false;
	}
	/**
	 * @desc 添加排序字段
	 */
	public function orderBy( $field, $asc = 'asc' )
	{
		$field_arr = $this->fields->getFieldsArr();
		if ( in_array( $field, $field_arr ) && in_array( $asc, array( 'asc', 'desc' ) ) )
		{
			$key = count( $this->order_by );
			$this->order_by[$key] = "{$field} {$asc}";
			return $key;
		}
		return false;
	}
	/**
	 * @desc 清楚搜索条件
	 */
	public function clearSearchCond()
	{
		$this->select = array();
		$this->group_by = array();
		$this->order_by = array();
		$this->search_cond->clear();
	}
	/**
	 * @desc 清空数据集
	 */
	public function clearCollection()
	{
		$this->collection->clear();
	}
	/**
	 * @desc 根据{条件}获取数据
	 * @param integer $limit 限制条数
	 * @param unknown $primary_begin 主键开始值
	 * @return baseCollection 数据集
	 */
	public function &getDataByCond( $limit = 0, $primary_begin = false, $primary_field = '' )
	{
		return $this->__getDataByCond( $limit, $primary_begin, $primary_field );
	}
	/**
	 * @desc 判断表是否存在
	 */
	public function existsTable()
	{
		return $this->__existsTable();
	}
	/**
	 * @desc 占位符替换
	 * @param string $src_occ 源占位符
	 * @param string $dst_occ 目标占位符
	 * @param bool $autoincrease 是否自增
	 */
	public function replaceOcc( $sql, $src_occ, $dst_occ, $autoincrease = true )
	{
		return $this->__replaceOcc( $sql, $src_occ, $dst_occ, $autoincrease );
	}
}