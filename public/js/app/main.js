var app = angular.module('App', [
    'ngRoute',
    'ui.bootstrap',
    'infinite-scroll',
    'infinite-scroll-with-container'
]);

/* Introducing Interceptor to check every request/response and produce result according to response code */
app.config(['$httpProvider', function ($httpProvider) {
        //$compileProvider.debugInfoEnabled(false);
        $httpProvider.interceptors.push(['$q', '$location', function ($q, $location) {
                return {
                    request: function ($config) {
                        //$config.headers['Content-Type'] = 'application/json';
                        return $config;
                    },
                    response: function (response) {
                        return response || $q.when(response);
                    },
                    responseError: function (rejection) {
                        if (rejection.ResponseCode == 401) {
                            //window.location.href = siteUrl;
                        } else if (rejection.ResponseCode == 404) {
                            //window.location.href = siteUrl + 'error_404';
                        }
                        return $q.reject(rejection);
                    }
                }
            }]);
        $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
    }]);
/*------------------------------------------------------------------------------------------------------*/
app.factory('appInfo', function () {
    return {
        serviceUrl: API_BASE_URL
    };
});