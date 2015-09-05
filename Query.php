<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
				
class Query{
	protected $ci;
	public $dbase;
	
    public function __construct( $params = array() ){
		$this->ci =& get_instance();
		
		if( 
			!empty( $params ) 
			&&
			isset( $params['dbase'] )
			&&
			$params['dbase'] != ''
		){
			switch( $params['dbase'] ){
				case '':
					$this->set_dbase($this->ci->load->database('default', TRUE)); 
				break;
				default:
					$this->set_dbase($this->ci->load->database( $params['dbase'], TRUE )); 
				break;
			}
		} else{
			$this->set_dbase($this->ci->load->database('default', TRUE)); 
		}
    }
	
	public function set_dbase( $val ){
		$this->dbase = $val;
	}
	
	public function get_dbase(){
		return $this->dbase;
	}
	
	public function native_query($sql, $return = true){
		$query = $this->dbase->query($sql);
		
		if( !$query )
			$query = $this->get_error();
		
		if( $return && $query )
			return $query->result_array();
		else
			return $query;
	}
	
	public function select( $details = array(), $check_query = FALSE, $count = FALSE, $escape = TRUE ){	
		$ret = array();
		
		try{
			if( CI_VERSION < 3 )
				$this->dbase->_protect_identifiers = $escape;
			else
				$this->dbase->protect_identifiers($details['table'], $escape);
			
			$table = $details['table'];
			$fields = isset( $details['fields'] ) ? ( is_array( $details['fields'] ) ? implode( ',', $details['fields'] ) : $details['fields'] ) : "`{$table}`.*";
			$order = isset( $details['order'] ) && strlen( trim( $details['order'] ) ) > 0 ? $details['order'] : '';
			$group = isset( $details['group'] ) && strlen( trim( $details['group'] ) ) > 0 ? $details['group'] : '';
			$limit = isset( $details['limit'] ) ? $details['limit'] : '';
			$start = isset( $details['start'] ) ? $details['start'] : ( ( !empty( $limit ) ) ? 0 : '' );
			$distinct = isset( $details['distinct'] ) ? (( $details['distinct'] ) ? TRUE : FALSE ) : FALSE;
			$string_conditions = isset( $details['string_conditions'] ) ? $details['string_conditions'] : '';
			
			/* format conditions */
			$like_conditions = array();
			$wildcard = 'both';
			if( isset( $details['conditions']['like'] ) ){
				$wildcard = isset( $details['conditions']['like']['wildcard'] ) ? $details['conditions']['like']['wildcard'] : $wildcard;
				unset( $details['conditions']['like']['wildcard'] );
				$like_conditions = $details['conditions']['like'];
				unset( $details['conditions']['like'] );
			}
			$or_like_conditions = array();
			if( isset( $details['conditions']['or_like'] ) ){
				$or_like_conditions = $details['conditions']['or_like'];
				unset( $details['conditions']['or_like'] );
			}
			$not_like_conditions = array();
			if( isset( $details['conditions']['not_like'] ) ){
				$not_like_conditions = $details['conditions']['not_like'];
				unset( $details['conditions']['not_like'] );
			}
			$or_not_like_conditions = array();
			if( isset( $details['conditions']['or_not_like'] ) ){
				$or_not_like_conditions = $details['conditions']['or_not_like'];
				unset( $details['conditions']['or_not_like'] );
			}
			$or_conditions = array();
			if( isset( $details['conditions']['or'] ) ){
				$or_conditions = $details['conditions']['or'];
				unset( $details['conditions']['or'] );
			}
			$and_conditions = array();
			if( isset( $details['conditions']['and'] ) ){
				$and_conditions = $details['conditions']['and'];
				unset( $details['conditions']['and'] );
			}
			$conditions = isset( $details['conditions'] ) ? $details['conditions'] : array();
			/* end */
			
			
			if( $distinct )
				$this->dbase->distinct();
			
			if( !empty( $fields ) )
				$this->dbase->select( $fields, $escape );
			
			if( !empty( $order ) )
				$this->dbase->order_by( $order );
				
			if( !empty( $group ) )
				$this->dbase->group_by( $group );
				
			$this->dbase->where( $conditions );
			
			if( !empty( $or_conditions ) )
				$this->dbase->or_where( $or_conditions );
				
			if( !empty( $and_conditions ) )
				$this->dbase->where( $and_conditions );
				
			if( !empty( $like_conditions ) )
				$this->dbase->like($like_conditions, false, $wildcard);
				
			if( !empty( $or_like_conditions ) )
				$this->dbase->or_like($or_like_conditions); 
				
			if( !empty( $not_like_conditions ) )
				$this->dbase->not_like($not_like_conditions); 
				
			if( !empty( $or_not_like_conditions ) )
				$this->dbase->not_like($or_not_like_conditions); 
			
			if( !empty( $string_conditions ) )
				$this->dbase->where( $string_conditions, NULL, FALSE );
				
			/* joins */
			if( 
				isset( $details['joins'] ) 
				&&
				!empty( $details['joins'] )
				&&
				is_array( $details['joins'] )
			){
				foreach( $details['joins'] as $key => $values ){
					$_join = $values;
					$type = isset( $_join['type'] ) ? $_join['type'] : '';
					unset( $_join['type'] );
					$this->dbase->join( $key, http_build_query($_join,'',' AND '), $type); 
				}
			}
			/* end */
			
			if( !empty( $limit ) )
				$this->dbase->limit( $limit, $start );
			
			if( $count )
				$query = $this->dbase->from( $table );
			else
				$query = $this->dbase->get( $table );
				
			if( $check_query )
				echo $this->dbase->last_query();
			else
				$this->dbase->save_queries = FALSE; 
				
			if( !$query )
				$ret = $this->get_error();
			else {
				$ret = ( $count ) ? $query->count_all_results() : $query->result_array();
				
				if( !$count )
					$query->free_result();
			}
		} catch ( Exception $e ){
			$ret = $e->getMessage();
		}
		
		return $ret;
	}
	
