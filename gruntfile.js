module.exports = function(grunt) {

    grunt.loadNpmTasks('grunt-sync');
    grunt.registerTask('default', 'sync');
}