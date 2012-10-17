'use strict';

// Declare app level module which depends on filters, and services
angular.module('myApp', ['myApp.filters', 'myApp.services', 'myApp.directives']).
  config(['$routeProvider', function($routeProvider) {
    $routeProvider.when('/view1/:item_id', { templateUrl : 'templates/tmpl_homepage.php', controller : View1Controller });
    $routeProvider.when('/view2', { templateUrl : 'templates/tmpl_email.php', controller : MyCtrl2 });
    $routeProvider.otherwise({ redirectTo : '/view2' });
}]);