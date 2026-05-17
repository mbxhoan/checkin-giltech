<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Class History
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $object
 * @property string|null $function
 * @property string|null $method
 * @property array|null $parameters
 * @property string|null $error
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class History extends Model
{
	protected $table = 'historys';

    const FUNCTION_INDEX    = 'index';
    const FUNCTION_DETAIL   = 'edit';
    const FUNCTION_CREATE   = 'create';
    const FUNCTION_SHOW     = 'show';
    const FUNCTION_UPDATE   = 'update';
    const FUNCTION_STORE    = 'store';
    const FUNCTION_ATTACH   = 'attach';
    const FUNCTION_EAN      = 'ean';
    const FUNCTION_LOGIN    = 'login';
    const FUNCTION_LOGOUT   = 'logout';

    const FUNCTION_TEXT = [
        self::FUNCTION_INDEX    => 'Xem danh sách',
        self::FUNCTION_DETAIL   => 'Chỉnh sửa',
        self::FUNCTION_SHOW     => 'Xem',
        self::FUNCTION_CREATE   => 'Khởi tạo',
        self::FUNCTION_UPDATE   => 'Cập nhật',
        self::FUNCTION_STORE    => 'Tạo mới',
        self::FUNCTION_LOGIN    => 'Đăng nhập',
        self::FUNCTION_LOGOUT   => 'Đăng xuất',
    ];

	protected $casts = [
		'user_id'       => 'int',
		'parameters'    => 'json'
	];

	protected $fillable = [
		'user_id',
		'action',
		'object',
		'function',
		'method',
		'parameters',
		'error'
	];

    public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

    static public function getFunctions()
    {
        return self::FUNCTION_TEXT;
    }

    public function getFunctionText()
    {
        return self::FUNCTION_TEXT[$this->function] ?? null;
    }

    /* Get all table names in the database */

    static public function getAllObjects() {
        $tables = [];
        $customs = [];
        $auths = [
            'login',
            'logout',
            'register',
            'reset-password',
            'change-password',
            'verify-email'
        ];

        $excepts = [
            'historys'
        ];

        $rawTables = DB::select('SHOW TABLES');

        foreach ($rawTables as $table) {
            $key = "Tables_in_".env("DB_DATABASE");
            $tableName = $table->$key;
            if (!in_array($tableName, $excepts)) {
                // $tableName = self::singularize($tableName);
                $tableName = $tableName;
                $tables[] = $tableName;
            }
        }

        return [
            'auth'   => $auths,
            'tables' => array_merge($tables, $auths)
        ];
    }

    /* static public function getTicketTables()
    {
        return [
            'batch',
            'goods_delivery',
            'goods_receive',
            'goods_return',
            'inventory',
            'move',
            'purchase_order',
            'sale_order',
            'transfer',
        ];
    } */

    /* public function convertQueryToText()
    {
        $parameters = $this->parameters;

        if (empty($parameters)) return null;

        $parameters = $parameters['data'] ?? $parameters;

        if ($this->method == "POST") {
            $ref = ($parameters['doc_entry'] ?? $parameters['code']) ?? $parameters['name'];
        } elseif ($this->method == "PATCH"){
            $ref = ($parameters['name'] ?? $parameters['doc_entry']) ?? $parameters['id'];
        }
    } */

    static function singularize($str)
    {
        return Str::singular($str);
    }
}
