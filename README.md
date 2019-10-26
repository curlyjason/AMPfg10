# Progress

- 10/15 
   - The repo was moved in, Cake updated and CakeDC/Users updated. 
   - All the non-composer files like those in config were updated by hand
   - Changes to the values in users.php (plugin config) was breaking the system. Fixed.
   - END OF DAY: 
      -System can display cake error page.
- 10/16
   - db migration was done. Tables came on line and login works.
   - phpunit testing was fixed
      - command line running has an autoload problem
      - webrunner must be called on a full path `dev.fg.com/app/webroot/test.php`. Some .htaccess problem?
   - Testing data connections are not working.
   - Took care of Session->flash change to Flash Helper/Component
   - END OF DAY: 
      - Login is possible. 
      - Error free access to status page established. 
      - Testing is possible through the webrunner at `dev.fg.com/app/webroot/test.php`.
      - Baking fixtures with data works through console
      - Controller deprecations down to 19 from starting count of 303
         
# Plan
- Deprecation removal
   - Currently working on Controller folder. 19 of 303 remaining.
- Search out php changes
