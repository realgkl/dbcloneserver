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
	public function createUpSql( $table_name, &$params = array(), &$fields = null, $occ = '?' )
	{
		$field_name_arr = $fields->getFieldsArr();
		$data_arr = array();
		$params = array();
		$where = '';
		$param_last = null;
		foreach ( $field_name_arr as $field_name )
		{
			$field = &$fields->getByName( $field_name );
			$data = $this->getByField( $field_name );			
			$data_type = strtoupper( $field->getType() );
			$data_len = $field->getLen();
			$param = null;
			switch ( $data_type )
			{
				case 'DATE':
					if ( $data == '0000-00-00 00:00:00' )
					{
						$data = '';
					}
					$param = $data;
					$data = "to_date({$occ},'yyyy-mm-dd HH24:MI:SS')";
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
						$param = $data;
						$data = "utl_raw.cast_to_raw({$occ})";
					}
					else
					{
						$param = $data;
						$data = $occ;
					}
					break;
				default:
					$param = $data;
					$data = $occ;
					break;
			}
			if ( $field->getIsPrimary() === true )
			{
				$where = "WHERE {$field_name} = {$data}";
				$param_last = $param;
			}
			else
			{
				$data_arr[] = "{$field_name} = {$data}";
				$params[] = $param;
			}
			unset( $param );
		}
		if ( is_null( $param_last ) )
		{
			return '';
		}
		$params[] = $param_last;
		return "UPDATE {$table_name} SET " . implode( ',', $data_arr ) . " {$where}";
	}
	/**
	 * @see baseRecord::createInsSql()
	 */
	public function createInsSql( $table_name, &$params = array(), &$fields = null, $occ = '?' )
	{
		$field_name_arr = $fields->getFieldsArr();
		$data_arr = array();
		$field_arr = array();
		$params = array();
		foreach ( $field_name_arr as $field_name )
		{
			$field = &$fields->getByName( $field_name );
			$data = $this->getByField( $field_name );			
			$data_type = strtoupper( $field->getType() );
			$data_len = $field->getLen();
			$param = null;
			switch ( $data_type )
			{
				case 'DATE':
					if ( $data == '0000-00-00 00:00:00' )
					{
						$data = '';
					}
					$param = $data;
					$data = "to_date({$occ},'yyyy-mm-dd HH24:MI:SS')";
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
						$param = $data;
						$data = "utl_raw.cast_to_raw({$occ})";
					}
					else
					{
						$param = $data;
						$data = $occ;
					}
					break;
				default:
					$param = $data;
					$data = $occ;
					break;
			}
			$field_arr[] = $field_name;
			$data_arr[] = $data;
			$params[] = $param;
			unset( $param );
		}
		return "INSERT INTO {$table_name} (" . implode( ',', $field_arr ) . ') VALUES (' . implode( ',', $data_arr ) . ')';
	}
}