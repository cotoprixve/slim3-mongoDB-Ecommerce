<?php
namespace App\Model;

use App\Lib\Response;
use App\Lib\Constants AS CT;
use Monolog\Logger;
use MongoDB;

class AppModel
{
    private $conn;
    private $response;
    private $crypt;
    private $logger;
    
    public function __CONSTRUCT()
    {
        $this->conn = new MongoDB\Driver\Manager( CT\DB_HOST );
        $this->response = new Response();
        # INITIALIZED THE CLASS TO ENCRYPT AND DESENCRIPT THE IDENTIFIERS
//        $this->crypt = new CRYPTO\Crypter(Constantes::getConstantes()['ENCRYPTION_KEY'],Constantes::getConstantes()['ENCRYPTION_IV']);
//        $this->logger = $logger;
    }
    
    private function error_msg($filename, $e)
    {
        echo "The $filename script has experienced an error.\n<br>";
        echo "It failed with the following exception:\n<br>";
        echo "Exception:", $e->getMessage(), "\n<br>";
        echo "In file:", $e->getFile(), "\n<br>";
        echo "On line:", $e->getLine(), "\n<br>";
    }

    public function isLoggin()
    {
        return ( isset( $_SESSION['account'] ) ) ? true: false;
    }

    public function inicio()
    {
        try {
            
            # Set table
            $table = CT\TABLE_PRODUCTS;
            
            # Set up query
            $query = new MongoDB\Driver\Query([]); 

            # Run the query and position record
            $rows = $this->conn->executeQuery( $table, $query );

            # Decode form object
            $cursor = json_decode( json_encode( $rows->toArray() ), true );

            $arr = array(
                "cursor" => $cursor,

                "num_docs" => count( $cursor )

            );

            return $arr;

        } catch ( MongoDB\Driver\Exception\Exception $e ) {

            # Filename with errors
            $filename = basename(__FILE__);

            # Error message
            self::error_msg( $filename, $e );

        }
    }

    public function start()
    {
        try {
            
            # Set table
            $table = CT\TABLE_USERS;
            
            # Set up filter
            $filter = [ '_id' => new MongoDB\BSON\ObjectID( $_SESSION["account"] ) ]; 
            
            # Set up query
            $query = new MongoDB\Driver\Query( $filter );                        

            # Run the query and position record
            $rows = $this->conn->executeQuery( $table, $query );

            $address = '';

            foreach ($rows as $row) {

                if ( isset( $row->address ) )
                    $address = $row->address;                
            
            }

            # Set up filter
            $filter = [ 'user_id' => new MongoDB\BSON\ObjectID( $_SESSION["account"] ) ]; 
            
            # Set up query
            $query = new MongoDB\Driver\Query( $filter );                        
            
            # Run the query and position record
            $rows = $this->conn->executeQuery( CT\TABLE_ORDERS, $query );
            
            # Decode form object
            $something = json_decode( json_encode( $rows->toArray() ), true );

            return array( 'address' => $address, 'something' => $something );

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }

    }

    public function orders($data)
    {
        try {

            # Set table
            $table = CT\TABLE_ORDERS;
            
            # Set up filter
            $filter = array( '_id' => new MongoDB\BSON\ObjectID( $data ) );

            # Set up query
            $query = new MongoDB\Driver\Query( $filter );
            
            # Run the query and position record
            $cursor = $this->conn->executeQuery( $table, $query );

            # Read cursor
            foreach ( $cursor as $obj ) {
                $ids = $obj->ids;
                $ids = explode( ";", $ids );
            }

            $_ids = array();
            
            # Read ids
            foreach ( $ids as $separateIds ) {
                $_ids[] = $separateIds instanceof \MongoDB\BSON\ObjectID ? $separateIds : new MongoDB\BSON\ObjectID( $separateIds );
            }
            
            # Count values
            $how_many = array_count_values( $ids );
            
            # Set up filter
            $filter = array( '_id' => array( '$in' => $_ids ) );
            
            # Set up query            
            $query = new MongoDB\Driver\Query( $filter );
            
            # Run the query and position record
            $cursor = $this->conn->executeQuery( CT\TABLE_PRODUCTS, $query );
            
            # Decode form object
            $thisSearch = json_decode( json_encode( $cursor->toArray() ), true );
            
            # Return
            return array( 'thisSearch' => $thisSearch, 'how_many' => $how_many );

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }

    }

    public function login($data)
    {
        try {

            # Set table
            $table = CT\TABLE_USERS;

            $username = (isset( $data["username"] ) ? $data["username"] : $username = null);

            $password = (isset( $data["password"] ) ? md5( $data["password"] ) : $password = null);

            # Set up filter
            $filter = array( 'username' => $username, "password" => $password );

            # Set up query
            $query = new MongoDB\Driver\Query( $filter );
            
            # Run the query and position record
            $rows = $this->conn->executeQuery( $table, $query );

            # Position the record
            $login = current( $rows->toArray() );

            # Response query
            if ( !empty( $login ) ) {

                $_SESSION["account"] = $login->_id;
                
                $_SESSION['role'] = $login->role;
                
                return array('msg' => CT\MSG['OKLOGIN'], 'status' => 200 );

            } else {
                
                $_SESSION["account"] = null;
                
                $_SESSION['role'] = null;
                
                return array('msg' => CT\ERROR['FAILLOGIN'], 'status' => 400 );


            }


        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }
    }

