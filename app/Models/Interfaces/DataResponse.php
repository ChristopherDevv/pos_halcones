<?php 

namespace App\Models\Interfaces;

class DataResponse{
	public $status;
	public $message;
	public $data;

	public function __construct(string $message, string $status,$data){
		$this->message = $message;
		$this->status = $status;
		$this->data = $data;
	}

	public function toJson() {
		    return json_encode($this);
	}
}