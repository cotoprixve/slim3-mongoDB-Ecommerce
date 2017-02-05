<?php
use App\Model\AppModel;

$app->group('/account/', function () {
   
    $this->get('login', function ( $req, $res, $args ) {

        $elp = explode( '/', $req->getUri()->getBasePath() );

        $x['path'] = '/' . $elp[1] . '/';

        return $this->renderer->render( $res, 'account.phtml', $x );

    })->setName('login');

    $this->get('detail', function ( $req, $res, $args ) {

        if( !isset( $_SESSION['account'] ) ) {

            return $res->withRedirect( $this->router->pathFor('login') );

        }

        $um = new AppModel();

        $x = $um->start();

        $nro = count( explode( '/', $req->getUri()->getPath() ) )  ;

        $x['path'] = str_repeat( "../", $nro );

        return $this->renderer->render( $res, 'account.phtml', $x );

    })->setName('detail');

    $this->get('order[/{id}]', function ($req, $res, $args) {

        $um = new AppModel();

        switch ( $args['id'] ) {

            case 'account':
            case 'detail':

                return $res->withRedirect( $this->router->pathFor('detail') );

                break;        

            default:

                $x = $um->orders( $args['id'] );

                $nro = count( explode( '/', $req->getUri()->getPath() ) );

                $x['path'] = str_repeat( "../", $nro );

                return $this->renderer->render( $res, 'orderdetail.phtml', $x );

                break;
        }
        
    });

    $this->post('register', function ( $req, $res, $args ) {

        $um = new AppModel();

        $x = $um->register(
                $req->getParsedBody()
             );
        
        if ( $x ) {
            return $res->withRedirect( $this->router->pathFor('detail') );
        } else {

        }

    })->setName('detail');

    $this->post('address/save', function ( $req, $res ) {

        $um = new AppModel();
        
        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->saveaddress(
                    $req->getParsedBody()
                )
            )
        );

    });
    
    $this->post('delete/{id}', function ( $req, $res, $args ) {
        $um = new AppModel();
        
        return $res
           ->withHeader('Content-type', 'application/json')
           ->getBody()
           ->write(
            json_encode(
                $um->Delete( $args['id'] )
            )
        );
    });
    
    $this->post('account/login', function ( $req, $res, $args ) {

        $um = new AppModel();

        $arr = $um->login( $req->getParsedBody() );

        return $res->withRedirect( $this->router->pathFor('detail') );

    });
    
    $this->get('account/logout', function ( $req, $res, $args ) {

        session_destroy();

        return $res->withRedirect( $this->router->pathFor('') );

    });
    
    
});

$app->group('/admin/', function () {

    $this->get('', function( $req, $res, $args ) {

        if( !isset( $_SESSION['account'] ) ) {

            return $res->withRedirect( $this->router->pathFor('login') );

        }

        if( $_SESSION['role'] != 'admin' ) {

            return $res->withRedirect( $this->router->pathFor('noadmin') );

        }

        $nro = count( explode( '/', $req->getUri()->getPath() ) );

        $x['path'] = str_repeat( "../", $nro );

        return $this->renderer->render( $res, 'admin.phtml', $x );

    })->setName('admin');

    $this->get('noadmin', function( $req, $res, $args ) {

        return $this->renderer->render( $res, 'noadmin.phtml' );

    })->setName('noadmin');

    $this->get('manage[/{params:.*}]', function( $req, $res, $args ) {

        if( !isset( $_SESSION['account'] ) ) {

            return $res->withRedirect( $this->router->pathFor('login') );

        }

        $um = new AppModel();

        $params = explode( '/', $req->getAttribute('params') );

        $rute = ( isset( $params[0] ) ) ? $params[0] : '';

        switch ( $rute ) {

            case 'update':

                $x = $um->manage_up( $params[1] );

                $nro = count( explode( '/', $req->getUri()->getPath() ) );

                $x['path'] = str_repeat( "../", $nro );

                return $this->renderer->render( $res, 'manage_update.phtml', $x );

                break;
            
            default:

                $x = $um->manage();

                $nro = count( explode( '/', $req->getUri()->getPath() ) );

                $elp = explode( '/', $req->getUri()->getBasePath() );

                $x['path'] = '/' . $elp[1] . '/';//str_repeat("../", $nro);

                return $this->renderer->render( $res, 'manage_products.phtml', $x );

                break;

        }
    })->setName('manage');

    $this->post('manage/update/save', function( $req, $res, $args ) {

        $files = $req->getUploadedFiles();

        if ( empty( $files['fileToUpload'] ) ) {

            throw new Exception('Expected a fileToUpload');

        }

        $uploadFileName = null;

        $newfile = $files['fileToUpload'];
        
        $target_dir = "../uploads/";

        if ($newfile->getError() === UPLOAD_ERR_OK) {

            $uploadFileName = $newfile->getClientFilename();

            $temp = explode( '.', $uploadFileName );

            $uploadFileName = round( microtime(true) ) . '.' . end($temp);

            $newfile->moveTo("$target_dir/$uploadFileName");

        }

        $um = new AppModel();

        $x = $um->save(

                $req->getParsedBody(), $uploadFileName

            );

        return $res->withRedirect( $this->router->pathFor('manage') );
        
    });

});