<?php
/**
 * @desc model基类扩展
 * @author gkl
 * @since 20130724
 */
abstract class baseModelEx
{	
	/**
	 * @desc db
	 */
	protected $db = null;
	/**
	 * 事务开启计数
	 */
	protected static $trans_num = 0;
	/**
	 * @desc 是否显示执行时间
	 */
	protected $show_time = false;
	/**
	 * @desc 生成有绑定参数的sql
	 */
	protected function __createSqlByParams( $sql, $params )
	{
		if ( !empty( $params ) )
		{
			foreach ( $params as $v )
			{
				$sql = preg_replace( '/\?/', "'{$v}'", $sql, 1 );
			}
		}
		return $sql;
	}
	/**
	 * @desc 抛出异常
	 */
	protected function __error( $sql = '', $err_die = true, &$err_no = '', &$err_msg = '' )
	{
		if ( self::$trans_num > 0 )
			$this->rollback();
		$err_arr = $this->db->errorInfo();
		$err_no = $err_arr[1];
		$err_msg = $err_arr[2];
		$msg = "\n[$err_arr[0]]ErrorNo {$err_no} : {$err_msg}";
		if ( $sql != '' )
		{
			$msg .= "\n{$sql}";
		}
		if ( $err_die === true )
		{
			baseCommon::__die( $msg );
		}
	}
	/**
	 * @desc 过滤参数，防止非法字符和SQL注入
	 */
	protected function __filter_params ( $params )
	{
		if ( is_array( $params) && !empty( $params ) )
		{
			foreach ( $params as &$v )
			{
				$v = self::setValueToDb( $v );
			}
		}
		else if ( is_string( $params ) )
		{
			$params = self::setValueToDb( $params );
		}
		return $params;
	}
	/**
	 * @desc 检查日期
	 */
	protected function checkDate ( &$date, $delimiter = '-' )
	{
		if ( $date === false )
		{
			return true;
		}
		if ( $delimiter !== '-' )
		{
			$date = str_replace( $delimiter, '-', $date );
		}
		$match = preg_match( '/[^0-9\-]/', $date );
		if ( $match === 1 )
		{
			return false;
		}
		$date_arr = explode( $delimiter, $date );
		if ( count( $date_arr ) != 3 )
		{
			return false;
		}
		$y	= intval( $date_arr[0] );
		$m	= intval( $date_arr[1] );
		$d	= intval( $date_arr[2] );
		if ( $m > 12 || $m < 1 || $d < 1 || $d > 31)
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 构造函数
	 */
	public function __construct ()
	{
		if ( defined( 'SHOW_SQL_TIME' ) )
			$this->show_time = SHOW_SQL_TIME;
	}
	/**
	 * @desc 读取数据
	 */
	public static function getValueFromDb( $value )
	{
		if ( is_string( $value ) )
		{
			// 去除字符串两边空格
			$value = trim( $value );
			// 如果是html把html实体转换成字符，单双引号都转换(不转换中文字)
			$value = htmlspecialchars_decode( $value, ENT_QUOTES );
			// 去除转义符
			$value = stripslashes( $value );
			return $value;
		}
		return $value;
	}
	/**
	 * @desc 写入数据过滤
	 */
	public static function setValueToDb( $value )
	{
		if ( is_string( $value ) )
		{
			// 去除字符串两边空格
			$value = trim( $value );
			// 如果开启魔术引号, 去除反斜杠
			if ( @get_magic_quotes_gpc() )
			{
				$value = stripslashes( $value );
			}
			// 加斜杠转义
			$value = addslashes( $value );
			// 如果是html把字符转换成html实体，单双引号都转换(不转换中文字)
			$value = htmlspecialchars( $value, ENT_QUOTES );
			// 转义通配符
			// $value = str_replace( '%', '\%', $value );
			// $value = str_replace( '_', '\_', $value );
			return $value;
		}
		return $value;
	}
	/**
	 * @desc 过滤字符串
	 */
	public function escape_string ( $string )
	{
		return self::setValueToDb( $string );
	}
	/**
	 * @desc 获取微妙差值
	 */
	public function diff ( $t = 0, $sql = '' )
	{
		if ( $t == 0 )
		{
			return microtime( true );
		}
		else
		{
			echo $sql . '|' . ( microtime( true ) - $t ) . "<br/>";
		}
	}
	/**
	 * @desc 从memcache获取数据
	 */
	public function getAllByMem ( $sql, $params = array(), $expire = 0 )
	{
		$mem_model = m('memcache', false );
		if ( $mem_model !== false )
		{
			$total_sql = $sql;
			if ( !empty( $params ) )
			{
				foreach ( $params as $v )
				{
					$total_sql = preg_replace( '[\?]', $v, $total_sql, 1 );
				}
			}
			$res = $mem_model->get( $total_sql );
			if ( $res === false )
			{
				$data = $this->getAll( $sql, $params );
				if ( !empty( $data ) )
				{
					$mem_model->set( $total_sql, $data, $expire );
				}
				$res = $data;
			}
			return $res;
		}
		return false;
	}
	/**
	 * @desc 从文件获取数据
	 */
	public function getAllByFile( $sql, $params = array(), $expire = 0 )
	{
		$file_model = m('file_mem');
		if ( $file_model !== false )
		{
			$total_sql = $sql;
			if ( !empty($params ) )
			{
				foreach($params as $v)
				{
					$total_sql = preg_replace('[\?]', $v, $total_sql, 1);
				}
			}
			$res = $file_model->get($total_sql);
			if($res === false)
			{
				$data = $this->getAll( $sql, $params );
				if( !empty( $data ) )
				{
					$file_model->set( $total_sql, $data, $expire);
					$res = $data;
				}
			}
			return $res;
		}
		return false;
	}
	/**
	 * @desc 判断是否存在{表}
	 */
	public function exists_table( $table_name )
	{
		return $this->__exists_table( $table_name );
	}
	/**
	 * @desc 获取所有数据
	 */
	abstract public function getAll( $sql, $params = array() );
	/**
	 * @desc 获取单条数据
	 */
	abstract public function getRow( $sql, $params = array() );
	/**
	 * @desc 执行数据[update,delete,insert]
	 */
	abstract public function exec( $sql, $params = array() );
	/**
	 * @desc 开启事务
	 */
	abstract public function begin();
	/**
	 * @desc 事务回滚
	 */
	abstract public function rollback();
	/**
	 * @desc 提交事务
	 */
	abstract public function commit();
	/**
	 * @desc 查找记录是否存在
	 */
	abstract public function recordExists( $table, $key_filed, $value );
	/**
	 * @desc 是否存在{表}
	 */
	abstract protected function __exists_table( $table_name );
}