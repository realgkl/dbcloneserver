<?php
/**
 * @desc oci数据模块基类
 * @author gkl
 * @since 20131211
 */
class baseModelOci extends baseModelEx
{
	/**
	 * @desc sql占有符
	 */
	const OCC = ':FIELD_';
	/**
	 * @desc 用占位符绑定参数
	 * @param PDOStatement $query
	 */
	protected function __useOccBindParams( $params, $query )
	{
		if ( !empty( $params ) )
		{
			foreach ( $params as $k => $value )
			{
				if ( is_string( $value ) && strlen( $value ) > 1000 )
				{
					$query->bindParam( self::OCC.$k, $value, PDO::PARAM_STR, strlen( $value ) );
				}
				else
				{
					$query->bindValue( self::OCC.$k, $value );
				}
			}
		}
		return true;
	}
	/**
	 * @desc 生成有绑定参数的sql
	 */
	protected function __createSqlByParams( $sql, $params )
	{
		if ( !empty( $params ) )
		{
			$occ_no = 0;
			foreach ( $params as $v )
			{
				$occ = self::OCC . $occ_no;
				$sql = preg_replace( '/' . $occ . '/', "'{$v}'", $sql, 1 );
				$occ_no++;
			}
		}
		return $sql;
	}
	/**
	 * @desc oracle一些类型的sql转换
	 */
	protected function __oracleSqlExchange( $type, $len, &$sql_part, &$value, $occ = '?' )
	{
		switch ( $type )
		{
			case 'DATE':
				$sql_part = "to_date({$occ},'yyyy-mm-dd HH24:MI:SS')";
				if ( $value == '0000-00-00 00:00:00' )
				{
					$value = '';
				}
				break;
			case 'VARCHAR2':
			case 'RAW':
				$str_len = strlen( $value );
				if ( $str_len > $len )
				{
					$value = substr( $value, 0, $len );
				}
				if ( $type == 'RAW' )
				{
					$sql_part = "utl_raw.cast_to_raw({$occ})";
				}
				else
				{
					$sql_part = $occ;
				}
				break;
			default:
				$sql_part = $occ;
				break;
		}
	}
	/**
	 * @desc 拼insert sql语句
	 * @param array $datas 要插入的数组
	 * @param string $table_name 表名
	 * @param array $oracle_fields oracle字段数组
	 * @param array $params 返回的参数数组
	 * @return int|string 成功返回sql语句，失败返回错误代码
	 */
	protected function __parse_insert_sql ( $datas, $table_name, $oracle_fields, &$params )
	{
		$params = array();
		if ( empty( $datas ) )
		{
			return -1;
		}
		if ( empty( $oracle_fields ) )
		{
			return -2;
		}
		$oracle_src_fields = array();
		foreach ( $oracle_fields as $field )
		{
			$oracle_src_fields[strtoupper( $field['src_field'] )] = array(
				'field'	=> strtoupper( $field['field'] ),
				'type' => $field['type'],
				'len' => $field['len'],
			);
		}
		$sql = "insert into {$table_name}\n";
		$sql_arr = array();
		$occ_no = 0;
		foreach ( $datas as $k => $data )
		{
			if ( $k === 0 )
			{
				$datas_fields = array();
			}
			$sql_data = array();
			foreach ( $data as $data_field => $data_value )
			{
				$data_field = strtoupper( $data_field );
				if ( $k === 0 )
				{
					$datas_fields[] = $oracle_src_fields[$data_field]['field'];
				}
				$cur_data = is_null( $data_value ) ? '' : $data_value;
				$cur_type = $oracle_src_fields[$data_field]['type'];
				$cur_len = $oracle_src_fields[$data_field]['len'];
				$sql_part = '';
				$occ = self::OCC . $occ_no;
				$this->__oracleSqlExchange( $cur_type, $cur_len, $sql_part, $cur_data, $occ );
				$sql_data[] = $sql_part;
				$params[] = $cur_data;
				$occ_no++;
			}
			if ( $k === 0 )
			{
				$sql .= '(' . implode( ',', $datas_fields ) . ')' . "\n";
			}
			$sql_arr[] = 'select ' . implode( ',', $sql_data ) . ' from dual';
		}
		$sql .= implode( " union\n", $sql_arr );
		return $sql;
	}
	/**
	 * @desc 拼update sql语句
	 * @param array $data 单条数据
	 * @param string $table_name 表名
	 * @param string $key_field 定位主键或唯一索引
	 * @param array $params 返回的参数数组
	 * @return int|string 成功返回sql语句，失败返回错误代码
	 */
	protected function __parse_update_sql ( $data, $table_name, $oracle_fields, $key_field, &$params )
	{
		$params = array();
		if ( empty( $data ) )
			return -1;
		if ( empty( $oracle_fields ) )
			return -2;
		$oracle_src_fields = array();
		$oracle_fields = $this->__upperDataKey( $oracle_fields );
		foreach ( $oracle_fields as $field )
		{
			$oracle_src_fields[strtoupper( $field['src_field'] )] = array(
					'field'	=> strtoupper( $field['field'] ),
					'type' => $field['type'],
					'len' => $field['len'],
			);
		}
		$datas_fields = array_keys( $data );
		foreach ( $datas_fields as &$v )
		{
			$v = strtoupper( $v );
			if ( !isset( $oracle_src_fields[$v] ) )
			{
				return -3;
			}
		}
		unset( $v );
		$key_field_arr = array();
		if ( is_array( $key_field ) && !empty( $key_field ) )
		{
			foreach ( $key_field as $v )
			{
				$v = strtoupper( $v );
				if ( !isset( $oracle_fields[$v] ) )
				{
					return -3;
				}
				$key_field_arr[] = $v;
			}
			unset( $v );
		}
		else
		{
			$key_field = strtoupper( $key_field );
			if ( !isset( $oracle_fields[$key_field] ) )
			{
				return -3;
			}
			$key_field_arr[] = $key_field;
		}
		if ( empty( $key_field_arr ) )
		{
			return -4;
		}
		$sql = "update {$table_name} set ";
		$sql_arr = array();
		$occ_no = 0;
		foreach ( $data as $field_name => $data_value )
		{
			$field_name = strtoupper( $field_name );
			if ( in_array( $field_name, $key_field_arr ) )
			{
				$data[$field_name] = $data_value;
				continue;
			}
			$cur_data = $data_value;
			$cur_type = $oracle_src_fields[$field_name]['type'];
			$cur_len = $oracle_src_fields[$field_name]['len'];
			$sql_part = '';
			$occ = self::OCC . $occ_no;
			$this->__oracleSqlExchange( $cur_type, $cur_len, $sql_part, $cur_data, $occ );
			$sql_arr[] = "{$oracle_src_fields[$field_name]['field']}={$sql_part}";
			$params[] = $cur_data;
			$occ_no++;
		}
		$sql .= implode( ', ', $sql_arr );
		$sql .= ' where ';
		$sql_arr = array();
		foreach ( $key_field_arr as $v )
		{
			$oracle_field =$oracle_fields[$v]; 
			$src_field = $oracle_field['src_field'];
			if ( !isset( $data[$src_field] ) )
			{
				return -5;
			}
			$cur_data = $data[$src_field];
			$cur_type = $oracle_field['type'];
			$cur_len = $oracle_field['len'];
			$sql_part = '';
			$occ = self::OCC . $occ_no;
			$this->__oracleSqlExchange( $cur_type, $cur_len, $sql_part, $cur_data, $occ );
			$sql_arr[] = "{$v}={$sql_part}";
			$params[] = $cur_data;
			$occ_no++;
		}
		$sql .= implode( ' AND ', $sql_arr );
		return $sql;
	}
	/**
	 * @desc 构造函数获取db
	 */
	public function __construct ()
	{
		$this->db = basePdoConnOci::connect()->getConn();
		parent::__construct();
	}
	/**
	 * @see baseModelEx::getAll()
	 */
	public function getAll ( $sql, $params = array(), $need_filter = true, $err_die = true, &$err_no ='', &$err_msg = '' )
	{
		if ( $this->show_time )
			$t = $this->diff();
		if ( $need_filter === true )
		{
			$params = $this->__filter_params( $params );
		}
		$query = $this->db->prepare( $sql );
		$this->__useOccBindParams( $params, $query );
		$res = $query->execute();
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			return $query->fetchAll();
		}
		else
		{
			$sql = $this->__createSqlByParams( $sql, $params );
			$this->__error( $sql, $err_die, $err_no, $err_msg );
		}
		return false;
	}
	/**
	 * @see baseModelEx::getRow()
	 */
	public function getRow( $sql, $params = array(), $need_filter = true, $err_die = true, &$err_no ='', &$err_msg = '' )
	{
		if ( $this->show_time )
			$t = $this->diff();
		if ( $need_filter === true )
		{
			$params = $this->__filter_params( $params );
		}
		$query = $this->db->prepare( $sql );
		$this->__useOccBindParams( $params, $query );
		$res = $query->execute();
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			return $query->fetch();
		}
		else
		{
			$sql = $this->__createSqlByParams( $sql, $params );
			$this->__error( $sql, $err_die, $err_no, $err_msg );
		}
		return false;
	}
	/**
	 * @see baseModelEx::exec()
	 */
	public function exec( $sql, $params = array(), $err_die = true, &$err_no ='', &$err_msg = '', $need_filter = true )
	{
		if ( $this->show_time )
			$t = $this->diff();
		if ( $need_filter === true )
		{
			$params = $this->__filter_params( $params );
		}
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
		$query = $this->db->prepare( $sql );
		$this->__useOccBindParams( $params, $query );
		$res = $query->execute();
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			$affact = $query->rowCount();
			/*
			if ( $affact <= 0 )
			{ 
				$sql = $this->__createSqlByParams( $sql, $params );
				echo $sql;
			}
			*/
			return $affact;
		}
		else
		{
			$sql = $this->__createSqlByParams( $sql, $params );
			$this->__error( $sql, $err_die, $err_no, $err_msg );
			return false;
		}
	}
	/**
	 * @see baseModelEx::begin()
	 */
	public function begin ()
	{
		if ( self::$trans_num == 0 )
		{
			$res = $this->db->beginTransaction();
			if ( !$res )
				$this->__error();
			self::$trans_num = 1;
		}
		return true;
	}
	/**
	 * @see baseModelEx::rollback()
	 */
	public function rollback ()
	{
		if ( self::$trans_num > 0 )
		{
			$res = $this->db->rollBack();
			if ( !$res )
				$this->__error();
			self::$trans_num = 0;
		}
		return true;
	}
	/**
	 * @see baseModelEx::commit()
	 */
	public function commit ()
	{
		if ( self::$trans_num > 0 )
		{
			$res = $this->db->commit();
			if ( !$res )
			{
				$this->__error();
			}
			self::$trans_num = 0;
		}
		return true;
	}
	/**
	 * @see baseModelEx::recordExists()
	 */
	public function recordExists( $table, $key_filed, $value )
	{
		if ( is_array( $key_filed ) && is_array( $value ) )
		{
			if ( count( $key_filed ) != count( $value ) )
			{
				return false;
			}
			$sql = "
				select 1 from $table where
			";
			$params = array();
			$sql_arr = array();
			foreach ( $key_filed as $k => $v )
			{
				$sql_arr[] = " {$v}=" . self::OCC . "{$k} ";
				$params[] = $value[$k];
			}
			$sql .= implode( ',', $sql_arr );
		}
		else
		{
			$sql = "
				select 1 from {$table} where {$key_filed} = " . self::OCC . "0
			";
			$params = array( $value );
		}
		$res = $this->getRow( $sql, $params );
		if ( $res === false )
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 超过长度的字段、表名的截取方式
	 */
	public static function subNameForMysqlToOracle( $name )
	{
		
		return substr( md5( $name ), 0, 10 );
	}
	/**
	 * @desc 获取oracle返回数组的值
	 */
	public function getValue( $res, $field )
	{
		return $res[strtoupper( $field )];
	}
	/**
	 * @desc 判断是否存在{表}
	 */
	protected function __exists_table ( $table_name )
	{
		$sql = "
				select count(*) as count from user_tables where table_name = " . self::OCC . "0
		";
		$params = array(
				strtoupper( $table_name ),
		);
		$res = $this->getRow( $sql, $params );
		if ( $res !== false && $this->getValue( $res, 'count' ) > 0 )
		{
			return true;
		}
		return false;
	}
	/**
	 * @desc 判断是否存在{自增序列}
	 */
	protected function __exists_seq( $seq_name )
	{
		$sql = "
				select count(*) as count from user_sequences where sequence_name = " . self::OCC . "0
		";
		$params = array(
			strtoupper( $seq_name ),
		);
		$res = $this->getRow( $sql, $params );
		if ( $res !== false && $this->getValue( $res, 'count' ) > 0 )
		{
			return true;
		}
		return false;
	}
	/**
	 * @desc 创建自增序列如果存在同名先删除后创建
	 */
	protected function __create_seq( $seq_name, $start = 1, $stepby = 1 )
	{
		if ( $this->__exists_seq( $seq_name ) === true )
		{
			$this->__drop_seq( $seq_name );
		}
		$sql = "
				create sequence {$seq_name} increment by {$stepby} start with {$start}
		";
		return $this->exec( $sql );
	}
	/**
	 * @desc 删除自增序列
	 */
	protected function __drop_seq( $seq_name )
	{
		$seq_name = strtoupper( $seq_name );
		$sql = "
				drop sequence {$seq_name}
		";
		return $this->exec( $sql );
	}
	/**
	 * @desc 删除表
	 */
	protected function __drop_table( $table_name )
	{
		$table_name = strtoupper( $table_name );
		$sql = "
				drop table {$table_name}
		";
		return $this->exec( $sql );
	}
	/**
	 * @desc 将data数组的key转换为大写
	 */
	protected function __upperDataKey( $data )
	{
		$result = array();
		if ( !empty( $data ) )
		{
			foreach ( $data as $key => $value )
			{
				$result[strtoupper( $key )] = $value;
			}
		}
		return $result;
	}
	/**
	 * @desc 删除表
	 */
	public function dropTable( $table_name )
	{
		return $this->__drop_table( $table_name );
	}
	/**
	 * @desc 创建自增序列
	 */
	public function create_seq( $seq_name, $start = 1, $stepby = 1 )
	{
		return $this->__create_seq( $seq_name, $start, $stepby );
	}
	/**
	 * @desc 判断是否存在{自增序列}
	 */
	public function exists_seq( $seq_name )
	{
		return $this->__exists_seq( $seq_name );
	}
}