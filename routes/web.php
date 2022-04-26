<?php

/** @var \Laravel\Lumen\Routing\Router $router */



//group middleware cekrequest
$router->group(['prefix' => 'api',  'middleware' => 'cekrequest'], function($router){

    //access
    $router->post('/login', 'access\manage@login');
    $router->post('/signup', 'access\manage@signup');
    $router->post('/signup-new', 'account\signup@newmain');


    //registers success
    $router->get('/registers/success', 'account\manage@registersuccess');
    $router->post('/reverifaccount', 'account\manage@reverifaccount');

    //reset password
    $router->post('/resetpassword', 'access\manage@resetpassword');
    $router->get('/account/changepassword', 'account\index@getchangepassword');
    $router->post('/account/changepassword-out', 'account\manage@sendchangepassword');

    // verifcation
    $router->get('/account/verification', 'account\manage@verification');

    $router->post('/account/sendverification', 'account\manage@sendverification');

    // ADMIN OR PENGGUNA

    // GET PRODUCT ===========>
    // list on widget
    $router->get('/product/list/widget', 'products\lists@widget');
    

    // GET BULKING LIST ON WIDGET
    $router->get('/orders/bulking/listwg', 'bulkingpayment\manage@listwg');
});


// middleware cekrequest and key account
$router->group(['prefix' => 'api',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){

    // MANAGE ================>

    //PENGGUNA
    $router->get('/manage/pengguna/table', 'manage\pengguna\table@main');
    $router->post('/manage/pengguna/create', 'manage\pengguna\manage@create');
    $router->get('/manage/pengguna/view', 'manage\pengguna\manage@view');


    //PELAYANAN -> LEMBAGA
    $router->get('/pelayanan/lembaga/table', 'pelayanan\lembaga\table@main');
    $router->post('/pelayanan/lembaga/create', 'pelayanan\lembaga\manage@create');
    $router->get('/pelayanan/lembaga/view', 'pelayanan\lembaga\manage@view');

    // PELAYANAN - PENGGUNA
    $router->get('/pelayanan/pengguna/table', 'pelayanan\pengguna\table@main');
    $router->post('/pelayanan/pengguna/create', 'pelayanan\pengguna\manage@create');

    // END MANAGE ===============>


    // NEWS =================>

    // NEWS PENGUMUMAN
    $router->get('/news/pengumuman/table', 'news\pengumuman\table@main');
    $router->post('/news/pengumuman/create', 'news\pengumuman\manage@create');
    $router->get('/news/pengumuman/view', 'news\pengumuman\manage@view');


    //NEWS HIBAH
    $router->get('/news/hibah/table', 'news\hibah\table@main');
    $router->post('/news/hibah/create', 'news\hibah\manage@create');
    $router->get('/news/hibah/view', 'news\hibah\manage@view');

    // GET CUSTOMER ============>
    //list on widget
    // $router->get('/customers/list/widget', 'customers\lists@widget');
    // $router->get('/customers/address/list', 'customers\address@list');

    // ORDERS 
    //new
    // $router->get('/orders/table', 'orders\table@main');
    // $router->post('/orders/widget/new', 'orders\manage@new'); //new orders
    // $router->post('/orders/widget/additem', 'models\orders@updateadditem');
    // $router->post('/orders/destination/set', 'orders\manage@setaddress');
    // $router->post('/orders/address/keepinorder', 'orders\manage@keepaddress');
    // $router->post('/orders/courier/setcost', 'orders\manage@setcostcourier');
    // $router->post('/orders/metodepayment', 'orders\manage@metodepayment');
    // $router->get('/orders/metodepayment/list', 'orders\payment@metode');
    // $router->get('/orders/vdetail', 'orders\manage@viewdetail');
    // $router->get('/orders/vcheck', 'orders\manage@viewcheck');
    // $router->post('/orders/checkout', 'orders\widget@checkout');
    // $router->post('/orders/setpayment', 'orders\widget@setpayment');
    // $router->post('/orders/delete', 'orders\manage@delete');

    //check in verif orders
    // $router->get('/orders/checkveriforders', 'orders\manage@checkveriforders');
   
    // // ORDER STOCK
    // $router->get('/orderstock/table', 'orderstock\table@main');

    // // ORIGIN
    // $router->get('/origin/list', 'origin\index@list');
    // $router->post('/origin/set', 'origin\index@set');

    // //update quantity
    // $router->post('/orders/widget/updateqty', 'orders\manage@updateqty');
    // $router->post('/orders/widget/delete/item', 'orders\manage@deleteitem');

    // //cart
    // $router->get('/orders/widget/cart', 'orders\manage@getcart');

    // // PRODUCT ====>
    // $router->get('/product/list/widgetmodal', 'products\lists@widgetmodal');
    // $router->get('/product/list', 'products\lists@view');
    // $router->get('/product/table', 'products\table@main');
    // $router->get('/product/distributor-list', 'products\lists@distributor');
    // $router->get('/product/view-list-distributor', 'products\lists@viewDistributor');
    // $router->post('/product/create', 'products\manage@create');
    // $router->get('/product/detail', 'products\manage@detail');
    
    // // COURIER
    // $router->get('/courier/list/widget', 'courier\index@list');
    // $router->get('/courier/cost', 'courier\index@cost');
    // $router->get('/courier/cost/single', 'courier\index@checking');

    // //SRC KOTA dan KECAMATAN
    // $router->get('/data/bps/provinsi', 'data\bps@provinsi');
    // $router->get('/data/bps/city', 'data\bps@city');
    // $router->get('/data/bps/kecamatan', 'data\bps@kecamatan');


    // //SEARCH DATA
    // $router->get('/data/srckotakecamatan', 'data\kotakecamatan@list');
    $router->get('/data/srckotakecamatan-provinsi', 'data\kotakecamatan@listprovinsi');
    $router->get('/data/src-lembaga', 'data\lembaga\manage@list');
    // $router->get('/data/consumable', 'data\consumable@main');


    // // ADDRESS ====== >
    // $router->get('/customers/address/single', 'customers\address@view');
    // $router->get('/customers/table', 'customers\table@main');



    // //PAYMENT
    // $router->get('/bank/list', 'orders\payment@banklist');

    // // VERIF ORDERS
    // $router->post('/orders/upload', 'orders\manage@upload');
    // $router->post('/orders/verification', 'orders\manage@verification');
    // $router->get('/veriforder/table', 'veriforder\table@main');

    // // SHIPING
    // $router->get('/shiping/table', 'shiping\table@main');
    // $router->post('/shiping/addnoresi', 'shiping\manage@addnoresi');
    // $router->post('/shiping/pickup', 'shiping\manage@pickup');

    // // BULKING PAYMENT
    // $router->post('/orders/bulking/payments', 'bulkingpayment\manage@check');
    // $router->get('/bulkingpayment/table', 'bulkingpayment\table@main');
    // $router->post('/orders/bulking/deletelist', 'bulkingpayment\manage@deletelist');
    // $router->get('/orders/bulking/metodepayment/list', 'bulkingpayment\manage@metodepayment');
    // $router->post('/orders/bulking/setmetodepayment', 'bulkingpayment\manage@setmetodepayment');
    // $router->get('/orders/bulking/vcheck', 'bulkingpayment\index@check');
    // $router->post('/orders/bulking/delete', 'bulkingpayment\manage@delete');
    // //upload bulking
    // $router->post('/orders/uploadbulking', 'bulkingpayment\manage@upload');
    // $router->post('/verifbulking/verification', 'verifbulking\manage@verification');


    // // CUSTOMERS
    // $router->post('/customers/manage/add', 'customers\manage@add');
    // $router->post('/customers/manage/changeprogress', 'customers\manage@changeprogress');
    // $router->post('/customers/manage/changetaging', 'customers\manage@changetaging');
    // $router->post('/customers/manage/addnote', 'customers\manage@addnote');
    // $router->get('/customers/detail', 'customers\manage@detail');
    // $router->get('/customers/vs-edit', 'customers\manage@vshortedit');
    // $router->post('/customers/s-edit', 'customers\manage@sedit');

    // // EXPORT TO EXCEL ======>

    // // BULKING VERIFICATION PAYMENT
    
    // $router->get('/verifbulking/table', 'verifbulking\table@main');
    // $router->get('/verifbulking/check', 'verifbulking\manage@check');
    
    // // ADMIN
    // // $router->get('/admin/table', 'admin\table@main');
    // // $router->get('/admin/manage/view', 'admin\manage@view');
    // // $router->post('/admin/manage/changestatus', 'admin\manage@changestatus');
    // // $router->post('/admin/manage/resendverification', 'admin\manage@resendverification');
    // $router->post('/admin/manage/create', 'admin\manage@create');
    // // $router->get('/admin/user-orders', 'admin\manage@userOrders');


    // // PARTNER
    // $router->get('/partner/table', 'partner\table@main');
    // $router->post('/partner/create', 'partner\manage@create');
    // $router->get('/partner/view', 'partner\manage@view');

    // // SUNTING PARTNER
    // $router->post('/partner/sunting/label', 'partner\manage@suntingLabel');
    // $router->post('/partner/sunting/contact', 'partner\manage@suntingContact');
    // $router->post('/partner/sunting/owner', 'partner\manage@suntingOwner');
    // $router->post('/partner/sunting/address', 'partner\manage@suntingAddress');
    // $router->post('/partner/sunting/admin', 'partner\manage@suntingAdmin');
    // $router->post('/partner/sunting/chpricedistributor', 'partner\manage@createPrice');
    // $router->post('/partner/sunting/delete-distprice', 'partner\manage@deletePriceDistributor');
    // $router->get('/partner/sunting/getdistributor-pricelist', 'partner\manage@getlists');
    // $router->post('/partner/sunting/product-distributor', 'partner\manage@CreatePDistributor');
    
    // // MANAGE GLOBAL
    // $router->get('/manage-global/detail', 'manageglobal\manage@detail');
    // $router->post('/manage-global/sunting/bank', 'manageglobal\manage@bank');
    // $router->post('/manage-global/sunting/gudang', 'manageglobal\manage@gudang');
    // $router->post('/manage-global/sunting/delete-bank', 'manageglobal\manage@deletebank');
    // $router->post('/manage-global/sunting/delete-gudang', 'manageglobal\manage@deletegudang');
    // $router->post('/manage-global/sunting/chuniqnum', 'manageglobal\manage@changeuniqnum');

    // //customers address
    // $router->post('/customers/address/create', 'customers\address@create');
    
    // // EXPORT ORDERS
    // $router->get('/export/orders', 'export\orders\index@main');
    // $router->get('/export/bulkings', 'export\bulkings\index@main');

    // // ACCOUNT
    // // $router->get('/account/CheckRefreshtokenJWT', 'access\manage@CheckRefreshJWT');
    // $router->post('/account/changepassword', 'account\manage@ChangePassword');
    // $router->post('/account/exeprofile-bio', 'account\manage@ChangeBio');
    // $router->post('/account/exeprofile-password', 'account\manage@ChangeUserPassword');


    // //
    // $router->get('/companies/list', 'companies\index@lists');

    // //MAKLON
    // $router->get('/maklon/list', 'maklon\manage@list');


    // //NOTIFICATIONS
    // //TEST
    // $router->get('/notifications/add', 'notification\index@test');


    // // ASIDE RIGHT
    $router->get('/getaside', 'config\aside@viewSingle');

    // // PRINT
    // $router->get('/shiping/print/token', 'tools\prints\orders@shiping');
    // $router->get('/invoice/print/token', 'tools\prints\orders@invoice');

    // //OUTER REQUEST GET
    // $router->get('/data/distributor/list', 'data\partner\manage@list');
    // $router->get('/data/company/list', 'data\company\manage@list');
    
    // $router->get('/data/cs/list', 'data\cs\manage@list');
    // $router->get('/config/aside/view', 'config\aside@view');

    // $router->get('/data/partner/list', 'data\partner\manage@list');

    $router->get('/data/sublevel/list', 'data\account\manage@sublevel');
    $router->get('/data/lembaga/type', 'data\lembaga\manage@type');


    // // FOR BONNE

    // //EMPLOYE CONTROLLER
    // $router->get('/employe/table', 'home\employes\table@main');
    // $router->post('/employe/create', 'home\employes\manage@create');
    // $router->get('/employe/data/groups', 'home\employes\data@listgroup');
    // $router->get('/employe/show', 'home\employes\manage@show');
    // $router->get('/employe/listmodal', 'home\employes\data@listmodal');

    // //EMPLOYE DOCUMENT
    // $router->get('/employe/document/show', 'home\employes\data@document');
    // $router->post('/employe/document/create', 'home\employes\manage@createdocument');
    // $router->get('/employe/document/listmodal', 'home\employes\data@listmodaldoc');
    // $router->get('/employe/document/type/list', 'home\employes\data@typedocumentlist');
    // $router->get('/employe/document/subtype/list', 'home\employes\data@subtypedocumentlist');
    // $router->post('/employe/document/delete', 'home\employes\manage@documentdelete');

    // //HRD SDM
    // $router->get('/hrd/sdm/table', 'hrd\sdm\table@main');


    // //INVENTORY ASSETS
    // $router->get('/inventory/tools/table', 'home\inventory\tools\table@main');
    // $router->post('/inventory/tools/create', 'home\inventory\tools\manage@create');
    // $router->get('/inventory/tools/data/types', 'home\inventory\tools\data@types');
    // $router->get('/inventory/tools/show', 'home\inventory\tools\manage@show');
    // $router->get('/inventory/tools/listmodal', 'home\inventory\tools\data@listmodal');

    // //PENGAJAN ALAT
    // $router->get('/inventory/order/table', 'home\inventory\order\tools@main');

    // // INVENTORY CONSUMABLE
    // $router->get('/inventory/consumable/table', 'home\inventory\consumable\table@main');
    // $router->post('/inventory/consumable/create', 'home\inventory\consumable\manage@create');
    // $router->get('/inventory/consumable/data/types', 'home\inventory\consumable\data@types');
    // $router->get('/inventory/consumable/show', 'home\inventory\consumable\manage@show');


    // // INVENTORY CONSUMABLE OUT
    // $router->get('/inventory/consumable/out/table', 'home\inventory\consumable\out\table@main');
    // $router->get('/inventory/consumable/out/checkpo', 'home\inventory\consumable\out\manage@checkpo');
    // $router->post('/inventory/consumable/out/add', 'home\inventory\consumable\out\manage@add');
    // $router->post('/inventory/consumable/out/delete', 'home\inventory\consumable\out\manage@delete');
    

    // //CUSTOMERS
    // $router->get('/customers/table', 'customers\table@main');
    // $router->get('/customers/data/type', 'customers\data@main');
    // $router->post('/customers/create', 'customers\manage@create');
    // $router->get('/customers/show', 'customers\manage@show');
    // $router->get('/customers/inmodal', 'customers\data@listmodal');

    // //MARKETING
    // $router->get('/marketing/pengajuan/table', 'marketing\pengajuan\table@main');
    // $router->post('/marketing/pengajuan/create', 'marketing\pengajuan\manage@create');
    // $router->get('/marketing/pengajuan/show', 'marketing\pengajuan\manage@show');

    // //MARKETING SDM
    // $router->get('/marketing/pengajuan/sdm', 'marketing\pengajuan\manage@sdm');
    // $router->post('/marketing/pengajuan/sdm/add', 'marketing\pengajuan\manage@addsdm');
    // $router->get('/marketing/pengajuan/sdm/list', 'marketing\pengajuan\manage@listsdm');
    // $router->post('/marketing/pengajuan/sdm/delete', 'marketing\pengajuan\manage@deletesdm');
    // $router->post('/marketing/pengajuan/sdm/verification', 'marketing\pengajuan\manage@verifsdm');

    // //MARKETING TOOLS
    // $router->get('/marketing/pengajuan/tools', 'marketing\pengajuan\manage@tools');
    // $router->post('/marketing/pengajuan/tools/add', 'marketing\pengajuan\manage@addtools');
    // $router->get('/marketing/pengajuan/tools/list', 'marketing\pengajuan\manage@listtools');
    // $router->post('/marketing/pengajuan/tools/delete', 'marketing\pengajuan\manage@deletetools');
    // $router->post('/marketing/pengajuan/tools/verification', 'marketing\pengajuan\manage@veriftools');
    

    // //COMPANY PROFILE
    // $router->get('/manage/company/profile', 'company\index@profile');

    // //MANAGE DATA
    // $router->get('/manage/data/type-tools', 'manage\data@tools');
    // $router->get('/manage/data/type-sk', 'manage\data@sk');
    // $router->get('/manage/data/document-companies', 'manage\data@document');
    // $router->get('/manage/data/document-type', 'manage\data@doccomptype');

    // //COMPANY SUNTING
    // $router->post('/company/sunting/label', 'company\manage@label');
    // $router->post('/company/sunting/contact', 'company\manage@contact');
    // $router->post('/company/sunting/owner', 'company\manage@owner');
    // $router->post('/company/sunting/address', 'company\manage@address');
    // $router->post('/company/sunting/document', 'company\manage@document');
    // $router->post('/company/sunting/type-tools', 'company\manage@typetools');
    // $router->post('/company/sunting/type-sk', 'company\manage@typesk');

    // //COMPANY DELETE
    // $router->post('/manage/company/delete-document', 'company\manage@deletedocument');
    // $router->post('/manage/data/delete/type-tools', 'company\manage@deletetypetools');
    // $router->post('/manage/data/delete/type-sk', 'company\manage@deletetypesk');


    // //FINANCIAL
    // $router->get('/financial/orders/table', 'financial\orders\table@main');
    

    
});

