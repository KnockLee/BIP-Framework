<?php
/**
 * @file     system/models/Model.php
*
* @author   Hristo Georigev
* @company  hgeorgiev.com
* @email    me@hgeorgiev.com
* @license  check LICENSE.txt
*/
abstract class Model {

	protected $db;

	public function Model($profile = false){
		$this->db = Db::getInstance( $profile );
		
	}


	public function __get($var){
		if(property_exists($this, $var))
			return $this->$var;
	}

	public function __set($name, $value) {
		$this->$name = $value;
	}

	abstract function getTable();


	public function get($id) {
		$data = $this->getData(array('id' => $id), 1);
		if(!empty($data))
			$this->buildObject($data[0]);
	}



	public static function getAll($where = null, $limit = null, $order = null){
		$class = get_called_class();

		$data =	self::getData($where, $limit, $order);
		if(!empty($data)) {
			$objects = array();
			foreach($data as $d) {
				$obj = new $class();
				$obj->buildObject($d);
				$objects[] = $obj;
			}
			return $objects;
		} else {
			return array();
		}
	}

	public static function getRowCount($where = null){
		$db = Db::getInstance();

		if($where !== null)
			$db->where($where);

		$class = get_called_class();
		$obj = new $class();
		$table = $obj->getTable();

		$db->select('COUNT(id) AS num_res');
		$res = $db->get($table);


		if(!empty($res)) {
			return (int)$res[0]['num_res'];
		}
		return 0;
	}


	protected static function getData($where = null, $limit = null, $order = null){
		
		$db = Db::getInstance();

		if($where !== null)
			$db->where($where);

		if($limit !== null)
			$db->limit($limit);

		if($order !== null){
			$db->order_by($order);
		}

		$class = get_called_class();
		$obj = new $class();
		$table = $obj->getTable();

		$res = $db->get($table);

		if(!empty($res)) {
			return $res;
		}
	}


	public function buildObject($data){
		foreach($data as $key => $val) {
			$this->$key = $val;
		}
	}

	public function save(){
		$data = $this->toArray();

		if($this->id !== null && is_numeric($this->id)) {
			// update
			unset($data['id']);
			$this->db->where('id', $this->id);
			$this->db->update($this->getTable(), $data);
		} else {
			// insert
			$this->db->insert($this->getTable(), $data);
			$this->id = $this->db->last_id();
		}
	}

	public function delete(){
		$this->db->where('id', $this->id);
		$this->db->delete($this->getTable());
	}


	public function toArray(){
		$data = get_object_vars($this);
		unset($data['db']);
		return $data;
	}
}