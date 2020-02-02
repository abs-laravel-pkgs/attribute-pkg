@if(config('attribute-pkg.DEV'))
    <?php $attribute_pkg_prefix = '/packages/abs/attribute-pkg/src';?>
@else
    <?php $attribute_pkg_prefix = '';?>
@endif

<!-- FIELD -->
<script type="text/javascript">
    //var field_get_filter_data_url = "{{route('getFieldFilterdata')}}";
    var field_list_template_url = "{{URL::asset($attribute_pkg_prefix.'/public/themes/'.$theme.'/attribute-pkg/field/list.html')}}";
    //var field_list_url = "{{url('#!/attribute-pkg/field/list')}}";
    var field_form_template_url = "{{URL::asset($attribute_pkg_prefix.'/public/themes/'.$theme.'/attribute-pkg/field/form.html')}}";
    // var field_get_form_data_url = "{{url('/attribute-pkg/field/get-form-data')}}";
    // var field_delete_url = "{{url('/attribute-pkg/field/delete')}}";
    // var field_form_url = "{{url('#!/attribute-pkg/field/add')}}";
</script>
<script type="text/javascript" src="{{URL::asset($attribute_pkg_prefix.'/public/themes/'.$theme.'/attribute-pkg/field/controller.js?v=2')}}"></script>

<script type="text/javascript">
    var field_group_get_filter_data_url = "{{route('getFieldGroupFilterdata')}}";
    var field_group_list_template_url = "{{URL::asset($attribute_pkg_prefix.'/public/angular/attribute-pkg/pages/field-group/list.html')}}";
    var field_group_list_url = "{{url('#!/attribute-pkg/field-group/list')}}";
    var field_group_form_template_url = "{{URL::asset($attribute_pkg_prefix.'/public/angular/attribute-pkg/pages/field-group/form.html')}}";
    var field_group_get_form_data_url = "{{url('/attribute-pkg/field-group/get-form-data')}}";
    var field_group_delete_url = "{{url('/attribute-pkg/field-group/delete')}}";
    var field_group_form_url = "{{url('#!/attribute-pkg/field-group/add')}}";
</script>
<script type="text/javascript" src="{{URL::asset($attribute_pkg_prefix.'/public/angular/attribute-pkg/pages/field-group/controller.js?v=2')}}"></script>