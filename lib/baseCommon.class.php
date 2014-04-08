<?php
/**
 * @desc 公用函数
 * @author gkl
 * @since 20131122
 */
class baseCommon
{
	/**
	 * @desc 全局model数组
	 * @var ArrayObject
	 */
	public static $models_arr = array();
	/**
	 * @desc 生成前台控制或后台服务对象或数据模块对象
	 * @return baseCtrl|baseCtrlServer|baseModelEx 前台控制或后台服务对象或数据模块对象
	 */
	public static function newClass( $class )
	{
		return new $class();
	}
	/** 
	 * @desc 转至对应方法
 	 */
	public static function doAction()
	{
		baseXHPROF::init();
		register_shutdown_function( array( 'baseXHPROF', 'free' ) );
		$act				= isset( $_GET['act'] ) ? $_GET['act'] : ( defined( 'DEFAULT_ACTION' ) ? DEFAULT_ACTION : 'index' );
		$st				= isset( $_GET['st'] ) ? $_GET['st'] : ( defined( 'DEFAULT_FUNC' ) ? DEFAULT_FUNC : 'index' );
		$class			= self::checkClass( $act, ACT_PATH );
		$obj				= self::newClass( $class );
		$obj->act	= $act;
		$obj->st		= $st;
		self::runAction( $obj, $st );
	}
	/**
	 * @desc 检查并加载库文件
	 * @return string 类名
	 */
	protected static function checkClass ( $class_name, $class_dir, $class_type = 'Ctrl', $class_ext = '.class.php' )
	{
		$class_full_name	 = $class_name . $class_type;
		$class_file = $class_dir . $class_full_name . $class_ext;
		if ( !is_file( $class_file ) )
		{
			self::html404();
		}
		$include_res = self::includeClass( $class_full_name, $class_file );
		if( $include_res === false )
		{
			self::__die('错误，类不存在');
		}
		return $class_full_name;
	}
	/**
	 * @desc 封装die
	 */
	public static function __die( $msg = '' )
	{
		self::__echo( $msg );			
		self::__exit();
	}
	/**
	 * @desc 封装echo
	 */
	public static function __echo ( $msg = '', $enter = false, $charset = 'utf-8//IGNORE' )
	{
		if ( $enter !== false )
		{
			$msg .= "{$enter}";
		}
		$msg = iconv( 'utf-8', $charset, $msg );
		if ( !defined( 'CLOSE_LOG' ) || CLOSE_LOG === false )
		{
			self::writeLog( $msg );
		}
		echo $msg;
	}
	/**
	 * @desc 输出404错误
	 */
	public static function html404 ()
	{
		$res = self::render( 'index', 'html404' );
		if ( $res === false )
		{
			header( 'HTTP/1.1 404' );
			self::__exit();
		}
	}
	/**
	 * @desc 执行后台服务
	 */
	public static function doServer ( $server_name )
	{
		$class = self::checkClass( $server_name, ACT_PATH );
		$obj = self::newClass( $class );
		$obj->name = $server_name;
		$obj->init();
	}
	/**
	 * @desc 执行方法
	 * @param baseCtrl $obj
	 */
	protected static function runAction( $obj, $func )
	{
		if ( method_exists( $obj, 'beforeAction' ) )
		{
			call_user_func_array( array( $obj, 'beforeAction' ), array() );
		}
		if ( !method_exists( $obj, $func ) )
		{
			self::html404();
		}
		call_user_func_array( array( $obj, $func ), array() );
		if ( method_exists( $obj, 'afterAction' ) )
		{
			call_user_func_array( array( $obj, 'afterAction' ), array() );
		}
	}
	/**
	 * @desc 内部跳转
	 */
	public static function render( $ctrl = 'index', $act = 'index' )
	{
		$class				= self::checkClass( $ctrl, ACT_PATH );
		$obj					= self::newClass( $class );
		$obj->act		= $ctrl;
		$obj->st			= $act;
		self::runAction( $obj, $act   );
		self::__exit();
	}
	/**
	 * @desc 生成唯一md5
	 */
	public static function uniqueMD5()
	{
		mt_srand( microtime( true ) * 10000 );
		$random = mt_rand();
		return md5( $random );
	}
	/**
	 * @desc 中断
	 */
	public static function __exit()
	{
		die;
	}
	/**
	 * @desc 加载类扩展
	 */
	public static function includeClass( $class_name, $class_file )
	{
		if ( !class_exists( $class_name, false ) )
		{
			include $class_file;
		}
		if ( !class_exists( $class_name, false ) )
		{
			return false;
		}
		return true;
	}
	/**
	 * @desc 加载model类
	 */
	public static function m( $modelName, $noExistsExit = true )
	{
		if(empty( $modelName ) )
		{
			return false;
		}
		if ( !isset( self::$models_arr[$modelName] ) )
		{
			$model_file = MODEL_PATH.$modelName.'.model.php';
			if ( !is_file( $model_file ) )
			{
				if ( $noExistsExit === true )
				{
					self::__die('错误，文件不存在');
				}
				else
				{
					return false;
				}
			}
			$model_full_name = $modelName . 'Model';
			$include_res = self::includeClass( $model_full_name, $model_file );
			if( $include_res === false )
			{
				if ( $noExistsExit === true )
				{
					self::__die('错误，类不存在');
				}
				else
				{
					return false;
				}
			}
			self::$models_arr[$modelName] = self::newClass( $model_full_name );
		}
		return self::$models_arr[$modelName];
	}
	/**
	 * @desc 初始化model数组
	 */
	public static function iniModels()
	{
		self::$models_arr = array();
	}
	/**
	 * @desc 写日志文件
	 * @since 20140227
	 * @param unknown $logs
	 * @param string $file_name
	 */
	public static function writeLog( $logs, $file_name = '' )
	{
		$logs = date('Y-m-d H:i:s') . "\t" . $logs . "\n";
		if ( empty( $file_name ) )
		{
			$file = ROOT_PATH . '/logs/' . date( 'Y/m/d' ) . '-logs.txt';
		}
		else
		{
			$file = ROOT_PATH . '/logs/' . date( 'Y/m/d' ) . '-' . $file_name . '.txt';
		}
		if ( !file_exists( dirname( $file ) ) )
		{
			mkdir( dirname( $file ), 0775, true );
		}
		error_log( $logs, 3, $file );
	}
}