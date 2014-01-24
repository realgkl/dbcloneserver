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
	/*
	public function imtu()
	{
		m( 'dbCloneNew' )->iniMysqlTUser();
	}
	*/
}