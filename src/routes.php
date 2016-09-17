<?php
// MAIN
$app->get('/', 'controllers.index:indexAction')
    ->bind('index');

$app->post('/search', 'controllers.index:indexSearchAction')
    ->bind('index_search');

$app->get('/read/{id}', 'controllers.index:readAction')
	->bind('read');

$app->get('/read_pdf/{id}', 'controllers.index:readPDFAction')
	->bind('read_pdf');

$app->get('/result_search/{search}', 'controllers.index:indexSearchDatatablesAction')
    ->bind('index_search_datatables');

$app->get('/error/{code}', 'controllers.index:errorAction')
	->bind('error');

$app->get('/last_painting', 'controllers.index:lastPaintingAction')
	->bind('last_painting');

$app->get('/stat_painting', 'controllers.index:statPaintingAction')
	->bind('stat_painting');

$app->get('/author/{id}', 'controllers.index:authorAction')
	->bind('author');

$app->get('/author_painting_datatables/{authorId}', 'controllers.index:authorDatatablesAction')
	->bind('author_painting_datatables');

$app->get('/byauthors', 'controllers.index:byAuthorsAction')
    ->bind('byauthors');

$app->get('/byauthors_datatables', 'controllers.index:byAuthorsDatatablesAction')
    ->bind('byauthors_datatables');

$app->get('/country/{id}', 'controllers.index:countryAction')
	->bind('country');

$app->get('/country_painting_datatables/{countryId}', 'controllers.index:countryDatatablesAction')
	->bind('country_painting_datatables');

$app->get('/bycountries', 'controllers.index:byCountriesAction')
    ->bind('bycountries');

$app->get('/bycountries_datatables', 'controllers.index:byCountriesDatatablesAction')
    ->bind('bycountries_datatables');

$app->get('/movement/{id}', 'controllers.index:movementAction')
	->bind('movement');

$app->get('/movement_painting_datatables/{movementId}', 'controllers.index:movementDatatablesAction')
	->bind('movement_painting_datatables');

$app->get('/bymovements', 'controllers.index:byMovementsAction')
    ->bind('bymovements');

$app->get('/bymovements_datatables', 'controllers.index:byMovementsDatatablesAction')
    ->bind('bymovements_datatables');

$app->get('/location/{id}', 'controllers.index:locationAction')
	->bind('location');

$app->get('/location_painting_datatables/{locationId}', 'controllers.index:locationDatatablesAction')
	->bind('location_painting_datatables');

$app->get('/bylocations', 'controllers.index:byLocationsAction')
    ->bind('bylocations');

$app->get('/bylocations_datatables', 'controllers.index:byLocationsDatatablesAction')
    ->bind('bylocations_datatables');

$app->get('/admin', 'controllers.admin:indexAction')
	->bind('admin');

$app->get('/country_painting_datatables/{countryId}', 'controllers.index:countryDatatablesAction')
	->bind('country_painting_datatables');
	
// SEND PAINTING
$app->get('send_painting/index/{paintingId}', 'controllers.sendpainting:indexAction')
	->assert('paintingId', '\d+')
	->bind('send_painting');

$app->post('send_painting/send/{paintingId}', 'controllers.sendpainting:sendAction')
	->assert('paintingId', '\d+')
	->bind('send_painting_go');

// SITEMAP
$app->get('/sitemap.xml', 'controllers.sitemap:sitemapAction')
    ->bind('sitemap');

$app->get('/generate_sitemap', 'controllers.sitemap:generateAction')
    ->bind('generate_sitemap');

// CAPTCHA
$app->get('/captcha', 'controllers.index:')
	->bind('captcha');

// GRAVATAR
$app->get('/gravatar', 'controllers.index:reloadGravatarAction')
	->bind('gravatar');

// COMMENT
$app->get('/comment/{paintingId}', 'controllers.comment:indexAction')
	->assert('paintingId', '\d+')
	->bind('comment');

$app->post('comment/create/{paintingId}', 'controllers.comment:createAction')
	->assert('paintingId', '\d+')
	->bind('comment_create');

$app->get('comment/load/{paintingId}', 'controllers.comment:loadCommentAction')
	->assert('paintingId', '\d+')
	->bind('comment_load');

// PAINTINGVOTE
$app->get('/vote_painting/{idPainting}', 'controllers.paintingvote:voteAction')
	->bind('vote_painting');

// ADMIN AJAX
$app->get('/user/painting_vote_datatables/{username}', 'controllers.user:votesUserDatatablesAction')
	->bind('painting_vote_datatables');

