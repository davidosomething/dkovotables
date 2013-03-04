/*global module:false*/
module.exports = function (grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: '<json:package.json>',

    jshint: {
      all: [
        'Gruntfile.js',
        'assets/js/*.js'
      ],
      options: {
        curly:    true,
        eqeqeq:   true,
        immed:    true,
        indent:   2,
        latedef:  true,
        newcap:   true,
        noarg:    true,
        sub:      true,
        undef:    true
      }
    },
    uglify: {
      /*
      ios: {
        files: {
          'js/min/ios.min.js' : [
            'js/vendor/ios-orientationchange-fix/ios-orientationchange-fix.js',
            'js/ios-landscape-zoom-fix.js'
          ]
        }
      },
     */
    },

    sass: {
      prod: {
        options: {
          style: 'compressed'
        },
        files: {
          'assets/css/admin.css': 'assets/sass/admin.scss'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-sass');

  grunt.registerTask('default', ['jshint', 'sass']);
};
