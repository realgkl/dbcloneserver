<?php
/**
 * @desc 后台服务PHP
 */
class baseCtrlServer extends baseCtrl
{
	/**
	 * @desc 参数
	 */
	public $args = array();
	/**
	 * @desc 服务名
	 */
	public $name = '';
	/**
	 * @desc 是否http请求
	 */
	protected $from_http;
	/**
	 * @desc 构造函数
	 */
	public function __construct ()
	{
		$this->from_http = false;
		if ( isset( $_SERVER['SERVER_PROTOCOL'] ) && strpos( $_SERVER['SERVER_PROTOCOL'], 'HTTP' ) !== false )
		{
			set_time_limit( 0 );
			$this->from_http = true;
		}
		if ( $this->from_http === true )
		{
			baseCommon::html404();
		}
	}
	/**
	 * @desc 获取参数
	 */
	protected function __get_argements()
	{
		// 默认方法名称index
		$method = defined( 'DEFAULT_FUNC' ) ? DEFAULT_FUNC : 'index';
		// 默认参数
		$argv_arr = array();
		if ( $this->from_http === false )
		// php执行
		{
			$argv_arr = $_SERVER['argv'];
			if ( !is_array( $argv_arr ) )
				$this->__error();
			$server = array_shift( $argv_arr );
			if ( !empty( $argv_arr ) )
				$method = array_shift( $argv_arr ); // 第一参数为方法名
			if ( method_exists( $this, $method ) )
			{
				call_user_func_array( array( $this, $method ), $argv_arr );
			}
			else
			{
				die( 'func {' .  $method . '} not found in class ' . get_class( $this ) );
			}
		}
	}
	/**
	 * @desc 初始化
	 */
	public function init ()
	{
		$this->__get_argements();
	}
	/**
	 * @desc 抛出异常
	 */
	protected function __error( $msg = 'error!' )
	{
		echo "{$msg}\n";
		die;
	}
	/**
	 * @desc 输出结果
	 */
	protected function __out ( $msg = '', $enter = "\n", $charset = 'gb2312' )
	{
		$result = "{$msg}{$enter}";
		echo iconv( 'utf-8', $charset, $result );
		return $result;
	}
	/**
	 * @desc 获取model
	 * @return baseModelEx 数据模块对象
	 */
	public function model()
	{
		return m( $this->name );
	}
}
