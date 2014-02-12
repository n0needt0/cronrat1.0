define( [window.location.protocol + '//www.google.com/jsapi?callback=define'], {
                 load: function( name, req, load, config ) {
                         var request = name.split( '/' );
                         google.load( request[0], request[1], {
                                 callback: load,
                                 language: 'en',
                                 other_params: ((typeof request[2] === 'string')?request[2]:'')
                         } );
                 }

         } ); 