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
}