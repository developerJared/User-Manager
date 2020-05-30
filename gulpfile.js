'use strict';
/*******************
 * File Layout
 *  - Includes
 *  - Core declarations
 *  - International declarations
 *  - National declarations
 *  - tasks to run
 ******************/

//Module Includes
var gulp        = require('gulp');
var gutil       = require('gulp-util');
var zip         = require('gulp-zip');
var gulp_sftp   = require('gulp-sftp');
var GulpSSH     = require('gulp-ssh');
var ifElse      = require('gulp-if-else');
var cond        = require('gulp-cond');
var q           = require('q');
var fs          = require('fs');

//File Includes
var config = require('./deployServerConfig.json');

/****************************************************
 * Core Function Declarations
 ***************************************************/

function sftp(server_list,countryLab) {
    gutil.log('Starting sftp...');

    var sftp_promises = [];
    server_list.forEach(function (server) {
        switch (server) {
            case 'NZ':
                sftp_promises.push(sftpNZForSftp()
                    .then(function () { return sftpNZForHttp(channel) }));
                break;
            case 'International':
                sftp_promises.push(sftpInternational(countryLab));
                break;
            case 'All':
                sftp_promises.push(sftpNZForSftp()
                    .then(function () { return sftpNZForHttp(channel) }));
                sftp_promises.push(sftpInternational());
                break;
        }
    });

    return q.all(sftp_promises);
}

/****************************************************
 * International Function Declarations
 ***************************************************/
function runChangeOwnerInternational (directoryToChange) {
    return new GulpSSH({
        ignoreErrors: false,
        sshConfig: config.Config.International
        })
        .shell(['chown www-data:www-data -R' + directoryToChange])
        .pipe(gulp.dest('logs'));
}

function createRemoteDirectory (directoryToMake){
    return new GulpSSH({
        ignoreErrors: false,
        sshConfig: config.Config.International
    })
        .shell(['mkdir ' + directoryToMake])
        .pipe(gulp.dest('logs'));
}
/****************************************************
 * National Function Declarations
 ***************************************************/



function sftpInternational(countryLab) {
    // check country lab for not null
    //ifElse(condition, ifCallback, elseCallback)

    var basePath = '/var/www/'
    var directory = 'directory'

    //cond(
    //    typeof countryLab !== 'undefined' && countryLab,
    //         basePath = '/var/www/'
    //         directory = 'directory' ,
    //         basePath = '/var/www/'
    //         directory = 'directory'
    //
    //
    //);

    var deferred = q.defer();
    var path = basePath + directory;

    gutil.log('Starting sftp International...');

    //add loop for international manifest loop over file directories defined in manifest.
    createRemoteDirectory(path);
    gulp.src(['./*.*','!.env'])
       .pipe(gulp_sftp({
            host: config.International.host,
            user: config.International.username,
            pass: config.International.password,
            remotePath: path,
            timeout: 30000,
            callback: deferred.resolve
        }))
        .on('end', function () {
            gutil.log('Finished sftp International...');
            runChangeOwnerInternational(path);
            deferred.resolve();
        })

        .on('error', deferred.reject);

    return deferred.promise;
}

/****************************************************
 * Task Declarations
 ***************************************************/

gulp.task('release_to_national', function () {
    return q.when(null)
        .then(function () {
            return sftp(['NZ']);
        });
});

gulp.task('release_to_all', function () {
    return q.when(null)
        .then(function () {
            return sftp(['NZ']);
        });
});

gulp.task('release_to_international', function () {
    return q.when(null)
        .then(
            prompt.prompt([{
                type: 'input',
                name: 'first',
                message: 'If you are deploying to all type \'all\', otherwise, type \'no\'?'
            }], function(res){
                switch (res.first){
                    case "all":
                        return sftp(['International']);
                        break;
                    case "no":
                        prompt.prompt([{
                                type: 'input',
                                name: 'Country',
                                message: 'What country would you like to deploy to? (eg. IT for italy, FR for france, JP for japan)'
                            },{
                                type: 'input',
                                name: 'Lab',
                                message: 'What is the lab code to release to? (eg. ZIT for zespri italy)'
                            }],
                            function(res){
                                return sftp(['International'],res);
                            });
                        break;
                    default:
                        return deferred.reject("Invalid response, please try again...");
                }
            })
        )
});