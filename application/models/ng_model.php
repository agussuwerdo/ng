<?php
class Ng_model extends CI_Model 
{
	public $schema = '';
	public $table = '';
	public $pk = array();
	public $limit = 0;
	public $offset = 0;
	public $like = array();
	public $order_by = array();
	
    function __construct() 
	{
        parent::__construct();

    }
	
	function set_table($table)
	{
		$this->table = $table;
	}
	
	public function set_schema($schema='public')
	{
		$this->schema = $schema;
	}
	
	public function get_schema()
	{
		return $this->schema;
	}
	
	public function get_table()
	{
		return $this->table;
	}
	
	public function set_pk($pkey)
	{
		$this->pk = $pkey;
	}
	
	public function set_where($where=array())
	{
		if ($where)
			$this->where = $where;
	}
	
	public function set_order($order=array())
	{
		if ($order)
			$this->order_by = $order;
	}
	
	public function set_group($group=array())
	{
		if ($group)
			$this->group_by = $group;
	}
	
	public function set_limit($limit=0)
	{
		if ($limit > 0)
			$this->limit = $limit;
	}
	
	public function set_offset($offset=0)
	{
		if ($offset > 0)
			$this->offset = $offset;
	}
	
	function get($where='') 
	{
		$data = array();
	
		if ( is_array($where) )
		{
			foreach ($where as $key => $value)
			{
				$this->db->where($key, $value);
			}
		}else if(isset($this->where))
		{
			$this->db->where($this->where);
		}else
		{
			if(isset($this->pk[0]))
			{
				$this->db->where($this->pk[0], $where?:null);
				
			}else{
				echo 'no pk fields specified in models';
				echo '<br>';
				echo 'use set_pk() function to set pkey';
				die;
			}
		}
		// get array data
		$this->db->select('tbl.*');
		// print_r($this->pk[0]);
		// die;
		
		if(isset($this->pk))
		{
			foreach($this->pk as $key=>$value)
			{
				$this->db->order_by($this->pk[$key]);
			}
		}
		
		$query = $this->db->get($this->schema.'.'.$this->table.' tbl');
		
		if ( $query->num_rows() == 1)
		{
            $data = $query->row_array();
			$data['field_info'] =  $this->get_field_info();
			$query->free_result();
			
		}else
		{
			$data = $this->feed_blank();
			// $data['num_rows'] = $query->num_rows();
			// $data['field_info'] =  $this->get_field_info();
		}
		// echo $this->db->last_query();
        return $data;
	}
	
	
    function get_list() 
	{
		$this->db->select('tbl.*');
		
		if($this->where)
		$this->db->where($this->where);
		
		foreach ($this->order_by as $key => $value)
		{
			$this->db->order_by($key, $value);
		}

		if (!$this->limit AND !$this->offset)
			$query = $this->db->get($this->schema.'.'.$this->table.' tbl');
		else
			$query = $this->db->get($this->schema.'.'.$this->table.' tbl',$this->limit,$this->offset);
		
		// echo $this->db->last_query();
		// exit;
		//  = array('1'=>'2');
		// print_r($query->result_array());
		$query->field_info = $this->get_field_info();
        if($query->num_rows()>0)
		{
			return $query;
        
		}else
		{
			$query->free_result();
			$query = $this->feed_blank();
            return $query;
        }
	}
	
