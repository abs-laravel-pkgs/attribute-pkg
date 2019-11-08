app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //FIELDS
    when('/attribute-pkg/field/list/:category_id', {
        template: '<field-list></field-list>',
        title: 'Fields',
    }).
    when('/attribute-pkg/field/add/:category_id', {
        template: '<field-form></field-form>',
        title: 'Add Field',
    }).
    when('/attribute-pkg/field/edit/:category_id/:id', {
        template: '<field-form></field-form>',
        title: 'Edit Field',
    }).

    //FIELD GROUPS
    when('/attribute-pkg/field-group/list/:category_id', {
        template: '<field-group-list></field-group-list>',
        title: 'Field Groups',
    }).
    when('/attribute-pkg/field-group/add', {
        template: '<field-group-form></field-group-form>',
        title: 'Add Field Group',
    }).
    when('/attribute-pkg/field-group/edit/:id', {
        template: '<field-group-form></field-group-form>',
        title: 'Edit Field Group',
    });
}]);