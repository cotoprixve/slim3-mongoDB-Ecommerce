<?php
namespace App\Lib\Constants;

/** Database config */
const DB_USERNAME = '';
const DB_PASSWORD = '';

const DB_HOST = "mongodb://localhost:27017";

const DB_NAME = 'test';
const TABLE_PRODUCTS = DB_NAME . '.' . 'products';
const TABLE_USERS = DB_NAME . '.' . 'users';
const TABLE_ORDERS = DB_NAME . '.' . 'orders';
const DB_PREFIX = "";

const USER_CREATED_SUCCESSFULLY = 0;
const USER_CREATE_FAILED = 1;
const USER_ALREADY_EXISTED = 2;

/** Debug modes */
const PHP_DEBUG_MODE = true;
const SLIM_DEBUG = true;
/** ERROR CODE **/
const ERROR = array(
					501 => "Failed update registry or updated registry",
					);
/** MESSAGES **/
const MSG = array(
				  'OKUPDATE' 	=> 'Success!',
				  'OKUPDATEADDRESS'	=> '%s success',
				  'OKGET'	 	=> 'Success data delivery',
				  'SENDDATA' 	=> 'Success data delivery',
				  'OKINSERT' 	=> "Successful income",
				  'OKINSERT1' 	=> " Joined/",
				  'OKDELETE' 	=> "Delete successful",
				  'OKDELETE1'	=> " delete/",
				  'FAILDELETE' 	=> "Elimination Failure",
				  );

?>