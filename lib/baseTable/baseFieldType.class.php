<?php
/**
 * @desc 字段类型
 * @author gkl
 * @since 20131226
 */
class baseFieldType
{
	// mysql	整形
	const FT_MY_TINYINT = 'tinyint';
	const FT_MY_SMALLINT = 'smallint';
	const FT_MY_MEDIUMINT = 'mediumint';
	const FT_MY_INT = 'int';
	const FT_MY_BIGINT = 'bigint';
	// mysql 小数
	const FT_MY_DECIMAL = 'decimal';
	const FT_MY_FLOAT = 'float';
	const FT_MY_DOUBLE = 'double';
	// mysql 日期
	const FT_MY_DATE = 'date';
	const FT_MY_DATETIME = 'datetime';
	const FT_MY_TIMESTAMP = 'timestamp';
	const FT_MY_TIME = 'time';
	const FT_MY_YEAR = 'year';
	// mysql	文本
	const FT_MY_CHAR = 'char';
	const FT_MY_VARCHAR = 'varchar';
	// mysql 长文本
	const FT_MY_TINYTEXT = 'tinytext';
	const FT_MY_TEXT = 'text';
	const FT_MY_MEDIUMTEXT = 'mediumtext';
	const FT_MY_LONGTEXT = 'longtext';
	// mysql	二进制
	const FT_MY_BINARY = 'binary';
	const FT_MY_VARBINARY = 'varbinary';
	// mysql blob
	// 暂不支持	
	// oracle 数字型
	const FT_ORA_NUMBER = 'number';
	// oracle 浮点型
	const FT_ORA_FLOAT = 'float';
	// oracle 文本型
	const FT_ORA_CHAR = 'char';
	const FT_ORA_VARCHAR2 = 'varchar2';
	// oracle 长文本型
	const FT_ORA_CLOB = 'clob';
	// oracle 日期时间等
	const FT_ORA_DATE = 'date';
	// oracle 二进制
	const FT_ORA_RAW = 'raw';
	/**
	 * @desc mysql支持的类型数组
	 * @var array
	 */
	public static $FT_MY_ARR = array(
			self::FT_MY_TINYINT,
			self::FT_MY_SMALLINT,
			self::FT_MY_MEDIUMINT,
			self::FT_MY_INT,
			self::FT_MY_BIGINT,
			self::FT_MY_DECIMAL,
			self::FT_MY_FLOAT,
			self::FT_MY_DOUBLE,
			self::FT_MY_DATE,
			self::FT_MY_DATETIME,
			self::FT_MY_TIMESTAMP,
			self::FT_MY_TIME,
			self::FT_MY_YEAR,
			self::FT_MY_CHAR,
			self::FT_MY_VARCHAR,
			self::FT_MY_TINYTEXT,
			self::FT_MY_TEXT,
			self::FT_MY_MEDIUMTEXT,
			self::FT_MY_LONGTEXT,
			self::FT_MY_BINARY,
			self::FT_MY_VARBINARY,
	);
	/**
	 * @desc mysql支持的不需要长度的类型
	 */
	public static $FT_MY_NO_LEN_ARR = array(
			self::FT_MY_FLOAT,
			self::FT_MY_DOUBLE,
			self::FT_MY_DATE,
			self::FT_MY_DATETIME,
			self::FT_MY_TIMESTAMP,
			self::FT_MY_TIME,
			self::FT_MY_YEAR,
			self::FT_MY_TINYTEXT,
			self::FT_MY_TEXT,
			self::FT_MY_MEDIUMTEXT,
			self::FT_MY_LONGTEXT,
	);
	/**
	 * @desc mysql支持的需要精度的类型
	 */
	public static $FT_MY_NEED_PREC_ARR = array(
			self::FT_MY_DECIMAL,
	);
	/**
	 * @desc oracle支持的字段类型
	 */
	public static $FT_ORA_ARR = array(
		self::FT_ORA_NUMBER,
		self::FT_ORA_CHAR,
		self::FT_ORA_VARCHAR2,
		self::FT_ORA_DATE,
		self::FT_ORA_CLOB,
		self::FT_ORA_RAW,
	);
	/**
	 * @desc oracle支持的不需要长度的类型
	 */
	public static $FT_ORA_NO_LEN_ARR = array(
		self::FT_ORA_DATE,
		self::FT_ORA_CLOB,
	);
	/**
	 * @desc oracle支持的需要精度的类型
	 */
	public static $FT_ORA_NEED_PREC_ARR = array(
		self::FT_ORA_NUMBER,
	);
}