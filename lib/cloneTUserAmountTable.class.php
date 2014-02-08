<?php
/**
 * @desc t_user_amount镜像表
 * @author guankailin
 *
 */
class cloneTUserAmountTable extends useOracleTable
{
	/**
	 * @desc 构造函数
	 */
	public function __construct( &$conn_obj )
	{
		parent::__construct( $conn_obj, 'clone_t_user_amount' );
	}
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
					f_id NUMBER(20) NOT NULL,
					f_user_id NUMBER(10),
					f_amount NUMBER(10,2),
					f_freeze_amount NUMBER(10,2),
					f_freeze_point NUMBER(10,2),
					f_withdraw_max NUMBER(10,2),				
					f_date DATE,
					gmt_create DATE,
					gmt_modified DATE
				)
		";
		$this->conn_obj->exec( $sql );
		// 索引
		$sql = "
				create index {$table_name}_idx1 on {$table_name} (f_user_id)
		";
		$this->conn_obj->exec( $sql );
		$sql = "
				create index {$table_name}_idx2 on {$table_name} (f_date)
		";
		$this->conn_obj->exec( $sql );
		$sql = "
				create index {$table_name}_idx3 on {$table_name} (gmt_create)
		";
		$this->conn_obj->exec( $sql );
		$sql = "
				create index {$table_name}_idx4 on {$table_name} (gmt_modified)
		";
		$this->conn_obj->exec( $sql );
		// 主键
		$sql = "
				alter table {$table_name} add constraint {$table_name}_pk1 primary key (f_id)
		";
		$this->conn_obj->exec( $sql );
		// 触发器
		$sql = "
				create or replace trigger trg_{$table_name}_seq before insert on {$table_name} for each row
				begin
					select {$seq_name}.nextval into :new.f_id from dual;
				end;
		";
		$this->conn_obj->exec( $sql );
		return true;
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
	/**
	 * @desc 获取{用户}{当天的}值
	 */
	public function &getByDate( $user_id, $date )
	{
		$this->clearSearchCond();
		$this->select( 'f_amount', 'sum', 'f_amount' );
		$this->select( 'f_freeze_amount', 'sum', 'f_freeze_amount' );
		$this->where( 'to_char(f_date,\'yyyy-mm-dd\')', '<=',  $date );
		$this->where( 'f_user_id', '=', $user_id );
		return $this->getDataByCond();
	}
	/**
	 * @desc 存入{用户}{当天的}彩金和冻结变动
	 */
	public function saveAmountFreezeByUseridDate( $amount, $freeze, $user_id, $date )
	{
		$data = array(
			'f_user_id'					=> $user_id,
			'f_amount'					=> $amount,
			'f_freeze_amount'		=> $freeze,
			'f_date'						=> $date,
			'gmt_create'				=> date( 'Y-m-d H:i:s' ),
			'gmt_modified'			=> date( 'Y-m-d H:i:s' ),
		);
		$record = new baseRecordOracle();
		$record->setData( $data );
		$src_occ = '?';
		$dst_occ = baseModelOci::OCC;
		$params = array();
		$sql = $record->createInsSql( $this->name, $params, $this->fields, $src_occ );
		unset( $record );
		$sql = $this->__replaceOcc( $sql, $src_occ, $dst_occ );
		return $this->conn_obj->exec( $sql, $params );
	}
}