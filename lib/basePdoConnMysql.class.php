<?php
/**
 * @desc pdo mysql基类
 * @author	gkl
 * @since 20131211
 */
class basePdoConnMysql extends basePdoConn
{
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		parent::__construct( 'mysql', MYSQL_HOST, MYSQL_DBNAME,
				MYSQL_USERNAME, MYSQL_PASSWORD,
				MYSQL_CHARSET, PDO::ERRMODE_EXCEPTION );
    }
    /**
     * @desc 返回的连接对象
     * @param bool $single 是否单例
     * @return basePdoConnMysql 返回basePdoConnMysql 对象
     */
    public static function connect( $single = true )
    {
    	if ( $single === false )
    	{
    		$id = baseCommon::uniqueMD5();
    	}
    	else
    	{
    		$id = 'single';
    	}
    	if (empty( self::$_instance) || !( self::$_instance[$id] instanceof self ) )
    	{
    		self::$_instance[$id] = new self();
    	}
    	return self::$_instance[$id];
    }
}
