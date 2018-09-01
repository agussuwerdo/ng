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
			$query->free_result();
			
		}else
		{
			$data = $this->feed_blank();
			$data['num_rows'] = $query->num_rows();
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
        if($query->num_rows()>0)
		{
			return $query;
        
		}else
		{
			$query->free_result();
            return $query;
        }
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