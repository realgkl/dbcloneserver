<?php
/**
 * @desc 数据库转换类
 * @autho gkl
 * @since 20131231
 */
class baseDbExchange
{
	/**
	 * @desc oracle保留字
	 */
	protected $ora_reserved = array(
			'id',
			'uid',
			'number',
			'resource',
			'sort',
			'child',
			'name',
			'initial',
			'desc',
			'type',
			'level',
			'key',
	);
	/**
	 * @desc 名称mysql转换成oracle
	 * @param string $src
	 * @param string $add 保留字段的前缀
	*/
	protected static function nameMyToOra( $src, $add = '' )
	{
		if ( preg_match( "/^{$add}\_/i", $src ) <= 0 )
		{
			$src = $add . '_' . $src;
		}
		$len = strlen( $src );
		if ( $len > 30 )
		{
			$src = $add . '_' . substr( md5( $src ), 0, 10 );
		}
		// 去异常字符，比如空格
		$src = preg_replace( '/[\s]/i', '_', $src );
		return $src;
	}
	/**
	 * @desc 表名mysql转换成oracle
	 * @param string $src mysql表名
	 * @return string
	 */
	public static function tablenameMyToOra( $src )
	{
		return self::nameMyToOra( $src, 't' );
	}
	/**
	 * @desc 字段名mysql转换成oracle
	 * @param string $src mysql字段名
	 * @return string
	 */
	public static function fieldnameMyToOra( $src )
	{
		return self::nameMyToOra( $src, 'f' );
	}
	/**
	 * @desc 字段mysql转换成oracle
	 * @param baseTableFieldMysql $src
	 * @param baseTableOracle $dst_table
	 * @return baseTableFieldOracle
	 */
	public static function fieldMyToOra( $src, &$dst_table )
	{
		$name			= self::fieldnameMyToOra( $src->getName() );
		$type			= $src->getType();
		$len				= $src->getLen();
		$prec			= $src->getPrec();
		$not_null		= $src->getNotNull();
		$default		= $src->getDefault();
		$is_primary	= $src->getIsPrimary();
		switch ( $type )
		{
			case baseFieldType::FT_MY_TINYINT:
				$type = baseFieldType::FT_ORA_NUMBER;
				$len = 3;
				$prec = 0;
				break;
			case baseFieldType::FT_MY_SMALLINT:
				$type = baseFieldType::FT_ORA_NUMBER;
				$len = 5;
				$prec = 0;
				break;
			case baseFieldType::FT_MY_MEDIUMINT:
				$type = baseFieldType::FT_ORA_NUMBER;
				$len = 8;
				$prec = 0;
				break;
			case baseFieldType::FT_MY_INT:
				$type = baseFieldType::FT_ORA_NUMBER;
				$len = 10;
				$prec = 0;
				break;
			case baseFieldType::FT_MY_BIGINT:
				$type = baseFieldType::FT_ORA_NUMBER;
				$len = 20;
				$prec = 0;
				break;
			case baseFieldType::FT_MY_DECIMAL:
				$type = baseFieldType::FT_ORA_NUMBER;
				break;
			case baseFieldType::FT_MY_FLOAT:
			case baseFieldType::FT_MY_DOUBLE:
				$type = baseFieldType::FT_ORA_FLOAT;
				$len = 24;
				$prec = false;
				break;
			case baseFieldType::FT_MY_DATE:
			case baseFieldType::FT_MY_DATETIME:
			case baseFieldType::FT_MY_TIMESTAMP:
			case baseFieldType::FT_MY_TIME:
			case baseFieldType::FT_MY_YEAR:
				$type = baseFieldType::FT_ORA_DATE;
				$len = false;
				$prec = false;
				break;
			case baseFieldType::FT_MY_CHAR:
				$type = baseFieldType::FT_ORA_CHAR;
				$prec = false;
				break;
			case baseFieldType::FT_MY_VARCHAR:
				if ( $len > 4000 )
				{
					$type = baseFieldType::FT_ORA_CLOB;
					$len = false;
					$prec = false;
				}
				else
				{
					$type = baseFieldType::FT_ORA_VARCHAR2;
					$match = preg_match( '/\'/', $default );
					if ( $match == 0 )
					{
						if ( $default != '' )
						{
							$default = "'{$default}'";
						}						
					}
					$prec = false;
				}
				break;
			case baseFieldType::FT_MY_TINYTEXT:
			case baseFieldType::FT_MY_TEXT:
			case baseFieldType::FT_MY_MEDIUMTEXT:
			case baseFieldType::FT_MY_LONGTEXT:
				$type = baseFieldType::FT_ORA_CLOB;
				$len = false;
				$prec = false;
				break;
			case baseFieldType::FT_MY_BINARY:
			case baseFieldType::FT_MY_VARBINARY:
				$type = baseFieldType::FT_ORA_RAW;
				$prec = false;
				break;
			default:
				return false;
				break;
		}
		$field = new baseTableFieldOracle( $dst_table, $name );
		$field->setTypeLenPrec( $type, $len, $prec, $not_null, $default, $is_primary );
		return $field;
	}
	/**
	 * @desc 索引mysql转换成oracle
	 * @param baseTableIndex $src
	 * @param baseTableOracle $dst_table
	 * @return baseTableIndex
	 */
	public static function indexMyToOra( $src, $dst_table )
	{
		$dst_index_key = $dst_table->getIndexList()->count() + 1;
		$dst_index_name = "idx_{$dst_table->getName()}_{$dst_index_key}";
		$dst_index = new baseTableIndex( $dst_table, $dst_index_name );
		$src_index_fields = $src->getFieldList();
		$keys = $src_index_fields->getKeys();
		foreach ( $keys as $key )
		{
			$src_field = $src_index_fields->getByKey( $key );
			$dst_field_name = self::fieldnameMyToOra( $src_field->getName() );
			$dst_field = &$dst_table->getFieldList()->getByName($dst_field_name );
			if ( $dst_field !== false )
			{
				$dst_index->addIndexField( $dst_field );
			}
			unset( $dst_field );
			unset( $src_field );
		}
		unset( $src_index_fields );
		return $dst_index;
	}
	/**
	 * @desc 主键mysql转换成oracle
	 * @param baseTablePrimary $src
	 * @param baseTableOracle $dst_table
	 * @return bool
	 */
	public static function primaryMyToOra( $src, $dst_table )
	{
		$src_field_name = $src->get()->getName();
		$dst_field_name = self::fieldnameMyToOra( $src_field_name );
		$dst_field = &$dst_table->getFieldList()->getByName( $dst_field_name );
		if ( $dst_field !== false )
		{
			$dst_table->setPrimary( $dst_field );
			return true;
		}
		return false;
	}
	/**
	 * @desc 数据mysql转换成oracle
	 * @param baseRecord $src
	 * @return baseRecordOracle
	 */
	public static function recordMyToOra( $src )
	{
		$dst_rec = new baseRecordOracle();
		$src_fields = $src->getFields();
		$src_primary = $src->getPirmary();
		$dst_primary = self::fieldnameMyToOra( $src_primary );
		foreach ( $src_fields as $src_field )
		{
			$dst_field = self::fieldnameMyToOra( $src_field );
			$is_primary = false;
			if ( $src_field === $src_primary )
			{
				$is_primary = true;
			}
			$dst_rec->setByField($dst_field, $src->getByField($src_field ), $is_primary );
		}
		return $dst_rec;
	}
	/**
	 * @desc 数据集mysql转换成oracle
	 * @param baseCollection $src
	 * @return baseCollection
	 */
	public static function collectionMyToOra( $src)
	{
		if ( !$src->isEmpty() )
		{
			$dst_collection = new baseCollection();
			$keys = $src->getKeys();
			foreach ( $keys as $key )
			{
				$src_record = &$src->getByKey( $key );
				$dst_record = self::recordMyToOra( $src_record );
				$dst_collection->add( $dst_record );
				unset( $src_record );
				unset( $dst_record );
			}
		}
		return $dst_collection;
	}
	/**
	 * @desc 表mysql转换成oracle
	 * @param baseTableMysql $src
	 * @param baseModelEx $conn_obj
	 * @return baseTableOracle
	 */
	public static function tableMyToOra( $src, $conn_obj )
	{
		$name = self::tablenameMyToOra( $src->getName() );
		$dst = new baseTableOracle( $conn_obj, $name );
		$src_fields = $src->getFieldList();
		if ( !$src_fields->isEmpty() )
		{
			$keys = $src_fields->getKeys();
			foreach ( $keys as $key )
			{
				$src_field = $src_fields->getByKey( $key );
				$dst_field = self::fieldMyToOra( $src_field, $dst );
				$dst->addField( $dst_field );
				unset( $dst_field );
				unset( $src_field );
			}
		}
		unset( $src_fields );
		$src_indexs = $src->getIndexList();
		if ( !$src_indexs->isEmpty() )
		{
			$keys = $src_indexs->getKeys();
			foreach ( $keys as $key )
			{
				$src_index = $src_indexs->getByKey( $key );
				$dst_index = self::indexMyToOra( $src_index, $dst );
				$dst->addIndex( $dst_index );
				unset( $dst_index );
				unset( $src_index );
			}
		}
		unset( $src_indexs );
		$src_primary = $src->getPrimary();
		self::primaryMyToOra( $src_primary, $dst );
		unset( $src_primary );
		return $dst;
	}
}