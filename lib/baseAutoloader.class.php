<?php
/**
 * @desc autoloader 类
 * @author  gkl
 * @since 20131211
 */
class baseAutoloader
{	
	/**
	 * @desc 类名后缀
	 */
	const CLASS_EXT	= '.class.php';
	/**
	 * @desc 接口后缀
	 */
	const INT_EXT	= '.int.php';
	/**
	 * @desc 对象实例
	 */
	public static $loader;
    /**
     * 构造函数
     */
    public function __construct() {
        spl_autoload_register(array(
            $this,
            'import'
        ));
    }
    /**
     * @desc 初始化
     * @return autoloaderBase
     */
    public static function init() {
        // 静态化自调用
        if (self::$loader == NULL)
            self::$loader = new self();

        return self::$loader;
    }
    /**
     * 固定路径的class 类文件 以.class.php 结尾
     */
    protected function import( $className )
    {
    	$error = TRUE;
    	$file_name = LIB_PATH . $className . self::CLASS_EXT;
    	if ( file_exists( $file_name ) )
		{
			$error = false;
		}
		else
		{
			$file_name = INT_PATH . $className . self::INT_EXT;
			if ( file_exists( $file_name ) )
			{
				$error = false;
			}
		}
		if ( $error === TRUE )
		{
			$this->err_fn( $className );
		}
		else
		{
			include $file_name;	
		}
    }
    /**
     * @desc 文件出错提示
     */
    protected function err_fn($className) {
        exit("class $className files includes err!!");
    }
}
