app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //FIELDS
    when('/attribute-pkg/field/list/:category_id', {
        template: '<field-list></field-list>',
        title: 'Fields',
    }).
    when('/attribute-pkg/field/add', {
        template: '<field-form></field-form>',
        title: 'Add Tax Code',
    }).
    when('/tax-pkg/tax-code/edit/:id', {
        template: '<tax-code-form></tax-code-form>',
        title: 'Edit Tax Code',
    }).

    //FIELD GROUPS
    when('/attribute-pkg/field-group/list/:category_id', {
        template: '<field-group-list></field-group-list>',
        title: 'Field Groups',
    }).
    when('/tax-pkg/tax/add', {
        template: '<tax-form></tax-form>',
        title: 'Add Tax',
    }).
    when('/tax-pkg/tax/edit/:id', {
        template: '<tax-form></tax-form>',
        title: 'Edit Tax',
    });
}]);