<?php
/**
 * @desc mysql表对象-基类
 * @author gkl
 * @since 20131231
 */
class baseTableMysql extends baseTable
{
	protected function __filterFieldType( $type )
	{
		// 字段类型
		$pattern = '/^([^\(\)]+)(\(([\S]+)\))*(\sunsigned)*$/i';
		$match = array();
		preg_match( $pattern, $type, $match );
		if ( empty( $match ) )
		{
			return false;
		}
		$type = isset( $match[1] ) ? $match[1] : false;
		if ( $type === false )
		{
			return false;
		}
		$type = strtolower( $type );
		$len = isset( $match[3] ) ? $match[3] : false;
		$prec = false;
		switch ( $type )
		{
			case 'decimal':
				$len_arr = explode( ',', $len );
				if ( count( $len_arr ) !== 2 )
				{
					$len = intval( $len_arr[0] );
					$prec = false;
				}
				else
				{
					$len = intval( $len_arr[0] );
					$prec = intval( $len_arr[1] );
				}
				break;
			default:
				$len = intval( $len );
				$prec = false;
				break;
		}
		return array(
				'type' => $type,
				'len' => $len,
				'prec' => $prec,
		);
	}
	/**
	 * @see baseTable::__initFields()
	 */
	protected function __initFields()
	{
		if ( $this->fields->isEmpty() )
		{
			$table_name = $this->name;
			$sql ="
					desc {$table_name}
			";
			$res = $this->conn_obj->getAll( $sql );
			if ( !empty( $res ) )
			{
				foreach ( $res as $v )
				{
					$field_name = $v['Field'];
					$field_type_arr = $this->__filterFieldType( $v['Type'] );
					$field_type = $field_type_arr['type'];
					$field_len = $field_type_arr['len'];
					$field_prec = $field_type_arr['prec'];
					if ( $field_type === false )
					{
						return false;
					}
					$field_not_null = $v['Null'] === 'YES' ? false : true;
					$field_is_primary = $v['Key'] === 'PRI' ? true : false;
					$field_default = is_null( $v['Default'] ) ? null : $v['Default'];
					$field_obj = new baseTableFieldMysql( $this,  $field_name );
					$field_obj->setTypeLenPrec( $field_type, $field_len, $field_prec, $field_not_null, $field_default, $field_is_primary );
					$this->fields->add( $field_obj );
					$field_obj = null;
					unset( $field_obj );
				}
				return true;
			}
		}
		return false;
	}
	/**
	 * @see baseTable::__initIndexs()
	 */
	protected function __initIndexs()
	{
		if ( $this->indexs->isEmpty() )
		{
			$table_name = $this->name;
			$sql = "
					show index from {$table_name}
			";
			$res = $this->conn_obj->getAll( $sql );
			if ( !empty( $res ) )
			{
				foreach ( $res as $v )
				{
					$index_name = $v['Key_name'];
					$non_unqiue = $v['Non_unique'] == 0 ? false : true;
					$index_column = $v['Column_name'];
					$is_primary = ( $index_name === 'PRIMARY' ) ? true : false;
					$field = &$this->fields->getByName( $index_column );
					if ( $is_primary )
					{
						if ( $field !== false )
						{
							$this->primary->set( $field );
						}
						else
						{
							return false;
						}
					}
					else
					{
						$index = $this->indexs->getByName( $index_name );
						if ( $index === false )
						{							
							$index = new baseTableIndex( $this, $index_name );
							$index->addIndexField( $field );
							$index->setUnique( $non_unqiue );
							$this->indexs->add( $index );
							$index = null;
							unset( $index );
						}
						else
						{
							$index->addIndexField( $field );
							$index->setUnique( $non_unqiue );
							unset( $index );
						}
					}
					unset( $field );
				}
				return true;
			}
		}
		return false;
	}
	/**
	 * @see baseTable::__existsTable()
	 */
	protected function __existsTable()
	{
		$table_name = $this->name;
		return $this->conn_obj->exists_table( $table_name );
	}
	/**
	 * @see baseTable::__getData()
	 * @since 20140110 获取数据实际逻辑 gkl
	 */
	protected function &__getData( $raw_update_time, $limit = 0, $primary_begin = 0 )
	{
		$fields_arr_keys = $this->fields->getKeys();
		$field_primary = $this->primary->get()->getName();
		$where_arr = array();
		$params = array();
		
		$where_arr[] = "`{$field_primary}` > ?";
		$params[] = $primary_begin;
		$date_time_pattern = '/^([1-9][0-9][0-9][0-9])\-([0][1-9]|1[0-2])\-([0][1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3])\:([0-5][0-9])\:([0-5][0-9])$/i';
		if ( $raw_update_time !== false &&
			preg_match( $date_time_pattern, $raw_update_time ) > 0
		)
		{
			$where_arr[] = "`raw_update_time` > ?";
			$params[] = $raw_update_time;
		}
		$sql = 'select ';
		$select_arr = array();
		foreach ( $fields_arr_keys as $key )
		{
			$field = $this->fields->getByKey( $key );			
			$select_arr[] = $field->getName();
			unset( $field );
		}
		$where = implode( ' AND ', $where_arr );
		$sql .= implode( ',', $select_arr );
		$sql .= " from `{$this->getName()}` where {$where} order by `{$field_primary}` limit {$limit}";
		$res = $this->conn_obj->getAll( $sql, $params );
		$this->collection->clear();
		foreach ( $res as $data )
		{
			$rec = new baseRecord();
			$rec->setData( $data, $field_primary );
			$this->collection->add( $rec );
			unset( $rec );
		}
		return $this->collection;
	}
	/**
	 * @desc 转换成oracle表
	 * @param baseModelEx $conn_obj
	 */
	public function toOracle( $conn_obj )
	{
		return baseDbExchange::tableMyToOra( $this, $conn_obj );
	}
}