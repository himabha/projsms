<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "brandname".
 *
 * @property int $fsuid
 * @property string $foldername
 * @property string $email
 * @property string $password
 * @property string $cld1
 */
class Brandname extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'brandname';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['admin_id','foldername', 'brandname'], 'safe'],
            //[['cld1'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin',
            'foldername' => 'Folder Name',
            'brandname' => 'Brand Name'
        ];
    }