module.exports = function(grunt) {
    grunt.initConfig({
        dirs: {
            css: 'web/static/css',
            js: 'web/static/js'
        },
        pkg: grunt.file.readJSON('package.json'),
        sass: {
			dist: {
				files: {
					'<%= dirs.css %>/style.css' : '<%= dirs.css %>/sass.scss'
				}
			}
		},
        concat: {
            css: {
                src: [
                    '<%= dirs.css %>/*.css',
                    '!<%= dirs.css %>/combined.css'
                ],
                dest: '<%= dirs.css %>/combined.css'
            },
            js : {
                src : [
                    //'<%= dirs.js %>/*.js',
                    '<%= dirs.js %>/main.js', //required only for few pages
                    '!<%= dirs.js %>/combined.js'
                ],
                dest : '<%= dirs.js %>/combined.js'
            }
        },
        cssmin: {
            css:{
                src: '<%= dirs.css %>/combined.css',
                dest: '<%= dirs.css %>/combined.css'
            }
        },
        uglify: {
            js:{
                files: {
                    '<%= dirs.js %>/combined.js': ['<%= dirs.js %>/combined.js']
                }
            }
        },
        jshint: {
            files: ['Gruntfile.js', '<%= dirs.js %>/main.js', '<%= dirs.js %>/core.js'],
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
            //files: ['<%= jshint.files %>'],
            //tasks: ['jshint', 'cssmin', 'uglify']
            css: {
				files: '<%= dirs.css %>/**/*.scss',
				tasks: ['sass']
			}
        }
    });
    
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    //grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-sass');
    grunt.registerTask('default', [ 'sass', 'jshint', 'concat:css', 'cssmin:css', 'concat:js', 'uglify:js', ]);
};