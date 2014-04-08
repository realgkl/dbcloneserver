<?php
/**
 * @desc 采用面向对象的新数据库复制模块
 * @author gkl
 * @since 20131231
 */
class dbCloneNewModel extends baseModelComm
{
	/**
	 * @desc 复制表数据限制
	 * @var integer
	 */
	protected $copy_limit = 500;
	/**
	 * @desc 需要初始化的表数组
	 */
	protected $table_list = array(
		't_user',
		't_user_amount',
		't_user_amount_change_log',
		't_user_amount_freeze',
		't_lottery_buy',
		't_pay_log',
		't_recharge_log',
		't_point_change_log',
		't_withdraw_apply',
		't_voucher',
		't_voucher_type',
		't_voucher_batch',
		't_insure_user',
		't_wlt_lottery_order',
	);
	/**
	 * @desc mysql数据库列表对象
	 */
	protected $umtList;
	/**
	 * @desc oracle数据库列表对象
	 */
	protected $uotList;
	/**
	 * @desc mysql model
	 * @var baseModelMysql
	 */
	protected $mm;
	/**
	 * @desc oracle model
	 * @var baseModelOci
	 */
	protected $om;
	/**
	 * @desc 构造函数
	 */
	public function __construct()
	{
		parent::__construct();
		$this->umtList = new useTableList();
		$this->uotList = new useTableList();
		$this->mm = new baseModelMysql();
		$this->om = new baseModelOci();
	}
	/**
	 * @desc 析构函数
	 */
	public function __destruct()
	{
		unset( $this->umtList );
		unset( $this->uotList );
		unset( $this->mm );
		unset( $this->om );
	}
	/**
	 * @desc 复制mysql表数据到oracle
	 */
	protected function __copyDataByTableMyToOra()
	{
		if ( !empty( $this->table_list ) )
		{
			$end_time = date( 'Y-m-d H:i:s' );
			foreach ( $this->table_list as $table_name )
			{
				$m_t = $this->umtList->getByName( $table_name );
				if ( $m_t === false )
				{
					baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t复制表 {$m_t->getName()} 数据失败：名称 {$table_name} 的mysql表不存在", "\n" );
					continue;
				}
				$o_t = $this->uotList->getByName( $table_name );
				if ( $o_t === false )
				{
					baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t复制表 {$m_t->getName()} 数据失败：名称 {$table_name} 的oracle表不存在", "\n" );
					continue;
				}
				baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t开始复制表 {$m_t->getName()} 数据", "\n" );
				$this->om->begin();
				$primary_begin = 0;
				$raw_update_time = $o_t->getLastRawupdatetime();
				do 
				{
					$datas =  &$m_t->getData( $raw_update_time, $end_time, $this->copy_limit, $primary_begin );
					if ( $datas->isEmpty() )
					{
						$primary_begin = -1;
					}
					else
					{
						$primary_begin = $datas->getMaxPrimary();
						if ( is_null( $primary_begin ) )
						{
							$primary_begin = -1;
						}
						else
						{
							$res = $o_t->saveData( $datas );
							if ( $res === false )
							{
								$this->om->rollback();
								return false;
							}
							baseCommon::__echo( '.' );
						}
					}
					$datas->clear();
					unset( $datas );
				}
				while ( $primary_begin >= 0 );
				baseCommon::__echo( "\n" );
				$this->om->commit();
				baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t复制表 {$m_t->getName()} 数据成功", "\n" );
			}
		}
		return true;
	}
	/**
	 * @desc 初始化mysql表
	 */	
	protected function __iniMysqlTables()
	{
		$this->umtList->clear();
		if ( !empty( $this->table_list ) )
		{
			foreach ( $this->table_list as $table_name )
			{
				$table = new useMysqlTable( $this->mm, $table_name );
				$table->init();							
				$this->umtList->add( $table );
				unset( $table );
			}
		}
	}
	/**
	 * @desc 初始化oracle表
	 */
	protected function __iniOracleTables()
	{
		$this->uotList->clear();
		if ( !empty( $this->table_list ) )
		{
			foreach ( $this->table_list as $table_name )
			{
				$table = useOracleTable::newByTablename( $this->om, $table_name );
				$table->init();
				$this->uotList->add( $table, $table->getSrcName() );
				unset( $table );
			}
		}
	}
	/**
	 * @desc 根据{表名}初始化mysql表
	 * @param string $table_name 表名
	 * @param baseModelEx $model 数据库链接模块
	 */
	protected function &__iniMyTableByName( $table_name, $model = false )
	{
		if ( in_array( $table_name, $this->table_list ) )
		{
			if ( $model === false )
			{
				$model = $this->mm;
			}
			$table = new useMysqlTable( $model, $table_name );
			$table->init();
			return $table;
		}
		return false;
	}
	/**
	 * @desc 根据{表名}初始化oracle表
	 * @param string $table_name 表名
	 * @param baseModelEx $model 数据库连接模块
	 */
	protected function &__iniOraTableByName( $table_name, &$model = false )
	{
		if ( in_array( $table_name, $this->table_list ) )
		{
			if ( $model === false )
			{
				$model = &$this->om;
			}
			$table = useOracleTable::newByTablename( $model, $table_name );
			$table->init();
			return $table;
		}
		return false;
	}
	/**
	 * @desc 转换成oracle表
	 */
	protected function __toOracleTables()
	{
		$keys = $this->umtList->getKeys();
		if ( !empty( $keys ) )
		{
			foreach( $keys as $key )
			{
				$src = $this->umtList->getByKey( $key );
				$table = $src->toOracle( $this->om );
				$this->uotList->add( $table );
				unset( $table );
				unset( $src );
			}
		}
		unset( $keys );
	}
	/**
	 * @desc oracle表初始化创建
	 */
	protected function __iniCreateOracleTables()
	{
		$keys = $this->uotList->getKeys();
		if ( !empty( $keys ) )
		{
			foreach ( $keys as $key )
			{
				$table = $this->uotList->getByKey( $key );
				$res = $table->create();
				if ( in_array( $res, array( 1, 2 ) ) )
				{
					baseCommon::__echo( "复制表 {$table->getName()} 结构成功", "\n" );
				}
				else
				{
					baseCommon::__die( "复制表 {$table->getName()} 结构失败", "\n" );
				}
			}
		}
	}
	/**
	 * @desc 初始化t_user_amount镜像表
	 * @return cloneTUserAmountTable
	 */
	protected function &__iniCloneTUserAmount( &$model = false )
	{
		if ( $model === false )
		{
			$model = &$this->om;
		}
		$table = new cloneTUserAmountTable( $model );
		$table->init();
		return $table;
	}
	/**
	 * @desc 克隆t_user_amount表
	 */
	protected function __cloneTableTUserAmount()
	{
		// 初始化oracle的t_user_amount表
		$src_t = &$this->__iniOraTableByName( 't_user_amount', $this->om );
		// 初始化t_user_amount镜像表
		$dst_t = &$this->__iniCloneTUserAmount( $this->om );
		$limit = $this->copy_limit;
		$primary_begin = 0;
		$end_time = date( 'Y-m-d H:i:s' );
		$begin_time = $dst_t->getFuncDateField( 'f_date', 'max' );
		baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t开始克隆表 {$src_t->getName()} 数据", "\n" );
		do
		{
			$this->om->begin();
			// 根据user_id升序获取用户的彩金和冻结
			$src_t->clearSearchCond();
			$src_t->select( 'f_user_id' );
			$src_t->select( 'f_amount' );
			$src_t->select( 'f_freeze_amount' );
			if ( $begin_time !== false  )
			{
				$src_t->where( 'f_raw_update_time', '>', $begin_time, null, 'to_date', array('\'yyyy-mm-dd hh24:mi:ss\'') );
			}
			$src_t->where( 'f_raw_update_time', '<=', $end_time, null, 'to_date', array('\'yyyy-mm-dd hh24:mi:ss\'') );
			$src_t->orderBy( 'f_user_id' );
			$datas = &$src_t->getDataByCond( $limit, $primary_begin, 'f_user_id' );
			if ( $datas->isEmpty() )
			{
				$primary_begin = -1;
			}
			else
			{
				$primary_begin = $datas->getMaxPrimary();
				if ( is_null( $primary_begin ) || $primary_begin == '' )
				{
					$primary_begin = -1;
				}
				else
				{
					$keys = $datas->getKeys();
					foreach ( $keys as $key )
					{
						// 获取单个用户的id、彩金和冻结准备和镜像库做比较
						$data = &$datas->getByKey( $key );
						$user_id = $data->getByField( 'f_user_id' );
						$amount = $data->getByField( 'f_amount' );
						$freeze = $data->getByField( 'f_freeze_amount' );
						unset( $data );
						// 不存在用户id的时候为异常不做处理
						if ( !is_null( $user_id ) )
						{
							// 获取当前的clone数据值
							$date = date( 'Y-m-d' );
							$clone_datas = &$dst_t->getByDate( $user_id, $date );							
							if ( !$clone_datas->isEmpty() )
							// 存在则比较差值，并存入
							{
								$clone_data = &$clone_datas->getByKey( 0 );
								$clone_amount = $clone_data->getByField( 'f_amount' );
								$clone_freeze = $clone_data->getByField( 'f_freeze_amount' );
								unset( $clone_data );
								if ( is_null( $clone_amount ) && is_null( $clone_freeze ) )
								// 不存在则直接存入
								{
									$res = $dst_t->saveAmountFreezeByUseridDate( $amount, $freeze, $user_id, $date  );
									if ( $res === false )
									{
										$this->om->rollback();
										return false;
									}
								}
								else
								{
									// 当有差值时再镜像
									$amount_diff = round( $amount - $clone_amount, 2 );
									$freeze_diff = round( $freeze - $clone_freeze, 2 );
									$diff = ( $amount_diff != 0 ) || ( $freeze_diff != 0 ) ? true : false;
									if ( $diff === true )
									{
										$res = $dst_t->saveAmountFreezeByUseridDate( $amount_diff, $freeze_diff, $user_id, $date  );
										if ( $res === false )
										{
											$this->om->rollback();
											return false;
										}
									}
								}
							}
							else
							// 不存在则直接存入
							{
								$res = $dst_t->saveAmountFreezeByUseridDate( $amount, $freeze, $user_id, $date  );
								if ( $res === false )
								{
									$this->om->rollback();
									return false;
								}
							}
							unset( $clone_datas );							
						}
					}
				}
			}
			unset( $datas );
			baseCommon::__echo( '.' );
			$this->om->commit();
		}		
		while ( $primary_begin >= 0 );
		unset( $src_t );
		unset( $dst_t );
		baseCommon::__echo( "\n" );
		return true;
	}
	/**
	 * @desc 初始化月报表t_report_record
	 */
	protected function __iniTReportRecord()
	{
		$table = new tReportRecordTable( $this->om );
		$table->init();		
	}
	/**
	 * @desc 复制表
	 */
	public function cloneDb()
	{		
		$this->__iniMysqlTables();		
		$this->__toOracleTables();
		$this->__iniCreateOracleTables();
		$this->__iniTReportRecord();
	}
	/**
	 * @desc 删除所有表
	 */
	public function clear()
	{
		if ( !empty( $this->table_list ) )
		{
			foreach ( $this->table_list as $table_name )
			{				
				$table = useOracleTable::newByTablename( $this->om, $table_name );
				$table->init();
				$table->dropTable();
				unset( $table );
			}
		}
	}
	/**
	 * @desc 复制数据
	 */
	public function copyData()
	{
		$this->__iniMysqlTables();
		$this->__iniOracleTables();
		$this->__copyDataByTableMyToOra();
	}
	/**
	 * @desc 克隆t_user_amount表
	 */
	public function cloneTableTUserAmount()
	{
		$res = $this->__cloneTableTUserAmount();
		if ( $res === true )
		{
			baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t克隆表 t_user_amount 成功", "\n" );
		}
		else
		{
			baseCommon::__echo( date( 'Y-m-d H:i:s' ) . "\t克隆表 t_user_amount 失败", "\n" );
		}
	}
	/**
	 * @desc 插入mysql t_user测试数据
	 */
	/*
	public function iniMysqlTUser()
	{
		$table_name = 't_user';
		$count = 100;
		$row = 500;
		$this->mm->begin();
		for ( $i = 0; $i < $count; $i++ )
		{
			$sql = "insert into `{$table_name}` (`name`, `nick_name`, `password`,`pay_password`,`last_login_time`,`last_login_ip`,`raw_add_time`) values ";
			$sql_p = array();
			for ( $j = 0; $j < $row; $j++ )
			{
				$micro_time = microtime();
				$micro_time_arr = explode( ' ', $micro_time );
				$micro_time_number = round( floatval( date('ymdHis') ) + floatval( $micro_time_arr[0] ) , 6 );
				$micro_time_number = str_replace( '.', '', $micro_time_number );
				$name = $micro_time_number . substr( '000' . $j, -4, 4 );
				$sql_p[] = "('{$name}', '{$name}','','','0000-00-00 00:00:00','',now())";
				usleep( 1 );
			}
			$sql .= implode( ',', $sql_p );
			$res = $this->mm->exec( $sql );
			if ( $res === false )
			{
				$this->mm->rollback();
				return;
			}
			baseCommon::__echo( '.' );
		}
		$this->mm->commit();
	}
	*/
	/**
	 * @desc 增加函数用来进行每个用户流水对帐
	 * @since 20140324 gkl
	 * @param date $day 日期
	 */
	public function checkUserByDay( $day )
	{
		$check = baseCommon::checkDate( $day );
		if ( $day === '' || $check === false )
		{
			$day = date( 'Y-m-d' );
		}
		$begin = $day . ' 00:00:00';
		$end = $day . ' 23:59:59';
		$users_collection = &m( 'checkUserByDay' )->getUsers( $begin, $end );
		if ( !$users->isEmpty() )
		{
			$keys = $users_collection->getKeys();
			foreach ( $keys as $key )
			{
				$users_obj = &$users_collection->getByKey( $key );
				$user_id = $users_obj->getByField( 'f_user_id' );
				$clone = m('checkUserByDay')->getCloneChange( $user_id, $date );
				$change = m('checkUserByDay')->getChange( $user_id, $begin, $end );
				$diff_1 = round( $clone[0] - $change[0], 2 );
				$diff_2 = round( $clone[1] - $change[1], 2 );
 				if ( $diff_1 != 0 || $diff_2 != 0 )
 				{
 					$log = "日期 {$date} 用户id {$user_id} 余额差 {$diff_1} 冻结差 {$diff_2}";
 					baseCommon::writeLog( $log, 'checkDay' );
 				}
 				unset( $users_obj );
			}
			unset( $keys );
		}
		unset( $users_collection );
	}
}