<?php
/**
 * @desc oracle表对象-基类
 * @author gkl
 * @since 20131231
 */
class baseTableOracle extends baseTable
{
	/**
	 * @desc 数据库连接对象
	 * @var baseModelOci
	 */
	protected $conn_obj;
	/**
	 * @desc 生成创建表的sql
	 */
	protected function __createTableSql()
	{
		$sql = "
				create table {$this->getName()} (
		";
		if ( $this->fields->isEmpty() )
		{
			return false;
		}
		$keys = $this->fields->getKeys();
		$sql_field_arr = array();
		foreach ( $keys as $key )
		{
			$field = $this->fields->getByKey( $key );
			if ( $field !== false )
			{
				$sql_field = "{$field->getName()} {$field->getType()}";
				if ( $field->getLen() !== false )
				{
					$sql_field .= "({$field->getLen()}";
					if ( $field->getPrec() !== false )
					{
						$sql_field .= ",{$field->getPrec()}";
					}
					$sql_field .= ")";
				}
				$sql_field .= ' ';
				if ( !is_null( $field->getDefault() ) && $field->getType() !== 'date' )
				{
					if ( $field->getType() === 'number' )
					{
						$sql_field .= "default {$field->getDefault()} ";
					}
					else
					{
						$sql_field .= "default '{$field->getDefault()}' ";
					}
				}
				// 除了主键全部可为空
				if ( $field->getIsPrimary() === true )
				{
					$sql_field .= 'NOT NULL';
				}
				$sql_field_arr[] = $sql_field;
			}
		}
		$sql .= implode( ',', $sql_field_arr ) . ' )';
		return $sql;
	}
	/**
	 * @desc 创建索引sql语句
	 * @return array
	 */
	protected function __createIndexSql()
	{
		$sql_arr = array();
		$keys = $this->indexs->getKeys();
		foreach ( $keys as $key )
		{
			$index = $this->indexs->getByKey( $key );
			$index_name = $index->getName();
			$index_fields_str = $index->getIndexFieldsStr();
			$sql_arr[] = 	"create index {$index_name} on {$this->getName()} ($index_fields_str)";
		}
		return $sql_arr;
	}
	/**
	 * @desc 创建主键sql语句
	 * @return string 
	 */
	protected function __createPrimarySql()
	{
		$field = $this->primary->get();
		$sql = "
				alter table {$this->getName()} add constraint pk_{$this->getName()}_1 primary key ({$field->getName()})
		";
		unset( $field );
		return $sql;
	}
	/**
	 * @desc 初始化主键约束
	 * @since 20140106
	 * @author gkl
	 */
	protected function __initPrimary()
	{
		if ( is_null( $this->primary->get() ) )
		{
			$table_name = strtoupper( $this->name );
			$sql = "
					select
						column_name from user_constraints c, user_cons_columns col
					where
						c.constraint_name = col.constraint_name
						and c.constraint_type = " . baseModelOci::OCC . "0
						and c.table_name = " . baseModelOci::OCC  . "1
			";
			$params = array(
				'P',
				$table_name,
			);
			$res = $this->conn_obj->getRow( $sql, $params );
			if ( $res !== false )
			{
				$primary_name = strtolower( $this->conn_obj->getValue( $res, 'column_name' ) );
				$primary_field = &$this->getFieldList()->getByName( $primary_name );
				if ( !is_null( $primary_field ) )
				{
					$this->primary->set( $primary_field );
				}
				unset( $primary_field );
			}
		}
		return true;
	}
	/**
	 * @since 20131226 覆盖父类虚拟化 gkl
	 * @since 20140106 增加实际逻辑 gkl
	 */
	protected function __initFields()
	{
		if ( $this->fields->isEmpty() )
		{
			$table_name = strtoupper( $this->name );
			$sql ="
				select column_name, data_type, data_length, data_precision, data_scale, nullable, data_default
				from user_tab_columns where table_name = " . baseModelOci::OCC . "0
			";
			$params = array(
				$table_name,
			);
			$res = $this->conn_obj->getAll( $sql, $params );
			if ( !empty( $res ) )
			{
				foreach ( $res as $v )
				{
					$field_name = strtolower( $this->conn_obj->getValue( $v, 'column_name' ) );
					$field_type = strtolower( $this->conn_obj->getValue( $v, 'data_type' ) );
					if ( $field_type === baseFieldType::FT_ORA_NUMBER )
					{
						$field_len = $this->conn_obj->getValue( $v, 'data_precision' );
						$field_prec = $this->conn_obj->getValue( $v, 'data_scale' );
					}
					else
					{
						$field_len = $this->conn_obj->getValue( $v, 'data_length' );
						$field_prec = $this->conn_obj->getValue( $v, 'data_scale' );
						$field_prec = is_null( $field_prec ) ? false : $field_prec;
					}
					$field_not_null = $this->conn_obj->getValue( $v, 'nullable' ) == 'N' ? true : false;
					$field_default = trim( $this->conn_obj->getValue( $v, 'data_default' ) );
					$field_default = $field_default == "''" ? null : $field_default;
					if ( in_array( $field_type, array(
							baseFieldType::FT_ORA_DATE,
							baseFieldType::FT_ORA_NUMBER,
						) ) and $field_default == '' )
					{
						$field_default = null;
					}
					$field_is_primary = false;
					$field_obj = new baseTableFieldOracle( $this,  $field_name );
					$res = $field_obj->setTypeLenPrec( $field_type, $field_len, $field_prec, $field_not_null, $field_default, $field_is_primary );
					if ( $res === false )
					{
						return false;
					}
					$this->fields->add( $field_obj );
					unset( $field_obj );
				}
			}
		}
		return true;
	}
	/**
	 * @desc 初始化索引 
	 */
	protected function __initIndexs()
	{
		$res = $this->__initPrimary();
		if ( $res === false )
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 是否存在表
	 */
	protected function __existsTable()
	{
		$table_name = $this->name;
		return $this->conn_obj->exists_table( $table_name );
	}
	/**
	 * @desc 删除表
	 */
	protected function __dropTable()
	{
		return $this->conn_obj->dropTable( $this->getName() );
	}
	/**
	 * @desc 创建表
	 */
	protected function __createTable()
	{
		$sql = $this->__createTableSql();
		$res = $this->conn_obj->exec( $sql, array(), false, $err_no, $err_msg );
		if ( $res === false )
		{
			if  ( !in_array( $err_no, array(
					'955', // 已存在该名称表
				) ) )
			{
				return false;
			}
		}
		$res = $this->__createIndex();
		if ( $res === false )
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 创建索引和主键约束
	 */
	protected function __createIndex()
	{
		$sql_arr = $this->__createIndexSql();
		foreach ( $sql_arr as $sql )
		{
			$err_no = '';
			$err_msg = '';
			$res = $this->conn_obj->exec( $sql, array(), false, $err_no, $err_msg );
			if ( $res === false )
			{
				if ( in_array( $err_no, array(
						'955', // 已存在该名称索引
						'1408', // 已存在该字段索引
					) ) )
				{
					continue;	
				}
				else
				{
					return false;
				}
			}
		}
		$sql = $this->__createPrimarySql();
		$res = $this->conn_obj->exec( $sql, array(), false, $err_no, $err_msg );
		if ( $res === false )
		{
			if ( !in_array( $err_no, array(
					'955', // 已存在该名称主键
					'2260', // 已存在该字段主键约束
				) ) )
			{
				return false;
			}
		}
		return true;
	}
	/**
	 * @see baseTable::__getLastRawupdatetime()
	 */
	protected function __getLastRawupdatetime()
	{
		$raw_update_time = false;
		$field = strtoupper( baseDbExchange::fieldnameMyToOra( 'raw_update_time' ) );
		$table_name = strtoupper( $this->name );
		$sql = "
			select to_char(max({$field}),'yyyy-mm-dd HH24:MI:SS') as max from {$table_name}
		";
		$res = $this->conn_obj->getRow( $sql );
		if ( $res !== false )
		{
			$raw_update_time = $this->conn_obj->getValue( $res, 'max' );
			$raw_update_time = is_null( $raw_update_time ) ? false : $raw_update_time;
		}
		return $raw_update_time;
	}
	/**
	 * @see baseTable::__saveData()
	 */
	protected function __saveData( &$datas )
	{
		$ora_datas = baseDbExchange::collectionMyToOra( $datas );
		$keys = $ora_datas->getKeys();
		$ins_arr = array();
		$up_arr = array();
		$up_params = array();
		$up_occ = '?';
		$ins_sql = "INSERT INTO {$this->getName()} ";
		$fields_arr = $this->fields->getFieldsArr();
		$ins_sql .= "(" . strtoupper( implode( ',', $fields_arr ) ) . ") ";		
		foreach ( $keys as $key )
		{
			$rec = &$ora_datas->getByKey( $key );
			$fields = $this->fields;
			if ( !$this->__existsData( $rec ) )
			{
				$ins_arr[] = $rec->createSql( $fields );
			}
			else
			{
				$params = array();
				$key = count( $up_arr );				
				$up_arr[$key] =$rec->createUpSql( $this->name, $params, $fields, $up_occ );
				$up_params[$key] = $params;
				unset( $params );
			}
		}
		if ( !empty( $ins_arr ) )
		{
			$ins_sql .= implode( ' UNION ', $ins_arr );
			$res = $this->conn_obj->exec( $ins_sql );
			if ( $res === false)
			{
				return false;
			}
		}
		if ( !empty( $up_arr ) )
		{
			foreach ( $up_arr as $key => $up_sql )
			{
				$params = $up_params[$key];
				$dst_occ = baseModelOci::OCC;
				$up_sql = $this->__replaceOcc( $up_sql, $up_occ, $dst_occ );				
				$res = $this->conn_obj->exec( $up_sql, $params );
				if ( $res <= 0 )
				{
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * @see baseTable::__isChange()
	 */
	protected function __isChange( &$compare )
	{
		$now_fields = &$compare->getFieldList();
		return $this->fields->compare( $now_fields );
	}
	/**
	 * @see baseTable::__getNow()
	 * @return baseTableOracle
	 */
	protected function &__getNow( &$conn_obj, $name )
	{
		$now = new baseTableOracle( $conn_obj, $name );
		return $now;
	}
	/**
	 * @see baseTable::__modify()
	 */
	protected function __modify( &$compare )
	{
		$src_fields = &$compare->getFieldList();
		$dst_fields = &$this->getFieldList();
		if ( !$dst_fields->isEmpty() )
		{
			$keys = $dst_fields->getKeys();
			foreach ( $keys as $key )
			{
				$dst_field = &$dst_fields->getByKey( $key );
				$src_field = &$src_fields->getByName( $dst_field->getName() );
				if ( $src_field  !== false )
				{
					$isChange = $dst_field->compare( $src_field );
					if ( $isChange === false )
					{
						continue;
					}
					else
					{
						$sql = "alter table {$this->name} modify {$dst_field->getName()} {$dst_field->getTypeLenStr()} {$dst_field->getDefaultStr()}";
						$err_no = '';
						$err_msg = '';
						$res = $this->conn_obj->exec( $sql, array(), false,  $err_no, $err_msg );
						if ( $res === false )
						{
							if  ( !in_array( $err_no, array(
									'1440', // 要减小精度或标度, 则要修改的列必须为空
								) ) )
							{
								return false;
							}
						}
					}
				}
				else
				{
					$sql = "alter table {$this->name} add {$dst_field->getName()} {$dst_field->getTypeLenStr()} {$dst_field->getDefaultStr()}";					
					$res = $this->conn_obj->exec( $sql );
					if ( $res === false )
					{
						return false;
					}
				}
				unset( $dst_field );
				unset( $src_field );
			}
		}
		unset( $src_fields );
		unset( $dst_fields );
		return true;
	}
	/**
	 * @see baseTable::__existsData()
	 * @param baseRecordOracle $rec
	 */
	protected function __existsData( &$rec )
	{
		$primary_field = $this->primary->get();
		if ( is_null( $primary_field ) )
		{
			return false;
		}
		$primary_field_name = $primary_field->getName();
		$value = $rec->getByField( $primary_field_name );
		if ( is_null( $value ) )
		{
			return false;
		}
		$table_name = strtoupper( $this->getName() );
		return $this->conn_obj->recordExists( $table_name, $primary_field_name, $value );
	}
	/**
	 * @see baseTable::__getDataByCond()
	 */
	protected function &__getDataByCond( $limit = 0, $primary_begin = false, $primary_field = '' )
	{
		if ( $primary_field == '' )
		{
			$primary_name = $this->primary->get()->getName();
		}
		else
		{
			$primary_name = $primary_field;
		}
		if ( $primary_begin !== false )
		{
			$this->search_cond->add( $primary_name, '>', $primary_begin );
		}
		$params = array();
		$occ = '?';
		$where = $this->search_cond->createSql( $params, $occ );
		$where = $where === false ? '' : "WHERE {$where} ";
		if ( $limit > 0 )
		{
			$this->select[] = "ROWNUM as RN";
		}
		if ( !empty( $this->select ) )
		{
			$select = "SELECT " . implode( ', ', $this->select ) . " ";
		}
		else
		{
			$fields_arr = $this->fields->getFieldsArr();
			$select = "SELECT " . implode( ', ', $fields_arr ) . " ";
			unset( $fields_arr );
		}
		$from = "FROM {$this->name} ";
		if ( !empty( $this->order_by ) )
		{
			$order_by = 'ORDER BY ' . implode( ',', $this->order_by ) . ' ';
		}
		else
		{
			$order_by = '';
		}
		if ( !empty( $this->group_by ) )
		{
			$group_by = 'GROUP BY ' . implode( ', ', $this->group_by ) . ' ';
		}
		else
		{
			$group_by = '';
		}
		if ( $limit > 0 )
		{
			$sql = "SELECT a.* FROM ({$select}{$from}{$where}{$group_by}{$order_by}) a WHERE a.RN <= {$occ}";
			$params[] = $limit;
		}
		else
		{
			$sql = "{$select}{$from}{$where}{$group_by}{$order_by}";
		}
		$ora_occ = baseModelOci::OCC;
		$sql = $this->__replaceOcc($sql, $occ, $ora_occ );
		$res = $this->conn_obj->getAll( $sql, $params, false );
		$this->collection->clear();
		if ( !empty( $res ) )
		{
			foreach ( $res as $data )
			{
				$rec = new baseRecord();
				$rec->setData( $data );
				$rec->setPrimary( $primary_name );
				$this->collection->add( $rec );
				unset( $rec );
			}
		}
		return $this->collection;
	}
	/**
	 * @see baseTable::__replaceOcc()
	 */
	protected function __replaceOcc( $sql,  $src_occ, $dst_occ, $autoincrease = true )
	{
		$index = 0;
		do
		{
			$next = false;
			$found = preg_match( '/\\' . $src_occ . '/', $sql );
			if ( $found > 0 )
			{
				$replace = $dst_occ;
				if ( $autoincrease === true )
				{
					$replace .= $index;
				}
				$sql = preg_replace( '/\\' . $src_occ . '/', $replace, $sql, 1 );
				$next = true;
				$index++;
			}
		}
		while ( $next );
		return $sql;
	}
	/**
	 * @desc 创建mysql表名的表对象
	 * @param string $table_name mysql表名
	 * @return baseTableOracle
	 */
	public static function newByTablename( &$conn_obj, $table_name )
	{		
		$dst_table_name = baseDbExchange::tablenameMyToOra( $table_name );
		$table = new baseTableOracle( $conn_obj, $dst_table_name );
		$table->setSrcName( $table_name );
		return $table;
	}
}