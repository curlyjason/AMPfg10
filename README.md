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
- 10/26
    - Restructuring: made app/Vendor folder so it holds all the vendor 
    files including the cake core.   
    After some research this seemed to be the correct construction for a 
    2.10 app. This will result naturally from composing with the 
    composer.json file inside the app folder.
- 10/28
    - Controller deprecations are complete
    - disableCache() deprecation was ignored   
    This one sets some headers to prevent browser caching, a topic I don't 
    really understand. So adding new poorly understood fixes to a poorly 
    understood bit of code that is not slated for further updates 
    seemed unnecessary.
    - Testing of saveField() deprecations was abandoned.   
    I made some progress with testing and using mocks, but so many of these 
    fixes were a minor part of a larger process. I opted for careful proofing 
    of the fairly simple fixes required to remove the method call.
    - Model class deprecations done
   - END OF DAY: 
      - Login is possible. 
      - Error free access to status page established. 
      - Testing is possible through the webrunner at `dev.fg.com/app/webroot/test.php`.
      - Baking fixtures with data works through console
   
         
# Plan
- Deprecation removal
   - Controller folder complete
   - Model folder has 4 saveField() uses
- Search out php changes