//ROUTE SEND
$router->group(['prefix' => 'api/send',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){

    $router->post('/mail', 'send\mail\index@send');
});

//group middleware cekrequest and auth
$router->group(['prefix'=>'api', 'middleware'=>['cekrequest','auth']], function($router)
{
    $router->post('/logout', 'access\manage@logout');
    $router->get('/profile', 'access\manage@profile');
    
    // $router->get('/refresh', 'access\manage@refresh');

    //customers
    // $router->post('/customers/table', 'customers\table@main');
    // $router->post('/customers/manage/add', 'customers\manage@add');
    // $router->post('/customers/manage/changeprogress', 'customers\manage@changeprogress');
    // $router->post('/customers/manage/changetaging', 'customers\manage@changetaging');
    // $router->post('/customers/manage/addnote', 'customers\manage@addnote');

    // //customers address
    // $router->post('/customers/address/create', 'customers\address@create');
    
    // ====> ORDERS    
    // $router->post('/orders/checkout', 'orders\widget@checkout');
    // $router->post('/orders/setpayment', 'orders\widget@setpayment');
    // $router->post('/orders/delete', 'orders\manage@delete');

    // BULKING =======>
    // $router->post('/orders/bulking/payments', 'bulkingpayment\manage@check');
    
    // PENGGUNA  / ADMIN
    // $router->post('/admin/manage/create', 'admin\manage@create');

    // ACCOUNT
    $router->get('/account/refreshtoken', 'access\manage@refresh');

    //upload
    

    // //upload bulking
    // $router->post('/orders/uploadbulking', 'bulkingpayment\manage@upload');
    // $router->post('/verifbulking/verification', 'verifbulking\manage@verification');

    // export
    $router->post('/export/customers', 'export\customers\index@main');
});





