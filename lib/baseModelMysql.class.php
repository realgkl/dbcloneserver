<?php
/**
 * @desc mysql数据模块基类
 * @author gkl
 * @since 20131211
 */
class baseModelMysql extends baseModelEx
{
	/**
	 * @desc 检查是否存在表
	 */
	protected function __exists_table( $table_name )
	{
		$sql = "
				show tables like '{$table_name}'
		";
		$res = $this->getRow( $sql );
		if ( $res !== false  )
		{
			return true;
		}
		return false;
	}
	/**
	 * @desc 拼insert sql语句
	 * @param array $datas 要插入的数组
	 * @param string $table_name 表名
	 * @param array $params 返回的参数数组
	 * @return boolean|string 成功返回sql语句，失败返回false
	 */
	protected function __parse_insert_sql ( $datas, $table_name, &$params )
	{
		$params = array();
		if ( empty( $datas ) )
			return false;
		$sql = "insert into `{$table_name}`\n";
		$fields = array_keys( $datas[0] );
		if ( empty( $fields ) )
			return false;
		foreach ( $fields as &$v )
		{
			$v = "`{$v}`";
		}
		$sql .= '(' . implode( ',', $fields ) . ') values ';
		foreach ( $datas as $data )
		{
			$sql .= "\n(" . implode( ',', array_fill( 0, count( $data ), '?' ) ) . "),";
			foreach ( $data as $v )
			{
				$params[] = $v;
			}
		}
		if ( substr( $sql, strlen( $sql ) - 1, 1 ) == ',' )
			$sql = substr( $sql, 0, strlen( $sql ) - 1 );
		return $sql;
	}
	/**
	 * @desc 拼update sql语句
	 * @param array $data 单条数据
	 * @param string $table_name 表名
	 * @param string $key_field 定位主键或唯一索引
	 * @param array $params 返回的参数数组
	 * @return boolean|string 成功返回sql失败返回false
	 */
	protected function __parse_update_sql ( $data, $table_name, $key_field, &$params )
	{
		$params = array();
		if ( empty( $data ) )
			return false;
		$fields = array_keys( $data );
		if ( empty( $fields ) )
			return false;
		$key_index_arr = array();
		$key_field_arr = array();
		if ( is_array( $key_field ) )
		{
			$key_field_arr = $key_field;
		}
		else
		{
			$key_field_arr[] = $key_field;
		}
		// 取得主键字段的序号
		foreach ( $fields as $k => &$v )
		{
			$v = strtolower( $v );
			if ( in_array( $v, $key_field_arr ) )
			{
				array_splice( $fields, $k, 1 );
				$key_index_arr[] = $k;
			}
		}
		// 主键字段为空返回失败
		if ( empty( $key_index_arr ) )
			return false;
		$sql = "update `{$table_name}` set ";
		foreach ( $data as $k => &$v )
		{
			// 非定位主键或唯一索引
			if ( !in_array( $k, $key_field_arr ) )
			{
				$sql .= "`{$k}` = ?,";
				$params[] = $v;
			}
		}
		if ( substr( $sql, strlen( $sql ) - 1, 1 ) == ',' )
			$sql = substr( $sql, 0, strlen( $sql ) - 1 );
		$sql .= " where 1";
		foreach ( $key_field_arr as $k => $v )
		{
			$sql .= " and `{$v}` = ?";
			$params[] = $data[$v];
		}
		return $sql;
	}
	/**
	 * @desc 构造函数获取db
	 */
	public function __construct ()
	{
		$this->db = basePdoConnMysql::connect()->getConn();
		parent::__construct();
	}
	/**
	 * @see baseModelEx::getAll()
	 */
	public function getAll ( $sql, $params = array() )
	{
		if ( $this->show_time )
			$t = $this->diff();
		$params = $this->__filter_params( $params );
		$query = $this->db->prepare( $sql );
		$res = $query->execute( $params );
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			return $query->fetchAll();
		}
		else
		{
			$this->__error();
		}
		return false;
	}
	/**
	 * @see baseModelEx::getRow()
	 */
	public function getRow( $sql, $params = array() )
	{
		if ( $this->show_time )
			$t = $this->diff();
		$params = $this->__filter_params( $params );
		$query = $this->db->prepare( $sql );
		$res = $query->execute( $params );
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			return $query->fetch();
		}
		else
		{
			$this->__error();
		}
		return false;
	}
	/**
	 * @see baseModelEx::exec()
	 */
	public function exec( $sql, $params = array() )
	{
		if ( $this->show_time )
			$t = $this->diff();
		$params = $this->__filter_params( $params );
		$this->db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
		$query = $this->db->prepare( $sql );
		$res = $query->execute( $params );
		if ( $res )
		{
			if ( $this->show_time )
				$this->diff( $t, $sql );
			return $query->rowCount();
		}
		else
		{
			$this->__error( $sql );
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
				select 1 from `$table` where 1
			";
			$params = array();
			foreach ( $key_filed as $k => $v )
			{
				$sql .= " and `$v` = ?";
				$params[] = $value[$k];
			}
		}
		else
		{
			$sql = "
				select 1 from `$table` where `$key_filed` = ?
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
}