$app->get('/user/painting_comment_datatables/{username}', 'controllers.user:commentsUserDatatablesAction')
	->bind('painting_comment_datatables');
	
// USER
$app->get('/user/login', 'controllers.user:connect')
	->bind('login');

$app->get('/user/list', 'controllers.user:listAction')
	->bind('list');

$app->get('/user/show/{username}', 'controllers.user:showAction')
	->value('username', false)
	->bind('user_show');

$app->get('/user/new', 'controllers.user:newAction')
	->bind('user_new');

$app->post('/user/create', 'controllers.user:createAction')
	->bind('user_create');

$app->get('/user/edit/{id}', 'controllers.user:editAction')
	->value('id', false)
	->bind('user_edit');

$app->post('/user/update/{id}', 'controllers.user:updateAction')
	->value('id', false)
	->bind('user_update');

$app->get('/user/updatepassword', 'controllers.user:updatePasswordAction')
	->bind('user_udpatepassword');

$app->post('/user/updatepasswordsave', 'controllers.user:updatePasswordSaveAction')
	->bind('user_updatepasswordsave');

$app->get('/user/forgottenpassword', 'controllers.user:forgottenPasswordAction')
	->bind('user_forgottenpassword');

$app->post('/user/forgottenpasswordsend', 'controllers.user:forgottenPasswordSendAction')
	->bind('user_forgottenpasswordsend');

// ADMIN COUNTRY
$app->get('/admin/country/index', 'controllers.countryadmin:indexAction')
    ->bind('countryadmin_index');

$app->get('/admin/country/indexdatatables', 'controllers.countryadmin:indexDatatablesAction')
    ->bind('countryadmin_indexdatatables');

$app->get('/admin/country/new', 'controllers.countryadmin:newAction')
    ->bind('countryadmin_new');

$app->post('/admin/country/create', 'controllers.countryadmin:createAction')
    ->bind('countryadmin_create');

$app->get('/admin/country/show/{id}', 'controllers.countryadmin:showAction')
    ->bind('countryadmin_show');

$app->get('/admin/country/edit/{id}', 'controllers.countryadmin:editAction')
    ->bind('countryadmin_edit');

$app->post('/admin/country/upate/{id}', 'controllers.countryadmin:updateAction')
    ->bind('countryadmin_update');

// ADMIN CITY
$app->get('/admin/city/index', 'controllers.cityadmin:indexAction')
    ->bind('cityadmin_index');

$app->get('/admin/city/indexdatatables', 'controllers.cityadmin:indexDatatablesAction')
    ->bind('cityadmin_indexdatatables');

$app->get('/admin/city/new', 'controllers.cityadmin:newAction')
    ->bind('cityadmin_new');

$app->post('/admin/city/create', 'controllers.cityadmin:createAction')
    ->bind('cityadmin_create');

$app->get('/admin/city/show/{id}', 'controllers.cityadmin:showAction')
    ->bind('cityadmin_show');

$app->get('/admin/city/edit/{id}', 'controllers.cityadmin:editAction')
    ->bind('cityadmin_edit');

$app->post('/admin/city/upate/{id}', 'controllers.cityadmin:updateAction')
    ->bind('cityadmin_update');

// ADMIN BIOGRAPHY
$app->get('/admin/biography/index', 'controllers.biographyadmin:indexAction')
    ->bind('biographyadmin_index');

$app->get('/admin/biography/indexdatatables', 'controllers.biographyadmin:indexDatatablesAction')
    ->bind('biographyadmin_indexdatatables');

$app->get('/admin/biography/new', 'controllers.biographyadmin:newAction')
    ->bind('biographyadmin_new');

$app->post('/admin/biography/create', 'controllers.biographyadmin:createAction')
    ->bind('biographyadmin_create');

$app->get('/admin/biography/show/{id}', 'controllers.biographyadmin:showAction')
    ->bind('biographyadmin_show');

$app->get('/admin/biography/edit/{id}', 'controllers.biographyadmin:editAction')
    ->bind('biographyadmin_edit');

$app->post('/admin/biography/upate/{id}', 'controllers.biographyadmin:updateAction')
    ->bind('biographyadmin_update');

// ADMIN LOCATION
$app->get('/admin/location/index', 'controllers.locationadmin:indexAction')
    ->bind('locationadmin_index');

