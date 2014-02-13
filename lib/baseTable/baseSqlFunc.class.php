<?php
/**
 * @desc 数据库函数类-基类
 * @author gkl
 * @since 20140213
 *
 */
abstract class baseSqlFunc
{
	/**
	 * @desc 函数名	 
	 */
	protected $name;
	/**
	 * @desc 支持的函数
	 * @var array
	 */
	protected $func_lst;
	/**
	 * @desc 是否支持
	 * @var bool
	 */
	protected $is_valid;
	/**
	 * @desc 函数参数
	 * @var array()
	 */
	protected $params;
	/**
	 * @desc 构造函数
	 * @param string $name 函数名
	 */
	public function __construct( $name, $params = array() )
	{
		$this->name = $name;
		$this->is_valid = false;
		$this->func_lst = array();
		$this->params = $params;
		$this->__valid();
	}
	/**
	 * @desc 初始化支持的函数
	 */
	abstract protected function __initFuncLst();	
	/**
	 * @desc 判断是否支持的函数
	 */
	protected function __valid()
	{
		$this->__initFuncLst();
		if ( in_array( $this->name, $this->func_lst ) )
		{
			$this->is_valid = true;
		}
	}
	/**
	 * @desc 获取是否支持
	 * @return bool
	 */
	public function isValid()
	{
		return $this->is_valid;
	}
	/**
	 * @desc 获取包含函数的sql字符串
	 * @param unknown $value 需要替换占有符的值
	 * @param array $params 函数的参数部分
	 */
	public function getFuncSql( $occ = '?' )
	{
		$sql = '';
		if ( $this->is_valid === true )
		{
			$param_str = '';
			if ( !empty( $this->params ) )
			{
				$param_str = implode( ',', $this->params );
				$sql = "{$this->name}({$occ},{$param_str})";
			}
			else
			{
				$sql = "{$this->name}({$occ})";
			}
		}
		return $sql;
	}
}
/**
 * @desc 数据库函数类mysql-基类
 * @author gkl
 * @since 20140213
 *
 */
class baseSqlFuncMy extends baseSqlFunc
{
	
}
/**
 * @desc 数据库函数类oracle-基类
 * @author gkl
 * @since 20140213
 * 
 */
class baseSqlFuncOci extends baseSqlFunc
{
	protected function __initFuncLst()
	{
		$this->func_lst[] = 'to_date';
		$this->func_lst[] = 'to_char';
	}
}