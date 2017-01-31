module.exports = function(grunt) {
    grunt.initConfig({
        dirs: {
            css: 'web/assets/css',
            sass: 'web/assets/css/sass',
            pages: 'web/pages/**/',
            js: 'web/assets/js',
            images: 'web/assets/images'
        },

        pkg: grunt.file.readJSON('package.json'),

        /*Converting scss into css*/
        sass: {
            dist: {
                files: {
                    '<%= dirs.css %>/combined.css': '<%= dirs.css %>/combined.css'
                }
            }
        },

        concat: {
            /* Gathering all scss files together for easier sass conversion */
            sass: {
                src: [
                    '<%= dirs.sass %>/*.scss',
                    '<%= dirs.pages %>/*.scss'
                ],
                dest: '<%= dirs.css %>/combined.css'
            },

            scriptsJs: {
                src : [
                    'node_modules/vue/dist/vue.min.js',
                    'node_modules/vue-router/dist/vue-router.min.js',
                    'node_modules/axios/dist/axios.min.js',
                    'node_modules/jquery/dist/jquery.min.js'
                ],
                dest : '<%= dirs.js %>/scripts.js'
            },
            js: {
                src : [
                    '<%= dirs.js %>/pc.js',
                    '<%= dirs.js %>/main.js'
                ],
                dest : '<%= dirs.js %>/combined.js'
            },
            app: {
                src : [
                    '<%= dirs.js %>/app/app.js',
                    '<%= dirs.js %>/app/**/services/*.js',
                    '<%= dirs.js %>/app/**/directives/*.js',
                    '<%= dirs.js %>/app/**/controllers/*.js'
                ],
                dest : '<%= dirs.js %>/angular-combined.js'
            }
        },

        cssmin: {
            css:{
                src: '<%= dirs.css %>/combined.css',
                dest: '<%= dirs.css %>/combined.css'
            }
        },

        uglify: {
            scriptsJs: {
                files: {
                    '<%= dirs.js %>/scripts.js': ['<%= dirs.js %>/scripts.js']
                }
            },
            js: {
                files: {
                    '<%= dirs.js %>/combined.js': ['<%= dirs.js %>/combined.js']
                }
            },
            app: {
                files: {
                    '<%= dirs.js %>/angular-combined.js': ['<%= dirs.js %>/angular-combined.js']
                }
            }
        },
        
        jshint: {
            files: [
                'Gruntfile.js',
                '<%= dirs.js %>/pc.js',
                '<%= dirs.js %>/main.js'
            ],
            options: {
                ignores: ['<%= dirs.js %>/combined.js'],
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },

        watch: {
            default: {
				files: ['Gruntfile.js', '<%= dirs.sass %>/*.scss', '<%= dirs.pages %>/*.scss'],
				tasks: ['concat:sass', 'sass']
			},
            app: {
                files: ['Gruntfile.js', '<%= dirs.js %>/app/**'],
                tasks: ['concat:app']
            },
            js: {
                files: ['Gruntfile.js', '<%= dirs.js %>/pc.js', '<%= dirs.js %>/main.js', '<%= dirs.js %>/vue.js'],
                tasks: ['concat:js', 'uglify:js']
            },
        },
    });
    
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-sass');

    /* Main task*/
    grunt.registerTask('default', [
        'concat:sass',
        'sass',
        'jshint',
        'cssmin:css',
        'concat:scriptsJs',
        'uglify:scriptsJs',
        'concat:js',
        'uglify:js',
        'concat:app',
        'uglify:app'
    ]);

    /* Task used when developing */
    grunt.registerTask('dev', [
        'default',
        'watch'
    ]);
};