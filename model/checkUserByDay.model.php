<?php
/**
 * @desc 检查用户每日流水
 * @author gkl
 * @since 20140324 gkl
 */
class checkUserByDayModel extends baseModelOci
{
	/**
	 * @desc t_user_amount_change_log表实例
	 * @var baseTableOracle
	 * @since 20140324 gkl
	 */
	protected $t_u_a_c_l = null;
	/**
	 * @desc t_recharge_log表实例
	 * @var baseTableOracle
	 * @since 20140326 gkl
	 */
	protected $t_r_l = null;
	/**
	 * @desc 初始化t_recharge_log表对象
	 * @since 20140326 gkl
	 */
	protected function iniTableTRL()
	{
		if ( !isset( $this->t_r_l ) )
		{
			$this->t_r_l = new useOracleTable( $this, 't_recharge_log' );
		}
	}
	/**
	 * @desc 初始化t_user_amount_change_log表对象
	 * @since 20140324 gkl
	 */
	protected function iniTableTUACL()
	{
		if ( !isset( $this->t_u_a_c_l ) )
		{
			$this->t_u_a_c_l = new useOracleTable( $this, 't_user_amount_change_log' );
		}
	}
	/**
	 * @desc 获取时间段内的用户
	 * @since 20140324 gkl
	 * @param datetime $begin 流水开始时间
	 * @param datetime $end 流水结束时间
	 * @return baseCollection
	 */
	public function &getUsers( $begin, $end )
	{
		$this->iniTableTUACL();
		$this->t_u_a_c_l->clearSearchCond();
		$this->t_u_a_c_l->select( 'f_user_id' );
		$this->t_u_a_c_l->where( 'f_raw_add_time', 'between', $begin, $end );
		$this->t_u_a_c_l->groupBy( 'f_user_id' );
		$this->t_u_a_c_l->orderBy( 'f_user_id' );
		return $this->t_u_a_c_l->getDataByCond();
	}
	/**
	 * @desc 获取某个日期的变动金额
	 * @param integer $user_id
	 * @param date $date
	 * @return array(彩金变动,冻结变动)
	 */
	public function getCloneChange( $user_id, $date )
	{
		$this->iniTableTUACL();
		$this->t_u_a_c_l->clearSearchCond();
		$this->t_u_a_c_l->select( 'f_amount', 'sum', 'f_amount' );
		$this->t_u_a_c_l->select( 'f_freeze_amount', 'sum', 'f_freeze_amount' );
		$this->t_u_a_c_l->where( 'f_user_id', '=', $user_id );
		$this->t_u_a_c_l->where( 'f_date', '=', $date, null, 'to_date', 'yyyy-mm-dd' );
		$collect = &$this->t_u_a_c_l->getDataByCond();
		if ( $collect->isEmpty() )
		{
			return false;
		}
		$keys = $collect->getKeys();
		foreach ( $keys as $key )
		{
			$record = &$collect->getByKey( $key );
			break;
		}
		$amount = $record->getByField( 'f_amount' );
		$freeze =$record->getByField( 'f_freeze_amount' );
		unset( $record );
		unset( $collect );
		$this->t_u_a_c_l->clearCollection();
		return array(
			$amount,
			$freeze,
		);
	}
	/**
	 * @desc 获取充值
	 * @since 20140324 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getRecharge( $user_id, $begin, $end )
	{
		$this->iniTableTRL();
		$this->t_r_l->clearSearchCond();
		$this->t_r_l->select( 'f_amount', 'sum', 'f_amount' );
		$this->t_r_l->where( 'f_user_id', '=', $user_id );
		$this->t_r_l->where( 'f_recharge_time', 'between', $begin, $end, 'to_date', 'yyyy-mm-dd hh24:mi:ss', 'to_date', 'yyyy-mm-dd hh24:mi:ss' );
		$this->t_r_l->where( 'f_delete_flag', '=', 0 );
		$this->t_r_l->where( 'f_status', '=', 1 );
		$this->t_r_l->where( 'f_resource', '<>', 3 );
		$collect = &$this->t_r_l->getDataByCond();
		if ( $collect->isEmpty() )
		{
			return false;
		}
		$keys = $collect->getKeys();
		foreach ( $keys as $key )
		{
			$record = &$collect->getByKey( $key );
			break;
		}
		$amount = $record->getByField( 'f_amount' );
		unset( $record );
		unset( $collect );
		$this->t_r_l->clearCollection();
		return $amount;
	}
	/**
	 * @desc 获取礼金
	 * @since 20140324 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getGift( $user_id, $begin, $end )
	{
		$this->iniTableTUACL();
		$this->t_u_a_c_l->clearSearchCond();
		$this->t_u_a_c_l->select( 'f_amount', 'sum', 'f_amount' );
		$this->t_r_l->where( 'f_user_id', '=', $user_id );
		$this->t_r_l->where( 'f_raw_add_time', 'between', $begin, $end, 'to_date', 'yyyy-mm-dd hh24:mi:ss', 'to_date', 'yyyy-mm-dd hh24:mi:ss' );
		$this->t_r_l->where( 'f_delete_flag', '=', 0 );
		$this->t_r_l->where( 'f_trade_type', '=', 1 );
		$this->t_r_l->where( 'f_memo', '<>', '充值' );
		$this->t_r_l->where( 'f_memo', 'is not', 'null' );
		$collect = &$this->t_r_l->getDataByCond();
		if ( $collect->isEmpty() )
		{
			return false;
		}
		$keys = $collect->getKeys();
		foreach ( $keys as $key )
		{
			$record = &$collect->getByKey( $key );
			break;
		}
		$amount = $record->getByField( 'f_amount' );
		unset( $record );
		unset( $collect );
		$this->t_r_l->clearCollection();
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的彩金变动流水
	 * @since 20140324 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 * @return array(彩金变动,冻结变动)
	 */
	public function getChange( $user_id, $begin, $end )
	{
		$this->iniTableTUACL();
		$this->t_u_a_c_l->clearSearchCond();
		$this->t_u_a_c_l->select( 'f_amount' );
		$this->t_u_a_c_l->select( 'f_trade_type' );
		$this->t_u_a_c_l->select( 'f_memo' );
		$this->t_u_a_c_l->where( 'f_user_id', '=', $user_id );
		$this->t_u_a_c_l->where( 'f_date', '=', $date, null, 'to_date', 'yyyy-mm-dd' );
		$collect = &$this->t_u_a_c_l->getDataByCond();
	}
}