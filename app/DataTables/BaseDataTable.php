<?php

namespace App\DataTables;

use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class BaseDataTable extends DataTable
{
    public function __construct()
    {

    }

    public function getL()
    {
        return [
            'searchPlaceholder'     => "Tìm kiếm",
            'search'                => '',
            "decimal"               => "",
            "emptyTable"            => "Không có dữ liệu trong bảng",
            "info"                  => "Hiển thị _START_ đến _END_ trong tổng số _TOTAL_ mục",
            "infoEmpty"             => "Hiển thị 0 đến 0 trong tổng số 0 mục",
            "infoFiltered"          => "(được lọc từ tổng số _MAX_ mục)",
            "infoPostFix"           => "",
            "thousands"             => ",",
            "lengthMenu"            => "Hiển thị _MENU_ mục",
            "loadingRecords"        => "Đang tải...",
            "processing"            => "Đang xử lý...",
            "zeroRecords"           => "Không tìm thấy bản ghi phù hợp",
            // "paginate"              => [
            //     "first"             => "Đầu tiên",
            //     "last"              => "Cuối cùng",
            //     "next"              => "Tiếp",
            //     "previous"          => "Trước"
            // ],
            "aria"                  => [
                "orderable"         => "Sắp xếp cột này",
                "orderableReverse"  => "Sắp xếp ngược cột này"
            ]
        ];
    }
}