// ROUTER FOR HOME PAGE
// $router->group(['prefix' => 'api/home',  'middleware' => ['cekrequest','cekKeyAccount']], function($router)
// {

//     //MAIN
//     $router->get('/menu', 'home\menu\manage@getMenus');

//     //ABSENSI
//     $router->get('/absen/getabsen', 'home\absen\index@getAbsen');
//     $router->post('/absen/sendAttendance', 'home\absen\index@sendAttendance');
//     $router->get('/absen/location/print', 'home\absen\index@getAttendanceLocation');
//     $router->get('/absen/screen', 'home\absen\index@getScreen');
//     $router->get('/absen/infodate', 'home\absen\index@infodate');
//     $router->post('/absen/setinfo', 'home\absen\index@setInfo');
//     $router->get('/absen/viewsingle', 'home\absen\index@viewSingle');



//     //CALENDAR
//     $router->get('/calendar/attendance/employe', 'home\calendar\manage@attendance');

//     //SCREEN
//     $router->post('/absen/getQRdinamic', 'home\absen\index@getdinamic');



//     // REPORT ABSEN
//     $router->get('/absen/report/table', 'home\absen\report@main');
    

//     //DATA =============== >
//     $router->get('/data/employe/list', 'data\home\employe@list');

 
//     //TASK
//     $router->get('/task/table', 'home\task\table@main');
//     $router->get('/task/verif', 'home\task\table@verif');
//     $router->post('/task/create', 'home\task\manage@create');
//     $router->get('/task/view', 'home\task\manage@view');
//     $router->post('/task/verify', 'home\task\manage@verify');

