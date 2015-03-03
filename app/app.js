var app = angular.module('carnet',['ngRoute','ngDialog']);

app.config(function($routeProvider){
    $routeProvider
        .when('/', {templateUrl: 'view/listAddress.html', controller: 'HomeCtrl'});
});

app.factory('CoordFactory', function($http, $q){
    var factory = {
        coords: false,
        getCoords: function(){
            var deferred = $q.defer();
            if(this.coords !== false){
                deferred.resolve(this.coords);
            }
            else{
                $http.get('controllers/getAddress.php')
                    .success(function(data, status){
                        factory.coords = data;
                        console.log(data);
                        deferred.resolve(factory.coords);
                    }).error(function(data, status){
                        deferred.reject("Erreur lors de la récupération des données");
                    });

            }
            return deferred.promise;
        },
        getCoord : function(id){
            var coord = {};
            var deferred = $q.defer();
            if(factory.coords !== false){

                angular.forEach(factory.coords, function(value, key){
                    if(value.id == id){
                        coord = value;
                    }
                });
                deferred.resolve(coord);
            }
            else{
                var coords = factory.getCoords().then(function (coords){
                    angular.forEach(factory.coords, function(value, key){
                        if(value.id == id){
                            coord = value;
                        }
                    });
                    deferred.resolve(coord);
                }, function(error){
                    deferred.reject(error)
                });
            }
            return deferred.promise;
        }
    };
    return factory;
});
app.controller('HomeCtrl', function($scope, CoordFactory, $rootScope, ngDialog, $http){
    $scope.loading = true;
    $scope.success = false;
    $scope.error = false;
    $scope.sendNotOK = false;
    $scope.coords = CoordFactory.getCoords().then(function(coords) {
        $scope.loading = false;
        $scope.coords = coords;
    },function(error){
       console.log(error);
    });
    console.log($rootScope);
    $rootScope.$on('ngDialog.closed', function (e, $dialog) {
        console.log('$dialog');
        CoordFactory.coords = false;
        $scope.coords = CoordFactory.getCoords().then(function(coords) {
            $scope.loading = false;
            $scope.coords = coords;
        },function(error){
            console.log(error);
        });
    });
    $scope.viewCoord = function(id){
        ngDialog.open({
            template: 'view/viewCoord.html',
            controller: ['$scope', 'CoordFactory', function($scope, CoordFactory){
                $scope.viewCoord = CoordFactory.getCoord(id).then(function(coord){
                    $scope.coord = coord;
                }, function(error){
                    console.log(error);
                });
            }],
            classname: 'ngdialog-theme-default '});
    };
    $scope.viewEdit = function(id){
        ngDialog.open({
            template: 'view/viewEdit.html',
            controller: ['$scope', '$http', 'CoordFactory', function($scope, $http, CoordFactory){
                $scope.viewCoord = CoordFactory.getCoord(id).then(function(coord){
                    $scope.nom = coord.lastname;
                    $scope.prenom = coord.firstname;
                    $scope.tel = coord.phone;
                    $scope.ville = coord.city;
                    $scope.adresse = coord.address;
                    $scope.code = coord.code;
                }, function(error){
                    console.log(error);
                });
                console.log($scope);
                $scope.edit = function(){
                    $http.post('controllers/edit.php', {id:id, lastname:$scope.nom, firstname:$scope.prenom,
                        phone:$scope.tel, city:$scope.ville,
                        address:$scope.adresse, code:$scope.code})
                        .success(function(data) {
                            if(data != 'fail'){

                                $scope.$parent.success = true;
                                $scope.$parent.message = "Adresse éditée !";

                            }
                            else{
                                $scope.$parent.message = "Erreur lors de l'édition !";
                                $scope.$parent.error = true;
                            }
                            ngDialog.close();

                        })
                        .error(function(data, status, headers, config) {
                            $scope.$parent.message = "Erreur lors de lenvoie !";
                            $scope.$parent.error = true;
                            ngDialog.close();
                        });
                }
            }],
            classname: 'ngdialog-theme-default '});
    };

    $scope.deleteData = function(id){
        $http.post('controllers/delete.php', {id:id})
            .success(function(data) {
                if(data != 'fail'){
                    $scope.$parent.success = true;
                    $scope.$parent.message = "Adresse supprimée !";
                    CoordFactory.coords = false;
                    $scope.coords = CoordFactory.getCoords().then(function(coords) {
                        $scope.loading = false;
                        $scope.coords = coords;
                    },function(error){
                        console.log(error);
                    });
                }
                else{
                    $scope.$parent.message = "Erreur lors de la suppression !";
                    $scope.$parent.error = true;
                }
                ngDialog.close();

            })
            .error(function(data, status, headers, config) {
                $scope.$parent.message = "Erreur lors de lenvoie !";
                $scope.$parent.error = true;
                ngDialog.close();
            });
    };
    $scope.$parent.addView = function(){
        console.log($scope);
        ngDialog.open({
            template: 'view/addAddress.html',
            controller: ['$scope', '$http', 'CoordFactory', function($scope, $http, CoordFactory){

               $scope.add = function(){
                    $http.post('controllers/add.php', {lastname:$scope.nom, firstname:$scope.prenom,
                                                      phone:$scope.tel, city:$scope.ville,
                                                      address:$scope.adresse, code:$scope.code})
                        .success(function(data) {
                                if(data != 'fail'){
                                    $scope.$parent.success = true;
                                    $scope.$parent.message = "Adresse éditée !";

                                }
                                else{
                                    $scope.$parent.message = "Erreur lors de l'édition !";
                                    $scope.$parent.error = true;
                                }
                            ngDialog.close();

                        })
                        .error(function(data, status, headers, config) {
                            $scope.$parent.message = "Erreur lors de lenvoie !";
                            $scope.$parent.error = true;
                            ngDialog.close();
                        });
                }
            }],
            classname: 'ngdialog-theme-default'});
    };
    console.log($scope);
});

app.controller('editController')