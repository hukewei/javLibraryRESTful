<?php

/**
 * Collection count
 */

function mongoCollectionCount($server, $db, $collection, $query = null) {
  
  try {
  
    $conn = new Mongo($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    
    if($query) {
      return $collection->count($query);
    } else {
      return $collection->count();
    }
    
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }
  
}