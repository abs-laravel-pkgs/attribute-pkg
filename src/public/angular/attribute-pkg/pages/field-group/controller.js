app.component('fieldGroupList', {
    templateUrl: field_group_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $filter_data_url = typeof($routeParams.category_id) == 'undefined' ? field_group_get_filter_data_url : field_group_get_filter_data_url + '/' + $routeParams.category_id;
        $http.get(
            $filter_data_url
        ).then(function(res) {
            // console.log(res);
            if (!res.data.success) {
                var errors = '';
                for (var i in res.data.errors) {
                    errors += '<li>' + res.data.errors[i] + '</li>';
                }
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: errors
                }).show();

            } else {
                self.field_category = res.data.field_category;
                self.form_url = field_group_form_url + '/' + $routeParams.category_id;

                var dataTable = $('#field-group-table').dataTable({
                    "dom": cndn_dom_structure,
                    "language": {
                        // "search": "",
                        // "searchPlaceholder": "Search",
                        "lengthMenu": "Rows _MENU_",
                        "paginate": {
                            "next": '<i class="icon ion-ios-arrow-forward"></i>',
                            "previous": '<i class="icon ion-ios-arrow-back"></i>'
                        },
                    },
                    stateSave: true,
                    processing: true,
                    serverSide: true,
                    paging: true,
                    searching: true,
                    ordering: false,

                    ajax: {
                        url: laravel_routes['getFieldGroupList'],
                        type: "GET",
                        dataType: "json",
                        data: function(d) {}
                    },

                    columns: [
                        { data: 'action', searchable: false, class: 'action' },
                        { data: 'field_group_name', name: 'field_groups.name', searchable: true },
                        { data: 'fields_count', searchable: false },
                    ],
                    rowCallback: function(row, data) {
                        $(row).addClass('highlight-row');
                    },
                    infoCallback: function(settings, start, end, max, total, pre) {
                        $('#table_info').html(total)
                        $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
                    },
                });
                $('.dataTables_length select').select2();
                $("#search_field_group").keyup(function() { //alert(this.value);
                    dataTable.fnFilter(this.value);
                });

                $(".search_clear").on("click", function() {
                    $('#search_field_group').val('');
                    $('#field-group-table').DataTable().search('').draw();
                });

                $scope.calldeleteConfirm = function(id) {
                    $('#field_group_id').val(id);
                }
                $scope.deleteFieldGroupConfirm = function() {
                    var id = $('#field_group_id').val();
                    $http.get(
                        field_group_delete_url + '/' + id,
                    ).then(function(response) {
                        if (response.data.success) {
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Field group deleted successfully',
                            }).show();

                            $('#field-group-table').DataTable().ajax.reload();
                        }
                    });
                }
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
        $form_data_url = typeof($routeParams.id) == 'undefined' ? field_group_get_form_data_url + '/' + $routeParams.category_id : field_group_get_form_data_url + '/' + $routeParams.category_id + '/' + $routeParams.id;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        self.category_id = $routeParams.category_id;

        $http.get(
            $form_data_url
        ).then(function(response) {
            if (!response.data.success) {
                new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: response.data.error,
                }).show();
                $location.path('/attribute-pkg/field-group/list' + '/' + $routeParams.category_id)
                $scope.$apply()
            }
            self.list_url = field_group_list_url + '/' + $routeParams.category_id;
            self.extras = response.data.extras;
            self.field_category = response.data.field_category;
            self.field_group = response.data.field_group;
            self.action = response.data.action;
            $rootScope.loading = false;
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });

        // FIELDS
        self.addNewFields = function() {
            self.field_group.fields.push({
                id: '',
                is_required_status: 'Yes',
            });
        }
        //REMOVE FIELD
        self.removeField = function(index) {
            self.field_group.fields.splice(index, 1);
        }

        var form_id = '#form';
        var v = jQuery(form_id).validate({
            invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'Kindly check in each tab to fix errors',
                    animation: {
                        speed: 500 // unavailable - no need
                    },
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 5000);
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
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: errors,
                                animation: {
                                    speed: 500 // unavailable - no need
                                },
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 5000);
                        } else {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: res.message,
                                animation: {
                                    speed: 500 // unavailable - no need
                                },
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 5000);
                            $location.path('/attribute-pkg/field-group/list' + '/' + $routeParams.category_id);
                            $scope.$apply()
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                            animation: {
                                speed: 500 // unavailable - no need
                            },
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 5000);
                    });
            },
        });
    }
});