//     //NOTIFICATION
//     $router->post('/notifications/read', 'home\notifications\manage@read');

// });

// //DATA =============== >
// $router->group(['prefix' => 'api/home/data',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){
//     $router->get('/employe/groups/lists', 'home\employes\data@groups');
//     $router->get('/employe/list', 'home\employes\data@list');
// });

// $router->get('/api/home/absen/view', 'home\absen\index@getView');




// VOUCHER
$router->group(['prefix' => 'api/voucher',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){
    $router->get('/list', 'voucher\manage@list');
    $router->post('/set', 'voucher\manage@set');
    $router->post('/remove', 'voucher\manage@delete');
});

// DASHBOARD REPORT
$router->group(['prefix' => 'api/dashboard',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){
    $router->get('/report', 'dashboard\report@main');
    $router->get('/report/export', 'export\orders\index@dashboard');

});

//REPORT HOME
$router->group(['prefix' => 'api/home/export',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){
    $router->get('/attendance', 'export\home\attendance@main');
    //TASK
    $router->get('/task/excel', 'export\home\task@excel');
});



// CLICK WA
$router->group(['prefix' => 'api/clickwa',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){

    $router->get('/orders/invoice', 'clickwa\orders\manage@invoice');
    $router->get('/orders/shiping', 'clickwa\orders\manage@shiping');

});


