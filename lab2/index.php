<?php

header('Content-Type: application/json');

$host = '';
$user = 'root';
$pass = '';
$db = 'cloud';

$mysqli = new mysqli($host, $user, $pass, $db);

if($mysqli->connect_error) die( 'Connect Error (' . mysqli_connect_errno() . ') '. mysqli_connect_error() );

$http_method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'];

switch($http_method){
    case 'GET':     // GET
        REST_GET($id);
        break;
    case 'POST':    // POST
        REST_POST($id);
        break;
    case 'PUT':     // PUT
        REST_PUT($id);
        break;
    case 'DELETE':  // DELETE
        REST_DELETE($id);
        break;
}



function REST_GET($id=''){
    global $mysqli;
    
    if( $id != '' ) {
        $sql = 'SELECT * FROM movies WHERE id='.$id;
    } else {
        $sql = 'SELECT * FROM movies';
    }

    if( $result = $mysqli->query( $sql ) ){
        if( $result->num_rows == 0 ){
            echo json_error('Movie not found!');
            http_response_code(404);
            return;
        }
        
        if( $id == '' ) {
            while( $row = $result->fetch_array(MYSQLI_ASSOC) ){
                $movies[] = $row;
            }

            echo json_encode($movies);
        } else {
            $movie = $result->fetch_array(MYSQLI_ASSOC);

            echo json_encode($movie);
        }
        
        $result->close();
    }
}

function REST_POST($id=''){
    global $mysqli;

    $sql = "INSERT INTO movies (`name`, `description`, `director`, `writers`, `stars`, `popularity`) ".
        "VALUES('".htmlentities($_POST['name'])."', '".htmlentities($_POST['description'])."', '".htmlentities($_POST['director']).
        "', '".htmlentities($_POST['writers'])."', '".htmlentities($_POST['stars'])."', '".htmlentities($_POST['popularity'])."')";

    if( $mysqli->query($sql) ){
        echo json_encode( array(
            'success' => 'The movie was added'
        ) );
    } else {
        echo json_error('An error occured');
    }
}

function REST_PUT($id=''){
    global $mysqli;

    if($id==''){
        echo json_error('No movie id given!');
        http_response_code(404);
        return;
    }

    $contents = file_get_contents('php://input');
    parse_str($contents, $_PUT);

    $sql = "UPDATE movies ".
        "SET name='".htmlentities($_PUT['name'])."', description='".htmlentities($_PUT['description'])."', ".
        " director='".htmlentities($_PUT['director'])."', writers='".htmlentities($_PUT['writers'])."', ".
        " stars='".htmlentities($_PUT['stars'])."', popularity='".htmlentities($_PUT['popularity'])."' ".
        "WHERE id=".$id;

    if( $mysqli->query($sql) ){
        echo json_encode( array(
            'success' => 'The movie was updated'
        ) );
    }
}

function REST_DELETE($id=''){
    global $mysqli;

    if($id==''){
        echo json_error('No movie id given!');
        http_response_code(404);
    } else {
        if($mysqli->query('DELETE FROM movies WHERE id='.$id)){
            echo json_encode( array(
                'success' => 'The movie was deleted'
            ) );
        } else {
            echo json_error('Cannot delete that movie');
        }
    }
}

function json_error($message){
    $error_arr = array(
        'error' => $message
    );

    return json_encode($error_arr);
}

$mysqli->close();