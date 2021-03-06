<?php
/**
 * @desc 表字段对象Oracle-基类
 * @author gkl
 * @since 20131231
 */
class baseTableFieldOracle extends baseTableField
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
		if ( in_array( $type,  baseFieldType::$FT_ORA_ARR ) )
		{
			if ( !in_array( $type, baseFieldType::$FT_ORA_NO_LEN_ARR ) && $len === false )
			{
				return false;
			}
			else if ( in_array( $type, baseFieldType::$FT_ORA_NO_LEN_ARR ) )
			{
				$len = false;
			}
			if ( in_array( $type, baseFieldType::$FT_ORA_NEED_PREC_ARR  ) && $prec === false )
			{
				return false;
			}
			$this->type = $type;
			$this->len = $len;
			$this->precision = $prec;
		}
		return true;
	}
	/**
	 * @see baseTableField::__compare($compare)
	 */
	protected function __compare( &$compare )
	{
		if ( $this->type === baseFieldType::FT_ORA_DATE )
		{
			$this->setDefault( null );
		}
		if ( $this->is_primary === true )
		{
			$this->setNotNull( true );
		}
		else
		{
			$this->setNotNull( false );
		}
		if ( in_array( $this->type, array(
				baseFieldType::FT_ORA_CHAR,
				baseFieldType::FT_ORA_VARCHAR2,
				baseFieldType::FT_ORA_CLOB,
			) ) && is_null( $this->default ) )
		{
			$this->setDefault( '' ); 
		}
		$prop_1 = $this->getFieldProp();
		$prop_2 = $compare->getFieldProp();
		$res = $prop_1 !== $prop_2;
		if ( $res === true )
		{
			var_dump( $prop_1 );
			var_dump( $prop_2 );
			// 当{需要修改的长度}比{真实的长度}小时，不做修改
			if ( $this->getLen() < $compare->getLen() )
			{
				$res = false;
			}
		}
		return $res; 
	}
	/**
	 * @see baseTableField::getTypeLenStr()
	 */
	public function getTypeLenStr()
	{
		$field_type = $this->type;
		if ( $this->len !== false )
		{
			$field_type .= '(' . $this->len;
			if ( $this->precision !== false )
			{
				$field_type .= ',' . $this->precision;
			}
			$field_type .= ')';
		}
		return $field_type;
	}
	/**
	 * @see baseTableField::getDefaultStr()
	 */
	public function getDefaultStr()
	{
		$default = $this->default;
		if ( !is_null( $default ) && $default != '' )
		{
			$default = "default {$default}";		
		}
		else
		{
			$default = '';
		}
		return $default;
	}
}