//HACKD
$router->group(['prefix' => 'api/hack',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){
    $router->get('/menu/create', 'home\menu\manage@createIn');
});


// TESTING
$router->group(['prefix' => 'testing',  'middleware' => ['cekrequest','cekKeyAccount']], function($router){


    // $router->get('/checkreminder', 'models\reminder@test');
    // $router->get('/test/notification/add', 'notification\index@add');

    // // ====== TESTING AREA ====================== >
    // $router->get('/test/viewprofile', 'account\index@viewprofile');
    
    // // $router->post('/test', 'orders\manage@upload');
    // $router->post('/testbulking', 'bulkingpayment\manage@check');
    
    // $router->get('/testing', 'companies\manage@getpaymentlist');
    
    // $router->get('/testingusers', 'testing\data\getdata@users');
    // // $router->get('/testingviewaside', 'config\aside@viewSingle');
    // $router->get('/testingcreate', 'config\aside@test');
    
    // $router->post('/testing/logcustomer-add', 'testing\log\customers@add');
    

    
    // // DATA
    // $router->get('/uploadjson', 'testing\data\sap@origin');
    
    // //MENU HOME
    // $router->get('/menu/home', 'home\menu\manage@createMenus');
    // $router->get('/menu/employe', 'home\menu\manage@getMenus');
    // $router->get('/att/count', 'testing\home\index@AttCount');
    // $router->get('/att/view-time', 'testing\home\index@viewTime');
    // $router->get('/att/countlate', 'testing\home\index@countLate');
    // $router->get('/att/counttime', 'testing\home\index@cekcount');    

    // //
    // $router->get('/absen/report', 'testing\home\reportatt@main');    

    $router->post('/upload', 'testing\upload\index@test');

});