$app->get('/admin/location/indexdatatables', 'controllers.locationadmin:indexDatatablesAction')
    ->bind('locationadmin_indexdatatables');

$app->get('/admin/location/new', 'controllers.locationadmin:newAction')
    ->bind('locationadmin_new');

$app->post('/admin/location/create', 'controllers.locationadmin:createAction')
    ->bind('locationadmin_create');

$app->get('/admin/location/show/{id}', 'controllers.locationadmin:showAction')
    ->bind('locationadmin_show');

$app->get('/admin/location/edit/{id}', 'controllers.locationadmin:editAction')
    ->bind('locationadmin_edit');

$app->post('/admin/location/upate/{id}', 'controllers.locationadmin:updateAction')
    ->bind('locationadmin_update');

// ADMIN MOVEMENT
$app->get('/admin/movement/index', 'controllers.movementadmin:indexAction')
    ->bind('movementadmin_index');

$app->get('/admin/movement/indexdatatables', 'controllers.movementadmin:indexDatatablesAction')
    ->bind('movementadmin_indexdatatables');

$app->get('/admin/movement/new', 'controllers.movementadmin:newAction')
    ->bind('movementadmin_new');

$app->post('/admin/movement/create', 'controllers.movementadmin:createAction')
    ->bind('movementadmin_create');

$app->get('/admin/movement/show/{id}', 'controllers.movementadmin:showAction')
    ->bind('movementadmin_show');

$app->get('/admin/movement/edit/{id}', 'controllers.movementadmin:editAction')
    ->bind('movementadmin_edit');

$app->post('/admin/movement/upate/{id}', 'controllers.movementadmin:updateAction')
    ->bind('movementadmin_update');

// ADMIN PAINTING
$app->get('/admin/painting/index', 'controllers.paintingadmin:indexAction')
    ->bind('paintingadmin_index');

$app->get('/admin/painting/indexdatatables', 'controllers.paintingadmin:indexDatatablesAction')
    ->bind('paintingadmin_indexdatatables');

$app->get('/admin/painting/new', 'controllers.paintingadmin:newAction')
    ->bind('paintingadmin_new');

$app->post('/admin/painting/create', 'controllers.paintingadmin:createAction')
    ->bind('paintingadmin_create');

$app->get('/admin/painting/show/{id}', 'controllers.paintingadmin:showAction')
    ->bind('paintingadmin_show');

$app->get('/admin/painting/edit/{id}', 'controllers.paintingadmin:editAction')
    ->bind('paintingadmin_edit');

$app->post('/admin/painting/upate/{id}', 'controllers.paintingadmin:updateAction')
    ->bind('paintingadmin_update');

// ADMIN TYPE
$app->get('/admin/type/index', 'controllers.typeadmin:indexAction')
    ->bind('typeadmin_index');

$app->get('/admin/type/indexdatatables', 'controllers.typeadmin:indexDatatablesAction')
    ->bind('typeadmin_indexdatatables');

$app->get('/admin/type/new', 'controllers.typeadmin:newAction')
    ->bind('typeadmin_new');

$app->post('/admin/type/create', 'controllers.typeadmin:createAction')
    ->bind('typeadmin_create');

$app->get('/admin/type/show/{id}', 'controllers.typeadmin:showAction')
    ->bind('typeadmin_show');

$app->get('/admin/type/edit/{id}', 'controllers.typeadmin:editAction')
    ->bind('typeadmin_edit');

$app->post('/admin/type/upate/{id}', 'controllers.typeadmin:updateAction')
    ->bind('typeadmin_update');

// ADMIN CONTACT FORM
$app->get('/admin/contact/index', 'controllers.contactadmin:indexAction')
    ->bind('contactadmin_index');

$app->get('/admin/contact/indexdatatables', 'controllers.contactadmin:indexDatatablesAction')
    ->bind('contactadmin_indexdatatables');

$app->get('/admin/contact/show/{id}', 'controllers.contactadmin:showAction')
    ->bind('contactadmin_show');
	
// ADMIN USER
$app->get('/admin/user/index', 'controllers.useradmin:indexAction')
    ->bind('useradmin_index');

$app->get('/admin/user/indexdatatables', 'controllers.useradmin:indexDatatablesAction')
    ->bind('useradmin_indexdatatables');

$app->get('/admin/user/show/{id}', 'controllers.useradmin:showAction')
    ->bind('useradmin_show');

$app->get('/admin/user/enabled/{id}/{state}', 'controllers.useradmin:enabledAction')
    ->bind('useradmin_enabled');