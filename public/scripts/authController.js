/**
 * Created by Jared on 12/10/2015.
 */
// public/scripts/authController.js

(function() {

    'use strict';

    angular
        .module('authApp')
        .controller('AuthController', AuthController);


    function AuthController($auth, $state) {

        var vm = this;

        vm.login = function() {

            var credentials = {
                username: vm.username,
                password: vm.password
            }

            // Use Satellizer's $auth service to login
            $auth.login(credentials).then(function(data) {

                // If login is successful, redirect to the users state
                $state.go('users', {});
            });
        }

    }

})();