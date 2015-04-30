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
define('MONGO_MEMBER_PREFERENCE_COLLECTION', 'membersPreference');


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
          $return_value =  array("id" => "ERROR");
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
      die('create function is not allowed for this collection.');
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
          if($document == NULL && $collection == 'javLibrary.videos') {
            // search for all collections
            $all_other_possible_collections = array('most_wanted', 'best_rated', 'new_releases', 'new_entries');
            foreach ($all_other_possible_collections as $possible_collection){
                $collection = $_db->{$possible_collection};
                $document = $collection->findOne($criteria);
                if($document !== NULL) {
                  break 1;
                }
            }
          }
          $document['_id'] = $document['_id']->{'$id'};
          $allDocument[] = $document;
      }
      $conn->close();
      return $allDocument;
    } else {
      if($collection == 'javLibrary.'.MONGO_MEMBER_PREFERENCE_COLLECTION) {
        $criteria = array(
          'userID' => $id
        );
        
        $document = $collection->findOne($criteria);
        $conn->close();
        $document['_id'] = $document['_id']->{'$id'};
        return $document;
      } else {
        $criteria = array(
          '_id' => new MongoId($id)
        );
      
        $document = $collection->findOne($criteria);
        if($document == NULL && $collection == 'javLibrary.videos') {
          // search for all collections
          $all_other_possible_collections = array('most_wanted', 'best_rated', 'new_releases', 'new_entries');
          foreach ($all_other_possible_collections as $possible_collection){
              $collection = $_db->{$possible_collection};
              $document = $collection->findOne($criteria);
              if($document !== NULL) {
                break;
              }
          }
        }
        $conn->close();
        
        $document['_id'] = $document['_id']->{'$id'};
        
        return $document;
      }
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

function mongoUpdate($server, $db, $collection, $id, $document, $action) {

  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};



    if($collection == 'javLibrary.'.MONGO_MEMBER_PREFERENCE_COLLECTION) {
      if(!isset($action)) {
        $action = "PUSH";
      }
      $criteria = array(
        'userID' => $id
      );
      
      // make sure that an _id never gets through
      unset($document['_id']);
      if ($action == "PUSH") {
        $collection->update($criteria,array('$addToSet' => $document));
      } else if ($action == "PULL") {
        $collection->update($criteria,
          array('$pull' => $document),
          array(
            'multi' => true
          ));
      }
      $conn->close();
      $document['_id'] = $id;
      return $document;
    } else {
      die('update function is not allowed for this collection');
    }
    
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

function mongoDelete($server, $db, $collection, $id, $document) {

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
