<?php

/**
 * MongoDB CRUD functions
 *
 * Flattening _id objects for better JS models in the front end
 * 
 */

/**
 * Create (insert)
 */

define('MONGO_MEMBER_COLLECTION', 'javLibrary.members');
define('MONGO_MEMBER_PREFERENCE_COLLECTION', 'javLibrary.membersPreference');


function mongoCreate($server, $db, $collection, $document) {

  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    $return_value;

    if($collection == MONGO_MEMBER_COLLECTION) {
      $login = $document['email'];
      $response = $collection->findOne(array("email" => $login));
      if($response !==null) {
        if($response['password'] == $document['password']) {
          //login and pw matched
          $conn->close();
          $return_value =  $response['_id'];
        } else {
          //pw not correct
          $return_value =  array("id" => "");
        }
      } else {
        //create new account
        $collection->insert($document);
        $preference_collection = $_db->{MONGO_MEMBER_PREFERENCE_COLLECTION};
        $document['_id'] = $document['_id']->{'$id'};
        // initialise the preference collection
        $preference_collection->insert(array("userID" => $document['_id'], 
          "favorite_actors" => array(), "favorite_videos" => array(),
           "wanted_videos" => array(), "watched_videos" => array()));
        $conn->close();
        $return_value =   array("id" => $document['_id']);
      }
    } else {
      die('create function is not allowed for this collection, only '.$collection.' is allowed');
    }
    $conn->close();
    return $return_value;
    $collection->insert($document);
    $conn->close();
    
    $document['_id'] = $document['_id']->{'$id'};
    
    return $document;
    
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }
  
}

/**
 * Read (findOne)
 */

function mongoRead($server, $db, $collection, $id) {
  
  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    if (strpos($id,'@') !== false) {
      //multiple resources get
      $_ids = array();
      $mongoIdArray = explode("@", $id);
      $allDocument = array();
      $count = 0;

      foreach($mongoIdArray as $seprateIds){
          $criteria = array(
            '_id' => $seprateIds instanceof MongoId ? $seprateIds : new MongoId($seprateIds)
          );
        
          $document = $collection->findOne($criteria);          
          $document['_id'] = $document['_id']->{'$id'};
          $allDocument[] = $document;
      }
      $conn->close();
      return $allDocument;
    } else {
      $criteria = array(
        '_id' => new MongoId($id)
      );
    
      $document = $collection->findOne($criteria);
      $conn->close();
      
      $document['_id'] = $document['_id']->{'$id'};
      
      return $document;
    }
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }
  
}


/**
 * Update (set properties)
 */

function mongoUpdate($server, $db, $collection, $id, $document) {

  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    
    $criteria = array(
      '_id' => new MongoId($id)
    );
    
    // make sure that an _id never gets through
    unset($document['_id']);
    
    $collection->update($criteria,array('$set' => $document));
    $conn->close();
    
    $document['_id'] = $id;

    return $document;
    
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }
  
}



/**
 * Delete (remove)
 */

function mongoDelete($server, $db, $collection, $id) {

  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    
    $criteria = array(
      '_id' => new MongoId($id)
    );

    $collection->remove(
      $criteria,
      array(
        'safe' => true
      )
    );
    
    $conn->close();
    
    return array('success'=>'deleted');
    
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }
  
}
