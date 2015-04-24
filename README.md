Slim PHP Mongo REST server
==========================

MongoDB REST server using Slim PHP.

I've just thrown this up quickly, there might be an error or two. But I am hoping to polish it a bit and add a few features to bring it more in line with the Node.js one I have used before. Probably a good starting point for people looking to do more advanced things, or just of interest anyone looking into Slim or Mongo with PHP. And if someone uses it and I get a pull request, all the better!

Requirements
============

PHP, with MongoDB driver
http://php.net/manual/en/mongo.installation.php

A web server such as nginx, lighttpd or Apache httpd

Notes
=====

Update will only update fields present in the request, leaving any existing fields in tact. I will add the faster 'save' update (which is effectively a delete/insert for the same key) in future.

Usage
=====

Configure MONGO_HOST at the top of index.php

Here's some jQuery -

Fetch collection
----------------

Get all in collection (respecting limit set in mongo-list.php)
    
```javascript
$.getJSON(
  '/database/collection',
  function(data) {
    console.log(data);
  }
);
```

Get blogs with phone in the title

```javascript
$.ajax({
    url: '/database/collection',
    type: 'GET',
    contentType: 'application/json',
    dataType: 'json',
    data: JSON.stringify({
      filter: {
        type: 'blogs'
      },
      wildcard: {
        title: 'phone'
      }
    }),
    success: function(data) {
      console.log(data);
    },
    error: function (data) {
      console.log(data);
    }
});
```

And in Backbone

```javascript
var collection = Backbone.Collection.extend({
  url: '/database/collection',
  // drill down to the actual results
  parse: function(response) {
    return response.results;
  }
});

collection.fetch({
  data: {
    filter: {
      type: 'blogs'
    }
    // etc.
  }
});
```

Save document
-------------

```javascript
$.ajax({
  url: '/database/collection',
  type: 'POST',
  contentType: 'application/json',
  dataType: 'json',
  data: JSON.stringify({
    title: 'My Object',
    body: 'text'
    // etc.
  }),
  success: function(data) {
    console.log(data);
  },
  error: function (data) {
    console.log(data);
  }
});
```

Update document
---------------

```javascript
$.ajax({
  url: '/database/collection/id',
  type: 'PUT',
  contentType: 'application/json',
  dataType: 'json',
  data: JSON.stringify({
    title: 'My Object',
    body: 'text'
    // etc.
  }),
  success: function(data) {
    console.log(data);
  },
  error: function (data) {
    console.log(data);
  }
});
```

Todo
====

* Save/Update/Delete multiple
* Alternate update using save()
* Check it works... :-D
