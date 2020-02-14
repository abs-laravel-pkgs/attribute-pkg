app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
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

app.component('fieldGroupList', {
    templateUrl: field_group_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http.get(
            laravel_routes['getFieldGroupFilterdata'], {
                params: {
                    category_id: $routeParams.category_id,
                }
            }
        ).then(function(res) {
            if (!res.data.success) {
                custom_noty('error', res.data.errors);
            } else { console.log(res.data);
                self.field_category = res.data.field_category;
                var dataTable = $('#field-group-table').DataTable({
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
                        url: laravel_routes['getFieldGroupList'],
                        data: function(d) {}
                    },
                    columns: [
                        { data: 'action', searchable: false, class: 'action' },
                        { data: 'field_group_name', name: 'field_groups.name', searchable: true },
                        { data: 'fields_count', searchable: false },
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
                $('.page-header-content .display-inline-block .data-table-title').html('Field Groups <span class="badge badge-secondary" id="table_info">0</span>');
                $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
                $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
                $('.add_new_button').html(
                    '<a href="#!/attribute-pkg/field-group/add/'+self.field_category.id+'" type="button" class="btn btn-secondary" dusk="add-btn">' +
                    'Add New' +
                    '</a>'
                );

                $('.btn-add-close').on("click", function() {
                    $('#field-group-table').DataTable().search('').draw();
                });

                $('.btn-refresh').on("click", function() {
                    $('#field-group-table').DataTable().ajax.reload();
                });

                //DELETE
                $scope.calldeleteConfirm = function($id) {
                    $('#field_group_id').val($id);
                }
                $scope.deleteConfirm = function() {
                    $id = $('#field_group_id').val();
                    $http.get(
                        laravel_routes['deleteFieldGroup'], {
                            params: {
                                id: $id,
                            }
                        }
                    ).then(function(response) {
                        if (response.data.success) {
                            custom_noty('success', response.data.message);
                            $('#field-group-table').DataTable().ajax.reload();
                            $scope.$apply();
                        } else {
                            custom_noty('error', response.data.errors);
                        }
                    });
                }

                //FOR FILTER
                /*$('#card_type_code').on('keyup', function() {
                    dataTables.fnFilter();
                });
                $('#card_type_name').on('keyup', function() {
                    dataTables.fnFilter();
                });
                $('#mobile_no').on('keyup', function() {
                    dataTables.fnFilter();
                });
                $('#email').on('keyup', function() {
                    dataTables.fnFilter();
                });
                $scope.reset_filter = function() {
                    $("#card_type_name").val('');
                    $("#card_type_code").val('');
                    $("#mobile_no").val('');
                    $("#email").val('');
                    dataTables.fnFilter();
                }*/
            }
            $rootScope.loading = false;
        });
        
    }
});

//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------

app.component('fieldGroupForm', {
    templateUrl: field_group_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        self.category_id = $routeParams.category_id;

        $http.get(
            laravel_routes['getFieldGroupFormdata'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                    category_id: $routeParams.category_id,
                }
            }
        ).then(function(response) {
            if (!response.data.success) {
                custom_noty('error', response.data.error);
                $location.path('/attribute-pkg/field-group/list' + '/' + $routeParams.category_id)
                $scope.$apply()
            }
            self.extras = response.data.extras;
            self.field_category = response.data.field_category;
            self.field_group = response.data.field_group;
            self.action = response.data.action;
            self.theme = response.data.theme;
            $rootScope.loading = false;
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });

        // FIELDS
        $scope.addNewFields = function() {
            self.field_group.fields.push({
                id: '',
                is_required_status: 'Yes',
            });
        }
        //REMOVE FIELD
        $scope.removeField = function(index) {
            self.field_group.fields.splice(index, 1);
        }

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            invalidHandler: function(event, validator) {
                custom_noty('error', 'You have errors,Please check all tabs');
            },
            errorPlacement: function(error, element) {
                error.insertAfter(element)
            },
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    maxlength: 191,
                    minlength: 3,
                },
            },
            submitHandler: function(form) {

                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveFieldGroup'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        // console.log(res.success);
                        if (!res.success) {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
                        } else {
                            custom_noty('success', res.message);
                            $location.path('/attribute-pkg/field-group/list' + '/' + $routeParams.category_id);
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            },
        });
    }
});