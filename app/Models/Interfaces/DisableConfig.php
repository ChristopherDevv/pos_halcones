<?php
namespace App\Models\Interfaces;
use Illuminate\Http\Request;

class DisableConfig{

	public $config = Config::class;
	public $bolts;

	public function __construct(Request $request) {
		$config = $request->all();
		$this->config = $config['config'];
		$this->bolts = $config['boletos'];
	}
}


