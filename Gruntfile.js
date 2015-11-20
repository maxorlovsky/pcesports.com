module.exports = function(grunt) {
    grunt.initConfig({
        dirs: {
            css: 'web/static/css',
            sass: 'web/static/css/sass',
            js: 'web/static/js',
            images: 'web/static/images'
        },

        pkg: grunt.file.readJSON('package.json'),

        /*Converting scss into css*/
        sass: {
            dist: {
                files: {
                    '<%= dirs.css %>/combined-sass': '<%= dirs.css %>/combined-sass'
                }
            }
        },

        concat: {
            /* Gathering all scss files together for easier sass conversion */
            sass: {
                src: [
                    '<%= dirs.sass %>/*.scss',
                ],
                dest: '<%= dirs.css %>/combined-sass'
            },

            /* Gathering all css external files and project combined sass file together */
            css: {
                src: [
                    '<%= dirs.css %>/slider.css',
                    '<%= dirs.css %>/highslide.css',
                    '<%= dirs.css %>/combined-sass'
                ],
                dest: '<%= dirs.css %>/combined.css'
            },

            scriptsJs: {
                src : [
                    '<%= dirs.js %>/jquery.js', //jquery is a separate file because it is used by widget and already minified
                    '<%= dirs.js %>/socket.io.js', //same reason as jquery
                    '<%= dirs.js %>/externals.js'
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
                    '<%= dirs.js %>/app/**/controllers/*.js'
                ],
                dest : '<%= dirs.js %>/angular-combined.js'
            },
            widget: {
                src : [
                    '<%= dirs.js %>/widget-work.js'
                ],
                dest : '<%= dirs.js %>/widget.js'
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
            },
            widget: {
                files: {
                    '<%= dirs.js %>/widget.js': ['<%= dirs.js %>/widget.js']
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
				files: ['Gruntfile.js', '<%= dirs.sass %>/*.scss'],
				tasks: ['concat:sass', 'sass', 'concat:css']
			},
            app: {
                files: ['Gruntfile.js', '<%= dirs.js %>/app/**'],
                tasks: ['concat:app']
            },
            widget: {
                files: ['<%= dirs.js %>/app/widget-work.js'],
                tasks: ['concat:widget']
            }
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
        'concat:css',
        'cssmin:css',
        'concat:scriptsJs',
        'uglify:scriptsJs',
        'concat:js',
        'uglify:js',
        'concat:app',
        'uglify:app',
        'concat:widget',
        'uglify:widget'
    ]);

    /* Task used when developing */
    grunt.registerTask('dev', [
        'default',
        'watch'
    ]);

    grunt.registerTask('imagemin', ['imagemin']);
};