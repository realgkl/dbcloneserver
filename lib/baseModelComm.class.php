<?php
/**
 * @desc 公用model不含实际逻辑
 * @author gkl
 * @since 20131211
 */
class baseModelComm extends baseModelEx
{
	/**
	 * @see baseModelEx::getAll()
	 */
	public function getAll( $sql, $params = array() )
	{
			
	}
	/**
	 * @see baseModelEx::getRow()
	 */
	public function getRow( $sql, $params = array() )
	{
		
	}
	/**
	 * @see baseModelEx::exec()
	 */
	public function exec( $sql, $params = array() )
	{
		
	}
	/**
	 * @see baseModelEx::begin()
	 */
	public function begin()
	{
		
	}
	/**
	 * @see baseModelEx::rollback()
	 */
	public function rollback()
	{
		
	}
	/**
	 * @see baseModelEx::recordExists()
	 */
	public function recordExists( $table, $key_filed, $value )
	{
		
	}
	/**
	 * @see baseModelEx::commit()
	 */
	public function commit()
	{
		
	}
	/**
	 * @see baseModelEx::__exists_table()
	 */
	protected function __exists_table( $table_name )
	{
		return false;
	}
}