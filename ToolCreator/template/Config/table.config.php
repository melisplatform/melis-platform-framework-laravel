<?php

return [
    'table' => [
        'attributes' => [
            'id' => 'tbl-moduletpl',
            'class' => 'table table-striped table-primary dt-responsive nowrap',
            'cellspacing' => '0',
            'width' => '100%',
        ],
        'ajaxUrl' => '/melis/moduletpl/get-list',
        'dataFunction' => '',
        'ajaxCallback' => '',
        'filters' => [
            'left' => [
                'show' => "l",
            ],
            'center' => [
                'search' => "f"
            ],
            'right' => [
                'refresh' => '<div class="moduletpl-table-refresh"><a class="btn btn-default moduletpl-refresh-content" data-toggle="tab" aria-expanded="true" title="'. __('moduletpl::messages.common_refresh') .'"><i class="fa fa-refresh"></i></a></div>'
            ],
        ],
        'columns' => [
#TABLECOLUMNS
        ],
        'searchables' => [
#TABLESEARCHBLECOLUMNS
        ],
        'actionButtons' => [
            'edit' => '<button class="btn btn-success moduletpl-add-update-action" title="'. __('moduletpl::messages.common_edit') .'"><i class="fa fa-pencil"></i></button>',
            'delete' => '<button class="btn btn-danger moduletpl-delete-action" title="'. __('moduletpl::messages.common_delete') .'"><i class="fa fa-times"></i></button>'
        ],
    ],
];