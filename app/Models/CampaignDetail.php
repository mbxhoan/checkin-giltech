<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CampaignDetail
 *
 * @property int $id
 * @property int $campaign_id
 * @property int|null $tag_id
 * @property string $name
 * @property string $qrcode
 * @property string|null $gender
 * @property string|null $email
 * @property string|null $phone
 * @property bool $email_sent
 * @property bool $zalo_sent
 * @property bool $sms_sent
 * @property string|null $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Campaign $campaign
 * @property Tag|null $tag
 *
 * @package App\Models
 */
class CampaignDetail extends BaseModel
{
	const GENDER_MR 		= 'MAN';
	const GENDER_MS 		= 'WOMAN';
	const GENDER_MALE 		= 'MALE';
	const GENDER_FEMALE 	= 'FEMALE';

	const GENDERS = [
        self::GENDER_MR 		=> 'Ông',
        self::GENDER_MS 		=> 'Bà',
        self::GENDER_MALE 		=> 'Anh',
        self::GENDER_FEMALE 	=> 'Chị',
    ];

	protected $table = 'campaign_details';

	protected $casts = [
		'campaign_id' => 'int',
		'tag_id' => 'int',
		'send_email' => 'bool',
		'send_zalo' => 'bool',
		'send_sms' => 'bool'
	];

	protected $fillable = [
		'campaign_id',
		'tag_id',
		'name',
		'qrcode',
		'img_qrcode',
		'document_pdf',
		'gender',
		'email',
		'email_form',
		'phone',
		'custom_fields',
		'send_email',
		'send_zalo',
		'send_sms',
		'status'
	];

	public function campaign()
	{
		return $this->belongsTo(Campaign::class);
	}

	static public function getGenders()
    {
        return self::GENDERS;
    }

	public function getCampaignDetails($campaign_id = null, $status = null)
    {
        $models = self::select('*');
		$models = $models->where('status', '!=', $this::STATUS_DELETED);

        if (!empty($campaign_id)) {
            $models = $models->where('campaign_id', '=', $campaign_id);
        }

        if(!empty($status)) {
            if(is_array($status)){
                $models = $models->whereIn('status', $status);
            } else {
                $models = $models->where([
                    'status' => $status
                ]);
            }
        }

        return $models->get();
    }
}
