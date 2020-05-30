<?php
header('Access-Control-Allow-Origin:*');

header('Access-Control-Allow-Methods:GET, POST, PUT, DELETE, OPTIONS');

header('Access-Control-Allow-Headers:Origin, Content-Type, Accept, Authorization, X-Requested-With');
/*************************************************************
 *              LOG IN AND AUTHENTICATION
 *************************************************************/
Route::get('/', 'PHPSite\AuthenticateController@index');

/**************************************************************
 *  WEB SITE : GRAPHICAL INTERFACE: Dashboard / Login / Other
 *************************************************************/
Route::group(['prefix' => 'site'], function () {
    //File for top level site function routes
    Route::get('userErrors/{user}/{keyString?}', 'PHPSite\UsersController@getUserErrors');

    Route::get('containers', 'PHPSite\ContainersController@showAll');
    Route::get('login', 'PHPSite\AuthenticateController@index');
    Route::post('users/create', 'PHPSite\UsersController@createUser');
    Route::post('users/groupsSA', 'PHPSite\UsersController@storeStaffAccess');
    Route::post('users/groupsTA', 'PHPSite\UsersController@storeTestAccess');
    Route::post('users/groupsCU', 'PHPSite\UsersController@storeCutters');
    Route::post('users/groupsFU', 'PHPSite\UsersController@storeFunctions');
    Route::post('users/groupsMFU', 'PHPSite\UsersController@storeFunctionsAccess');
    Route::post('authenticate', 'PHPSite\AuthenticateController@authenticate');
    Route::resource('users', 'PHPSite\UsersController', ['except' => ['create']]);
    Route::resource('container', 'PHPSite\ContainersController');

    /*********************************************************
     *          PAGES THAT USE A SINGLE USER
     *********************************************************/

    Route::get('singleUser', 'PHPSite\SingleUserActionController@index');
    Route::get('addUser', 'PHPSite\SingleUserActionController@addUser');
    Route::get('editUsers/{layout}', 'PHPSite\SingleUserActionController@editUserDashboard');
    Route::get('editUsers/editUser/{user}', 'PHPSite\SingleUserActionController@editUser');
    Route::get('editUsers/userCutters/{user}', 'PHPSite\SingleUserActionController@editUserCutters');
    Route::get('editUsers/userFunctions/{user}', 'PHPSite\SingleUserActionController@editUserFunctions');
    Route::get('editUsers/editUserTestGroups/{user}/{params}', array('uses' => 'PHPSite\SingleUserActionController@getUserErrors', function ($user, $errors) {
    }))->name('editTestGroups');


    /**********************************************************
     *        PAGES THAT USE MULTIPLE / ALL USERS
     **********************************************************/
    Route::get('allUsers', 'PHPSite\MultiUserActionController@index');
    Route::get('testAccess', 'PHPSite\MultiUserActionController@testAccess');
    Route::get('functionAccess', 'PHPSite\MultiUserActionController@functionsAccess');

    /***************************************************
     *    UTILITIES ROUTES USED IN SITE JQUERY SIDE
     ***************************************************/
    Route::get('ug', function () {
        return \App\UserGroup::all()->toArray();
    });
    Route::get('user_group/{user}', function ($user) {
        return \App\UserGroup::where('users_id', $user->id)->get()->toArray();
    });
    Route::get('g', function () {
        return \App\Group::where('id', '>', 4)->get()->toArray();
    });

});

//This was introduced when trying to upgrade to a Javascript site
Route::group(['prefix' => 'jssite'],function() {
    Route::get('users/{id?}','JSSite\JSUsersController@get');
    Route::post('pin/test/{pin}','JSSite\JSUsersController@checkUniquePIN');
    Route::post('users/create','JSSite\JSUsersController@createUser');
    Route::post('authenticatetoken', 'JSSite\JSAuthController@checkToken');
    Route::delete('users/remove/{id}','JSSite\JSUsersController@deleteUser');
});

/**********************************************
 *      FOR NL2 APPLICATION USE
 **********************************************/
