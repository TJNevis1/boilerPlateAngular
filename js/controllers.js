'use strict';

function View1Controller($scope, $routeParams, $http){
	console.log($routeParams); //#/view1/2 would show item_id : '2' in $routeParams
	
	$scope.item_id = $routeParams['item_id'];
	$scope.partial_template = 'templates/tmpl_email.php';
	$scope.submitEmail = function(){ //Notice the ng-click attribute on the #submit_email button
		console.log('I\'ve submitted my email address!');
	};
	
	$http.get('template_sample.json').success(function(data) {
    	$scope.phones = data;
    	console.log(data);
  	}).error(function() {
    	console.log('error');
  	});
}



function MyCtrl2(){
	console.log('MyCtrl2');
}
MyCtrl2.$inject = [];