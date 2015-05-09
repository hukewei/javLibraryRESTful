<?php

define('MONGO_LIST_DEFAULT_PAGE_SIZE',500);
define('MONGO_LIST_MAX_PAGE_SIZE',false); // set to a number to enforce a max page size

/**
 * Mongo list with sorting and filtering
 *
 *  $select = array(
 *    'limit' => 0, 
 *    'only_id' => 0, 
 *    'page' => 0,
 *    'filter' => array(
 *      'field_name' => 'exact match'
 *    ),
 *    'regex' => array(
 *      'field_name' => '/expression/i'
 *    ),
 *    'sort' => array(
 *      'field_name' => -1
 *    )
 *  );
 */



function mongoList($server, $db, $collection, $select = null) {

  try {
    
    $conn = new MongoClient($server);
    $_db = $conn->{$db};
    $collection = $_db->{$collection};
    
    $criteria = NULL;
    $only_id = false;
    
    // add exact match filters if they exist
    
    if(isset($select['filter']) && count($select['filter'])) {
      $criteria = $select['filter'];
    }

    if(isset($select['only_id']) && $select['only_id']) {
      $only_id = true;
    }

    // add regex match filters if they exist
    if($select['wildcard'] != false && isset($select['wildcard']) && count($select['wildcard'])) {
      foreach($select['wildcard'] as $key => $value) {
        $criteria[$key] = new MongoRegex($value);
      }
    }
    
    // get results
    if(!$only_id) {
      if($criteria) {
        $cursor = $collection->find($criteria);
      } else {
        $cursor = $collection->find();
      }
    } else {
      if($criteria) {
        $cursor = $collection->find($criteria, array('_id'=> 1));
      } else {
        $cursor = $collection->find(array(), array('_id'=> 1));
      }
    }
    // sort the results if specified
    
    if(isset($select['sort']) && $select['sort'] && count($select['sort'])) {
      $sort = array();
      foreach($select['sort'] as $key => $value) {
        $sort[$key] = (int) $value;
      }
      $cursor->sort($sort);
    } else {
        $cursor->sort(array('_id'=> -1));
    }

    // set a limit
    
    if(isset($select['limit']) && $select['limit']) {
      if(MONGO_LIST_MAX_PAGE_SIZE && $select['limit'] > MONGO_LIST_MAX_PAGE_SIZE) {
        $limit = MONGO_LIST_MAX_PAGE_SIZE;
      } else {
        $limit = $select['limit'];
      }
    } else {
      $limit = MONGO_LIST_DEFAULT_PAGE_SIZE;
    }
    
    if($limit) {
      $cursor->limit($limit);
    }
    
    // choose a page if specified
    
    if(isset($select['page']) && $select['page']) {
      $skip = (int)($limit * ($select['page'] - 1));
      $cursor->skip($skip);
    }
    
    // prepare results to be returned
    
    $output = array(
      'total' => $cursor->count(),
      'pages' => ceil($cursor->count() / $limit),
      'results' => array(),
    );
    
    foreach ($cursor as $result) { 
      // 'flattening' _id object in line with CRUD functions
      $result['_id'] = $result['_id']->{'$id'};
      $output['results'][] = $result;
    }

    $conn->close();
    
    return $output;
    
  } catch (MongoConnectionException $e) {
    die('Error connecting to MongoDB server');
  } catch (MongoException $e) {
    die('Error: ' . $e->getMessage());
  }

}
