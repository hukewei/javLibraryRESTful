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

function mongoCreate($server, $db, $collection, $document) {

  try {
  
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
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
    
    $criteria = array(
      '_id' => new MongoId($id)
    );
    
    $document = $collection->findOne($criteria);
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
