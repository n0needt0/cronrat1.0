require([
  // Libs
  "jquery",
  "use!backbone"
],
        
function( jQuery, Backbone) {

    var App = {
            Views: {},
            Models: {},
            Collections: {},
            Routers: {},
            init: function() {
                debug('initializing');
                debug(Conf);
                debug(App);
                App.Views.appview = new App.Views.AppView();
            }
        };

    
    
      // Our overall **AppView** is the top-level piece of UI.
      App.Views.AppView = Backbone.View.extend({

        // Instead of generating a new element, bind to the existing
          
        el: '#page' ,
        data:{},
        answer: {},
        // Delegated events for creating new items, and clearing completed ones.
        events: {
          "click #pin" : "enterPin",
          "focus #textarea" : "enterText",
          "submit #form" : "checkPin"
         }, 
        
        initialize: function () { 
            _.bindAll(this, "render");
            //render
            this.render();
        },

        render: function () {
            debug('binding to application element: ');
        },
        
        checkPin: function(e){
        	
        try{
          		var pin = $('#pin').val();

                if( ('Enter your Email' == pin) || ('' == pin.trim()) )
                {
                	alert("Enter your Email.");
            	    return false;
                }
                
                var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
                
                if( !pattern.test(pin.trim()))
                {
                	alert("Invalid Email.");
            	    return false;
                }	
                
                var pin = $('#textarea').val();

                if( ('Problem Description (BE SPECIFIC!!!)' == pin) || ('' == pin.trim()) )
                {
                	alert("Please enter Problem Description.");
            	    return false;
                }
                
                return true;
            }
              catch (err)
            {
                debug(err);
                return false();
            }
        },
        
        enterPin: function(e){
          	try{
          		
          		var pin = $(e.currentTarget).val();

                if('Enter your Email' == pin)
                {
                	$(e.currentTarget).val("");
                }
            }
              catch (err)
            {
                debug(err);
            }
        },
        enterText: function(e){
          	try{
          		
          		var pin = $(e.currentTarget).val();

                if('Problem Description (BE SPECIFIC!!!)' == pin)
                {
                	$(e.currentTarget).val("");
                }
            }
              catch (err)
            {
                debug(err);
            }
        }        
      });

    App.init();
});
