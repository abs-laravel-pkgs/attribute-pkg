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
    when('/attribute-pkg/field-group/add/:category_id', {
        template: '<field-group-form></field-group-form>',
        title: 'Add Field Group',
    }).
    when('/attribute-pkg/field-group/edit/:category_id/:id', {
        template: '<field-group-form></field-group-form>',
        title: 'Edit Field Group',
    });
}]);

app.component('fieldList', {
    templateUrl: field_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#fields_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            stateSave: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['getFieldList'],
                data: function(d) {

                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'field_name', name: 'fields.name', searchable: true },
                { data: 'short_name', name: 'field_types.short_name', searchable: true },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + '/' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            },
            initComplete: function() {
                $('.search label input').focus();
            },
        });
        $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Fields <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/attribute-pkg/field/add/' + $routeParams.category_id + '" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Field' +
            '</a>'
        );

        $('.btn-add-close').on("click", function() {
            $('#fields_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#fields_list').DataTable().ajax.reload();
        });

        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_field').val('');
            $('#fields_list').DataTable().search('').draw();
        }

        var dataTables = $('#fields_list').dataTable();
        $("#search_field").keyup(function() {
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteField = function($id) {
            $('#field_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#field_id').val();
            $http.get(
                field_delete_data_url + '/' + $id,
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'Field Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $('#fields_list').DataTable().ajax.reload(function(json) {});
                    $location.path('/atribute-pkg/field/list');
                }
            });
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('fieldForm', {
    templateUrl: field_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        self.category_id = $routeParams.category_id;
        $('#initial-focus').focus();

        $http({
            url: laravel_routes['getFieldFormData'],
            method: 'GET',
            params: {
                'category_id': $routeParams.category_id,
                'id': $routeParams.id,
            }
        }).then(function(response) {
            if (!response.data.success) {
                showErrorNoty(response);
                $location.path('/attribute-pkg/field/list' + '/' + $routeParams.category_id)
                $scope.$apply()

            }

            self.extras = response.data.extras;
            self.field_category = response.data.field_category;
            self.field = response.data.field;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.field.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
            }
        });

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'category_id': {
                    required: true,
                    number: true,
                },
                'name': {
                    required: true,
                    maxlength: 191,
                    minlength: 3,
                },
                'min_length': {
                    // required: true,
                },
                'max_length': {
                    // required: true,
                },
                'min_date': {
                    // required: true,
                },
                'max_date': {
                    // required: true,
                },
            },
            invalidHandler: function(event, validator) {
                showCheckAllTabErrorNoty()
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveField'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success) {
                            custom_noty('success', res.message)
                            $location.path('/attribute-pkg/field/list/' + $routeParams.category_id);
                            $scope.$apply();
                        } else {
                            $('#submit').button('reset');
                            showErrorNoty(res)
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        showServerErrorNoty()
                    });
            }
        });
    }
});
