app.component('fieldList', {
    templateUrl: field_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $element) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $filter_data_url = typeof($routeParams.category_id) == 'undefined' ? field_get_filter_data_url : field_get_filter_data_url + '/' + $routeParams.category_id;
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
                self.form_url = form_url + '/' + $routeParams.category_id;

                var dataTable = $('#field-table').dataTable({
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
                        url: laravel_routes['getFieldList'],
                        type: "GET",
                        dataType: "json",
                        data: function(d) {}
                    },

                    columns: [
                        { data: 'action', searchable: false, class: 'action' },
                        { data: 'field_name', name: 'fields.name', searchable: true },
                        { data: 'short_name', name: 'field_types.short_name', searchable: true },
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
                $("#search_field").keyup(function() { //alert(this.value);
                    dataTable.fnFilter(this.value);
                });

                $(".search_clear").on("click", function() {
                    $('#search_field').val('');
                    $('#field-table').DataTable().search('').draw();
                });

                $scope.calldeleteConfirm = function(id) {
                    $('#field_id').val(id);
                }
                $scope.deleteFieldConfirm = function() {
                    var id = $('#field_id').val();
                    $http.get(
                        field_delete_url + '/' + id,
                    ).then(function(response) {
                        if (response.data.success) {
                            new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: 'Field deleted successfully',
                            }).show();

                            $('#field-table').DataTable().ajax.reload();
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

app.component('fieldForm', {
    templateUrl: field_form_template_url,
    controller: function($http, $location, $location, HelperService, $routeParams, $rootScope, $scope) {
        $form_data_url = typeof($routeParams.id) == 'undefined' ? field_get_form_data_url + '/' + $routeParams.category_id : field_get_form_data_url + '/' + $routeParams.category_id + '/' + $routeParams.id;
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
                $location.path('/attribute-pkg/field/list' + '/' + $routeParams.category_id)
                $scope.$apply()
            }
            self.list_url = list_url + '/' + $routeParams.category_id;
            self.extras = response.data.extras;
            self.field_category = response.data.field_category;
            self.field = response.data.field;
            self.action = response.data.action;
            $rootScope.loading = false;
        });

        $.validator.addMethod("minLength", function(value, element) {
            var max_length = $('#max_length').val();
            var min_length = value;
            if (min_length > max_length) {
                return false;
            }
            return true;
        }, "Min length should be lesser than max length");

        $.validator.addMethod("maxLength", function(value, element) {
            var max_length = value;
            var min_length = $('#min_length').val();
            if (max_length < min_length) {
                return false;
            }
            return true;
        }, "Max length should be greater than min length");

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
                'min_length': {
                    required: true,
                    minLength: true,
                },
                'max_length': {
                    required: true,
                    maxLength: true,
                },
                'min_date': {
                    required: true,
                },
                'max_date': {
                    required: true,
                },
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
                            $location.path('/attribute-pkg/field/list' + '/' + $routeParams.category_id);
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