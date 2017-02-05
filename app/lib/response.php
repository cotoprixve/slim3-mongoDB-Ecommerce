<?php
namespace App\Lib;

class Response
{
	public $result     = null;
	public $response   = false;
	public $message    = 'Ocurrio un error inesperado.';
	public $href       = null;
	public $function   = null;
	public $count	   = null;
	public $title	   = "";
	public $condition  = "";
	
	public $filter     = null;
	
	public function SetResponse($response, $c = 0, $m = '', $t = '')
	{
		$this->response = $response;
		$this->count 	= $c;
		$this->message  = $m;
		$this->title 	= $t;

		if(!$response && $m = '') $this->response = 'Ocurrio un error inesperado';
	}
	public function SetResponse_name($variable,$value)
	{
		$this->{$variable} = $value;
	}
}