	function save($data,$act=null)
	{
		$msg = array();
		$msg['result'] 	= 'success';
		$msg['message'] = '';
		$msg['data'] 	= $data;
		$return = 0;
		$action = '';
		$data = $this->clean_data($data);
		$data = $this->cut_oversize_data($data);
		$msg['message'] = $this->message;
		if(isset($this->pk))
		{
			foreach($this->pk as $key=>$value)
			{
				$where[$this->pk[$key]] = $data[$this->pk[$key]];
			}
			$msg['data'] 	= $where;
		}
		$this->db->select($this->pk[0]);
		if (isset($this->pk)){
			if($data[$this->pk[$key]]=='')
			{
				$msg['result'] 	= 'failed';
				$msg['message'] = 'DATA EMPTY';
				return $msg;
			}
			$this->db->where($where);
			$msg['data'] 	= $where;
		}
		$query = $this->db->get($this->schema.'.'.$this->table);
		//
		$num_rows = $query->num_rows();
		if($num_rows==0)
		{
			$msg['message'] . 'Insert Sukses';
			$action = 'New Data';
			// insert new row
			$new = $this->db->insert($this->schema.'.'.$this->table, $data);		
			$return = $new;
			
		}elseif($num_rows==1)
		{
			$msg['message'] .= 'Update Sukses';
			$this->db->where($where);
			$value_old = $this->db->get($this->schema.'.'.$this->table);
			$value_old = $value_old->row_array();
			$action = 'Edit Data';
			// update data
			$this->db->where($where);		
			$update = $this->db->update($this->schema.'.'.$this->table,$data);
			
			if(isset($this->pk))
			{
				foreach($this->pk as $key=>$value)
				{
					$return .= $data[$this->pk[$key]];
				}
			}
			
		}elseif($num_rows>1)
		{
			$return = false;
			$msg['result'] 	= 'failed';
			$msg['message']  .= 'Ditemukan data di server lebih dari satu [ '.$num_rows.' rows]';
			
			if(isset($this->pk))
			{
				foreach($this->pk as $key=>$value)
				{
					$msg .= chr(13).$data[$this->pk[$key]];
				}
			}
			
			return $msg;
		}
		return $msg;
	}
	
	function clean_data($value, $table = FALSE)
	{
		/* $data = $value;
		*/
		$data = array();
		foreach($value as $key => $val)
        {	
            if(!is_array($val))
            {
                $data[$key]     = $val;
                $data[$key]     = strip_image_tags($data[$key]);
                $data[$key]     = quotes_to_entities($data[$key]);
                $data[$key]     = encode_php_tags($data[$key]);
                $data[$key]     = trim($data[$key]);
            }
        }		
       
		$cleaned_data = array();
	
		if ( ! empty($data))
		{
			$table = ($table !== FALSE) ? $table : $this->table;
			
			$fields = $this->db->list_fields($table);
			
			$fields = array_fill_keys($fields,'');
	
			$cleaned_data = array_intersect_key($data, $fields);
		}
		return $cleaned_data;
	}
	
	function cut_oversize_data($data)
	{
		$new_data = array();
		$msg = '';
		$qry = '';
		foreach($data as $key=>$value)
		{
			$qry = $this->db->query('SELECT COALESCE(character_maximum_length,0) lth,column_name
			FROM INFORMATION_SCHEMA. COLUMNS WHERE
			table_schema = '.$this->db->escape($this->schema).'
			AND TABLE_NAME = '.$this->db->escape($this->table).'
			AND COLUMN_NAME = '.$this->db->escape($key).'');
			$qry = $qry->row_array();
			$max_length = $qry['lth'];
			if(strlen($value)>$max_length)
			{
				$new_data[$key] = substr($value,0,$max_length);
				$msg .= 'Field : "'.$qry['column_name'].'" terpotong karena terlalu panjang. '.chr(13);
			}else{
				$new_data[$key] = $value;
			}
		}
		$this->message = $msg ;
		return $new_data;
	}
	
	function get_field_info()
	{
		$new_data = array();
		$datafield = $this->db->query('SELECT COALESCE(character_maximum_length,0) length,column_name,data_type,is_nullable
		FROM INFORMATION_SCHEMA. COLUMNS WHERE
		table_schema = '.$this->db->escape($this->schema).'
		AND TABLE_NAME = '.$this->db->escape($this->table).'');
		foreach($datafield->result_array() as $key=>$row)
		{
			foreach($row as $key2=>$value)
			{
				$new_data[$row['column_name']]['length'] = $row['length'];
				$new_data[$row['column_name']]['data_type'] = $row['data_type'];
				$new_data[$row['column_name']]['is_nullable'] = $row['is_nullable'];
			}
		}
		return $new_data;
	}
	
	function feed_blank()
	{
		$template = array();
		$fields = $this->db->list_fields($this->table);

		$fields_data = $this->field_data($this->table);

		foreach ($fields as $field)
		{
			//$field_data = array_values(array_filter($fields_data, create_function('$row', 'return $row["Field"] == "'. $field .'";')));
			$field_data = (isset($field_data[0])) ? $field_data[0] : false;

			$template[$field] = (isset($field_data['Default'])) ? $field_data['Default'] : '';
		}
		return $template;
	}
	
	
	function field_data($table)
	{
		return $this->db->list_fields($table);
	}
}