Route::group(['prefix' => 'app'], function () {

     /***********************************
     *          USERS ROUTES
     ************************************/
    //Route::get('users/ClockOutHousekeeping/{staffID}', 'Import\ImportController@ClockOutHousekeeping');
    //Route::get('utest', 'Import\UsersController@import');
    //Route::get('users/amqptest', 'App\UsersController@amqp');
    Route::get('users/samplers', 'App\UsersController@getSamplers');
    Route::get('users/init', 'App\UsersController@getBareUsers');
    Route::get('users/byRole/{role}', 'App\UsersController@getByRole');
    Route::get('users/byGroup/{group}', 'App\UsersController@getByGroup');
    Route::get('users/byTestGroup/{testType}/{group}', 'App\UsersController@getByTestGroup');
    Route::get('users/inGroup/{groupId}/{id}', 'App\UsersController@isUserInGroup');
    Route::get('users/inTestGroup/{groupId}/{id}/{test?}', 'App\UsersController@isUserInGroup');
    Route::get('users/getTestGroup/{user}/{test}', 'App\UsersController@getUserCompetency');
    Route::get('users/byPin/{pin}', 'App\UsersController@getByPin');
    Route::get('users/accessLevel/{group}/{user}/{test}/{superID}', 'App\UsersController@changeAccessLevel');

    Route::post('users/login', 'App\AuthenticateController@authenticate');
    Route::post('users/assignGroup','App\AccessController@assignToGroup'); // Same as inital test compentency but for single use
    Route::post('users/removeGroup','App\AccessController@removeFromGroup'); //this needs to set back to no access if test...
    Route::post('users/initialTestCompetency','App\AccessController@assignInitialTestCompetency');
    Route::post('users/incrementTestCompetency','App\AccessController@incrementTestCompetency');
    Route::post('users/decrementTestCompetency','App\AccessController@decrementTestCompetency');
    Route::post('users/resetTestCompetency','App\AccessController@resetTestCompetency');
    Route::post('users/bulkResetAccess','App\AccessController@bulkResetTestCompetency');
    Route::post('users/upgradeAccessLevel', 'App\UsersController@upgradeAccessLevel');

    /***************NEW ROUTES*****************************/
    Route::post('authenticatetoken', 'App\AuthenticateController@checkToken');
    Route::get('users/{id?}','App\UsersController@get');
    Route::get('roles/get/{id?}','App\RolesController@get');
    Route::get('groups/get/{id?}','App\GroupsController@get');
    Route::get('testtypes/get/{id?}','App\TestTypesController@get');
    Route::get('groups/getTestLevels','App\GroupsController@getTestAccessLevels');
    Route::get('crops/get','App\CropsController@get');
    Route::get('access/group_info/{group_id}','App\AccessController@getNumUsersForGroup');
    Route::get('access/test_info','App\AccessController@getNumUsersAssignedToTest');
    Route::put('users/update','App\UsersController@store');
    Route::put('users/bulkUpdate','App\UsersController@bulkStore');

    /****************
     * TEST ROUTES
     ****************/
    Route::get('test/stats/average/{test_id}','App\TestStatisticsController@getTestAverage');
    Route::put('test/stats/scanTime','App\TestStatisticsController@scanTime');
    Route::put('test/stats/setTimes','App\TestStatisticsController@setTimes');

    /****************
     * GROUPS ROUTES
     ****************/
    Route::post('groups/store','App\GroupsController@store');

    /************************************
     *      SAMPLES ROUTES
     ************************************/
    Route::get('samples/{id}', 'App\SamplesController@show');
    Route::get('sample/history/seeds/{id}', 'App\SamplesController@SeedsProxy');
    Route::post('samples','App\SamplesController@store');
    //Route::resource('samples', 'App\SamplesController', ['except' => ['index']]);

    /***********************************
     *      CONTAINERS ROUTES
     ************************************/
    Route::get('containers', 'App\ContainersController@index');
    Route::get('containers/byTypeBarcodes/{type}/{barcodes?}', 'App\ContainersController@getContainers');
    Route::get('containers/byTypeBarcode/{type}/{barcode}', 'App\ContainersController@byTypeBarcode');
    Route::get('containers/check/{type}/{barcode}', 'App\ContainersController@checkContainer');
    Route::post('containers','App\ContainersController@create');
    Route::put('containers','App\ContainersController@update');

    /***********************************
     *      EVENTS ROUTES
     ************************************/
    Route::get('events/bySample/{sampleNumber}', 'App\EventsController@bySample');
    Route::get('events/last2days/', 'App\EventsController@last2days');
    Route::post('events','App\EventsController@save');

    /***********************************
     *      UTILITIES ROUTES
     ************************************/
    Route::get('getDBURL', 'Base\ConfigurationController@getDBURL');
    //TimeGetter
    Route::get('getServerTime', 'Base\ConfigurationController@getServerTime');
    //AMQP Test
    Route::get('amqptest', 'App\UsersController@amqp');
    Route::get('phpInfo', function(){
        return phpinfo();
    });

    /***********************************
     *      DEVICES ROUTES
     ************************************/
    Route::get('allDevices', 'App\DevicesController@getAllDevices');
});

/*****************************************
 *  EXTERNAL / ESB / RABBIT (AMQP) ROUTES
 ****************************************/
Route::group(['prefix' => 'import'], function () {
    // Route::get('service/updater','Import\SettingsController@update');
    //Route::get('support','Import\ImportUsersController@AGF_SUPPORT_IMPORT');
    Route::get('users','Import\ImportUsersController@userImportEndpoint');
    Route::get('boats','Import\ImportContainersController@boatsImport');
    Route::post('ContainerPreload','Import\ImportContainersController@preloadContainers');
    // Route::get('testingC','Import\ContainersController@seederContainersImport');
   /* Route::get('RabbitTest','Import\ImportController@RabbitTest');
    Route::get('RabbitImport','Import\ImportController@amqpStaffImport');
    Route::get('setOld','Import\ImportController@setOldAccess');
    Route::get('Access/{id}','Import\ImportController@setNewAccess');
    Route::post('users/NL1Hook', 'Import\ImportController@processHook');
    Route::get('users/byRole', 'Import\ImportController@getByRole');
    Route::get('users/init', 'Import\ImportController@getBareUsers');
    Route::get('samples', 'Import\ImportController@index');*/
    // Route::resource('sample', 'Import\ImportUsersController', ['except' => ['index']]);
    //Route::resource('users', 'Import\ImportUsersController');
    //Route::resource('containers', 'Import\ImportUsersController');
});
