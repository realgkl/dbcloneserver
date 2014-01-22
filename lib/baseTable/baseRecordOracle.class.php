<?php
/**
 * @desc oracle结构体-基类
 * @author gkl
 * @since 20140113
 */
class baseRecordOracle extends baseRecord
{
	/**
	 * @see baseRecord::createSql()
	 */
	public function createSql( &$fields = null )
	{		
		$field_name_arr = $fields->getFieldsArr();
		$data_arr = array();
		foreach ( $field_name_arr as $field_name )
		{
			$data = $this->getByField( $field_name );
			$field = &$fields->getByName( $field_name );
			$data_type = strtoupper( $field->getType() );
			$data_len = $field->getLen();
			switch ( $data_type )
			{
				case 'DATE':
					if ( $data == '0000-00-00 00:00:00' )
					{
						$data = '';
					}
					$data = "to_date('{$data}','yyyy-mm-dd HH24:MI:SS')";
					break;
				case 'VARCHAR2':
				case 'RAW':
				case 'CHAR':
					$str_len = strlen( $data );
					if ( $str_len > $data_len )
					{
						$data = substr( $data, 0, $data_len );
					}
					if ( $data_type == 'RAW' )
					{
						$data = "utl_raw.cast_to_raw('{$data}')";
					}
					else
					{
						$data = "'{$data}'";
					}
					break;
				default:
					if ( $data == '' )
					{
						$data = 'NULL';
					}
					break;
			}
			$data_arr[] = $data;
		}
		return "SELECT " . implode( ',', $data_arr ) . " FROM DUAL";
	}
	/**
	 * @see baseRecord::createUpSql()
	 */
	public function createUpSql( &$fields = null )
	{
		$field_name_arr = $fields->getFieldsArr();
		$data_arr = array();
		$where = '';		
		foreach ( $field_name_arr as $field_name )
		{
			$field = &$fields->getByName( $field_name );
			$data = $this->getByField( $field_name );			
			$data_type = strtoupper( $field->getType() );
			$data_len = $field->getLen();
			switch ( $data_type )
			{
				case 'DATE':
					if ( $data == '0000-00-00 00:00:00' )
					{
						$data = '';
					}
					$data = "to_date('{$data}','yyyy-mm-dd HH24:MI:SS')";
					break;
				case 'VARCHAR2':
				case 'RAW':
				case 'CHAR':
					$str_len = strlen( $data );
					if ( $str_len > $data_len )
					{
						$data = substr( $data, 0, $data_len );
					}
					if ( $data_type == 'RAW' )
					{
						$data = "utl_raw.cast_to_raw('{$data}')";
					}
					else
					{
						$data = "'{$data}'";
					}
					break;
				default:
					if ( $data == '' )
					{
						$data = "''";
					}
					break;
			}
			if ( $field->getIsPrimary() === true )
			{
				$where = "WHERE {$field_name} = {$data}";
			}
			else
			{
				$data_arr[] = "{$field_name} = {$data}";
			}
		}
		return "SET " . implode( ',', $data_arr ) . " {$where}";
	}
}