<?php
/**
 * @desc pdo oci链接基类
 * @author gkl
 * @since 20131211
 */
class basePdoConnOci extends basePdoConn
{
	public function __construct()
	{
		parent::__construct( 'oci', ORACLE_HOST, ORACLE_DB,
				ORACLE_USER, ORACLE_PASS,
				ORACLE_CHARSET, PDO::ERRMODE_EXCEPTION );
	}
	/**
	 * @desc 返回的连接对象
	 * @param bool $single 是否单例
	 * @return basePdoConnOci 返回basePdoConnOci 对象
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