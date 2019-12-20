# Progress

##Testing
run testing at 
http://localhost/AMPfg10/app/webroot/test.php 
although I've previously had it running at
http://dev.fg.com/app/webroot/test.php

###Testing improvements
To get an in-test debug you have to 

`debug($var); die;`

I should be able to write some new function to do both those things... 
or more? write debug to a file that I can then view? Is LOG available?

Can I get a test that will run all in a folder? right now it's all or one.

##Code Development

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
   - Finish structural change from 10/26:
      - dump files external to app (lib, vendor, plugin)   
      These were old versions of software and did not seem to be in the 
      correct place. System runs without them
   - END OF DAY: 
      - Login is possible. 
      - Error free access to status page established. 
      - Testing is possible through the webrunner at `dev.fg.com/app/webroot/test.php`.
      - Baking fixtures with data works through console
         
# Plan
- Click around to see how app survived deprecation fixes
   - write tests for code that fails
- Search out php changes
- It would be best to get testing working on amp-fg so I could write tests 
that pass in legacy mode, then move them in to fg10 form conformation 
during refactoring.
   - the alternative is to take html-grabs and debugs from amp-fg to 
   use as `expected` data for the tests.
- Record any schema changes!!! 
    - Make a table definition file for amp-fg version so it can be 
    diff'd against a matching file for fg10

## Found errors
- Putting an item on the cart yields error
   - >Database Error
   >
   >Error: SQLSTATE[42000]: Syntax error or access violation: 
   >1140 In aggregated query without GROUP BY, expression #1 of SELECT list 
   >contains nonaggregated column 'fg10.OrderItem.id'; this is incompatible with 
   >sql_mode=only_full_group_by
   >
   >SQL Query: SELECT `OrderItem`.`id`, SUM(each_quantity), `OrderItem`.`item_id` 
   >FROM `fg10`.`order_items` AS `OrderItem` WHERE `pulled` = 0 
   >AND `OrderItem`.`item_id` = (45) 
- Editing a customers catalog group access in the tree page yields:
   - >Database Error
   >
   >Error: SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect 
   >DOUBLE value: '285liadd5854b05000a65f9b968bc17d15d97abc397ed'
   >
   >SQL Query: UPDATE `fg10`.`users` SET `parent_id` = 25, `sequence` = 2, 
   >`id` = '285liadd5854b05000a65f9b968bc17d15d97abc397ed', `active` = 1, 
   >`folder` = '0', `first_name` = 'Random', `last_name` = 'Person', 
   >`username` = 'random@person.com', `role` = 'Staff Guest', `modified` = '2019-10-28 21:02:27' 
   >WHERE `fg10`.`users`.`id` = '285liadd5854b05000a65f9b968bc17d15d97abc397ed'
   - Doing the save from the 'detail' page gives a more useful:
      >Error: SQLSTATE[HY000]: General error: 1364 Field 'modified' 
      >doesn't have a default value
   - Also, the edit resulted in a flash message:
      >That username is taken. Try another. 
