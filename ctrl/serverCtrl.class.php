<?php
/**
 * @desc 测试
 */
class serverCtrl extends baseCtrlServer
{
	public function cld()
	{
		m( 'dbCloneNew' )->cloneDB();
	}
	
	public function cc()
	{
		m( 'dbCloneNew' )->clear();
	}
	
	public function cpd()
	{
		m( 'dbCloneNew' )->copyData();
	}
	
	public function ctua()
	{
		m( 'dbCloneNew' )->cloneTableTUserAmount();
	}
	/**
	 * @desc 增加函数先备份数据再镜像t_user_amount
	 */
	public function cpdActua()
	{
		m( 'dbCloneNew' )->copyData();
		m( 'dbCloneNew' )->cloneTableTUserAmount();
	}
	/*
	public function imtu()
	{
		m( 'dbCloneNew' )->iniMysqlTUser();
	}
	*/
	/**
	 * @desc 增加函数用来进行每个用户流水对帐
	 * @since 20140324 gkl
	 * @param date $day 日期
	 */
	public function cubd( $day = '' )
	{
		m( 'dbCloneNew' )->checkUserByDay( $day );
	}
}