<?php
/**
 * @desc 检查用户每日流水
 * @author gkl
 * @since 20140324 gkl
 */
class checkUserByDayModel extends baseModelOci
{
	/**
	 * @desc 获取时间段内的用户
	 * @since 20140324 gkl
	 * @param datetime $begin 流水开始时间
	 * @param datetime $end 流水结束时间
	 * @return Array
	 */
	public function getUsers( $begin, $end )
	{
		$sql = "
			select
				f_user_id
			from
				t_user_amount_change_log
			where
				f_raw_add_time between to_date(" . baseModelOci::OCC . "0,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss')
			group by
				f_user_id
			order by
				f_user_id
				";
		$params = array();
		$params[] = $begin;
		$params[] = $end;		
		return $this->getAll( $sql, $params, false );
	}
	/**
	 * @desc 获取某个日期的变动金额
	 * @param integer $user_id
	 * @param date $date
	 * @return array(彩金变动,冻结变动)
	 */
	public function getCloneChange( $user_id, $date )
	{
		$sql = "
			select
				sum(f_amount) as f_amount, sum(f_freeze_amount) as f_freeze_amount
			from
				clone_t_user_amount
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_date = to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd')
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $date;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		$freeze = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
			$freeze = $res[strtoupper( 'f_freeze_amount' )];
			$amount = is_null( $amount ) ? 0 : $amount;
			$freeze = is_null( $freeze ) ? 0 : $freeze;
		}
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
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_recharge_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_recharge_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_status = " . baseModelOci::OCC . "4
				and f_resource <> " . baseModelOci::OCC . "5
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 1;
		$params[] = 3;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取礼金和活动赠金
	 * @since 20140324 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getGift( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and 
					(
						(
							f_trade_type = " . baseModelOci::OCC . "4
							and f_memo <> " . baseModelOci::OCC . "5
							and f_memo is not null
						)
						OR f_trade_type = " . baseModelOci::OCC . "6
					)
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 1;
		$params[] = '充值';
		$params[] = 30;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的中奖金额
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getPirze( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 9;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的保险索赔
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getInsureClaims( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 15;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的保险购买
	 * @since 20140410 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getInsureBuy( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 14;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的投注冻结
	 * @since 20140410 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getOrderFreeze( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type in (" . baseModelOci::OCC . "4," . baseModelOci::OCC . "5)
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 5;
		$params[] = 10;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的投注出票
	 * @since 20140410 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getOrderBuy( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 3;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的投注退款
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getOrderBack( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
				and f_memo not like " . baseModelOci::OCC . "5
				and f_memo not like " . baseModelOci::OCC . "6
				and f_memo <> " . baseModelOci::OCC . "7
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 11;
		$params[] = '提现审核不通过%';
		$params[] = '打款失败%';
		$params[] = '保本方案，返回用户保本金额';
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的人工解冻
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getOrderManualUnfreeze( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 8;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的提现驳回
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getWithdrawBack( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and
					(
						(
							f_trade_type = " . baseModelOci::OCC . "4
							and
								(
									f_memo like " . baseModelOci::OCC . "5
									or f_memo like " . baseModelOci::OCC . "6
								)
						)
						or f_trade_type = " . baseModelOci::OCC . "7
					)
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 11;
		$params[] = '提现审核不通过%';
		$params[] = '打款失败%';
		$params[] = 17;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的提现退款
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getWithdrawReturn( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 16;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的提现冻结
	 * @since 20140410 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getWithdrawFreeze( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type in (" . baseModelOci::OCC . "4," . baseModelOci::OCC . "5)
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 2;
		$params[] = 13;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的提现成功
	 * @since 20140410 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getWithdrawSucc( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 18;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的客户返佣
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getBrokerage( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and f_trade_type = " . baseModelOci::OCC . "4
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 12;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
		return $amount;
	}
	/**
	 * @desc 获取某个时间段内的保本方案
	 * @since 20140409 gkl
	 * @param integer $user_id
	 * @param datetime $begin
	 * @param datetime $end
	 */
	public function getBreakEven( $user_id, $begin, $end )
	{
		$sql = "
			select
				sum(f_amount) as f_amount
			from
				t_user_amount_change_log
			where
				f_user_id = " . baseModelOci::OCC . "0
				and f_raw_add_time between to_date(" . baseModelOci::OCC . "1,'yyyy-mm-dd hh24:mi:ss') and to_date(" . baseModelOci::OCC . "2,'yyyy-mm-dd hh24:mi:ss')
				and f_delete_flag = " . baseModelOci::OCC . "3
				and
					(
						(
							f_trade_type = " . baseModelOci::OCC . "4
							and f_memo = " . baseModelOci::OCC . "5
						)
						or f_trade_type = " . baseModelOci::OCC . "6
					)
			";
		$params = array();
		$params[] = $user_id;
		$params[] = $begin;
		$params[] = $end;
		$params[] = 0;
		$params[] = 11;
		$params[] = '保本方案，返还用户保本金额';
		$params[] = 19;
		$res = $this->getRow( $sql, $params, false );
		$amount = 0;
		if ( $res !== false )
		{
			$amount = $res[strtoupper( 'f_amount' )];
		}
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
		// 彩金入
		$in_arr = array();
		// 彩金出
		$out_arr = array();
		// 冻结入
		$f_in_arr = array();
		// 冻结出
		$f_out_arr = array();
		// 彩金入明细
		$in_arr[] = $this->getRecharge( $user_id, $begin, $end );
		$in_arr[] = $this->getGift( $user_id, $begin, $end );
		$in_arr[] = $this->getPirze( $user_id, $begin, $end );
		$in_arr[] = $this->getInsureClaims( $user_id, $begin, $end );
		$in_arr[] = $this->getOrderBack( $user_id, $begin, $end );
		$in_arr[] = $this->getWithdrawBack( $user_id, $begin, $end );
		$in_arr[] = $this->getWithdrawReturn( $user_id, $begin, $end );
		$in_arr[] = $this->getOrderManualUnfreeze( $user_id, $begin, $end );
		$in_arr[] = $this->getBrokerage( $user_id, $begin, $end );
		$in_arr[] = $this->getBreakEven( $user_id, $begin, $end );
		// 彩金出明细
		$out_arr[] = $this->getInsureBuy( $user_id, $begin, $end );
		$out_arr[] = $this->getOrderFreeze( $user_id, $begin, $end );
		$out_arr[] = $this->getWithdrawFreeze( $user_id, $begin, $end );
		// 冻结入明细
		$f_in_arr[] = $this->getOrderFreeze( $user_id, $begin, $end );
		$f_in_arr[] = $this->getWithdrawFreeze( $user_id, $begin, $end );
		// 冻结出明细
		$f_out_arr[] = $this->getOrderBack( $user_id, $begin, $end );
		$f_out_arr[] = $this->getOrderBuy( $user_id, $begin, $end );
		$f_out_arr[] = $this->getWithdrawSucc( $user_id, $begin, $end );
		$f_out_arr[] = $this->getWithdrawBack( $user_id, $begin, $end );
		$f_out_arr[] = $this->getOrderManualUnfreeze( $user_id, $begin, $end );
		// 计算彩金变动
		$amount = round( array_sum( $in_arr ) - array_sum( $out_arr ), 2 );
		$freeze = round( array_sum( $f_in_arr ) - array_sum( $f_out_arr ), 2 );
		return array(
			$amount,
			$freeze,
		);
	}
}