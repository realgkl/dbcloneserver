<?php
/**
 * @desc 表字段对象Mysql-基类
 * @author gkl
 * @since 20131226
 */
class baseTableFieldMysql extends baseTableField
{
	/**
	 * @desc 设置字段类别、长度和精度
	 */
	public function setTypeLenPrec( $type, $len = false, $prec = false, $not_null = false, $default = null, $is_primary = false )
	{
		$this->type = false;
		$this->len = false;
		$this->precision = false;
		$this->default = $default;
		$this->not_null = $not_null;
		$this->is_primary = $is_primary;
		$type = strtolower( $type );
		if ( in_array( $type,  baseFieldType::$FT_MY_ARR ) )
		{
			if ( !in_array( $type, baseFieldType::$FT_MY_NO_LEN_ARR ) && $len === false )
			{
				return;
			}
			if ( in_array( $type, baseFieldType::$FT_MY_NEED_PREC_ARR  ) && $prec === false )
			{
				return;
			}
			$this->type = $type;
			$this->len = $len;
			$this->precision = $prec;
		}
	}
}