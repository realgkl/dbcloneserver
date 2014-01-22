<?php
/**
 * @desc pdo链接基类
 * @author gkl
 * @since 20131211
 */
class basePdoConn
{
	/**
	 * @desc oracle数据库
	 */
	const DB_OCI = 'oci';
	/**
	 * @desc mysql数据库
	 */
	const DB_MYSQL = 'mysql';
	/**
	 * @desc 链接对象
	 */
	protected $conn = false;
	/**
	 * @desc 是否已链接
	 */
	protected $connected = false;
	/**
	 * @desc 支持的数据库
	 */
	protected $support_db = array(
		self::DB_OCI,
		self::DB_MYSQL,
	);
	/**
	 * @desc 实例数组
	 */
	public static $_instance = array();
	/**
	 * @desc 检查是否支持数据库
	 */
	private function __checkSupportDB( $type )
	{
		if ( !in_array( $type, $this->support_db ) )
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 获取mysql数据库DSN
	 */
	private function __getMysqlDSN( $host, $db  )
	{
		return "mysql:host={$host};dbname={$db}";
	}
	/**
	 * @desc 获取oracle数据库DSN
	 */
	private function __getOracleDSN( $host, $db, $charset )
	{
		return "oci:dbname={$host}/{$db};charset={$charset}";
	}
	/**
	 * @desc 构造函数
	 */
	public function __construct( $type, $host, $db, $user, $pass, $charset='utf-8', $errmode = PDO::ERRMODE_EXCEPTION )
	{
		$this->__checkSupportDB( $type );
		$options 																	= array();
		$options[PDO::ATTR_DEFAULT_FETCH_MODE]	= PDO::FETCH_ASSOC;		
		switch ( $type )
		{
			case self::DB_MYSQL:
				$dsn = $this->__getMysqlDSN( $host, $db );
				$options[PDO::MYSQL_ATTR_INIT_COMMAND]				= "set names {$charset}";
				$options[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY]	= true;
				$options[PDO::ATTR_AUTOCOMMIT]								= true;
				break;
			case self::DB_OCI:
				$dsn = $this->__getOracleDSN( $host, $db, $charset );
				$options[PDO::ATTR_AUTOCOMMIT]								= true;
				break;
		}		
		$this->conn = new PDO( $dsn, $user, $pass, $options );
		$this->connected = true;
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{		
		unset( $this->conn );
		$this->connected = false;
	}
	/**
	 * @desc 设置options
	 */
	public function setOptions( $key, $value )
	{
		if ( $conn !== false )
		{
			return $this->conn->setAttribute( $key, $value );
		}
		return false;
	}
	/**
	 * @desc 返回pdo链接类
	 * @return PDO pdo链接类
	 */
	public function getConn()
	{
		return $this->conn;
	}
	/**
	 * @desc 创建数据链接
	 * @param basePdoConn
	 */
	public static function connect( $single = true )
	{
		
	}
}