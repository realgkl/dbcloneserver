<?php
/**
 * @desc t_report_record表
 * @autho gkl
 * @since 20140124
 */
class tReportRecordTable extends useOracleTable
{
	/**
	 * @desc 没有表时创建表
	 */
	protected function __initTable()
	{
		$table_name = $this->name;
		$seq_name = 'seq_' . $table_name;
		$this->conn_obj->create_seq( $seq_name );
		$sql = "
				create table {$table_name} (
					f_report_id NUMBER(20) NOT NULL,
					f_freq NUMBER(3) DEFAULT 0,
					f_report_type NUMBER(10) DEFAULT 0,
					f_report_date DATE,
					f_report_data CLOB DEFAULT '',
					f_deleted_flag NUMBER(3) DEFAULT 0,
					f_order_index NUMBER(10) DEFAULT 0,
					f_raw_add_time DATE,
					f_raw_update_time DATE
				)
		";
		$this->conn_obj->exec( $sql );
		// 索引
		$sql = "
				create index {$table_name}_idx1 on {$table_name} (f_report_date)
		";
		$this->conn_obj->exec( $sql );
		// 主键
		$sql = "
				alter table {$table_name} add constraint {$table_name}_pk1 primary key (f_report_id)
		";
		$this->conn_obj->exec( $sql );
		// 触发器
		$sql = "
				create or replace trigger trg_{$table_name}_seq before insert on {$table_name} for each row
				begin
					select {$seq_name}.nextval into :new.f_report_id from dual;
				end;
		";
		$this->conn_obj->exec( $sql );
		return true;
	}
	/**
	 * @desc 构造函数
	 */
	public function __construct( &$conn_obj )
	{
		parent::__construct( $conn_obj, 't_report_record' );
	}
	/**
	 * @desc 初始化
	 */
	public function init()
	{
		if ( !$this->__existsTable() )
		{
			$this->__initTable();
		}
		parent::init();
	}
}