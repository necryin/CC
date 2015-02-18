angular.module('cc', [])

    .controller('MainController', ['$scope', '$http',
        function ($scope, $http) {

            $scope.defaultProvider = 'cb';
            $scope.providers = [];
            $scope.rates = [];
            $scope.amount = 1;
            $scope.result = 1;

            $http.get('/providers').
                success(function (data, status, headers, config) {
                    console.log(data);
                    $scope.providers = data;
                    $scope.currentProvider = $scope.defaultProvider;
                    console.log($scope.providers);
                }).
                error(function (data, status, headers, config) {
                    console.log("fail");
                    console.log(data);
                });

            $http.get('/' + $scope.defaultProvider + '/rates').
                success(function (data, status, headers, config) {
                    console.log(data);
                    $scope.rates[$scope.defaultProvider] = data.rates;
                    $scope.currentProvider = $scope.defaultProvider;
                    $('#from option:first-child').remove();
                    $('#to option:first-child').remove();
                    console.log($scope.rates[$scope.defaultProvider]);
                }).
                error(function (data, status, headers, config) {
                    console.log("fail");
                    console.log(data);
                });

            $scope.calculate = function () {
                var params = {
                    from: $('#from option:selected')[0].text,
                    to: $('#to option:selected')[0].text,
                    amount: $scope.amount,
                    provider: $scope.currentProvider
                };
                console.log(params);
                $http.get('/currency', {
                    params: params
                }).
                    success(function (data, status, headers, config) {
                        console.log(data);
                        $scope.result = data.value;
                        console.log($scope.result);
                    }).
                    error(function (data, status, headers, config) {
                        console.log("fail");
                        console.log(data);
                    });
            };

            $scope.updateRates = function () {

                console.log($scope.rates);
                console.log($scope.currentProvider);
                if (undefined == $scope.rates[$scope.currentProvider]) {
                    $http.get('/' + $scope.currentProvider + '/rates').
                        success(function (data, status, headers, config) {
                            console.log(data);
                            $scope.rates[$scope.currentProvider] = data.rates;
                            $('#from option:first-child').remove();
                            $('#to option:first-child').remove();
                            console.log($scope.rates);
                        }).
                        error(function (data, status, headers, config) {
                            console.log("fail");
                            console.log(data);
                        });
                }
            };
        }
    ]);
