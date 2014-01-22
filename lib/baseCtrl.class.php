<?php
/**
 * @desc 控制器基类扩展
 * @author gkl
 * @time 2013-09-03
 */
class baseCtrl 
{
	/**
	 * @desc 请求类型POST 
	 */
	const R_M_POST		= 'POST';
	/**
	 * @desc 请求类型GET
	 */
	const R_M_GET		= 'GET';
	/**
	 * @desc 默认view文件
	 */
	protected $view = false;
	/**
	 * @desc view参数
	 */
	protected $view_vars = array();
	/**
	 * @desc layout布局参数
	 */
	protected $layout = false;
	/**
	 * @desc 是否展示布局
	 */
	protected $showLayout = true;
	/**
	 * @desc 执行类
	 */
	public $act;
	/**
	 * @desc 执行方法
	 */
	public $st;
	/**
	 * @desc 显示view
	 */
	protected function display()
	{
		if ( $this->view === false )
		{
			$this->view = 'index';
		}
		baseView::init( $this->layout, $this->showLayout );
		$view_file = VIEW_PATH . $this->act . '/' . $this->view;
		baseView::display( $view_file, $this->view_vars );
	}
	/**
	 * @desc after act函数
	 */
	public function afterAction ()
	{
		$this->display();
	}
	/**
	 * @desc before act 函数
	 */
	public function beforeAction()
	{
		
	}
	/**
	 * @desc 获取请求类型
	 */
	protected function getMethod()
	{
		return isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( $_SERVER['REQUEST_METHOD'] ) : false;
	}
	/**
	 * @desc 不显示view
	 */
	protected function noView()
	{
		exit();
	}
	/**
	 * @desc 设置view变量
	 */
	protected function setVar( $var_name, $var_value )
	{
		$this->view_vars[$var_name] = $var_value;
	}
	/**
	 * @desc 获取model
	 * @return baseModelEx 数据模块对象
	 */
	public function model()
	{
		return m( $this->act );
	}
}