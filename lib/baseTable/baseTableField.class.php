<?php
/**
 * @desc 字段对象-基类
 * @author gkl
 * @since 20131226
 */
abstract class baseTableField extends baseTableChildElement
{
	/**
	 * @desc 字段类别
	 * @var string
	 */
	protected $type;
	/**
	 * @desc 字段长度
	 * @var integer
	 */
	protected $len;
	/**
	 * @desc 字段精度
	 * @var integer
	 */
	protected $precision;
	/**
	 * @desc 默认值
	 */
	protected $default;
	/**
	 * @desc 是否可为空
	 */
	protected $not_null;
	/**
	 * @desc 是否为主键
	 * @var bool
	 */
	protected $is_primary;
	/**
	 * @desc 字段比较
	 * @param baseTableFieldOracle $compare 要比较的字段
	 * @return bool 是否不同
	 */
	protected function __compare( &$compare )
	{
		return false;
	}
	/**
	 * @desc 设置字段类别、长度和精度
	 */
	abstract public function setTypeLenPrec( $type, $len = false, $prec = false, $not_null = false, $default = null, $is_primary = false );
	/**
	 * @desc 构造函数
	*/
	public function __construct( $table, $name )
	{
		parent::__construct( $table, $name );
		$this->type = false;
		$this->len = false;
		$this->precision = false;
		$this->default = null;
		$this->not_null = false;
		$this->is_primary = false;
	}
	/**
	 * @desc 获取字段属性
	 */
	public function getFieldProp()
	{
		$default = is_null( $this->default ) ? '' : $this->default;
		return $this->name . '|' . $this->type . '|' . $this->len . '|' . $this->precision . '|' . $default . '|' . $this->not_null . '|' . $this->is_primary;
	}
	/**
	 * @desc 打印出字段属性
	 */
	public function printFieldProp()
	{
		echo $this->getFieldProp();
	}
	/**
	 * @desc 获取类别
	 */
	public function getType()
	{
		return $this->type;
	}
	/**
	 * @desc 获取长度
	 */
	public function getLen()
	{
		return $this->len;
	}
	/**
	 * @desc 获取精度
	 */
	public function getPrec()
	{
		return $this->precision;
	}
	/**
	 * @desc 获取是否不能为空
	 */
	public function getNotNull()
	{
		return $this->not_null;
	}
	/**
	 * @desc 获取默认
	 */
	public function getDefault()
	{
		return $this->default;
	}
	/**
	 * @desc 获取是否主键
	 */
	public function getIsPrimary()
	{
		return $this->is_primary;
	}
	/**
	 * @desc 设置默认值
	 */
	public function setDefault( $value )
	{
		$this->default = $value;
	}
	/**
	 * @desc 设置是否主键
	 */
	public function setIsPrimary( $value )
	{
		$this->is_primary = $value;
	}
	/**
	 * @desc 设置是否不能为空
	 */
	public function setNotNull( $value )
	{
		$this->not_null = $value;
	}
	/**
	 * @desc 字段比较
	 * @param baseTableFieldOracle $compare 要比较的字段
	 */
	public function compare( &$compare )
	{
		return $this->__compare( $compare );
	}
	/**
	 * @desc 获取字段默认值的字符串
	 * 例如 default 0.00
	 * @return string
	 */
	public function getDefaultStr()
	{
		return '';
	}
	/**
	 * @desc 获取字段类型加长度的字符串
	 * 例如 number(10,2)
	 * @return string
	 */
	public function getTypeLenStr()
	{
		return '';
	}
}