	public function insert( 
		$tablename = '', 
		$details = array(), 
		$return_id = FALSE, 
		$check_query = FALSE 
	){			
		$result = array();
		
		try{
				
			$res = $this->dbase->insert($tablename, $details);
			
			if( $check_query )
				echo $this->dbase->last_query();
			
			if( !$res )
				$res = $this->get_error();
			else
				$insert_id = $this->dbase->insert_id();
			
			$result = ( $return_id ) ? array( 'id' => isset( $insert_id ) ? $insert_id : NULL, 'response' => $res ) : $res;
			
		} catch ( Exception $e ){
			$result = $e->getMessage();
		}
		
		return $result;
	}
	
	public function insert_batch( $tablename = '', $details = array(), $check_query = false ){
		$query = array();
		
		try{
			
			$query = $this->dbase->insert_batch($tablename, $details);
			
			if( $check_query )
				echo $this->dbase->last_query();
			
		} catch ( Exception $e ){
			$query = $e->getMessage();
		}
		
		return $query;
	}
	
	public function update( $tablename = '', $conditions = array(), $details = array(), $check_query = false ){	
		$res = array();
		
		try{
			
			$this->dbase->where( $conditions );
			$res = $this->dbase->update($tablename, $details);
			
			if( $check_query )
				echo $this->dbase->last_query();
				
			if( !$res )
				$res = $this->get_error();
		
		} catch ( Exception $e ){
			$res = $e->getMessage();
		}
		
		return $res;
	}
	
	public function delete( $tablename = '', $conditions, $check_query = false ){
		$res = array();
		
		try{
			
			$res = $this->dbase->delete($tablename, $conditions);
			
			if( $check_query )
				echo $this->dbase->last_query();
				
			if( !$res )
				$res = $this->get_error();
		
		} catch ( Exception $e ){
			$res = $e->getMessage();
		}
		
		return $res;
	}
	
	private function get_error(){
		if( CI_VERSION < 3 ){
			return array(
				'code' => $this->dbase->_error_number(),
				'message' => $this->dbase->_error_message()
			);				
		} else 
			return $this->dbase->error();
	}
}

?>