    public function register($data)
    {
        try {

            # Set table
            $table = CT\TABLE_USERS;

            // Create an array of values to insert
            $username = (isset($data["username"]) ? $data["username"] : $username = null);
            $password = (isset($data["password"]) ? md5($data["password"]) : $password = null);

            $email = (isset($data["email"]) ? $data["email"] : $email = null);
            $address = (isset($data["address"]) ? $data["address"] : $address = null);

            # This is a new document to be inserted. The MongoDB\BSON\ObjectID generates a new ObjectId. It is a value used to uniquely identify documents in a collection.
            $doc = array(
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'address' => $address,
                'role' => 'user'
            );

            # The MongoDB\Driver\BulkWrite collects one or more write operations that should be sent to the server.
            $bulk = new MongoDB\Driver\BulkWrite();

            # insert user
            $id = $bulk->insert($doc);

            $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);

            $result = $this->conn->executeBulkWrite($table, $bulk, $writeConcern);


            # Response query
            if ( !empty( $id ) ) {

                $_SESSION["account"] = $id;
                
                $_SESSION['role'] = $doc['role'];
                
                return true;

            } else {
                
                $_SESSION["account"] = null;
                
                $_SESSION['role'] = null;
                
                return false;

            }

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }
    }

    public function saveaddress( $data )
    {

        try {
            
            # Set table
            $table = CT\TABLE_USERS;
            
            #Prepare writing (bulkwrite)
            $bulk = new MongoDB\Driver\BulkWrite;
            
            $bulk->update(
                ['_id' => new MongoDB\BSON\ObjectID( $_SESSION["account"] )],
                ['$set' => ['address' => $data["u_address"] ]]
            );
            
            # Save back to the database            
            $res = $this->conn->executeBulkWrite( $table , $bulk );

            # Response query
            if ( $res ) {

                return array('msg' => sprintf( CT\MSG['OKUPDATEADDRESS'], 'Address' ) );

            }else{

                return array('msg' => CT\ERROR[501] );

            }
                
        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }

    }

    public function manage()
    {
        try {
            
            # Set table
            $table = CT\TABLE_PRODUCTS;
            
            # Set up query
            $query = new MongoDB\Driver\Query([]); 
            
            # Run the query and position record
            $rows = $this->conn->executeQuery( $table, $query );
            
            return array( 'something' => $rows );

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }

    }
  
    public function manage_up( $id )
    {
        try {
            
            # Set table
            $table = CT\TABLE_PRODUCTS;
            
            # Set up filter
            $filter = array( '_id' => new MongoDB\BSON\ObjectID( $id ) );
            
            # Set up query
            $query = new MongoDB\Driver\Query( $filter );
            
            # Run the query and position record
            $rows = $this->conn->executeQuery( $table, $query );
            
            # Position the record
            $car = current( $rows->toArray() );
            
            # Response query
            if ( !empty( $car ) ) {

                return array( 
                            'id' => $car->_id,
                            'title' => $car->title,
                            'description' => $car->description,
                            'price' => $car->price
                        );

            } else {

                echo "No match found\n";
            }


        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # Filename with errors
            $filename = basename(__FILE__);
            
            # Error message
            self::error_msg( $filename, $e );

        }

    }

    public function save( $body, $image=null )
    {
         try {
            # Set table
            $table = CT\TABLE_PRODUCTS;
            
            # Decode form array
            $data = json_decode( json_encode( $body ), true );
            
            # Set up filter
            $id_array = array(
                '_id' => new MongoDB\BSON\ObjectID( $data["update"] )
            );

            // Create an array of values to update
            foreach ( $data as $key => $value ) {
                if ( $key == 'update' ) {
                    unset( $data[ $key ] );
                }else{
                    $data[ $key ] = ( $value != '' ) ? trim( $value ) : '0';
                }
            }
            
            # Update image if upload one
            if ( !is_null( $image ) )
                $data['image'] = $image;

            # Prepare writing (bulkwrite)
            $bulk = new MongoDB\Driver\BulkWrite;
            
            # Save back to the database            
            $bulk->update(

                $id_array,

                array('$set' => $data )

            );
            
            # Run the query
            $res = $this->conn->executeBulkWrite( $table, $bulk );
            
            # Response query
            if ( $res ) {

                return array( 'msg' => CT\MSG['OKUPDATE'] );

            } else {

                return array( 'msg' => CT\ERROR[501] );

            }

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # filename with errors
            $filename = basename(__FILE__);
            
            # error message
            self::error_msg( $filename, $e );

        }
    }
  
    public function add( $data, $image=null )
    {
         try {
            # Set table
            $table = CT\TABLE_PRODUCTS;

            # Update image if upload one
            if ( !is_null( $image ) )
                $data['image'] = $image;

            # Prepare writing (bulkwrite)
            $bulk = new MongoDB\Driver\BulkWrite;
            
            # Save back to the database            
            $bulk->insert(

                $data

            );
            
            # Run the query
            $res = $this->conn->executeBulkWrite( $table, $bulk );
            
            # Response query
            if ( $res ) {

                return array( 'msg' => CT\MSG['OKINSERT'], 'status' => 200 );

            } else {

                return array( 'msg' => CT\ERROR[501] );

            }

        } catch ( MongoDB\Driver\Exception\Exception $e ) {
            
            # filename with errors
            $filename = basename(__FILE__);
            
            # error message
            self::error_msg( $filename, $e );

        }
    }
  
}